<?php

function getAdvertisementBanners($iUserId, $eMemberType) {
    global $obj, $ADVERTISEMENT_TYPE, $tconfig, $SHOW_ADVERTISE_AFTER_MINUTES,$userDetailsArr;
    $todayDate = date("Y-m-d");
    $getBanners = $obj->MySQLSelect("SELECT iAdvertBannerId,tRedirectUrl,eValidityType,eImpression,dStartDate,dExpiryDate,iImpression,vBannerImage FROM advertise_banners WHERE eStatus='Active' ORDER BY iDispOrder ASC");
    $finalBannerArr = $idArr = array();
    if ($iUserId > 0) {
        $getUserCount = $obj->MySQLSelect("SELECT count(iBannerImpLog) as TOTAL FROM banner_impression WHERE iUserId='" . $iUserId . "' AND eUserType='" . $eMemberType . "'");
        $usedCount = 0;
        if (count($getUserCount) > 0) {
            $usedCount = $getUserCount[0]['TOTAL'];
        }
        for ($r = 0; $r < count($getBanners); $r++) {
            $validity = $getBanners[$r]['eValidityType'];
            $impression = $getBanners[$r]['eImpression'];
            $dStartDate = $getBanners[$r]['dStartDate'];
            $dExpiryDate = $getBanners[$r]['dExpiryDate'];
            $iImpression = $getBanners[$r]['iImpression'];
            $iAdvertBannerId = $getBanners[$r]['iAdvertBannerId'];
            $path = $tconfig['tsite_upload_advertise_banner'] . '/' . $getBanners[$r]['vBannerImage'];
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            $getBanners[$r]['dataImg'] = $base64;
            if ($validity == "Custom" && $dStartDate <= $todayDate && $dExpiryDate >= $todayDate) { //$dStartDate >= $todayDate now chnage to this <= by mrunalbhai 
                if ($impression == "Unlimited") {
                    $finalBannerArr[$iAdvertBannerId] = $getBanners[$r];
                    $idArr[] = $iAdvertBannerId;
                } else if ($iImpression > $usedCount) {
                    $finalBannerArr[$iAdvertBannerId] = $getBanners[$r];
                    $idArr[] = $iAdvertBannerId;
                }
            } else if ($validity == "Permanent") {
                if ($impression == "Unlimited") {
                    $finalBannerArr[$iAdvertBannerId] = $getBanners[$r];
                    $idArr[] = $iAdvertBannerId;
                } else if ($iImpression > $usedCount) {
                    $finalBannerArr[$iAdvertBannerId] = $getBanners[$r];
                    $idArr[] = $iAdvertBannerId;
                }
            }
        }
    }
    //print_r($finalBannerArr);die;
    if ($eMemberType == "Passenger") {
        $tableName = "register_user";
        $fieldName = "iUserId";
    } else if ($eMemberType == "Driver") {
        $tableName = "register_driver";
        $fieldName = "iDriverId";
    } else {
        $tableName = "company";
        $fieldName = "iCompanyId";
    }
    //Added By HJ On 09-06-2020 For Optimization Start
    if(isset($userDetailsArr[$tableName."_".$iUserId]) && count($userDetailsArr[$tableName."_".$iUserId]) > 0){
        $getLastBanner = $userDetailsArr[$tableName."_".$iUserId];
    }else{
        $getLastBanner = $obj->MySQLSelect("SELECT iAdvertBannerId,tSeenAdvertiseTime FROM " . $tableName . " WHERE " . $fieldName . "='" . $iUserId . "'");
    }
    //Added By HJ On 09-06-2020 For Optimization End
    $lastBannerId = $newAdvertBannerId = 0;
    if (count($getLastBanner) > 0) {
        $lastBannerId = $getLastBanner[0]['iAdvertBannerId'];
        $tSeenAdvertiseTime = $getLastBanner[0]['tSeenAdvertiseTime'];
    }
    $dateTime = date("Y-m-d H:i:s");
    //echo "<pre>";
    $minutes = 1;
    if ($tSeenAdvertiseTime != "0000-00-00 00:00:00") {
        $minutes = round(abs($dateTime - $tSeenAdvertiseTime) / 60, 2);
    }
    $start_date = new DateTime($tSeenAdvertiseTime);
    $since_start = $start_date->diff(new DateTime($dateTime));
    $minutes = $since_start->i;

    if ($minutes >= $SHOW_ADVERTISE_AFTER_MINUTES) {
        if ($ADVERTISEMENT_TYPE == "Sequential") {
            if (count($getLastBanner) > 0) {
                $lastBannerId = $getLastBanner[0]['iAdvertBannerId'];
            }
            $seqData = $update_banner = array();
            foreach ($finalBannerArr as $key => $val) {
                if ($key > $lastBannerId) {
                    $seqData = $finalBannerArr[$key];
                    $newAdvertBannerId = $key;
                    break;
                }
            }
            if ($newAdvertBannerId == 0) {
                $newKeysArr = array_keys($finalBannerArr);
                if (count($newKeysArr) > 0) {
                    $newAdvertBannerId = $newKeysArr[0];
                    $seqData = $finalBannerArr[$newAdvertBannerId];
                }
            }
            return $seqData;
            //Sequential Banner Code here
        } else if ($ADVERTISEMENT_TYPE == "Random") { //Random Banner here
            $randKey = array_rand($idArr);
            $randValue = $idArr[$randKey];
            return $finalBannerArr[$randValue];
        } else {
            $finalBannerArr = "";
            return $finalBannerArr;
        }
    } else {
        $finalBannerArr = "";
        return $finalBannerArr;
    }
}

function insertBannereImpressionCount($iAdvertBannerId, $iUserId, $eMemberType) {
    global $obj, $tconfig, $_REQUEST, $generalobj;

    $dateTime = date("Y-m-d H:i:s");
    if ($iAdvertBannerId > 0 && $iUserId > 0) {
        $insert_banner = array();
        $insert_banner['iAdvertBannerId'] = $iAdvertBannerId;
        $insert_banner['dDateTime'] = $dateTime;
        if ($eMemberType == "Company") {
			$eMemberType='Store';
		 }
        $insert_banner['eUserType'] = $eMemberType;
        $insert_banner['vIP'] = $generalobj->get_client_ip();
        $insert_banner['iUserId'] = $iUserId;
        $id = $obj->MySQLQueryPerform("banner_impression", $insert_banner, 'insert');
        $returnArr['Action'] = "1";
        $getBanners = $obj->MySQLSelect("SELECT vBannerImage FROM advertise_banners WHERE iAdvertBannerId='" . $iAdvertBannerId . "'");
        $base64 = $path = "";
        if (count($getBanners) > 0) {
            $path = $tconfig['tsite_upload_advertise_banner'] . '/' . $getBanners[0]['vBannerImage'];
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
        if ($eMemberType == "Passenger") {
            $tableName = "register_user";
            $fieldName = "iUserId";
        } else if ($eMemberType == "Driver") {
            $tableName = "register_driver";
            $fieldName = "iDriverId";
        } else {
            $tableName = "company";
            $fieldName = "iCompanyId";
        }
        $whereUserId = " $fieldName = '" . $iUserId . "'";
        $update_banner['iAdvertBannerId'] = $iAdvertBannerId;
        $update_banner['tSeenAdvertiseTime'] = $dateTime;
        $obj->MySQLQueryPerform($tableName, $update_banner, 'update', $whereUserId);
        //header("Location:".$path);die;
        $path = $tconfig['tsite_url'] . "resizeImg.php?src=" . $path . "&w=" . $_REQUEST['imgWidth'] . "&h=" . $_REQUEST['imgHeight'] . "&IMG_MAX_HEIGHT=" . $_REQUEST['IMG_MAX_HEIGHT'];
        ob_start();
        header("Location:" . $path);
        die;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_DATA_AVAIL";
    }
    return $returnArr;
}

function getAdvertisementBannersAsPerDevice($iMemberId, $UserType) {
    global $generalobj, $obj, $demo_site_msg, $tconfig, $_REQUEST, $ENABLE_RIDER_ADVERTISEMENT_BANNER, $ENABLE_DRIVER_ADVERTISEMENT_BANNER, $ENABLE_RESTAURANTS_ADVERTISEMENT_BANNER, $ADVERTISEMENT_TYPE;
    $advertise_banner_data = "";
    if ($UserType == "Passenger" && $ENABLE_RIDER_ADVERTISEMENT_BANNER == "No") {
        $ADVERTISEMENT_TYPE = "Disable";
    } else if ($UserType == "Driver" && $ENABLE_DRIVER_ADVERTISEMENT_BANNER == "No") {
        $ADVERTISEMENT_TYPE = "Disable";
    } else if ($UserType == "Company" && $ENABLE_RESTAURANTS_ADVERTISEMENT_BANNER == "No") {
        $ADVERTISEMENT_TYPE = "Disable";
    }

    if ($ADVERTISEMENT_TYPE != "Disable") {
        $adBannerData = getAdvertisementBanners($iMemberId, $UserType);
        $bannerData = array();

        if (isset($adBannerData['iAdvertBannerId']) && $adBannerData['iAdvertBannerId'] > 0) {
            $iAdvertBannerId = $adBannerData['iAdvertBannerId'];
            $pathOfAdvImage = $tconfig['tsite_upload_advertise_banner'] . '/' . $adBannerData['vBannerImage'];
            $pathOfAdvImage_chk = $tconfig['tsite_upload_advertise_banner_path'] . '/' . $adBannerData['vBannerImage'];

            if (file_exists($pathOfAdvImage_chk)) {
                $imagedata = getimagesize($pathOfAdvImage);
                $bannerData['vImageWidth'] = strval($imagedata[0]);
                $bannerData['vImageHeight'] = strval($imagedata[1]);
                $bannerData['tRedirectUrl'] = $adBannerData['tRedirectUrl'];

                $sysTime = round(microtime(true) * 1000);

                $bannerData['image_url'] = $tconfig['tsite_url'] . "webservice_shark.php?type=insertBannereImpressionCount&iAdvertBannerId=" . $iAdvertBannerId . "&iUserId=" . $iMemberId . "&UserType=" . $UserType . "&sysTime=" . $sysTime;


                $imgFileInfo = new SplFileInfo($pathOfAdvImage);
                if (!empty($imgFileInfo->getExtension()) && ($imgFileInfo->getExtension() == "gif" || $imgFileInfo->getExtension() == "GIF")) {
                    $bannerData['IS_GIF_IMAGE'] = "Yes";
                } else {
                    $bannerData['IS_GIF_IMAGE'] = "No";
                }

                if (isset($_REQUEST['deviceWidth']) && $_REQUEST['deviceWidth'] != "") {
                    $imageRatio = $imagedata[0] / $imagedata[1];

                    $paramWidth = $imagedata[0] > $_REQUEST['deviceWidth'] ? $_REQUEST['deviceWidth'] : $imagedata[0];
                    if ($paramWidth < 80) {
                        $paramWidth = 80;
                    }

                    $paramHeight = ($paramWidth / $imageRatio);

                    if ($paramHeight > $_REQUEST['deviceHeight']) {
                        $paramHeight = $_REQUEST['deviceHeight'];
                        $paramWidth = $paramHeight * $imageRatio;
                    }

                    if ($paramHeight < 80) {
                        $paramHeight = 80;
                    }

                    $bannerData['vImageWidth'] = strval(round($paramWidth));
                    $bannerData['vImageHeight'] = strval(round($paramHeight));
                    $bannerData['image_url'] = $bannerData['image_url'] . "&imgWidth=" . $bannerData['vImageWidth'] . "&imgHeight=" . $bannerData['vImageHeight'] . "&IMG_MAX_HEIGHT=" . $_REQUEST['deviceHeight'] . "&deviceWidth=" . $_REQUEST['deviceWidth'] . "&deviceHeight=" . $_REQUEST['deviceHeight'];

                    $advertise_banner_data = json_encode($bannerData);
                } else {
                    $bannerData['image_url'] = $bannerData['image_url'] . "&IMG_MAX_HEIGHT=" . $_REQUEST['deviceHeight'] . "&IMG_MAX_WIDTH=" . $_REQUEST['deviceWidth'];
                }
            }
        }
    }

    return $advertise_banner_data;
}

function getNewsNotification($page, $iMemberId, $eMemberType, $eType) {
    global $obj, $tconfig;
    $todayDate = date("Y-m-d") . " 00:00:00";

    $returnArr['Action'] = "0";
    $returnArr['message'] = "LBL_NO_DATA_AVAIL";
    $returnArr['NextPage'] = "0";
    if ($eMemberType == "Passenger") {
        $tableName = "register_user";
        $fieldName = "iUserId";
    } else if ($eMemberType == "Driver") {
        $tableName = "register_driver";
        $fieldName = "iDriverId";
    } else {
        $tableName = "company";
        $fieldName = "iCompanyId";
    }
    $getUserLang = $obj->MySQLSelect("SELECT vLang FROM " . $tableName . " WHERE $fieldName='" . $iMemberId . "'");
    $langCode = "EN";
    if (count($getUserLang) > 0) {
        $langCode = $getUserLang[0]['vLang'];
    }
    $sql = "SELECT  `vCode` FROM  `language_master` WHERE eStatus = 'Active' AND `eDefault` = 'Yes' ";
    $default_label = $obj->MySQLSelect($sql);
    $defaultlangCode = (isset($default_label[0]['vCode']) && $default_label[0]['vCode']) ? $default_label[0]['vCode'] : 'EN';
    if ($eMemberType == "Passenger") {
        $eMemberType = "rider";
    } else if ($eMemberType == "Company") {
        $eMemberType = "company";
    } else if ($eMemberType == "Driver") {
        $eMemberType = "driver";
    }
    // for all type news 
    $eMemberTypeall = "all";
    $getNewData = $getNotifcationData = $notificationarr = array();

    if ($eType == "News" || $eType == "All") {
        $per_page = 10;
        $start_limit = 0;
        if ($eType == "News") {
            $sql_all = "SELECT COUNT(iNewsfeedId) As TotalIds FROM newsfeed WHERE (eUserType='" . $eMemberType . "' OR eUserType='" . $eMemberTypeall . "') AND eStatus='Active' AND vTitle!='' AND eType='News' ORDER BY tPublishdate DESC";
            $data_count_all = $obj->MySQLSelect($sql_all);
            $TotalPages = ceil($data_count_all[0]['TotalIds'] / $per_page);
            $start_limit = ($page - 1) * $per_page;
            $limit = " LIMIT " . $start_limit . ", " . $per_page;
            //print_r($data_count_all);die;
            if ($TotalPages > $page) {
                $returnArr['NextPage'] = "" . ($page + 1);
            }
        } else {
            $limit = " LIMIT " . $start_limit . ", " . $per_page;
        }
        $getNewData = $obj->MySQLSelect("SELECT vTitle,tDescription,tPublishdate,vNewfeedImage AS vImage FROM newsfeed WHERE (eUserType='" . $eMemberType . "' OR eUserType='" . $eMemberTypeall . "') AND eStatus='Active' AND vTitle!='' AND eType='News' ORDER BY tPublishdate DESC");

        for ($r = 0; $r < count($getNewData); $r++) {
            $title = (array) json_decode($getNewData[$r]['vTitle']);
            $description = (array) json_decode($getNewData[$r]['tDescription']);
            if (isset($title['vTitle_' . $langCode]) && $title['vTitle_' . $langCode] != "") {
                $getNewData[$r]['vTitle'] = $title['vTitle_' . $langCode];
            } else {
                $getNewData[$r]['vTitle'] = $title['vTitle_' . $defaultlangCode];
            }
            if (isset($description['tDescription_' . $langCode]) && $description['tDescription_' . $langCode] != "") {
                $getNewData[$r]['tDescription'] = $description['tDescription_' . $langCode];
            } else {
                $getNewData[$r]['tDescription'] = $description['tDescription_' . $defaultlangCode];
            }
            $getNewData[$r]['dDateTime'] = $getNewData[$r]['tPublishdate'];
            $path = $tconfig['tsite_upload_images_news_feed'] . '/' . $getNewData[$r]['vImage'];
            $getNewData[$r]['vImage'] = $path;
            $getNewData[$r]['eType'] = "News";
            unset($getNewData[$r]['tPublishdate']);
        }
        if (count($getNewData) > 0) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = $getNewData;
        }
    }

    if ($eType == "Notification" || $eType == "All") {
        $per_page = 10;
        $start_limit = 0;
        if ($eType == "Notification") {
            $sql_all = "SELECT COUNT(iPushnotificationId) As TotalIds FROM pushnotification_log WHERE eUserType='" . $eMemberType . "' AND iUserId='" . $iMemberId . "' AND tMessage != '' ORDER BY iPushnotificationId DESC";
            $data_count_all = $obj->MySQLSelect($sql_all);
            $TotalPages = ceil($data_count_all[0]['TotalIds'] / $per_page);
            $start_limit = ($page - 1) * $per_page;
            $limit = " LIMIT " . $start_limit . ", " . $per_page;
            if ($TotalPages > $page) {
                $returnArr['NextPage'] = "" . ($page + 1);
            }
        } else {
            $limit = " LIMIT " . $start_limit . ", " . $per_page;
        }
        $getNotifcationData = $obj->MySQLSelect("SELECT iPushnotificationId,tMessage,dDateTime FROM pushnotification_log WHERE eUserType='" . $eMemberType . "' AND iUserId='" . $iMemberId . "' AND tMessage != '' ORDER BY iPushnotificationId DESC $limit");
        //echo "SELECT iPushnotificationId,tMessage,dDateTime FROM pushnotification_log WHERE eUserType='" . $eMemberType . "' AND iUserId='" . $iMemberId . "' AND tMessage != '' ORDER BY iPushnotificationId DESC $limit";
//print_R($getNotifcationData); exit;
        for ($n = 0; $n < count($getNotifcationData); $n++) {
            $notificationarr[$n]['vTitle'] = $notificationarr[$n]['vImage'] = "";
            $msg = $getNotifcationData[$n]['tMessage'];
            $msg = str_replace("\\r\\n","\r\n",$msg);
            $notificationarr[$n]['tDescription'] = $msg;
            $notificationarr[$n]['dDateTime'] = $getNotifcationData[$n]['dDateTime'];
            $notificationarr[$n]['eType'] = "Notification";
        }
        if (count($notificationarr) > 0) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = $notificationarr;
        }
    }
    if (count($getNewData) > 0 && count($notificationarr) > 0) {
        $newArr = array_merge($getNewData, $notificationarr);
        array_multisort(array_column($newArr, 'dDateTime'), SORT_DESC, $newArr);
        if (count($newArr) > 0) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = $newArr;
        }
    }
    return $returnArr;
}

########## Tracking function  ##############

function getTrackingUrl($iTripId) {
    global $obj, $tconfig;
    $triplink = $tconfig["tsite_url"] . "trip_tracking.php?iTripId=" . base64_encode(base64_encode($iTripId));
    $liveTrackingUrl = get_tiny_url($triplink);
    return $liveTrackingUrl;
}

##############################################################

function getBlockData($eUserType, $iUserId) {
    global $obj, $tconfig,$userDetailsArr;
    $returnArr = "";
    if ($eUserType == "Driver") {
        //Added By HJ On 18-06-2020 For Optimization register_driver Table Query Start
        if(isset($userDetailsArr["register_driver_".$iUserId])){
            $Data_Driver = $userDetailsArr["register_driver_".$iUserId];
        }else{
            $Data_Driver = $obj->MySQLSelect("SELECT * FROM register_driver WHERE iDriverId='".$iUserId."' ");
            $userDetailsArr["register_driver_".$iUserId] = $Data_Driver;
        }
        //Added By HJ On 18-06-2020 For Optimization register_driver Table Query End
        $eIsBlocked = $Data_Driver[0]['eIsBlocked'];
        if ($eIsBlocked == 'Yes') {
            $returnArr = array();
            $returnArr['Action'] = "0";
            $returnArr['isShowContactUs'] = "Yes";
            $returnArr['message'] = "LBL_DRIVER_BLOCK";
        }
    } else {
        //Added By HJ On 18-06-2020 For Optimization register_user Table Query Start
        if(isset($userDetailsArr["register_user_".$iUserId])){
            $Data_Rider = $userDetailsArr["register_user_".$iUserId];
        }else{
           $Data_Rider = $obj->MySQLSelect("SELECT * FROM register_user WHERE iUserId='".$iUserId."'");
           $userDetailsArr["register_user_".$iUserId] = $Data_Rider;
        }
        //Added By HJ On 18-06-2020 For Optimization register_user Table Query End
        $eIsBlocked = $Data_Rider[0]['eIsBlocked'];
        if ($eIsBlocked == 'Yes') {
            $returnArr = array();
            $returnArr['Action'] = "0";
            $returnArr['isShowContactUs'] = "Yes";
            $returnArr['message'] = "LBL_RIDER_BLOCK";
        }
    }
    return $returnArr;
}

function CheckSurgeAirportFromGeoLocation($pickupAddress_Array, $DropoffAddress_Array = '', $selectedCarTypeID) {
    global $generalobj, $obj,$locationDataArr;
    //Added By HJ On 23-06-2020 For Optimize location_master Table Query Start
    if(isset($locationDataArr['location_master'])){
        $locationData = $locationDataArr['location_master'];
    }else{
        $locationData = $obj->MySQLSelect("SELECT * FROM location_master");
        $locationDataArr['location_master'] = $locationData;
    }
    $locationArr =$allowed_data= array();
    for($g=0;$g<count($locationData);$g++){
        if(strtoupper($locationData[$g]['eStatus']) == "ACTIVE" && strtoupper($locationData[$g]['eFor']) == "AIRPORTSURCHARGE"){
            $allowed_data[] = $locationData[$g];
        }
    }
    //Added By HJ On 23-06-2020 For Optimize location_master Table Query End
    //$sqlaa = "SELECT * FROM location_master WHERE eStatus='Active' AND eFor = 'AirportSurcharge'";
    //$allowed_data = $obj->MySQLSelect($sqlaa);
    $returnArr = array();
    $returnArr['fpickupsurchargefare'] = $returnArr['fdropoffsurchargefare'] = 0;
    //$DropoffAddress_Array[0] = "23.0734"; // For Check In Polygon
    //$DropoffAddress_Array[1] = "72.6266"; // For Check In Polygon
    if (!empty($allowed_data)) {
        $polygon = $locationArr = array();
        //print_r($allowed_data);die;
        //Added By HJ On 17-07-2019 For Optimize Code Start
        $AirportData = $obj->MySQLSelect("SELECT fpickupsurchargefare,fdropoffsurchargefare,iLocatioId,iVehicleTypeId,iLocationIds FROM airportsurcharge_fare WHERE eStatus='Active' AND iVehicleTypeId='" . $selectedCarTypeID . "'");
        for ($d = 0; $d < count($AirportData); $d++) {
            $locationArr[$AirportData[$d]['iLocationIds']] = $AirportData[$d];
        }
        //Added By HJ On 17-07-2019 For Optimize Code End
        //echo "<pre>";print_r($selectedCarTypeID);die;
        foreach ($allowed_data as $key => $val) {
            $latitude = explode(",", $val['tLatitude']);
            $longitude = explode(",", $val['tLongitude']);
            for ($x = 0; $x < count($latitude); $x++) {
                if (!empty($latitude[$x]) || !empty($longitude[$x])) {
                    $polygon[$key][] = array($latitude[$x], $longitude[$x]);
                }
            }
            //print_r($pickupAddress_Array);die;
            if ($polygon[$key]) {
                if (!empty($pickupAddress_Array)) {
                    $address = contains($pickupAddress_Array, $polygon[$key]) ? 'IN' : 'OUT';
                    if ($val['iLocationId'] != '' && $address == 'IN') {
                        //Commented By HJ On 17-07-2019 For Optimize Code Start
                        //$sqla = "SELECT fpickupsurchargefare,iLocatioId,iVehicleTypeId FROM airportsurcharge_fare WHERE eStatus='Active' AND iLocationIds = '" . $val['iLocationId'] . "' AND iVehicleTypeId='" . $selectedCarTypeID . "'";
                        //$AirportData = $obj->MySQLSelect($sqla);
                        //$returnArr['fpickupsurchargefare'] = $AirportData[0]['fpickupsurchargefare'];
                        //Commented By HJ On 17-07-2019 For Optimize Code End
                        $fpickupsurchargefare = 0;
                        if (isset($locationArr[$val['iLocationId']])) {
                            $fpickupsurchargefare = $locationArr[$val['iLocationId']]['fpickupsurchargefare'];
                        }
                        $returnArr['fpickupsurchargefare'] = $fpickupsurchargefare;
                    }
                }
                if (!empty($DropoffAddress_Array)) {
                    $addressdata = contains($DropoffAddress_Array, $polygon[$key]) ? 'IN' : 'OUT';
                    //$checkAdd = search_in_array($polygon[$key],$DropoffAddress_Array);
                    //print_r($addressdata);die;
                    //print_r($DropoffAddress_Array);echo '<br>';print_r($polygon[$key]);echo '<br>';echo $addressdata;die;
                    //$addressdata = "IN";
                    if ($val['iLocationId'] != '' && $addressdata == 'IN') {
                        //Commented By HJ On 17-07-2019 For Optimize Code Start
                        //$sqla = "SELECT fdropoffsurchargefare,iLocatioId,iVehicleTypeId FROM airportsurcharge_fare WHERE eStatus='Active' AND iLocationIds = '" . $val['iLocationId'] . "' AND iVehicleTypeId='" . $selectedCarTypeID . "'";
                        //$AirportDataDropOff = $obj->MySQLSelect($sqla);
                        //$returnArr['fdropoffsurchargefare'] = $AirportDataDropOff[0]['fdropoffsurchargefare'];
                        //Commented By HJ On 17-07-2019 For Optimize Code End
                        $fdropoffsurchargefare = 0;
                        if (isset($locationArr[$val['iLocationId']])) {
                            $fdropoffsurchargefare = $locationArr[$val['iLocationId']]['fdropoffsurchargefare'];
                        }
                        //echo $fdropoffsurchargefare;die;
                        $returnArr['fdropoffsurchargefare'] = $fdropoffsurchargefare;
                    }
                }
            }
        }
    }
    return $returnArr;
}

function search_in_array($value, $array) {
    if (in_array($value, $array)) {
        return true;
    }
    foreach ($array as $item) {
        if (is_array($item) && search_in_array($value, $item))
            return true;
    }
    return false;
}

//update insurance log
function update_driver_insurance_status($iDriverId, $eFor, $details_arr = array(), $eFromType = "", $vStatus = "", $allow_blank_latlong = "No") {
    global $generalobj, $obj, $ENABLE_INSURANCE_IDLE_REPORT, $ENABLE_INSURANCE_ACCEPT_REPORT, $ENABLE_INSURANCE_TRIP_REPORT;

    $datetime = date("Y-m-d H:i:s");
    $date = date("Y-m-d");

    $iTripId = $details_arr['iTripId'];

    // $Allow_Insert_record = "No";
    $ssql = "";
    if ($eFromType == "updateDriverStatus") { //online,offline
        $ssql .= "AND eAddedFor = '" . $eFor . "'";

        $Allow_Insert_record = $ENABLE_INSURANCE_IDLE_REPORT;
    } else if ($eFromType == "GenerateTrip") {
        $ssql .= "AND eAddedFor = 'Available'"; //to update end time in Available and add new record for Accept

        $Allow_Insert_record = $ENABLE_INSURANCE_ACCEPT_REPORT;
    } else if ($eFromType == "StartTrip" || $eFromType == "cancelTrip") {
        $ssql .= "AND eAddedFor = 'Accept'";
        if ($iTripId != "" && $iTripId > 0) {
            $ssql .= " AND iTripId = '$iTripId'";
        }
        $Allow_Insert_record = $ENABLE_INSURANCE_TRIP_REPORT;
    } else if ($eFromType == "ProcessEndTrip") {
        $ssql .= "AND eAddedFor = 'Trip'";
        if ($iTripId != "" && $iTripId > 0) {
            $ssql .= "AND iTripId = '$iTripId'";
        }
        $Allow_Insert_record = $ENABLE_INSURANCE_TRIP_REPORT;
    }

    $sqlaa = "SELECT * FROM driver_insurance_report WHERE iDriverId='" . $iDriverId . "' and date(dStartDate)='" . $date . "' $ssql order by iInsuranceReportId DESC limit 0,1";
    $data_insurance = $obj->MySQLSelect($sqlaa);

    $action = "Add";
    // if(!empty($data_insurance) && $data_insurance[0]['iInsuranceReportId'] > 0 && (($eFromType == "updateDriverStatus" && $vStatus == "Offline") ||  $eFromType == "ProcessEndTrip" || $eFromType == "cancelTrip")){
    if (($eFromType == "updateDriverStatus" && $vStatus == "Offline") || $eFromType == "ProcessEndTrip" || $eFromType == "cancelTrip") {
        $action = "Edit";
    }

    if ($action == "Add") {
        if ($Allow_Insert_record == "Yes") {
            $Data_update['iDriverId'] = $iDriverId;
            $Data_update['iTripId'] = $details_arr['iTripId'];
            $Data_update['dStartDate'] = $datetime;
            $Data_update['eAddedFor'] = $eFor;

            if (isset($details_arr['LatLngArr']['vLatitude']) && $details_arr['LatLngArr']['vLatitude'] != "") {
                $Data_update['tStartLat'] = $details_arr['LatLngArr']['vLatitude'];
                $Data_update['tStartLong'] = $details_arr['LatLngArr']['vLongitude'];
                $Data_update['tStartLocation'] = $details_arr['LatLngArr']['vLocation'];
            }

            $id = $obj->MySQLQueryPerform("driver_insurance_report", $Data_update, 'insert');
        }

        if (isset($data_insurance[0]['iInsuranceReportId']) && $data_insurance[0]['iInsuranceReportId'] > 0 && ($eFromType == "GenerateTrip" || $eFromType == "StartTrip")) {
            $where = " iInsuranceReportId='" . $data_insurance[0]['iInsuranceReportId'] . "'";
            $Data_update_old['tEndLat'] = $details_arr['LatLngArr']['vLatitude'];
            $Data_update_old['tEndLong'] = $details_arr['LatLngArr']['vLongitude'];
            $Data_update_old['tEndLocation'] = $details_arr['LatLngArr']['vLocation'];
            $Data_update_old['iTripId'] = $details_arr['iTripId'];
            $Data_update_old['dEndDate'] = $datetime;

            $Distance_Km = distanceByLocation($data_insurance[0]['tStartLat'], $data_insurance[0]['tStartLong'], $details_arr['LatLngArr']['vLatitude'], $details_arr['LatLngArr']['vLongitude'], "K");

            $Distance_Km = str_replace(",", "", $Distance_Km);
            $Data_update_old['vDistance'] = number_format($Distance_Km, 2);

            $id = $obj->MySQLQueryPerform("driver_insurance_report", $Data_update_old, 'update', $where);
        }
    } else {
        if (((isset($details_arr['LatLngArr']['vLatitude']) && $details_arr['LatLngArr']['vLatitude'] != "") || ($allow_blank_latlong == "Yes" && $eFromType == "cancelTrip"))) {
            if (isset($data_insurance[0]['iInsuranceReportId']) && $data_insurance[0]['iInsuranceReportId'] > 0) {
                $where = " iInsuranceReportId='" . $data_insurance[0]['iInsuranceReportId'] . "'";
                $Data_update['tEndLat'] = $details_arr['LatLngArr']['vLatitude'];
                $Data_update['tEndLong'] = $details_arr['LatLngArr']['vLongitude'];
                $Data_update['tEndLocation'] = $details_arr['LatLngArr']['vLocation'];
                $Data_update['dEndDate'] = $datetime;

                $Distance_Km = distanceByLocation($data_insurance[0]['tStartLat'], $data_insurance[0]['tStartLong'], $details_arr['LatLngArr']['vLatitude'], $details_arr['LatLngArr']['vLongitude'], "K");

                $Distance_Km = str_replace(",", "", $Distance_Km);
                $Data_update['vDistance'] = number_format($Distance_Km, 2);

                $id = $obj->MySQLQueryPerform("driver_insurance_report", $Data_update, 'update', $where);
            }
        }
    }
}

//update insurance log

function BookForSomeOneElse() {
    global $generalobj, $obj, $_REQUEST, $Data_update_passenger, $Data;

    $eBookForSomeOneElse = isset($_REQUEST["eBookForSomeOneElse"]) ? $_REQUEST["eBookForSomeOneElse"] : 'No'; // Book For Other Request
    $eBookSomeElseName = isset($_REQUEST["eBookSomeElseName"]) ? $_REQUEST["eBookSomeElseName"] : ''; // Other Book User Name
    $eBookSomeElseNumber = isset($_REQUEST["eBookSomeElseNumber"]) ? $_REQUEST["eBookSomeElseNumber"] : ''; // Other Book User Number
    //if (!empty($type) && $type == "ScheduleARide") {
    if ($type == "ScheduleARide") {
        $Data['eBookForSomeOneElse'] = $eBookForSomeOneElse;
        $Data['vBookSomeOneName'] = $eBookSomeElseName;
        $Data['vBookSomeOneNumber'] = $eBookSomeElseNumber;
    } else {
        $Data_update_passenger['eBookForSomeOneElse'] = $eBookForSomeOneElse;
        $Data_update_passenger['vBookSomeOneName'] = $eBookSomeElseName;
        $Data_update_passenger['vBookSomeOneNumber'] = $eBookSomeElseNumber;
    }

    //return $Data_update_passenger;
}

function InTransitMinutes($tripId) {
    global $generalobj, $obj;
    $totalHoldTimeInMinutes_trip = 0;
    $sql22 = "SELECT * FROM `trip_times` where iTripId='$tripId' ORDER BY `iTripTimeId` DESC";
    $db_tripTimes = $obj->MySQLSelect($sql22);
    if (!empty($db_tripTimes)) {
        if ($db_tripTimes[0]['dPauseTime'] == "" || $db_tripTimes[0]['dPauseTime'] == "0000-00-00 00:00:00") {
            $iTripTimeId = $db_tripTimes[0]['iTripTimeId'];
            $where = " iTripTimeId = '$iTripTimeId'";
            $Data_update['dPauseTime'] = date('Y-m-d H:i:s');
            $id = $obj->MySQLQueryPerform("trip_times", $Data_update, 'update', $where);
            $db_tripTimes[0]['dPauseTime'] = $Data_update['dPauseTime'];
        }
        $totalSec = 0;
        $iTripTimeId = '';
        foreach ($db_tripTimes as $dtT) {
            if ($dtT['dPauseTime'] != '' && $dtT['dPauseTime'] != '0000-00-00 00:00:00') {
                $totalSec += strtotime($dtT['dPauseTime']) - strtotime($dtT['dResumeTime']);
            }
        }
        $days = floor(($totalSec - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
        $hours = floor(($totalSec - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24) / (60 * 60));
        $minuts = floor(($totalSec - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60) / 60);
        $seconds = floor(($totalSec - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60 - $minuts * 60));
        if ($days > 0) {
            $hours = ($days * 24) + $hours;
        }
        if ($hours > 0) {
            $minuts = ($hours * 60) + $minuts;
        }
        if ($minuts > 0) {
            if ($seconds < 60 && $seconds > 0) {
                $minuts = $minuts + 1;
            }
        }
        if ($minuts <= 0 && $seconds > 0) {
            $minuts = 1;
        }
        $totalHoldTimeInMinutes_trip = $minuts;
    }
    return $totalHoldTimeInMinutes_trip;
}

function calculateTransitamount($Fare_data, $totalHoldTimeInMinutes_trip) {
    $fTripHoldFees = $Fare_data[0]['fTripHoldFees'];
    $fTripHoldPrice = round($Fare_data[0]['fTripHoldFees'] * $totalHoldTimeInMinutes_trip, 2);
    return $fTripHoldPrice;
}

function getAccesiblityOption($eChildSeatAvailable, $eWheelChairAvailable) {
    global $CHILD_SEAT_ACCESSIBILITY_OPTION, $WHEEL_CHAIR_ACCESSIBILITY_OPTION;
    $html = "";
    if ($eChildSeatAvailable == 'Yes') {
        $checked = 'checked';
    }
    if ($eWheelChairAvailable == 'Yes') {
        $checkedwheel = 'checked';
    }
    if (isset($CHILD_SEAT_ACCESSIBILITY_OPTION) && $CHILD_SEAT_ACCESSIBILITY_OPTION == "Yes") {
        $html .= '<span class="auto_assign001">
            <input type="checkbox" name="eChildSeatAvailable" id="eChildSeatAvailable" value="Yes" ' . $checked . '>
            <p>Child Seat available?</p>
        </span>';
    }
    return $html;
}

function ChildSeatAvailable($Data_Driver_Vehicle) {
    global $_REQUEST;
    $childAccess = isset($_REQUEST["ChildAccess"]) ? $_REQUEST["ChildAccess"] : 'No';
    $wheelChair = isset($_REQUEST["WheelChair"]) ? $_REQUEST["WheelChair"] : 'No';

    $Data_Driver_Vehicle['eChildSeatAvailable'] = $childAccess;
    $Data_Driver_Vehicle['eWheelChairAvailable'] = $wheelChair;

    return $Data_Driver_Vehicle;
}

function ChildSheetAvailability() {
    global $_REQUEST, $sql;

    $eChildSeatAvailableWeb = isset($_REQUEST["eChildSeatAvailable"]) ? $_REQUEST["eChildSeatAvailable"] : 'No';
    if ($eChildSeatAvailableWeb == 'Yes') {
        $sql .= " AND eChildSeatAvailable='Yes'";
    }

    $sql_builder = explode(",", $sql);

    if (count($sql_builder) > 0) {
        $sql_builder[1] = "dv.eChildSeatAvailable," . $sql_builder[1];
    }

    $sql = implode(",", $sql_builder);
    //return $ssql1;
}

################################### Get Trip Profile Organization Details #################################################

function getTripUserOrganizationProfileDetails($iTripId, $iUserId, $vLang, $iUserProfileId, $iOrganizationId) {
    global $generalobj, $obj, $APP_TYPE;
    $ProfilereturnArr = array();
    $sql1 = "SELECT up.*, upm.vProfileName, upm.vShortProfileName, upm.vImage, org.vCompany,org.ePaymentBy FROM user_profile as up 
                LEFT JOIN user_profile_master as upm ON up.iUserProfileMasterId=upm.iUserProfileMasterId
                LEFT JOIN organization as org ON up.iOrganizationId=org.iOrganizationId
                where up.iUserId = '" . $iUserId . "' AND up.iUserProfileId = '" . $iUserProfileId . "'";
    $db_data = $obj->MySQLSelect($sql1);
    if (count($db_data) > 0) {
        $vProfileName = "vProfileName_" . $vLang;
        $vShortProfileName = "vShortProfileName_" . $vLang;
        for ($i = 0; $i < count($db_data); $i++) {
            $vProfileNameArr = json_decode($db_data[$i]['vProfileName'], true);
            $db_data[$i]['vProfileName'] = $vProfileNameArr[$vProfileName];
            $vShortProfileNameArr = json_decode($db_data[$i]['vShortProfileName'], true);
            $db_data[$i]['vShortProfileName'] = $vShortProfileNameArr[$vShortProfileName];
            $Photo_Gallery_folder = $tconfig["tsite_upload_profile_master_path"] . '/' . $db_data[$i]['vImage'];
            if ($db_data[$i]['vImage'] != "" && file_exists($Photo_Gallery_folder)) {
                $db_data[$i]['vImage'] = $tconfig["tsite_upload_images_profile_master"] . "/" . $db_data[$i]['vImage'];
            } else {
                $db_data[$i]['vImage'] = $tconfig["tsite_upload_images_profile_master"] . "/defaulticon.png";
            }
        }
        $ProfilereturnArr = $db_data;
    }
    return $ProfilereturnArr;
}

######################################## Get Trip Profile Organization Details ################################################
########################### Update Trip Outstanding Amount Of Organization ##############################################

function UpdateOrganizationTripOutstandingAmount($iTripId, $ePaidByPassenger = "No", $ePaidToDriver = "No") {
    global $generalobj, $obj;
    $sql = "SELECT iUserId,iDriverId,fCancellationFare,fWalletDebit,vTripPaymentMode,vRideNo,iVehicleTypeId,iOrganizationId,ePaymentBy FROM trips WHERE iTripId='" . $iTripId . "'";
    $tripdata = $obj->MySQLSelect($sql);
    $iUserId = $tripdata[0]['iUserId'];
    $iDriverId = $tripdata[0]['iDriverId'];
    $fCancellationFare = $tripdata[0]['fCancellationFare'];
    $fWalletDebit = $tripdata[0]['fWalletDebit'];
    $vTripPaymentMode = $tripdata[0]['vTripPaymentMode'];
    $iVehicleTypeId = $tripdata[0]['iVehicleTypeId'];
    $iOrganizationId = $tripdata[0]['iOrganizationId'];
    $ePaymentBy = $tripdata[0]['ePaymentBy'];
    $fCommision = get_value('vehicle_type', 'fCommision', 'iVehicleTypeId', $iVehicleTypeId, '', 'true');
    if ($fCancellationFare > 0) {
        $fPendingAmount = $fCancellationFare;
    } else {
        $fPendingAmount = 0;
    }
    if ($fPendingAmount < 0) {
        $fPendingAmount = 0;
    }
    ## Calculate Driver's Commission and PendingAmount ##
    $DriverTotalAmount = $fWalletDebit + $fCancellationFare;
    $Site_Commision = round((($DriverTotalAmount * $fCommision) / 100), 2);
    $fDriverPendingAmount = $DriverTotalAmount - $Site_Commision;
    $fTripGenerateFare = $fWalletDebit + $fCancellationFare;
    $iFare = $fCancellationFare;
    ## Calculate Driver's Commission and PendingAmount ##
    $Data_trip_OutstandingAmount["iTripId"] = $iTripId;
    $Data_trip_OutstandingAmount["iUserId"] = $iUserId;
    $Data_trip_OutstandingAmount["iDriverId"] = $iDriverId;
    $Data_trip_OutstandingAmount["iOrganizationId"] = $iOrganizationId;
    $Data_trip_OutstandingAmount["fWalletDebit"] = $fWalletDebit;
    $Data_trip_OutstandingAmount["fCancellationFare"] = $fCancellationFare;
    $Data_trip_OutstandingAmount["vTripPaymentMode"] = $vTripPaymentMode;
    $Data_trip_OutstandingAmount["ePaidByPassenger"] = $ePaidByPassenger;
    $Data_trip_OutstandingAmount["ePaidToDriver"] = $ePaidToDriver;
    $Data_trip_OutstandingAmount["ePaidByOrganization"] = "No";
    $Data_trip_OutstandingAmount["ePaymentBy"] = $ePaymentBy;
    $Data_trip_OutstandingAmount["fPendingAmount"] = $fPendingAmount;
    $Data_trip_OutstandingAmount["fCommision"] = $Site_Commision;
    $Data_trip_OutstandingAmount["fDriverPendingAmount"] = $fDriverPendingAmount;
    $currencyList = get_value('currency', '*', 'eStatus', 'Active');
    for ($i = 0; $i < count($currencyList); $i++) {
        $currencyCode = $currencyList[$i]['vName'];
        $Data_trip_OutstandingAmount['fRatio_' . $currencyCode] = $currencyList[$i]['Ratio'];
    }
    $iTripOutstandId = $obj->MySQLQueryPerform("trip_outstanding_amount", $Data_trip_OutstandingAmount, 'insert');
    $updateQuery = "UPDATE trips set iFare = '" . $iFare . "',fTripGenerateFare = '" . $fTripGenerateFare . "',fCommision = '" . $Site_Commision . "',eOrganizationPaymentStatus = 'Unsettelled' WHERE iTripId = " . $iTripId;
    $obj->sql_query($updateQuery);
    return $iTripOutstandId;
}

########################### Update Trip Outstanding Amount Of Organization ##############################################

function GetSendRequestToDriverParam() {
    global $_REQUEST, $eWalletDebitAllow, $tripPaymentMode, $Data_update_passenger;
    ### Organization Profile Param ###
    $iUserProfileId = isset($_REQUEST["iUserProfileId"]) ? $_REQUEST["iUserProfileId"] : '';
    $iOrganizationId = isset($_REQUEST["iOrganizationId"]) ? $_REQUEST["iOrganizationId"] : '';
    $vProfileEmail = isset($_REQUEST["vProfileEmail"]) ? $_REQUEST["vProfileEmail"] : '';
    $ePaymentBy = isset($_REQUEST["ePaymentBy"]) ? $_REQUEST["ePaymentBy"] : '';
    ### Organization Profile Param ###

    if ($iUserProfileId != "") {
        $Data_update_passenger['iUserProfileId'] = $iUserProfileId;
        $Data_update_passenger['iOrganizationId'] = $iOrganizationId;
        $Data_update_passenger['vProfileEmail'] = $vProfileEmail;
        $Data_update_passenger['ePaymentBy'] = $ePaymentBy;
        $Data_update_passenger['ePayType'] = $tripPaymentMode;
        if ($ePaymentBy == "Organization") {
            $Data_update_passenger['eWalletDebitAllow'] = "No";
            $Data_update_passenger['ePayType'] = "Organization";
        } else {
            $Data_update_passenger['eWalletDebitAllow'] = $eWalletDebitAllow;
            $Data_update_passenger['ePayType'] = $tripPaymentMode;
        }
    }

    // return $Data_update_passenger;
}

################################

function reDefineGenerateTripQuery() {
    global $sql, $iCabRequestId;

    $sql_builder = explode(",", $sql);

    if (count($sql_builder) > 0) {
        $sql_builder[1] = "iUserProfileId,iOrganizationId,vProfileEmail,ePaymentBy,eBookForSomeOneElse,vBookSomeOneName,vBookSomeOneNumber,fAirportPickupSurge,fAirportDropoffSurge,iHotelBookingId," . ($iCabRequestId > 0 ? "ePoolRide,iPersonSize,iHotelId," : "") . $sql_builder[1];
    }

    $sql = implode(",", $sql_builder);
}

##########################################

function reDefineGenerateTripParam() {
    global $iCabRequestId, $iCabBookingId, $Data_trips, $POOL_ENABLE, $driver_id, $data_booking, $check_row, $generalobj, $obj, $Data_passenger_detail, $VehicleData, $DriverVehicleMake, $DriverVehicleModel, $Data_vehicle, $iTripId, $SITE_ISD_CODE, $PACKAGE_TYPE, $vTripPaymentMode, $vLangCode, $isdCode, $BOOK_FOR_ELSE_ENABLE, $Data_update_driver;
    $vBookSomeOneName = $vBookSomeOneNumber = "";
    $ePoolRide = $eBookForSomeOneElse = "No";
    $iPersonSize = $poolParentId = 0;

    if ($iCabBookingId != "" && $iCabBookingId > 0) {
        $iHotelBookingId = $data_booking[0]['iHotelBookingId'];
        $iUserProfileId = $data_booking[0]['iUserProfileId'];
        $iOrganizationId = $data_booking[0]['iOrganizationId'];
        $vProfileEmail = $data_booking[0]['vProfileEmail'];
        $ePaymentBy = $data_booking[0]['ePaymentBy'];
        // for airport surge
        $fAirportPickupSurge = $data_booking[0]['fAirportPickupSurge'];
        $fAirportDropoffSurge = $data_booking[0]['fAirportDropoffSurge'];

        $eBookForSomeOneElse = $data_booking[0]['eBookForSomeOneElse'];
        $vBookSomeOneName = $data_booking[0]['vBookSomeOneName'];
        $vBookSomeOneNumber = $data_booking[0]['vBookSomeOneNumber'];

        $tDestinationLatitude = $data_booking[0]['vDestLatitude'];
        $tDestinationLongitude = $data_booking[0]['vDestLongitude'];
    } else {

        $iHotelBookingId = $check_row[0]['iHotelBookingId'];
        $iHotelId = $check_row[0]['iHotelId'];
        $iUserProfileId = $check_row[0]['iUserProfileId'];
        $iOrganizationId = $check_row[0]['iOrganizationId'];
        $vProfileEmail = $check_row[0]['vProfileEmail'];
        $ePaymentBy = $check_row[0]['ePaymentBy'];
        // for airport surge
        $fAirportPickupSurge = $check_row[0]['fAirportPickupSurge'];
        $fAirportDropoffSurge = $check_row[0]['fAirportDropoffSurge'];
        $ePoolRide = $check_row[0]['ePoolRide'];
        $iPersonSize = $check_row[0]['iPersonSize'];
        $fDistance = $check_row[0]['fDistance'];
        $fDuration = $check_row[0]['fDuration'];
        $eBookForSomeOneElse = $check_row[0]['eBookForSomeOneElse'];
        $vBookSomeOneName = $check_row[0]['vBookSomeOneName'];
        $vBookSomeOneNumber = $check_row[0]['vBookSomeOneNumber'];

        $tDestinationLatitude = $check_row[0]['vDestLatitude'];
        $tDestinationLongitude = $check_row[0]['vDestLongitude'];
    }

    $Data_trips['iHotelBookingId'] = $iHotelBookingId;
    $Data_trips['ePoolRide'] = $ePoolRide;
    $Data_trips['iPersonSize'] = $iPersonSize;
    $Data_trips['fPoolDuration'] = $fDuration;
    $Data_trips['fPoolDistance'] = $fDistance;
    $Data_trips['eBookForSomeOneElse'] = $eBookForSomeOneElse;
    $Data_trips['vBookSomeOneName'] = $vBookSomeOneName;
    $Data_trips['vBookSomeOneNumber'] = $vBookSomeOneNumber;
    // for airport surge
    $Data_trips['fAirportPickupSurge'] = $fAirportPickupSurge;
    $Data_trips['fAirportDropoffSurge'] = $fAirportDropoffSurge;
    if ($ePoolRide == "Yes" && $POOL_ENABLE == "Yes") {
        $getAllTrips = $obj->MySQLSelect("SELECT iTripId FROM trips WHERE iDriverId='" . $driver_id . "' AND (iActive ='Active' || iActive ='On Going Trip') ORDER BY iTripId ASC");
        if (isset($getAllTrips[0]['iTripId']) && $getAllTrips[0]['iTripId'] > 0) {
            $poolParentId = $getAllTrips[0]['iTripId'];
        }
    }

    $Data_trips['iPoolParentId'] = $poolParentId;
    $Data_trips['iUserProfileId'] = $iUserProfileId;
    $Data_trips['iOrganizationId'] = $iOrganizationId;
    $Data_trips['vProfileEmail'] = $vProfileEmail;
    $Data_trips['ePaymentBy'] = $ePaymentBy;
    $Data_trips['iHotelId'] = $iHotelId;
    if ($ePaymentBy == "Passenger") {
        $Data_trips['eOrganizationPaymentStatus'] = "Settelled";
    }

    if ($poolParentId == 0) {
        $Data_update_driver['vPoolDestLat'] = $tDestinationLatitude;
        $Data_update_driver['vPoolDestLang'] = $tDestinationLongitude;
    }
}

#########################################

function GetScheduleParam() {
    global $_REQUEST, $Data;
    ### Organization Profile Param ###
    $iUserProfileId = isset($_REQUEST["iUserProfileId"]) ? $_REQUEST["iUserProfileId"] : '';
    $iOrganizationId = isset($_REQUEST["iOrganizationId"]) ? $_REQUEST["iOrganizationId"] : '';
    $vProfileEmail = isset($_REQUEST["vProfileEmail"]) ? $_REQUEST["vProfileEmail"] : '';
    $ePaymentBy = isset($_REQUEST["ePaymentBy"]) ? $_REQUEST["ePaymentBy"] : '';
    ### Organization Profile Param ###
    if ($iUserProfileId != "") {
        $Data['iUserProfileId'] = $iUserProfileId;
        $Data['iOrganizationId'] = $iOrganizationId;
        $Data['vProfileEmail'] = $vProfileEmail;
        $Data['ePaymentBy'] = $ePaymentBy;
        $Data['ePayType'] = "Organization";
        if ($ePaymentBy == "Organization") {
            $Data['eWalletDebitAllow'] = "No";
        }
    }
}

##############################################

function finalizeProcessEndTrip() {
    global $generalobj, $obj, $trip_start_data_arr, $tripId, $userId, $Data_update_trips;
    ## Update Organization Outstanding Amount ##

    $iOrganizationId = $trip_start_data_arr[0]['iOrganizationId'];
    $ePaymentBy = $trip_start_data_arr[0]['ePaymentBy'];
    if ($ePaymentBy == "Passenger") {
        $Data_update_trips['eOrganizationPaymentStatus'] = "Settelled";
    }
    if ($iOrganizationId != "" && $iOrganizationId > 0 && $ePaymentBy == "Organization") {
        $updateOrgOutAmtQury = "UPDATE trip_outstanding_amount set eBillGenerated = 'Yes', vTripAdjusmentId = '" . $tripId . "' WHERE iUserId = '" . $userId . "' AND ePaidByOrganization = 'No' AND eBillGenerated = 'No'";
        $obj->sql_query($updateOrgOutAmtQury);
    }
    ## Update Organization Outstanding Amount ##
}

#########################################################

function GetPassengerOutstandingAmountShark($iUserId) {
    global $generalobj, $obj, $_REQUEST, $data_trips, $iOrganizationId, $ePaymentBy, $type, $SYSTEM_PAYMENT_FLOW;

    if ($type == "checkSurgePrice") {
        global $data;
    }

    $iOrganizationId = isset($_REQUEST["iOrganizationId"]) ? $_REQUEST["iOrganizationId"] : $data_trips[0]['iOrganizationId'];
    $ePaymentBy = isset($_REQUEST["ePaymentBy"]) ? $_REQUEST["ePaymentBy"] : $data_trips[0]['ePaymentBy'];

    if ($iOrganizationId == "" || $iOrganizationId == NULL) {
        $iOrganizationId = 0;
    }
    if ($ePaymentBy == "" || $ePaymentBy == NULL) {
        $ePaymentBy = "Passenger";
    }

    if ($iOrganizationId > 0 && $ePaymentBy == "Organization" && $type == "checkSurgePrice") {
        $data['ShowPayNow'] = "No";
    }
    $outStandingSql = "";
    if ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') {
        $outStandingSql = " AND eAuthoriseIdName='No' AND iAuthoriseId ='0'";
    }
    if ($ePaymentBy == "Passenger") {
        $sql = "SELECT SUM(fPendingAmount) as fPendingAmount FROM trip_outstanding_amount WHERE iUserId='" . $iUserId . "' AND iUserId > 0 AND ePaidByPassenger = 'No' AND ePaymentBy = 'Passenger' $outStandingSql";
        //$sql = "SELECT fPendingAmount as fPendingAmount FROM trip_outstanding_amount WHERE iUserId='" . $iUserId . "' AND iUserId > 0 AND ePaidByPassenger = 'No' AND ePaymentBy = 'Passenger' $outStandingSql";
        if ($iOrganizationId > 0) {
            $sql = $sql . " AND iOrganizationId ='" . $iOrganizationId . "'";
        } else {
            $sql = $sql . " AND iOrganizationId ='0'";
        }
        //$sql = $sql .  " ORDER BY iTripOutstandId DESC LIMIT 1";
    } else {
        $sql = "SELECT SUM(fPendingAmount) as fPendingAmount FROM trip_outstanding_amount WHERE iUserId = '" . $iUserId . "' AND iUserId > 0 AND iOrganizationId    ='" . $iOrganizationId . "' AND ePaidByOrganization = 'No' AND ePaymentBy = 'Organization' AND eBillGenerated = 'No' $outStandingSql";
        //$sql = "SELECT fPendingAmount as fPendingAmount FROM trip_outstanding_amount WHERE iUserId = '" . $iUserId . "' AND iUserId > 0 AND iOrganizationId    ='" . $iOrganizationId . "' AND ePaidByOrganization = 'No' AND ePaymentBy = 'Organization' AND eBillGenerated = 'No' $outStandingSql";
        //$sql = $sql .  " ORDER BY iTripOutstandId DESC LIMIT 1";
    }

    $tripoutstandingdata = $obj->MySQLSelect($sql);
    $fPendingAmount = round($tripoutstandingdata[0]['fPendingAmount'], 2);
    if ($fPendingAmount == "" || $fPendingAmount == NULL) {
        $fPendingAmount = 0;
    }
    return $fPendingAmount;
}

################################################################

function getDriverPoolTrips($driverId) {
    $finalArr = $distanceArr = $mainLatLangTripArr = array();
    global $generalobj, $obj, $GOOGLE_SEVER_GCM_API_KEY, $POOL_ENABLE,$userDetailsArr,$vSystemDefaultLangCode,$Data_ALL_langArr,$generalTripRatingDataArr,$tripDetailsArr;
    //If Arrived and ongoing then not collect start lat and long
    //Don't Get Canceled,Finished Trip
    $getAllTrips = $obj->MySQLSelect("SELECT * FROM trips WHERE iDriverId='" . $driverId . "' AND (iActive='Active' || iActive= 'Arrived' || iActive = 'On Going Trip') AND ePoolRide='Yes' ORDER BY iTripId ASC");
    $latLongString = "";
    $startLatitude = $startLongitude = $endLatitude = $endLongitude = "";
    
    //Added By HJ On 17-06-2020 For Optimization register_driver Table Query Start
    $tblName = "register_driver";
    if(isset($userDetailsArr[$tblName."_".$driverId]) && count($userDetailsArr[$tblName."_".$driverId]) > 0){
        $getDriverData = $userDetailsArr[$tblName."_".$driverId];
    }else{
        //$row = $obj->MySQLSelect("SELECT * FROM ".$tblName." WHERE iUserId='$passengerID'"); //Commented By HJ On 17-06-2020 For Optimize register_driver Table Query
        $getDriverData = $obj->MySQLSelect("SELECT *,iDriverId as iMemberId FROM register_driver WHERE iDriverId='" . $driverId . "'");
        $userDetailsArr[$tblName."_".$driverId] = $getDriverData;
    }
    //Added By HJ On 17-06-2020 For Optimization register_driver Table Query End
    $where = " iDriverId = '" . $driverId . "'";
    if (count($getDriverData) > 0 && $POOL_ENABLE == "Yes") {
        $vLangCode = $getDriverData[0]['vLang'];
        $startLatitude = $getDriverData[0]['vLatitude'];
        $startLongitude = $getDriverData[0]['vLongitude'];
        $iTripId = $getDriverData[0]['iTripId'];
        if ($vLangCode == "" || $vLangCode == NULL) {
            if (!empty($vSystemDefaultLangCode)) {
                $vLangCode = $vSystemDefaultLangCode;
            } else {
                $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
            }
        }
        //Added By HJ On 17-06-2020 For Optimize language_master Table Query Start
        if(count($Data_ALL_langArr) > 0){
            for($g=0;$g<count($Data_ALL_langArr);$g++){
                if(strtoupper($Data_ALL_langArr[$g]['vCode']) == strtoupper($vLangCode)){
                    $vGMapLangCode  = $Data_ALL_langArr[$g]['vCode'];
                }
            }
        }else{
            $vGMapLangCode = get_value('language_master', 'vGMapLangCode', 'vCode', $vLangCode, '', 'true');
        }
        //Added By HJ On 17-06-2020 For Optimize language_master Table Query End
        $distance = 0;
        //Added By HJ On 17-06-2020 For Optimize ratings_user_driver Table Query Start
        $data_version =$checkRating= array();
        if($iTripId >0){
            if(isset($generalTripRatingDataArr['ratings_user_driver_'.$iTripId])){
                $getTripRateData = $generalTripRatingDataArr['ratings_user_driver_'.$iTripId];
            }else{
                $generalTripRatingDataArr = array();
                $getTripRateData = $obj->MySQLSelect("SELECT * FROM `ratings_user_driver` WHERE iTripId='".$iTripId."'");
                $generalTripRatingDataArr['ratings_user_driver_'.$iTripId] = $getTripRateData;
                //echo "<pre>";print_r($generalTripRatingDataArr);die;
            }
            for($r=0;$r<count($getTripRateData);$r++){
                $rateUserType = $getTripRateData[$r]['eUserType'];
                if(strtoupper($rateUserType) == "DRIVER"){
                    $checkRating[] =$getTripRateData[$r];
                }
            }
        }
        //Added By HJ On 17-06-2020 For Optimize ratings_user_driver Table Query Start
        //$checkRating = $obj->MySQLSelect("SELECT iRatingId FROM ratings_user_driver WHERE iTripId='" . $iTripId . "' AND eUserType='Driver'"); //Commented By HJ On 17-06-2020 For Optimize ratings_user_driver Table Query
        //Added By HJ On 17-06-2020 For Optimize trips Table Query Start
        $tripData = $tripDataPool = array();
        if(isset($tripDetailsArr["trips_".$iTripId])){
            $tripDataPool = $tripDetailsArr["trips_".$iTripId];
        }else{
            $tripDataPool = $obj->MySQLSelect("SELECT * FROM trips WHERE iTripId='" . $iTripId . "'");
            $tripDetailsArr["trips_".$iTripId] = $tripDataPool;
        }
        $existFromLoop = 1;
        for($g=0;$g<count($tripDataPool);$g++){
            if(strtoupper($tripDataPool[$g]['ePoolRide']) == "YES" && $existFromLoop > 0){
                $tripData[] = $tripDataPool[$g];
                $existFromLoop = 0;
            }
        }
        //Added By HJ On 17-06-2020 For Optimize trips Table Query End
        $whereTrip = " iDriverId = '" . $driverId . "' AND iTripId='" . $iTripId . "'";
        if (isset($tripData[0]['iActive']) && $tripData[0]['iActive'] == "Finished" && count($checkRating) == 0) {
            $data_version['vTripStatus'] = "Not Active";
            $obj->MySQLQueryPerform("register_driver", $data_version, 'update', $whereTrip);
            return 1;
        }
        for ($t = 0; $t < count($getAllTrips); $t++) {
            $startLat = $getAllTrips[$t]['tStartLat'];
            $startLang = $getAllTrips[$t]['tStartLong'];
            $endLat = $getAllTrips[$t]['tEndLat'];
            $endLang = $getAllTrips[$t]['tEndLong'];
            $iTripId = $getAllTrips[$t]['iTripId'];
            $iActive = $getAllTrips[$t]['iActive'];
            $ePoolRide = $getAllTrips[$t]['ePoolRide'];
            if ($ePoolRide == "Yes") {
                $distanceTrip = distanceByLocation($startLatitude, $startLongitude, $endLat, $endLang, "K");
                if ($distanceTrip > $distance) {
                    $distance = $distanceTrip;
                    $endLatitude = $endLat;
                    $endLongitude = $endLang;
                }
                $getAllTrips[$t]['distance'] = $distance;
                if ($iActive == "On Going Trip" || $iActive == "Arrived") {
                    $latLongString .= $endLat . "," . $endLang . "|";
                } else if ($iActive == "Active") {
                    $latLongString .= $startLat . "," . $startLang . "|";
                    $latLongString .= $endLat . "," . $endLang . "|";
                }
                $latLangTripArr = array();
                $latLangTripArr['lat'] = $startLat;
                $latLangTripArr['lang'] = $startLang;
                $latLangTripArr['iTripId'] = $iTripId;
                $latLangTripArr['tripStatus'] = $iActive;
                $mainLatLangTripArr[] = $latLangTripArr;
                $latLangTripArr = array();
                $latLangTripArr['lat'] = $endLat;
                $latLangTripArr['lang'] = $endLang;
                $latLangTripArr['iTripId'] = $iTripId;
                $latLangTripArr['tripStatus'] = $iActive;
                $mainLatLangTripArr[] = $latLangTripArr;
            }
        }
        //$endLatitude = $startLatitude;
        //$endLongitude = $startLongitude;
        if ($latLongString != "") {
            $latLongString = trim($latLongString, "|");
            $explodeString = explode("|", $latLongString);
            //print_r($explodeString);die;
            $GOOGLE_API_KEY = $GOOGLE_SEVER_GCM_API_KEY;
            $url = "https://maps.googleapis.com/maps/api/directions/json?origin=" . $startLatitude . "," . $startLongitude . "&destination=" . $endLatitude . "," . $endLongitude . "&sensor=true&key=" . $GOOGLE_API_KEY . "&language=" . $vGMapLangCode . "&waypoints=optimize:true|" . $latLongString;
            $headers = array(
                'Content-Type: application/json'
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            //Send the request
            $response = curl_exec($ch); //echo "<pre>";print_r($response);exit;
            $result = (array) json_decode($response);

            if (isset($result['status']) && $result['status'] == "OK") {
                if (isset($result['routes'][0]->waypoint_order)) {
                    $wayPointOrder = $result['routes'][0]->waypoint_order;
                    foreach ($wayPointOrder as $key => $val) {
                        if (isset($explodeString[$val])) {
                            $finalArr[] = $explodeString[$val];
                        }
                    }
                }
            }
        }
    }
    if (count($finalArr) > 0) {
        $explode = explode(",", $finalArr[0]);
        for ($r = 0; $r < count($mainLatLangTripArr); $r++) {
            $sLat = $mainLatLangTripArr[$r]['lat'];
            $sLang = $mainLatLangTripArr[$r]['lang'];
            if ($explode[0] == $sLat && $explode[1] == $sLang) {
                $data_version = array();
                $data_version['iTripId'] = $mainLatLangTripArr[$r]['iTripId'];
                $data_version['vTripStatus'] = $mainLatLangTripArr[$r]['tripStatus'];
                $obj->MySQLQueryPerform("register_driver", $data_version, 'update', $where);
            }
        }
    }
    return $finalArr;
}

###################################################################

function fetchtripstatustimeintervalForPool() {
    global $generalobj, $obj, $FETCH_TRIP_STATUS_TIME_INTERVAL_POOL, $Data, $POOL_ENABLE, $_REQUEST,$tripDetailsArr;
    $GeneralUserType = isset($_REQUEST['GeneralUserType']) ? trim($_REQUEST['GeneralUserType']) : ''; // Added BY HJ On 02-04-2019 For Check User Type
    $range = "";
    //Added By Hasmukh On 16-11-2018 For Check Trip Pool Status Start
    $checkPoolStatus = 0;
    if (isset($Data[0]['iTripId']) && $Data[0]['iTripId'] > 0 && $POOL_ENABLE == "Yes" && $GeneralUserType == "Driver") {
        if(isset($tripDetailsArr['trips_'.$Data[0]['iTripId']])){
            $getPoolStatus = $tripDetailsArr['trips_'.$Data[0]['iTripId']];
        }else{
            $getPoolStatus = $obj->MySQLSelect("SELECT ePoolRide from trips WHERE iTripId = '" . $Data[0]['iTripId'] . "'");
        }
        if (isset($getPoolStatus[0]['ePoolRide']) && $getPoolStatus[0]['ePoolRide'] == "Yes") {
            $checkPoolStatus = 1;
        }
    }
    if (isset($Data[0]['vTripStatus']) && $Data[0]['vTripStatus'] == "Active" || $Data[0]['vTripStatus'] == "ongoing" || $Data[0]['vTripStatus'] == "arrived" && $checkPoolStatus == 1) {
        $FETCH_TRIP_STATUS_TIME_INTERVAL_ARR = explode("-", $FETCH_TRIP_STATUS_TIME_INTERVAL_POOL);
        $FETCH_TRIP_STATUS_TIME_INTERVAL_MIN = $FETCH_TRIP_STATUS_TIME_INTERVAL_ARR[0];
        $FETCH_TRIP_STATUS_TIME_INTERVAL_MIN = $FETCH_TRIP_STATUS_TIME_INTERVAL_MIN - 4;
        if ($FETCH_TRIP_STATUS_TIME_INTERVAL_MIN < 15) {
            $FETCH_TRIP_STATUS_TIME_INTERVAL_MIN = 15;
        }
        $FETCH_TRIP_STATUS_TIME_INTERVAL_MAX = $FETCH_TRIP_STATUS_TIME_INTERVAL_ARR[1];
        $range = rand($FETCH_TRIP_STATUS_TIME_INTERVAL_MIN, $FETCH_TRIP_STATUS_TIME_INTERVAL_MAX);
    }
    //Added By Hasmukh On 16-11-2018 For Check Trip Pool Status End


    return $range;
}

##################################################################

function fetchtripstatustimeMAXintervalForPool() {
    global $generalobj, $obj, $FETCH_TRIP_STATUS_TIME_INTERVAL_POOL;
    //$FETCH_TRIP_STATUS_TIME_INTERVAL = $generalobj->getConfigurations("configurations", "FETCH_TRIP_STATUS_TIME_INTERVAL");
    $FETCH_TRIP_STATUS_TIME_INTERVAL_ARR = explode("-", $FETCH_TRIP_STATUS_TIME_INTERVAL_POOL);
    $FETCH_TRIP_STATUS_TIME_INTERVAL_MAX = $FETCH_TRIP_STATUS_TIME_INTERVAL_ARR[1];
    return $FETCH_TRIP_STATUS_TIME_INTERVAL_MAX;
}

//Added By Hasmukh On 05-12-2018 For Get Pool Driver Data Start
function getPoolDriverList($poolDriverArr, $Check_Driver_UFX, $isFromHotelPanel, $vLatitude, $vLongitude, $param, $sourceLat, $str_date, $sourceLon, $destLat, $destLon) {
    global $generalobj, $obj, $FEMALE_RIDE_REQ_ENABLE, $RESTRICTION_KM_NEAREST_TAXI_POOL, $RESTRICTION_KM_NEAREST_DESTINATION_POOL, $POOL_ENABLE;

    if ($POOL_ENABLE == "No") {
        return $poolDriverArr;
    }
    $ssql_available = "";
    $endLoction = 3;
    if ($Check_Driver_UFX == "No") {
        $ssql_available .= " AND vAvailability = 'Not Available' AND (vTripStatus = 'Active' || vTripStatus = 'Arrived' || vTripStatus = 'On Going Trip') AND tLocationUpdateDate > '$str_date' ";
    }
    // for hotel panel web
    if ($isFromHotelPanel == 'Yes') {
        if ($FEMALE_RIDE_REQ_ENABLE == 'Yes') {
            $ssql_available .= " AND eFemaleOnlyReqAccept = 'No' ";
        }
    }
    $sql = "SELECT ROUND(( 6371 * acos( cos( radians(" . $sourceLat . ") )
            * cos( radians( ROUND(" . $vLatitude . ",8) ) )
            * cos( radians( ROUND(" . $vLongitude . ",8) ) - radians(" . $sourceLon . ") )
            + sin( radians(" . $sourceLat . ") )
            * sin( radians( ROUND(" . $vLatitude . ",8) ) ) ) ),2) AS distance,ROUND(( 6371 * acos( cos( radians(" . $destLat . ") )
            * cos( radians( ROUND(vPoolDestLat,8) ) )
            * cos( radians( ROUND(vPoolDestLang,8) ) - radians(" . $destLon . ") )
            + sin( radians(" . $destLat . ") )
            * sin( radians( ROUND(vPoolDestLat,8) ) ) ) ),2) AS destdistance, concat('+',register_driver.vCode,register_driver.vPhone) as vPhonenumber, register_driver.*  FROM `register_driver`
            WHERE (" . $vLatitude . " != '' AND " . $vLongitude . " != '' $ssql_available AND eStatus='active')
            HAVING distance <= " . $RESTRICTION_KM_NEAREST_TAXI_POOL . " AND destdistance <= " . $RESTRICTION_KM_NEAREST_DESTINATION_POOL . " ORDER BY `register_driver`.`" . $param . "` ASC";
    $Data = $obj->MySQLSelect($sql);
    if (!empty($Data)) {   //added by SP on 22-7-2019 bc when no data the error in vTripStatusnot found in manual booking
        for ($r = 0; $r < count($Data); $r++) {
            $tripId = $Data[$r]['iTripId'];
            $Data[$r]['eTripStatusActive'] = "No";
            if (isset($Data[$r]['vTripStatus']) && $Data[$r]['vTripStatus'] == "On Going Trip" || $Data[$r]['vTripStatus'] == "Active" || $Data[$r]['vTripStatus'] == "Arrived") {
                $Data[$r]['eTripStatusActive'] = "Yes";
            }

            $checkPoolTrip = $obj->MySQLSelect("SELECT ePoolRide,iPersonSize FROM trips WHERE iTripId='" . $tripId . "'");
            if (isset($checkPoolTrip[0]['ePoolRide']) && $checkPoolTrip[0]['ePoolRide'] == "Yes") {
                $poolDriverArr[] = $Data[$r];
            }
        }
    }
    return $poolDriverArr;
}

//Added By Hasmukh On 05-12-2018 For Get Pool Driver Data End

function LoadAvailableCabVehicleQuery($ssql) {
    global $POOL_ENABLE, $APP_TYPE, $eRental;
    if ($POOL_ENABLE == "No" || strtoupper($eRental) == "YES" || strtoupper(PACKAGE_TYPE) != "SHARK" || $APP_TYPE == "Delivery" || $APP_TYPE == "UberX" || ONLYDELIVERALL == "Yes") {
        $ssql .= " AND ePoolStatus = 'No'";
    }
    return $ssql;
}

####################

function poolVariableGetForSendRequest() {
    global $generalobj, $obj, $_REQUEST, $POOL_ENABLE, $selectedCarTypeID, $final_message, $Data_update_passenger, $driver_id_auto, $ePoolStatus,$vehicleTypeDataArr;
    if ($POOL_ENABLE != "Yes") {
        return;
    }
    $iPersonSize = isset($_REQUEST["iPersonSize"]) ? $_REQUEST["iPersonSize"] : '0'; // Passanger Size
    $ePoolRequest = isset($_REQUEST["ePoolRequest"]) ? $_REQUEST["ePoolRequest"] : 'No'; // Pool Request

    $ePoolStatus = "No";
    $vehiclePesonSize = 1;

    //Added By Hasmukh On 16-11-2018 For Get Vehicle Category Pool Status
    //Added By HJ On 20-06-2020 For Optimized vehicle_type Table Query Start
    if(isset($vehicleTypeDataArr['vehicle_type'])){
        $VehicleTypeData = $vehicleTypeDataArr['vehicle_type'];
    }else{
        $VehicleTypeData = $obj->MySQLSelect("SELECT * from vehicle_type");
        $vehicleTypeDataArr['vehicle_type'] = $VehicleTypeData;
    }
    $tripVehicleDataArr =$tripVehicleData= array();
    for($h=0;$h<count($VehicleTypeData);$h++){
        $tripVehicleDataArr[$VehicleTypeData[$h]['iVehicleTypeId']] = $VehicleTypeData[$h];
    }
    if(isset($tripVehicleDataArr[$selectedCarTypeID])){
        $tripVehicleData[] = $tripVehicleDataArr[$selectedCarTypeID];
    }
    //Added By HJ On 20-06-2020 For Optimized vehicle_type Table Query End
    //$sqlv = "SELECT iVehicleCategoryId,fPoolPercentage,ePoolStatus,iPersonSize from vehicle_type WHERE iVehicleTypeId = '" . $selectedCarTypeID . "'";
    //$tripVehicleData = $obj->MySQLSelect($sqlv);
    if (isset($tripVehicleData[0]['ePoolStatus']) && $tripVehicleData[0]['ePoolStatus'] == "Yes" && $POOL_ENABLE == "Yes") {
        $ePoolStatus = $tripVehicleData[0]['ePoolStatus'];
    }
    $vehiclePesonSize = $tripVehicleData[0]['iPersonSize'];
    //Added By Hasmukh On 16-11-2018 For Get Vehicle Category Pool End

    $final_message['iPersonSize'] = $iPersonSize;
    $final_message['ePoolRequest'] = $ePoolRequest;
    $final_message['SelectedTypeName'] = $final_message['eFareType'] = "";

    $Data_update_passenger['ePoolRide'] = $ePoolStatus;
    $Data_update_passenger['iPersonSize'] = $iPersonSize;

    //Added On 06-12-2018 For Get Pool Driver Data Start
    if ($ePoolStatus == "Yes" && $POOL_ENABLE == "Yes") {
        //Added By Hasmukh On 17-12-2018 For Removed Code Of Check Passenger Seat Size Start
        $getActiveDriverTrip = "SELECT SUM(iPersonSize) AS iPersonSize,iDriverId FROM trips WHERE (iActive='Active' || iActive='On Going Trip' ||iActive='Arrived') AND iDriverId IN (" . $driver_id_auto . ")";
        $activeTrips = $obj->MySQLSelect($getActiveDriverTrip);
        $newDriverArr = explode(",", $driver_id_auto);
        for ($p = 0; $p < count($activeTrips); $p++) {
            $totalReqPerson = $activeTrips[$p]['iPersonSize'] + $iPersonSize;
            if ($totalReqPerson > $vehiclePesonSize) {
                for ($f = 0; $f < count($newDriverArr); $f++) {
                    if ($newDriverArr[$f] == $activeTrips[$p]['iDriverId']) {
                        unset($newDriverArr[$f]);
                    }
                }
            }
        }
        $driver_id_auto = implode(",", $newDriverArr);
        //Added By Hasmukh On 17-12-2018 For Removed Code Of Check Passenger Seat Size End
    }
    //Added On 06-12-2018 For Get Pool Driver Data End
}

function reGenerateRequestQueryForDriver() {
    global $ePoolStatus, $sql_driver_status_chk, $POOL_ENABLE;

    if ($ePoolStatus == "Yes" && $POOL_ENABLE == "Yes") {
        $sql_driver_status_chk .= " AND ((vAvailability='Not Available' AND (vTripStatus='Active' || vTripStatus='On Going Trip' || vTripStatus='Arrived')) OR vAvailability='Available')";
    } else {
        $sql_driver_status_chk .= " AND vAvailability='Available'";
    }
}

function configureTripForArriveStatus() {
    global $generalobj, $obj, $result, $message_arr, $_REQUEST, $languageLabelsArr;
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    if (isset($result[0]['iTripId']) && $result[0]['iTripId'] != "") {
        // Update Trip Table
        $where1 = " iTripId = '" . $result[0]['iTripId'] . "'";
        $Data_update_trips['tDriverArrivedDate'] = date('Y-m-d H:i:s');
        if ($result[0]['ePoolRide'] == "Yes") {
            $Data_update_trips['iActive'] = 'On Going Trip';
        }
        $id = $obj->MySQLQueryPerform("trips", $Data_update_trips, 'update', $where1);
    }

    if ($result[0]['ePoolRide'] == "Yes") {
        $message_arr = array();
        $message_arr['Message'] = "TripStarted";
        $message_arr['iDriverId'] = $iDriverId;
        $message_arr['iTripId'] = $result[0]['iTripId'];
        $message_arr['driverName'] = $result[0]['driverName'];
        $message_arr['vRideNo'] = $result[0]['vRideNo'];
        $message_arr['VerificationCode'] = $message_arr['iTripDeliveryLocationId'] = "";
        $message_arr['vTitle'] = $languageLabelsArr['LBL_START_TRIP_DIALOG_TXT'];
        $message_arr['eType'] = $result[0]['eType'];
        $message_arr['eSystem'] = "";
        //$message = json_encode($message_arr, JSON_UNESCAPED_UNICODE);
    }
}

function calculateFareForShark($personData) {
    global $generalobj, $obj;
    $ePoolRide = $personData['ePoolRide'];
    $iPersonSize = $personData['iPersonSize'];
    $POOL_ENABLE = $personData['POOL_ENABLE'];
    $finalFare = $personData['finalFare'];
    $total_fare = $personData['total_fare'];
    $fTripGenerateFare = $personData['fTripGenerateFare'];
    $fTripGenerateFare_For_Commission = $personData['fTripGenerateFare_For_Commission'];
    $vehicleTypeID = $personData['vehicleTypeID'];

    $fPoolPercentage = 1;
    $fareDetailArray = array();
    if ($ePoolRide == "Yes" && $POOL_ENABLE == "Yes" && $iPersonSize > 1) {
        $tripPercentage = $obj->MySQLSelect("SELECT fPoolPercentage FROM vehicle_type WHERE iVehicleTypeId='" . $vehicleTypeID . "'");
        if (count($tripPercentage) > 0) {
            $fPoolPercentage = $tripPercentage[0]['fPoolPercentage'];
            $finalFare = ($fPoolPercentage * $fTripGenerateFare) / 100;
            $finalFare = $finalFare * ($iPersonSize - 1);
            $totalFare = ($fPoolPercentage * $total_fare) / 100;
            $totalFare = $totalFare * ($iPersonSize - 1);
        }

        $fTripGenerateFare = $finalFare + $fTripGenerateFare;
        $total_fare = $totalFare + $total_fare;
        $fTripGenerateFare_For_Commission = $finalFare + $fTripGenerateFare_For_Commission;

        $fareDetailArray['fTripGenerateFare'] = $fTripGenerateFare;
        $fareDetailArray['total_fare'] = $total_fare;
        $fareDetailArray['fTripGenerateFare_For_Commission'] = $fTripGenerateFare_For_Commission;
        $fareDetailArray['fPoolPercentage'] = $fPoolPercentage;
        $fareDetailArray['totalFare'] = $totalFare;
    }

    return $fareDetailArray;
}

function GetWalletAmountPool($personData_wallet) {

    $ePoolRide = $personData_wallet['ePoolRide'];
    $iPersonSize = $personData_wallet['iPersonSize'];
    $POOL_ENABLE = $personData_wallet['POOL_ENABLE'];
    $oneSeatCharge = $personData_wallet['oneSeatCharge'];
    $fSurgePriceDiff = $personData_wallet['fSurgePriceDiff'];
    $fAirportPickupSurgeAmount = $personData_wallet['fAirportPickupSurgeAmount'];
    $fAirportDropoffSurgeAmount = $personData_wallet['fAirportDropoffSurgeAmount'];
    //echo $oneSeatCharge."==".$fAirportPickupSurgeAmount."===".$fAirportDropoffSurgeAmount;die;
    $fPoolPercentage = $personData_wallet['fPoolPercentage'];
    $iMinFare = $personData_wallet['iMinFare'];
    $discountValue = $personData_wallet['discountValue'];
    $fTax1 = $personData_wallet['fTax1'];
    $total_fare = $personData_wallet['total_fare'];
    //Added By HJ On 30-12-2018 For Calculate Pool Invoice As Per Discuss WIth QA Start
    if ($ePoolRide == "Yes" && $POOL_ENABLE == "Yes") {
        $totalOneSeatFare = $oneSeatCharge + $fSurgePriceDiff + $fAirportPickupSurgeAmount + $fAirportDropoffSurgeAmount;
        if ($iPersonSize > 1) {
            $twoSeatCharge = ($oneSeatCharge + ($totalOneSeatFare * $fPoolPercentage / 100));
            $iMinFare = $iMinFare * $iPersonSize;
            if ($twoSeatCharge < $iMinFare) {
                $twoSeatCharge = $iMinFare;
            }
            $poolTaxAmount = (($twoSeatCharge - $discountValue) * $fTax1) / 100;

            $wallet_fare = $twoSeatCharge - $discountValue + $poolTaxAmount;
        } else {
            if ($totalOneSeatFare < $iMinFare) {
                $totalOneSeatFare = $iMinFare;
            }
            $poolTaxAmount = (($totalOneSeatFare - $discountValue) * $fTax1) / 100;
            $wallet_fare = $totalOneSeatFare - $discountValue + $poolTaxAmount;
        }
    } else {
        $wallet_fare = $total_fare;
    }
    //Added By HJ On 30-12-2018 For Calculate Pool Invoice As Per Discuss WIth QA End
    return $wallet_fare;
}

########################################

function regenerateQueryForPool($ssql) {
    global $POOL_ENABLE;
    if ($POOL_ENABLE == "No") {
        $ssql .= " AND ePoolStatus = 'No'";
    }
    return $ssql;
}

################## Start Advertisement Finctions and Types ###########################################
if ($type == "getAdvertisementBanners") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger'; //Passenger,Driver
    $adBannerData = getAdvertisementBanners($iUserId, $eMemberType);
    setDataResponse($adBannerData);
}
###########################################################################################################

if ($type == "insertBannereImpressionCount") {
    $iAdvertBannerId = isset($_REQUEST["iAdvertBannerId"]) ? $_REQUEST["iAdvertBannerId"] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger'; //Passenger,Driver,Store

    $returnArr = insertBannereImpressionCount($iAdvertBannerId, $iUserId, $eMemberType);
    setDataResponse($returnArr);
}
################## End Advertisement Functions and Types ###########################################
################## News Types ###########################################
//Added By Hasmukh On 26-12-2018 For Get News and Notification Data Start
if ($type == "getNewsNotification") {
    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger'; //Passenger,Driver,Company
    $eType = isset($_REQUEST["eType"]) ? $_REQUEST["eType"] : 'All'; //News,Notification,All
    $returnArr = getNewsNotification($page, $iMemberId, $eMemberType, $eType);
    setDataResponse($returnArr);
}
//Added By Hasmukh On 26-12-2018 For Get News and Notification Data End
################## News Types ###########################################
#################################################user_profile_master #########################################################
if ($type == "DisplayProfileList") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger'; //Passenger,Driver
    $sql = "SELECT concat(vName,' ',vLastName) as senderName,vEmail,vPhone,vPhoneCode,vLang from  register_user  WHERE iUserId ='" . $iUserId . "'";
    $userdetail = $obj->MySQLSelect($sql);
    $vLang = $userdetail[0]['vLang'];
    $DefaultLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    if ($vLang == "" || $vLang == NULL) {
        $vLang = $DefaultLang;
    }
    //$sql = "SELECT * from  user_profile_master WHERE eStatus ='Active'";
    $sql = "SELECT upm.* from  user_profile_master  as upm WHERE upm.eStatus ='Active' and (select count(org.iOrganizationId) from organization as org where upm.iUserProfileMasterId=org.iUserProfileMasterId) > 0";
    $userprofilemaster = $obj->MySQLSelect($sql);
    if (count($userprofilemaster) > 0) {
        $vTitle = "vTitle_" . $vLang;
        $vSubTitle = "vSubTitle_" . $vLang;
        $vScreenHeading = "vScreenHeading_" . $vLang;
        $vScreenTitle = "vScreenTitle_" . $vLang;
        $tDescription = "tDescription_" . $vLang;
        $vScreenButtonText = "vScreenButtonText_" . $vLang;
        $vProfileName = "vProfileName_" . $vLang;
        $vShortProfileName = "vShortProfileName_" . $vLang;
        for ($i = 0; $i < count($userprofilemaster); $i++) {
            $vTitleArr = json_decode($userprofilemaster[$i]['vTitle'], true);
            if (trim($vTitleArr[$vTitle]) == "") {
                $vTitle = "vTitle_" . $DefaultLang;
            }
            $userprofilemaster[$i]['vTitle'] = $vTitleArr[$vTitle];
            $vSubTitleArr = json_decode($userprofilemaster[$i]['vSubTitle'], true);
            if (trim($vSubTitleArr[$vSubTitle]) == "") {
                $vSubTitle = "vSubTitle_" . $DefaultLang;
            }
            $userprofilemaster[$i]['vSubTitle'] = $vSubTitleArr[$vSubTitle];
            $vScreenHeadingArr = json_decode($userprofilemaster[$i]['vScreenHeading'], true);
            if (trim($vScreenHeadingArr[$vScreenHeading]) == "") {
                $vScreenHeading = "vScreenHeading_" . $DefaultLang;
            }
            $userprofilemaster[$i]['vScreenHeading'] = $vScreenHeadingArr[$vScreenHeading];
            $vScreenTitleArr = json_decode($userprofilemaster[$i]['vScreenTitle'], true);
            if (trim($vScreenTitleArr[$vScreenTitle]) == "") {
                $vScreenTitle = "vScreenTitle_" . $DefaultLang;
            }
            $userprofilemaster[$i]['vScreenTitle'] = $vScreenTitleArr[$vScreenTitle];
            $tDescriptionArr = json_decode($userprofilemaster[$i]['tDescription'], true);
            if (trim($tDescriptionArr[$tDescription]) == "") {
                $tDescription = "tDescription_" . $DefaultLang;
            }
            $userprofilemaster[$i]['tDescription'] = $tDescriptionArr[$tDescription];
            $vScreenButtonTextArr = json_decode($userprofilemaster[$i]['vScreenButtonText'], true);
            if (trim($vScreenButtonTextArr[$vScreenButtonText]) == "") {
                $vScreenButtonText = "vScreenButtonText_" . $DefaultLang;
            }
            $userprofilemaster[$i]['vScreenButtonText'] = $vScreenButtonTextArr[$vScreenButtonText];
            $vProfileNameArr = json_decode($userprofilemaster[$i]['vProfileName'], true);
            if (trim($vProfileNameArr[$vProfileName]) == "") {
                $vProfileName = "vProfileName_" . $DefaultLang;
            }
            $userprofilemaster[$i]['vProfileName'] = $vProfileNameArr[$vProfileName];
            $vShortProfileNameArr = json_decode($userprofilemaster[$i]['vShortProfileName'], true);
            if (trim($vShortProfileNameArr[$vShortProfileName]) == "") {
                $vShortProfileName = "vShortProfileName_" . $DefaultLang;
            }
            $userprofilemaster[$i]['vShortProfileName'] = $vShortProfileNameArr[$vShortProfileName];
            $Photo_Gallery_folder = $tconfig["tsite_upload_profile_master_path"] . '/' . $userprofilemaster[$i]['vImage'];
            if ($userprofilemaster[$i]['vImage'] != "" && file_exists($Photo_Gallery_folder)) {
                $userprofilemaster[$i]['vImage'] = $tconfig["tsite_upload_images_profile_master"] . "/" . $userprofilemaster[$i]['vImage'];
            } else {
                $userprofilemaster[$i]['vImage'] = $tconfig["tsite_upload_images_profile_master"] . "/defaulticon.png";
            }
            $Photo_Gallery_folder1 = $tconfig["tsite_upload_profile_master_path"] . '/' . $userprofilemaster[$i]['vWelcomeImage'];
            if ($userprofilemaster[$i]['vWelcomeImage'] != "" && file_exists($Photo_Gallery_folder1)) {
                $userprofilemaster[$i]['vWelcomeImage'] = $tconfig["tsite_upload_images_profile_master"] . "/" . $userprofilemaster[$i]['vWelcomeImage'];
            } else {
                $userprofilemaster[$i]['vWelcomeImage'] = $tconfig["tsite_upload_images_profile_master"] . "/defaultimage.png";
            }
            $userprofilemaster[$i]['eProfileAdded'] = "No";
            $userprofilemaster[$i]['iUserProfileId'] = "";
            $userprofilemaster[$i]['ProfileStatus'] = "";
            $userprofilemaster[$i]['vProfileEmail'] = "";
            $userprofilemaster[$i]['vCompany'] = "";
            $userprofilemaster[$i]['iOrganizationId'] = "";
            $sql = "SELECT up.*, upm.vProfileName, upm.vTitle, upm.vSubTitle, org.vCompany FROM user_profile as up 
                LEFT JOIN user_profile_master as upm ON up.iUserProfileMasterId=upm.iUserProfileMasterId
                LEFT JOIN organization as org ON up.iOrganizationId=org.iOrganizationId
                where upm.eStatus = 'Active' AND up.iUserId = '" . $iUserId . "' AND up.eStatus != 'Deleted' AND up.iUserProfileMasterId = '" . $userprofilemaster[$i]['iUserProfileMasterId'] . "'";
            //$sql = "SELECT * from  user_profile WHERE iUserId ='".$iUserId."' AND iUserProfileMasterId = '".$userprofilemaster[$i]['iUserProfileMasterId']."' AND eStatus != 'Deleted'";
            $userprofilelist = $obj->MySQLSelect($sql);
            if (count($userprofilelist) > 0) {
                $userprofilemaster[$i]['eProfileAdded'] = "Yes";
                $userprofilemaster[$i]['iUserProfileId'] = $userprofilelist[0]['iUserProfileId'];
                $userprofilemaster[$i]['ProfileStatus'] = $userprofilelist[0]['eStatus'];
                $userprofilemaster[$i]['vProfileEmail'] = $userprofilelist[0]['vProfileEmail'];
                $userprofilemaster[$i]['vCompany'] = $userprofilelist[0]['vCompany'];
                $userprofilemaster[$i]['iOrganizationId'] = $userprofilelist[0]['iOrganizationId'];
            }
        }
        $returnArr['Action'] = "1";
        $returnArr['message'] = $userprofilemaster;
        $returnArr['TotalCount'] = count($userprofilemaster);
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_DATA_AVAIL";
    }

    setDataResponse($returnArr);
}
#################################################user_profile_master #########################################################
#################################################Display Organization List #########################################################
if ($type == "DisplayOrganizationList") {
    $iUserProfileMasterId = isset($_REQUEST["iUserProfileMasterId"]) ? $_REQUEST["iUserProfileMasterId"] : '';

    $sql = "SELECT * from  organization WHERE iUserProfileMasterId ='" . $iUserProfileMasterId . "' AND eStatus = 'Active'";
    $orglist = $obj->MySQLSelect($sql);
    if (count($orglist) > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $orglist;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_DATA_AVAIL";
    }

    setDataResponse($returnArr);
}
#################################################Display Organization List #########################################################
#################################################Update User Organization Profile ##################################################
if ($type == "UpdateUserOrganizationProfile") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger'; //Passenger,Driver
    $iUserProfileId = isset($_REQUEST["iUserProfileId"]) ? $_REQUEST["iUserProfileId"] : '';
    $vProfileEmail = isset($_REQUEST["vProfileEmail"]) ? $_REQUEST["vProfileEmail"] : '';
    $iUserProfileMasterId = isset($_REQUEST["iUserProfileMasterId"]) ? $_REQUEST["iUserProfileMasterId"] : '';
    $iOrganizationId = isset($_REQUEST["iOrganizationId"]) ? $_REQUEST["iOrganizationId"] : '';
    $eStatus = isset($_REQUEST["eStatus"]) ? $_REQUEST["eStatus"] : ''; // Deleted
    if ($iUserProfileId == "" || $iUserProfileId == NULL) {
        $iUserProfileId = 0;
    }
    $Data_Update['iUserId'] = $iUserId;
    $Data_Update['iUserProfileMasterId'] = $iUserProfileMasterId;
    $Data_Update['iOrganizationId'] = $iOrganizationId;
    $Data_Update['vProfileEmail'] = $vProfileEmail;
    $Action = ($iUserProfileId != 0) ? 'Edit' : 'Add';
    ## Checking Of Trip Running ##
    if ($Action == "Edit") {
        $sqldata = "SELECT iTripId,eType FROM `trips` WHERE ( iActive='On Going Trip' OR iActive='Active' ) AND iUserId='" . $iUserId . "' AND iUserProfileId = '" . $iUserProfileId . "'";
        $TripData = $obj->MySQLSelect($sqldata);
        if (count($TripData) > 0) {
            $TripType = $TripData[0]['eType'];
            if ($TripType == "Ride") {
                $vLabel = "LBL_PROFILE_SELECTED_ON_RIDE";
            } else {
                $vLabel = "LBL_PROFILE_SELECTED_ON_DELIVERY";
            }
            $returnArr['Action'] = "0";
            $returnArr['message'] = $vLabel;
            setDataResponse($returnArr);
        }
    }
    ## Checking Of Trip Running ##
    if ($Action == "Add") {
        $profileStatus = "Pending";
        if (SITE_TYPE == "DEMO") {
            $profileStatus = "Active";
        }
        $Data_Update['eStatus'] = $profileStatus;
        $id = $obj->MySQLQueryPerform("user_profile", $Data_Update, 'insert');
    } else {
        if ($eStatus != "") {
            $Data_Update['eStatus'] = $eStatus;
        }
        $where = " iUserProfileId = '" . $iUserProfileId . "'";
        $id = $obj->MySQLQueryPerform("user_profile", $Data_Update, 'update', $where);
    }
    if ($id > 0) {
        $returnArr['Action'] = "1";
        if ($Action == 'Edit') {
            $labeltxt = ($eStatus == "Deleted") ? "LBL_PROFILE_DELETE_SUCCESS" : "LBL_PROFILE_UPDATE_SUCCESS";
        } else {
            $labeltxt = "LBL_PROFILE_ADD_SUCCESS_NOTE";
        }
        $returnArr['message'] = $labeltxt;
        ## Send Email To Organization ##
        if ($Action == "Add") {
            $sql = "SELECT CONCAT(vPhoneCode,' ',vPhone) AS Phone, CONCAT(vName,' ',vLastName) AS Name from register_user WHERE iUserId = '" . $iUserId . "'";
            $user_result = $obj->MySQLSelect($sql);
            $sql = "SELECT vCompany,vEmail FROM `organization` WHERE iOrganizationId = '" . $iOrganizationId . "' ";
            $result = $obj->MySQLSelect($sql);
            $vCompany = $result[0]['vCompany'];
            $Company_Email = $result[0]['vEmail'];
            $maildata['vCompany'] = $vCompany;
            $maildata['Company_Email'] = $Company_Email;
            $maildata['User_Email'] = $vProfileEmail;
            $maildata['User_Name'] = $user_result[0]['Name'];
            $maildata['User_Phone'] = $user_result[0]['Phone'];
            $mail = $generalobj->send_email_user('USER_REGISTRATION_ORGANIZATION', $maildata);
        }
        ## Send Email To Organization ##
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    setDataResponse($returnArr);
}
#################################################Update User Organization Profile ##################################################
#################################################Display User's Organization Profile ##################################################
if ($type == "DisplayUserOrganizationProfile") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger'; //Passenger,Driver
    $iUserProfileId = isset($_REQUEST["iUserProfileId"]) ? $_REQUEST["iUserProfileId"] : '';
    $sql = "SELECT concat(vName,' ',vLastName) as senderName,vEmail,vPhone,vPhoneCode,vLang from  register_user  WHERE iUserId ='" . $iUserId . "'";
    $userdetail = $obj->MySQLSelect($sql);
    $vLang = $userdetail[0]['vLang'];
    if ($vLang == "" || $vLang == NULL) {
        $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $ssql = "";
    if ($iUserProfileId != "") {
        $ssql .= " AND up.iUserProfileId = '" . $iUserProfileId . "'";
    }
    $sql1 = "SELECT up.*, upm.vProfileName, upm.vShortProfileName, upm.vImage, org.vCompany,org.ePaymentBy FROM user_profile as up 
                LEFT JOIN user_profile_master as upm ON up.iUserProfileMasterId=upm.iUserProfileMasterId
                LEFT JOIN organization as org ON up.iOrganizationId=org.iOrganizationId
                where upm.eStatus = 'Active' AND up.eStatus = 'Active' AND org.eStatus = 'Active' AND up.iUserId = '" . $iUserId . "'" . $ssql;
    $db_data = $obj->MySQLSelect($sql1);
    if (count($db_data) > 0) {
        $vProfileName = "vProfileName_" . $vLang;
        $vShortProfileName = "vShortProfileName_" . $vLang;
        for ($i = 0; $i < count($db_data); $i++) {
            $vProfileNameArr = json_decode($db_data[$i]['vProfileName'], true);
            $db_data[$i]['vProfileName'] = $vProfileNameArr[$vProfileName];
            $vShortProfileNameArr = json_decode($db_data[$i]['vShortProfileName'], true);
            $db_data[$i]['vShortProfileName'] = $vShortProfileNameArr[$vShortProfileName];
            $Photo_Gallery_folder = $tconfig["tsite_upload_profile_master_path"] . '/' . $db_data[$i]['vImage'];
            if ($db_data[$i]['vImage'] != "" && file_exists($Photo_Gallery_folder)) {
                $db_data[$i]['vImage'] = $tconfig["tsite_upload_images_profile_master"] . "/" . $db_data[$i]['vImage'];
            } else {
                $db_data[$i]['vImage'] = $tconfig["tsite_upload_images_profile_master"] . "/defaulticon.png";
            }
            ## Trip Reasons ##
            $tripreasons = array();
            $sql = "SELECT iTripReasonId,vReasonTitle from  trip_reason  WHERE iUserProfileMasterId ='" . $db_data[$i]['iUserProfileMasterId'] . "' AND eStatus='Active'";
            $tripreasons = $obj->MySQLSelect($sql);
            if (count($tripreasons) > 0) {
                $vReasonTitle = "vReasonTitle_" . $vLang;
                for ($j = 0; $j < count($tripreasons); $j++) {
                    $vReasonTitleArr = json_decode($tripreasons[$j]['vReasonTitle'], true);
                    $tripreasons[$j]['vReasonTitle'] = $vReasonTitleArr[$vReasonTitle];
                }
            }
            $db_data[$i]['tripreasons'] = $tripreasons;
            ## Trip Reasons ##
        }
        $returnArr['Action'] = "1";
        $returnArr['message'] = $db_data;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_DATA_AVAIL";
    }

    setDataResponse($returnArr);
}
#################################################Display User's Organization Profile ##################################################
##############################Added By HJ On 22-03-2019 For Send SMS of Book For Someelse Start#####################################

function sendSMSBookForSomeOneElse($vTripPaymentMode, $bokingData, $iTripId, $passangerData, $VehicleData, $DriverVehicleMake, $DriverVehicleModel, $Data_vehicle) {
    global $generalobj, $POOL_ENABLE, $PACKAGE_TYPE, $BOOK_FOR_ELSE_ENABLE, $SITE_ISD_CODE, $vLangCode,$languageLabelsArr;
    if (isset($bokingData[0]['vBookSomeOneNumber']) && $bokingData[0]['vBookSomeOneNumber'] != "" && strtoupper($PACKAGE_TYPE) == "SHARK") {
        $eBookForSomeOneElse = $bokingData[0]['eBookForSomeOneElse'];
        $vBookSomeOneName = $bokingData[0]['vBookSomeOneName'];
        $vBookSomeOneNumber = $bokingData[0]['vBookSomeOneNumber'];
        if ($eBookForSomeOneElse != "Yes" && $BOOK_FOR_ELSE_ENABLE != "Yes") {
            return;
        }
        $bookerName = "Someone else";
        if (count($passangerData) > 0) {
            $bookerName = $passangerData[0]['bookerName'];
        }
        $otpNumber = mt_rand(100000, 999999);
        $isdCode = $SITE_ISD_CODE;
        $maildata1 = array();
        // Tracking url
        $liveTrackingUrl = getTrackingUrl($iTripId);
        $maildata1['VEHICLE_TYPE'] = $VehicleData[0]['vVehicleType'];
        $maildata1['CAR_NUMBER'] = "(" . $DriverVehicleMake . " " . $DriverVehicleModel . " - ".$Data_vehicle[0]['DriverVehicleLicencePlate'].")";
        $maildata1['DRIVER_NAME'] = $Data_vehicle[0]['vName'] . " " . $Data_vehicle[0]['vLastName'];
        $maildata1['DRIVER_NUMBER'] = "(+" . $Data_vehicle[0]['vCode'] . " " . $Data_vehicle[0]['vPhone'] . ")";
        $maildata1['BOOKER_NAME'] = $bookerName;
        $maildata1['BOOK_OTP'] = $otpNumber;
        $maildata1['PAYMENT_MODE'] = ($vTripPaymentMode == "Cash") ? $languageLabelsArr['LBL_CASH_TXT']: $languageLabelsArr['LBL_CARD'];
        $maildata1['LIVE_TRACKING_URL'] = $liveTrackingUrl;
        
        $flyStationData = get_value('trips', 'iFromStationId, iToStationId', 'iTripId', $iTripId);
        
        if(!empty($flyStationData[0]['iFromStationId']) && !empty($flyStationData[0]['iToStationId'])) {
		$message_layout = $generalobj->send_messages_user("BOOK_FOR_SOMEONE_ELSE_SMS_FLY", $maildata1, "", $vLangCode);
        } else {
		$message_layout = $generalobj->send_messages_user("BOOK_FOR_SOMEONE_ELSE_SMS", $maildata1, "", $vLangCode);
        }

        $phonecode = $passangerData[0]['vPhoneCode'];

        $result = $generalobj->sendSystemSms($vBookSomeOneNumber, $phonecode, $message_layout); //added by SP for sms function change on 12-07-2019

        /* $phoneEBook = preg_replace("/[^0-9]/", "", $vBookSomeOneNumber);
          $vBookSomeOneNumber = "+" . $phoneEBook;
          if (strpos($phoneEBook, '+') !== false) {
          $vBookSomeOneNumber = $phoneEBook;
          }
          echo $vBookSomeOneNumber."aaaaa".$message_layout;exit;
          $result = sendEmeSms($vBookSomeOneNumber, $message_layout);
          if ($result == 0) {
          $receiverMobile3 = "+" . $vPhoneCode . $phoneEBook;
          if (strpos($phoneEBook, '+') !== false) {
          $receiverMobile3 = $vPhoneCode . $phoneEBook;
          }
          $result1 = sendEmeSms($receiverMobile3, $message_layout);
          if ($result1 == 0) {
          $phoneEBook = preg_replace("/[^0-9]/", "", $vBookSomeOneNumber);
          $vBookSomeOneNumber = "+" . $isdCode . $phoneEBook;
          if (strpos($phoneEBook, '+') !== false) {
          $vBookSomeOneNumber = $isdCode . $phoneEBook;
          }
          $result = sendEmeSms($vBookSomeOneNumber, $message_layout);
          }
          } */
    }
}

##############################Added By HJ On 22-03-2019 For Send SMS of Book For Someelse End#####################################
?>
