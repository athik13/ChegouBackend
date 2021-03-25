<?php
include_once('common.php');
include_once ('include_generalFunctions_dl.php');
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
$vLang = 'EN';
if (isset($_SESSION['sess_lang'])) {
    $vLang = $_SESSION['sess_lang'];
}
$fromOrder = "guest";
if (isset($_REQUEST['order']) && $_REQUEST['order'] != "") {
    $fromOrder = $_REQUEST['order'];
}
$orderDetailsSession = "ORDER_DETAILS_" . strtoupper($fromOrder);
$orderServiceSession = "MAUAL_ORDER_SERVICE_" . strtoupper($fromOrder);
$orderUserIdSession = "MANUAL_ORDER_USERID_" . strtoupper($fromOrder);
$orderAddressIdSession = "MANUAL_ORDER_ADDRESSID_" . strtoupper($fromOrder);
$orderAddressSession = "MANUAL_ORDER_ADDRESS_" . strtoupper($fromOrder);
$orderCouponSession = "MANUAL_ORDER_PROMOCODE_" . strtoupper($fromOrder);
$orderCouponNameSession = "MANUAL_ORDER_PROMOCODE_NAME_" . strtoupper($fromOrder);
$orderLatitudeSession = "MANUAL_ORDER_LATITUDE_" . strtoupper($fromOrder);
$orderLongitudeSession = "MANUAL_ORDER_LONGITUDE_" . strtoupper($fromOrder);
$orderServiceNameSession = "MANUAL_ORDER_SERVICE_NAME_" . strtoupper($fromOrder);
$orderDataSession = "MANUAL_ORDER_DATA_" . strtoupper($fromOrder);

$selServiceName = "";
if (isset($_SESSION[$orderServiceNameSession]) && $_SESSION[$orderServiceNameSession] != "") {
    $selServiceName = $_SESSION[$orderServiceNameSession] . " In >> ";
}
if (isset($_SESSION[$orderServiceSession]) && !empty($_SESSION[$orderServiceSession])) {
    $iServiceId = 1;
    if (isset($_SESSION[$orderServiceSession])) {
        $iServiceId = $_SESSION[$orderServiceSession];
    }
    global $intervalmins;
    $Data = array();
    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
    $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
    //$LIST_RESTAURANT_LIMIT_BY_DISTANCE = $generalobj->getConfigurations("configurations", "LIST_RESTAURANT_LIMIT_BY_DISTANCE");
    //$DRIVER_REQUEST_METHOD = $generalobj->getConfigurations("configurations", "DRIVER_REQUEST_METHOD");
    $param = ($DRIVER_REQUEST_METHOD == "Time") ? "tOnline" : "tLastOnline";
    $iUserId = $iUserAddressId = 0;
    $vLatitude = $vLongitude = $vBuildingNo = $vLandmark = $vAddressType = $vServiceAddress = $fulladdress = "";
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
    $checkUser = check_user_mr();
    $checkFavStore = checkFavStoreModule();
    if (!empty($_SESSION[$orderUserIdSession]) && empty($_SESSION[$orderLongitudeSession]) && empty($_SESSION[$orderLatitudeSession]) && !empty($_SESSION[$orderAddressIdSession])) {
        $vTimeZone = "Asia/Kolkata";
        $iUserId = $_SESSION[$orderUserIdSession];
        $iUserAddressId = $_SESSION[$orderAddressIdSession];
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
    if ((strtolower($checkUser) == 'rider' || strtolower($checkUser) == 'user') && $checkFavStore == 1) {
        include_once "include/features/include_fav_store.php";
        if (!empty($iUserId)) {
            $ssql_fav_q = getFavSelectQuery('', $iUserId);
        }
    }
    $having_ssql = "";
    if (SITE_TYPE == "Demo" && $searchword == "") {
        $vAddress = $vServiceAddress1;
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
			* sin( radians( vRestuarantLocationLat ) ) ) ),2) AS distance, company.*,company.vImage as companyImage " . $ssql_fav_q . "  FROM `company`  
			WHERE vRestuarantLocationLat != '' AND vRestuarantLocationLong != '' AND company.eStatus='Active' AND eSystem = 'DeliverAll' AND company.iServiceId = '" . $iServiceId . "' $ssql
			HAVING (distance < " . $LIST_RESTAURANT_LIMIT_BY_DISTANCE . $having_ssql . ") ORDER BY `company`.`iCompanyId` ASC";
    } else if ($cuisine != "") {
        $ssql .= " AND (cu.cuisineName_" . $vLang . " like '%$cuisine%' AND cu.eStatus = 'Active')";
        $sql = "SELECT DISTINCT (company.iCompanyId),ROUND(( 6371 * acos( cos( radians(" . $vLatitude . ") ) 
		* cos( radians( vRestuarantLocationLat ) ) 
			* cos( radians( vRestuarantLocationLong ) - radians(" . $vLongitude . ") ) 
			+ sin( radians(" . $vLatitude . ") ) 
			* sin( radians( vRestuarantLocationLat ) ) ) ),2) AS distance, company.*,company.vImage as companyImage , cu.* " . $ssql_fav_q . " FROM `company` LEFT JOIN company_cuisine as ccu ON ccu.iCompanyId=company.iCompanyId LEFT JOIN cuisine as cu ON ccu.cuisineId=cu.cuisineId
			WHERE vRestuarantLocationLat != '' AND vRestuarantLocationLong != '' AND company.eStatus='Active' AND eSystem = 'DeliverAll' AND company.iServiceId = '" . $iServiceId . "' $ssql
			HAVING (distance < " . $LIST_RESTAURANT_LIMIT_BY_DISTANCE . $having_ssql . ") ORDER BY `company`.`iCompanyId` ASC";
    } else if ($eFavStore != "") {
        if ((strtolower($checkUser) == 'rider' || strtolower($checkUser) == 'user') && $checkFavStore == 1) {

            $sql = "SELECT  ROUND(( 6371 * acos( cos( radians(" . $vLatitude . ") ) 
		* cos( radians( vRestuarantLocationLat ) ) 
			* cos( radians( vRestuarantLocationLong ) - radians(" . $vLongitude . ") ) 
			+ sin( radians(" . $vLatitude . ") ) 
			* sin( radians( vRestuarantLocationLat ) ) ) ),2) AS distance, company.*,company.vImage as companyImage " . $ssql_fav_q . "  FROM `company`  
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
			* sin( radians( vRestuarantLocationLat ) ) ) ),2) AS distance, company.*,company.vImage as companyImage " . $ssql_fav_q . "  FROM `company`  
			WHERE vRestuarantLocationLat != '' AND vRestuarantLocationLong != '' AND company.eStatus='Active' AND eSystem = 'DeliverAll' AND company.iServiceId = '" . $iServiceId . "' $ssql
			HAVING (distance < " . $LIST_RESTAURANT_LIMIT_BY_DISTANCE . $having_ssql . ") ORDER BY `company`.`iCompanyId` ASC";
            }
        }
    } else {
        $sql = "SELECT DISTINCT (company.iCompanyId),ROUND(( 6371 * acos( cos( radians(" . $vLatitude . ") ) 
		* cos( radians( vRestuarantLocationLat ) ) 
			* cos( radians( vRestuarantLocationLong ) - radians(" . $vLongitude . ") ) 
			+ sin( radians(" . $vLatitude . ") ) 
			* sin( radians( vRestuarantLocationLat ) ) ) ),2) AS distance, company.*,company.vImage as companyImage " . $ssql_fav_q . "   FROM `company` 
			WHERE vRestuarantLocationLat != '' AND vRestuarantLocationLong != '' AND company.eStatus='Active' AND eSystem = 'DeliverAll' AND company.iServiceId = '" . $iServiceId . "'  
			HAVING (distance < " . $LIST_RESTAURANT_LIMIT_BY_DISTANCE . $having_ssql . ") ORDER BY `company`.`iCompanyId` ASC";
    }
    $Data = $obj->MySQLSelect($sql);
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
$storeIdArr = array();
foreach ($Data as $k => $v) {
    $storeIdArr[] = $v['iCompanyId'];
    $Data_name[$sortfield][$k] = $v[$sortfield];
    $Data_name['restaurantstatus'][$k] = $v['restaurantstatus'];
}
array_multisort($Data_name['restaurantstatus'], SORT_DESC, $Data_name[$sortfield], $sortorder, $Data);
$Data = array_values($Data);
$tsite_url = $tconfig['tsite_url'];
if (strtolower($checkUser) == 'store' || strtolower($checkUser) == 'admin') {
    $redirect_location = $tsite_url . 'user-order-information';
} else {
    $redirect_location = $tsite_url . 'order-items';
}
$languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
$noOfferTxt = $languageLabelsArr['LBL_NO_OFFER_TXT'];

$page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
$Data_new = array_values($Data);
$per_page = 5;
$pagecount = $page - 1;
$start_limit = $pagecount * $per_page;
$next_page = $page + 1;
$Data = array_slice($Data_new, $start_limit, $per_page);

?> 	

    <?php
    if (!empty($Data)) {
        $storeDetails = getStoreDetails($storeIdArr, $iUserId, $iToLocationId, $languageLabelsArr);
        $currencySymbol = "$";
        if (isset($storeDetails['currencySymbol']) && $storeDetails['currencySymbol'] != "") {
            $currencySymbol = $storeDetails['currencySymbol'];
        }
        ?>

        <?php for ($i = 0; $i < count($Data); $i++) {
            $fDeliverytime = 0;
            $iCompanyId = $Data[$i]['iCompanyId'];
            $Data[$i]['vCompany'] = ucfirst($Data[$i]['vCompany']);
          
            if ($Data[$i]['companyImage'] != "") {
                $Data[$i]['vImage'] = $tconfig['tsite_upload_images_compnay'] . '/' . $Data[$i]['iCompanyId'] . '/3_' . $Data[$i]['companyImage'];
            } else {
                $Data[$i]['vImage'] = $tsite_url . '/assets/img/burger.jpg';
            }
           
            //Added By HJ On 26-06-2019 For Get And Display Store Demo Image Start
            if (isset($storeDetails['storeDemoImageArr'][$iCompanyId]) && $storeDetails['storeDemoImageArr'][$iCompanyId] != "" && SITE_TYPE == "Demo") {
                $demoImgPath = $tconfig['tsite_upload_demo_compnay_doc_path'] . $storeDetails['storeDemoImageArr'][$iCompanyId];
                if (file_exists($demoImgPath)) {
                    $demoImgUrl = $tconfig['tsite_upload_demo_compnay_doc'] . $storeDetails['storeDemoImageArr'][$iCompanyId];
                    $Data[$i]['vImage'] = $demoImgUrl;
                }
            }
    ?>
            <a href="<?= $tsite_url; ?>store-items?id=<?= $iCompanyId; ?>&order=<?= $fromOrder; ?>" class="list-group-item" data-page="<?= $next_page ?>">
                <img src="<?= ($Data[$i]['vImage']); ?>">
                <?= $Data[$i]['vCompany']; ?>
            </a>
    <?php
        }
    }
    ?>
    <?php
    if (empty($Data) && $page == 1) {
        ?>
        <a href="javascript:void(0);" class="list-group-item"><?= $languageLabelsArr['LBL_NO_RECORD_FOUND'] ?></a>
    <?php } ?>
