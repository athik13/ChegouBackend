<?php
include_once('common.php');
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
include_once ('include_generalFunctions_dl.php');
$vLang = "EN";
if (isset($_SESSION['sess_lang'])) {
    $vLang = $_SESSION['sess_lang'];
}
$fromOrder = "guest";
if (isset($_REQUEST['order']) && $_REQUEST['order'] != "") {
    $fromOrder = $_REQUEST['order'];
}
$Data = array();
$iServiceId = 1;
$checkUser = check_user_mr();
$checkFavStore = checkFavStoreModule();
$orderUserIdSession = "MANUAL_ORDER_USERID_" . strtoupper($fromOrder);
$orderAddressIdSession = "MANUAL_ORDER_ADDRESSID_" . strtoupper($fromOrder);
$orderAddressSession = "MANUAL_ORDER_ADDRESS_" . strtoupper($fromOrder);
$orderLatitudeSession = "MANUAL_ORDER_LATITUDE_" . strtoupper($fromOrder);
$orderLongitudeSession = "MANUAL_ORDER_LONGITUDE_" . strtoupper($fromOrder);
$orderServiceSession = "MAUAL_ORDER_SERVICE_" . strtoupper($fromOrder);

$vLatitude = $vLongitude = $iUserId = $iUserAddressId = $fulladdress = $vServiceAddress1 = $vServiceAddress = "";
if (isset($_SESSION[$orderUserIdSession])) {
    $iUserId = $_SESSION[$orderUserIdSession];
}
if (isset($_SESSION[$orderAddressIdSession])) {
    $iUserAddressId = $_SESSION[$orderAddressIdSession];
}
if (isset($_SESSION[$orderAddressSession])) {
    $vServiceAddress = $fulladdress = $_SESSION[$orderAddressSession];
}
if (isset($_SESSION[$orderLatitudeSession])) {
    $vLatitude = $_SESSION[$orderLatitudeSession];
}
if (isset($_SESSION[$orderLongitudeSession])) {
    $vLongitude = $_SESSION[$orderLongitudeSession];
}
//echo "<pre>";print_r($_SESSION);die;
if (isset($_SESSION[$orderServiceSession]) && !empty($_SESSION[$orderServiceSession])) {
    $iServiceId = $_SESSION[$orderServiceSession];
    global $intervalmins;
    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
    $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
    //$LIST_RESTAURANT_LIMIT_BY_DISTANCE = $generalobj->getConfigurations("configurations", "LIST_RESTAURANT_LIMIT_BY_DISTANCE");
    //$DRIVER_REQUEST_METHOD = $generalobj->getConfigurations("configurations", "DRIVER_REQUEST_METHOD");
    $param = ($DRIVER_REQUEST_METHOD == "Time") ? "tOnline" : "tLastOnline";
    //print_r($_SESSION);die;
    if (!empty($iUserId) && empty($vLongitude) && empty($vLatitude) && !empty($iUserAddressId)) {
        $Dataua = $obj->MySQLSelect("SELECT *  FROM `user_address`  WHERE iUserAddressId = '" . $iUserAddressId . "' AND iUserId = '" . $iUserId . "'");
        if (count($Dataua) > 0) {
            $vServiceAddress = ucfirst($Dataua[0]['vServiceAddress']);
            $vServiceAddress1 = $Dataua[0]['vServiceAddress'];
            $vBuildingNo = $Dataua[0]['vBuildingNo'];
            $vLandmark = $Dataua[0]['vLandmark'];
            $vAddressType = $Dataua[0]['vAddressType'];
            $vLatitude = $Dataua[0]['vLatitude'];
            $vLongitude = $Dataua[0]['vLongitude'];
            $vTimeZone = $Dataua[0]['vTimeZone'];
        }
        $a = $b = '';
        if ($vBuildingNo != '') {
            $a = ucfirst($vBuildingNo) . ", ";
        }
        if ($vLandmark != '') {
            $b = ucfirst($vLandmark) . ", ";
        }
        $fulladdress = $a . "" . $b . "" . $vServiceAddress;
    }
    $sourceLocationArr = array($vLatitude, $vLongitude);
    $iToLocationId = GetUserGeoLocationId($sourceLocationArr);
    //$allowed_ans = checkAllowedAreaNew($sourceLocationArr, "No");
    $ssql = $ssql_fav_q = "";
    $searchid = isset($_POST['searchid']) ? $_POST['searchid'] : '';
    $cuisine = isset($_POST['cuisine']) ? $_POST['cuisine'] : '';
    $eFavStore = isset($_POST['eFavStore']) ? $_POST['eFavStore'] : '';
    if ((strtolower($checkUser) == 'rider' || strtolower($checkUser) == "user") && $checkFavStore == 1 && !empty($iUserId)) {
        include_once "include/features/include_fav_store.php";
        $ssql_fav_q = getFavSelectQuery('', $iUserId);
    }
    $having_ssql = "";
    if (SITE_TYPE == "Demo" && $searchword == "") {
        $vAddress = $vServiceAddress1;
        // $ResCountry = ($vUserDeviceCountry == "IN")?"('IN')":"('IN','".$vUserDeviceCountry."')";
        // $ssql .=  "AND ( eDemoDisplay = 'Yes' OR eLock = 'No' )";
        $having_ssql .= " OR company.eDemoDisplay = 'Yes'";
        if ($vAddress != "") {
            //$ssql .= " AND ( company.vRestuarantLocation like '%$vAddress%' OR company.vRestuarantLocation like '%India%' OR company.eDemoDisplay = 'Yes')";
        } else {
            //$ssql .= " AND ( company.vRestuarantLocation like '%India%' OR company.eDemoDisplay = 'Yes')";
        }
    }
    if ($searchid != "") {
        $ssql .= " AND ( company.vCompany like '%$searchid%')";
        $sql = "SELECT  DISTINCT (company.iCompanyId),ROUND(( 6371 * acos( cos( radians(" . $vLatitude . ") ) 
		* cos( radians( vRestuarantLocationLat ) ) 
			* cos( radians( vRestuarantLocationLong ) - radians(" . $vLongitude . ") ) 
			+ sin( radians(" . $vLatitude . ") ) 
			* sin( radians( vRestuarantLocationLat ) ) ) ),2) AS distance, company.* " . $ssql_fav_q . "  FROM `company`  
			WHERE vRestuarantLocationLat != '' AND vRestuarantLocationLong != '' AND company.eStatus='Active' AND eSystem = 'DeliverAll' AND company.iServiceId = '" . $iServiceId . "' $ssql
			HAVING (distance < " . $LIST_RESTAURANT_LIMIT_BY_DISTANCE . $having_ssql . ") ORDER BY `company`.`iCompanyId` ASC";
    } else if ($cuisine != "") {
        $ssql .= " AND (cu.cuisineName_" . $vLang . " like '%$cuisine%' AND cu.eStatus = 'Active')";
        $sql = "SELECT DISTINCT (company.iCompanyId),ROUND(( 6371 * acos( cos( radians(" . $vLatitude . ") ) 
		* cos( radians( vRestuarantLocationLat ) ) 
			* cos( radians( vRestuarantLocationLong ) - radians(" . $vLongitude . ") ) 
			+ sin( radians(" . $vLatitude . ") ) 
			* sin( radians( vRestuarantLocationLat ) ) ) ),2) AS distance, company.* , cu.* " . $ssql_fav_q . " FROM `company` LEFT JOIN company_cuisine as ccu ON ccu.iCompanyId=company.iCompanyId LEFT JOIN cuisine as cu ON ccu.cuisineId=cu.cuisineId
			WHERE vRestuarantLocationLat != '' AND vRestuarantLocationLong != '' AND company.eStatus='Active' AND eSystem = 'DeliverAll' AND company.iServiceId = '" . $iServiceId . "' $ssql
			HAVING (distance < " . $LIST_RESTAURANT_LIMIT_BY_DISTANCE . $having_ssql . ") ORDER BY `company`.`iCompanyId` ASC";
    } else if ($eFavStore != "") {
        if ((strtolower($checkUser) == 'rider' || strtolower($checkUser) == "user") && $checkFavStore == 1) {

            $sql = "SELECT  ROUND(( 6371 * acos( cos( radians(" . $vLatitude . ") ) 
		* cos( radians( vRestuarantLocationLat ) ) 
			* cos( radians( vRestuarantLocationLong ) - radians(" . $vLongitude . ") ) 
			+ sin( radians(" . $vLatitude . ") ) 
			* sin( radians( vRestuarantLocationLat ) ) ) ),2) AS distance, company.* " . $ssql_fav_q . "  FROM `company`  
			WHERE vRestuarantLocationLat != '' AND vRestuarantLocationLong != '' AND company.eStatus='Active' AND eSystem = 'DeliverAll' AND company.iServiceId = '" . $iServiceId . "' $ssql
			HAVING (distance < " . $LIST_RESTAURANT_LIMIT_BY_DISTANCE . $having_ssql . ") ORDER BY `company`.`iCompanyId` ASC";
            $filterquery = getFavFilterCondition($sql);
            if (isset($filterquery) && !empty($filterquery)) {
                $sql = $filterquery;
            } else {
                $sql = "SELECT  DISTINCT (company.iCompanyId),ROUND(( 6371 * acos( cos( radians(" . $vLatitude . ") ) 
    		    * cos( radians( vRestuarantLocationLat ) ) 
    			* cos( radians( vRestuarantLocationLong ) - radians(" . $vLongitude . ") ) 
    			+ sin( radians(" . $vLatitude . ") ) 
    			* sin( radians( vRestuarantLocationLat ) ) ) ),2) AS distance, company.* " . $ssql_fav_q . "  FROM `company`  
    			WHERE vRestuarantLocationLat != '' AND vRestuarantLocationLong != '' AND company.eStatus='Active' AND eSystem = 'DeliverAll' AND company.iServiceId = '" . $iServiceId . "' $ssql
    			HAVING (distance < " . $LIST_RESTAURANT_LIMIT_BY_DISTANCE . $having_ssql . ") ORDER BY `company`.`iCompanyId` ASC";
            }
        }
    } else {

        $sql = "SELECT DISTINCT (company.iCompanyId),ROUND(( 6371 * acos( cos( radians(" . $vLatitude . ") ) 
		* cos( radians( vRestuarantLocationLat ) ) 
			* cos( radians( vRestuarantLocationLong ) - radians(" . $vLongitude . ") ) 
			+ sin( radians(" . $vLatitude . ") ) 
			* sin( radians( vRestuarantLocationLat ) ) ) ),2) AS distance, company.* " . $ssql_fav_q . "   FROM `company` 
			WHERE vRestuarantLocationLat != '' AND vRestuarantLocationLong != '' AND company.eStatus='Active' AND eSystem = 'DeliverAll' AND company.iServiceId = '" . $iServiceId . "' $ssql  
			HAVING (distance < " . $LIST_RESTAURANT_LIMIT_BY_DISTANCE . $having_ssql . ") ORDER BY `company`.`iCompanyId` ASC";
    }
    $Data = $obj->MySQLSelect($sql);
}

$languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
$noOfferTxt = $languageLabelsArr['LBL_NO_OFFER_TXT'];
$companyname = array();
$tsite_url = $tconfig['tsite_url'];
echo '<input type="hidden" id="totalstorecount" value="' . count($Data) . ' ' . $languageLabelsArr['LBL_RESTAURANTS'] . '">';
if (count($Data) > 0 && !empty($Data)) {
    $storeIdArr = array();
    for ($c = 0; $c < count($Data); $c++) {
        $storeIdArr[] = $Data[$c]['iCompanyId'];
    }
    //print_r($storeIdArr);die;
    $storeDetails = getStoreDetails($storeIdArr, $iUserId, $iToLocationId, $languageLabelsArr);
    $currencySymbol = "$";
    if (isset($storeDetails['currencySymbol']) && $storeDetails['currencySymbol'] != "") {
        $currencySymbol = $storeDetails['currencySymbol'];
    }
    //print_R($storeDetails);die;
    for ($i = 0; $i < count($Data); $i++) {
        $fDeliverytime = 0;
        $iCompanyId = $Data[$i]['iCompanyId'];
        $iServiceId = $Data[$i]['iServiceId'];
        //$iToLocationId = GetUserGeoLocationId($sourceLocationArr);
        array_push($companyname, $iCompanyId);
        $vAvgRating = $Data[$i]['vAvgRating'];
        $Data[$i]['vAvgRating'] = ($vAvgRating > 0) ? number_format($Data[$i]['vAvgRating'], 1) : 0;
        $Data[$i]['vAvgRatingOrig'] = $Data[$i]['vAvgRating'];
        $Data[$i]['vCompany'] = ucfirst($Data[$i]['vCompany']);
        //$CompanyDetailsArr = getCompanyDetails($Data[$i]['iCompanyId'], $iUserId, "No", "", $iServiceId, $vLang);
        //$restaurant_status_arr = calculate_restaurant_time_span($Data[$i]['iCompanyId'], $iUserId);
        $Data[$i]['Restaurant_Cuisine'] = "";
        $Restaurant_OfferMessage_short = $Restaurant_OfferMessage = $noOfferTxt;
        $Data[$i]['Restaurant_OrderPrepareTime'] = "0 mins";
        $Data[$i]['restaurantstatus'] = $restaurantstatus = "Closed";
        if (isset($storeDetails['companyCuisineArr'][$iCompanyId])) {
            $Data[$i]['Restaurant_Cuisine'] = implode(", ", $storeDetails['companyCuisineArr'][$iCompanyId]);
        }
        if (isset($storeDetails[$iCompanyId]['Restaurant_OrderPrepareTime'])) {
            $Data[$i]['Restaurant_OrderPrepareTime'] = $storeDetails[$iCompanyId]['Restaurant_OrderPrepareTime'];
        }
        $Data[$i]['Restaurant_OrderPrepareTime'] = str_replace('mins', '<img src="' . $tsite_url . 'assets/img/custome-store/delivery_time.png" class="delivery_time_ico" alt=' . $Data[$i]['Restaurant_OrderPrepareTime'] . '><br>mins', $Data[$i]['Restaurant_OrderPrepareTime']);
        if (isset($storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage_short'])) {
            $Restaurant_OfferMessage_short = $storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage_short'];
        }
        if (isset($storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage'])) {
            $Restaurant_OfferMessage = $storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage'];
        }
        if ($Restaurant_OfferMessage_short == "") {
            $Restaurant_OfferMessage_short = $Restaurant_OfferMessage = $noOfferTxt;
        }
        $Data[$i]['Restaurant_OfferMessage_short'] = $Restaurant_OfferMessage_short;
        $Data[$i]['Restaurant_OfferMessage'] = $Restaurant_OfferMessage;
        if (isset($storeDetails['restaurantStatusArr'][$iCompanyId]['status'])) {
            $Data[$i]['restaurantstatus'] = $restaurantstatus = $storeDetails['restaurantStatusArr'][$iCompanyId]['status'];
        }
        //echo $restaurantstatus."<br>";
        $restOpenTime = $restCloseTime = "";
        $timeSlotAvailable = "No";
        if (isset($storeDetails['restaurantStatusArr'][$iCompanyId]['opentime'])) {
            $restOpenTime = $storeDetails['restaurantStatusArr'][$iCompanyId]['opentime'];
        }
        if (isset($storeDetails['restaurantStatusArr'][$iCompanyId]['closetime'])) {
            $restCloseTime = $storeDetails['restaurantStatusArr'][$iCompanyId]['closetime'];
        }
        if (isset($storeDetails['restaurantStatusArr'][$iCompanyId]['timeslotavailable'])) {
            $timeSlotAvailable = $storeDetails['restaurantStatusArr'][$iCompanyId]['timeslotavailable'];
        }
        //echo "<pre>";print_r($storeDetails);die;
        $restPricePerPerson = $restMinOrdValue = 1;
        if (isset($storeDetails['restaurantPricePerPerson'][$iCompanyId])) {
            $restPricePerPerson = $storeDetails['restaurantPricePerPerson'][$iCompanyId];
        }
        $Data[$i]['Restaurant_fPricePerPer'] = $currencySymbol . $restPricePerPerson . ' <div>' . ucfirst(strtolower($languageLabelsArr['LBL_PER_PERSON_TXT'])) . "</div>";
        if (isset($storeDetails['restaurantMinOrdValue'][$iCompanyId])) {
            $restMinOrdValue = $storeDetails['restaurantMinOrdValue'][$iCompanyId];
        }
        $Data[$i]['Restaurant_fMinOrderValue'] = $currencySymbol . $restMinOrdValue . " <div>" . ucfirst(strtolower($languageLabelsArr['LBL_MIN_SMALL'])) . ". " . ucfirst(strtolower($languageLabelsArr['LBL_ORDER'])) . "</div>";
        $Data[$i]['Restaurant_Opentime'] = $restOpenTime;
        $Data[$i]['Restaurant_Closetime'] = $restCloseTime;
        $Data[$i]['timeslotavailable'] = $timeSlotAvailable;
        if (isset($Data[$i]['Restaurant_Opentime']) && !empty($Data[$i]['Restaurant_Opentime'])) {
            $Data[$i]['Restaurant_Open_And_Close_time'] = $languageLabelsArr['LBL_CLOSED_TXT'] . ' ' . $Data[$i]['Restaurant_Opentime'];
        } else {
            $Data[$i]['Restaurant_Open_And_Close_time'] = $languageLabelsArr['LBL_CLOSED_TXT'];
        }
        if (isset($Data[$i]['timeslotavailable']) && !empty($Data[$i]['timeslotavailable']) && $Data[$i]['timeslotavailable'] == 'Yes') {
            $Data[$i]['Restaurant_Open_And_Close_time'] = $languageLabelsArr['LBL_NOT_ACCEPT_ORDERS_TXT'];
        }
        if ($Data[$i]['vImage'] != "") {
            $Data[$i]['vImage'] = $tconfig['tsite_upload_images_compnay'] . '/' . $Data[$i]['iCompanyId'] . '/3_' . $Data[$i]['vImage'];
        } else {
            $Data[$i]['vImage'] = $tsite_url . 'assets/img/burger.jpg';
        }
        if ($Data[$i]['vCoverImage'] != "") {
            $Data[$i]['vCoverImage'] = $tconfig['tsite_upload_images_compnay'] . '/' . $Data[$i]['iCompanyId'] . '/' . $Data[$i]['vCoverImage'];
        }
        //Added By HJ On 26-06-2019 For Get And Display Store Demo Image Start
        if (isset($storeDetails['storeDemoImageArr'][$iCompanyId]) && $storeDetails['storeDemoImageArr'][$iCompanyId] != "" && SITE_TYPE == "Demo") {
            $demoImgPath = $tconfig['tsite_upload_demo_compnay_doc_path'] . $storeDetails['storeDemoImageArr'][$iCompanyId];
            if (file_exists($demoImgPath)) {
                $demoImgUrl = $tconfig['tsite_upload_demo_compnay_doc'] . $storeDetails['storeDemoImageArr'][$iCompanyId];
                $Data[$i]['vImage'] = $demoImgUrl;
            }
        }
        //Added By HJ On 26-06-2019 For Get And Display Store Demo Image End
    }
    $sortby = "";
    if ($sortby == "" || $sortby == NULL) {
        $sortby = "relevance";
    }
    if ($sortby == "rating") {
        $sortfield = "vAvgRatingOrig";
        $sortorder = SORT_DESC;
    } elseif ($sortby == "time") {
        $sortfield = "fPrepareTime";
        $sortorder = SORT_ASC;
    } elseif ($sortby == "costlth") {
        $sortfield = "fPricePerPerson";
        $sortorder = SORT_ASC;
    } elseif ($sortby == "costhtl") {
        $sortfield = "fPricePerPerson";
        $sortorder = SORT_DESC;
    } else {
        $sortfield = "restaurantstatus";
        $sortorder = SORT_DESC;
    }
    foreach ($Data as $k => $v) {
        $Data_name[$sortfield][$k] = $v[$sortfield];
        $Data_name['restaurantstatus'][$k] = $v['restaurantstatus'];
    }
    array_multisort($Data_name['restaurantstatus'], SORT_DESC, $Data_name[$sortfield], $sortorder, $Data);
    $Data = array_values($Data);
    //echo "<pre>";print_r($Data);die;
    for ($c = 0; $c < count($Data); $c++) {
        ?>
        <li <?php if (strtolower($Data[$c]['restaurantstatus']) == "closed") { ?> class="rest-closed" <?php } ?>>
            <a href="<?= $tsite_url; ?>store-items?id=<?= $Data[$c]['iCompanyId']; ?>&order=<?= $fromOrder; ?>" data-status="<?= $Data[$c]['Restaurant_Open_And_Close_time']; ?>">
                <!-- $iCompanyId  to $Data[$c]['iCompanyId'] changes done by sk-->
                <?php
                if ((strtolower($checkUser) == 'rider' || strtolower($checkUser) == "user") && $checkFavStore == 1) {
                    ?>
                    <div class="add-favorate">
                        <span class="fav-check">
                            <input id="favouriteManualStore" name="favouriteManualStore" class="favouriteManualStore" data-company="<?= $Data[$c]['iCompanyId']; ?>" data-service="<?= $Data[$c]['iServiceId']; ?>" onclick="updateFavStoreStatus(this);" type="checkbox" value="Yes" <?php
                            if (isset($Data[$c]['eFavStore']) && !empty($Data[$c]['eFavStore']) && $Data[$c]['eFavStore'] == 'Yes') {
                                echo "checked";
                            }
                            ?>>
                            <!--  $iServiceId to $Data[$c]['iServiceId'] and $iCompanyId to $Data[$c]['iCompanyId'] changes done by sk-->
                            <span class="custom-check"></span>
                        </span>
                    </div>
                <?php } ?> 
                <div class="rest-pro" style="background-image:url(<?= ($Data[$c]['vImage']); ?>);" ></div>
                <div class="procapt">
                    <strong title="<?= $Data[$c]['vCompany']; ?>">
                        <span class="item-list-name"><?= $Data[$c]['vCompany']; ?></span><span class="rating"><img src="<?= $tsite_url; ?>assets/img/star.svg" alt=""> <?= $Data[$c]['vAvgRatingOrig']; ?></span><?php
                        if (strtolower($Data[$c]['restaurantstatus']) == "closed") {
                            ?>&nbsp;
                                <?php
                            }
                            ?>
                        <div class="food-detail" title="<?= $Data[$c]['Restaurant_Cuisine']; ?>"><?= $Data[$c]['Restaurant_Cuisine']; ?></div></strong>
                    <div class="span-row">
                        <span class="timing"><?= $Data[$c]['Restaurant_OrderPrepareTime']; ?></span>
                        <span class="on-nin"><?= $Data[$c]['Restaurant_fMinOrderValue']; ?></span>
                        <?php if ($iServiceId == 1) { ?>
                            <span class="on-nos"><?= $Data[$c]['Restaurant_fPricePerPer']; ?></span>
                        <?php } ?>
                    </div>
                    <span class="discount-txt">
                        <img src="<?= $tsite_url; ?>assets/img/discount.svg" alt="">
                        <?= $Data[$c]['Restaurant_OfferMessage']; ?>
                    </span>
                </div>
            </a>
        </li>
        <?php
    }
}
?>
