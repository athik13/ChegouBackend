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
?> 	
<style>
            .error {
                color:red;
                font-weight: normal;
            }
            .select2-container--default .select2-search--inline .select2-search__field{
                width:500px !important;
            }
        </style>
        <?php /*
        <div id="main-uber-page">
        <div class="page-contant home-page-data">
                <div class="page-contant-inner _MLR0_  page-contant-inner-av">
<div class="listing-main" id="restlisting">
    <div class="flex-row">
        <h4><img src="<?= $tsite_url; ?>assets/img/placeholder.svg" class="locate_ico" alt=""><?= $selServiceName . $fulladdress; ?></h4>
        <?php
        if ((strtolower($checkUser) == 'rider' || strtolower($checkUser) == "user") && $checkFavStore == 1) {
            ?>
            <label class="filter-main ">
                <div class="filter-label"><img src="<?= $tsite_url; ?>assets/img/custome-store/controls.svg" alt="" width="30px;">
                <!-- <b><?php // echo $languageLabelsArr['LBL_FILTER_TXT'];                       ?></b> -->
                    <div></div></div>
                <div  class="filter-drop"><ul><li>
                            <span class="check-holder"><input id="filterFavStore" name="filterFavStore" class="filterFavStore" type="checkbox" value="Yes" onClick="favStore(this);"><span class="check-box"></span></span><em style="text-transform:capitalize;font-size: 12px;"><?= $languageLabelsArr['LBL_MANUAL_STORE_FILTER_FAVOURITE_STORE'] ?></em>

                        </li>
                    </ul></div>
            </label> <?php } ?> 
    </div>
</div>
<?php if (count($Data) > 0 && !empty($Data)) { ?>
    <div class="flex-row list-work" id="restaurantcount">
        <?= count($Data) . ' ' . $languageLabelsArr['LBL_RESTAURANTS']; ?>
    </div>  
<?php } ?> 
*/ ?>
<ul class="rest-listing" >
    <?php
    if (!empty($Data)) {
        $storeDetails = getStoreDetails($storeIdArr, $iUserId, $iToLocationId, $languageLabelsArr);
        $currencySymbol = "$";
        if (isset($storeDetails['currencySymbol']) && $storeDetails['currencySymbol'] != "") {
            $currencySymbol = $storeDetails['currencySymbol'];
        }
        for ($i = 0; $i < count($Data); $i++) {
            $fDeliverytime = 0;
            $iCompanyId = $Data[$i]['iCompanyId'];
            $vAvgRating = $Data[$i]['vAvgRating'];
            $Data[$i]['vAvgRating'] = ($vAvgRating > 0) ? number_format($Data[$i]['vAvgRating'], 1) : 0;
            $Data[$i]['vAvgRatingOrig'] = $Data[$i]['vAvgRating'];
            $Data[$i]['vCompany'] = ucfirst($Data[$i]['vCompany']);
            //$CompanyDetailsArr = getCompanyDetails($Data[$i]['iCompanyId'], $iUserId, "No", "", $iServiceId, $vLang);
            //$restaurant_status_arr = calculate_restaurant_time_span($Data[$i]['iCompanyId'], $iUserId);
            //echo "<pre>";print_r($restaurant_status_arr);die;
            //echo $iCompanyId."===".$Data[$i]['companyImage']."<br>";die;
            if ($Data[$i]['companyImage'] != "") {
                $Data[$i]['vImage'] = $tconfig['tsite_upload_images_compnay'] . '/' . $Data[$i]['iCompanyId'] . '/3_' . $Data[$i]['companyImage'];
            } else {
                $Data[$i]['vImage'] = $tsite_url . '/assets/img/burger.jpg';
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
            $Restaurant_Cuisine = array();
            //print_R($storeDetails['companyCuisineArr']);
            if (isset($storeDetails['companyCuisineArr'][$iCompanyId])) {
                $Restaurant_Cuisine = $storeDetails['companyCuisineArr'][$iCompanyId];
            }
            $restCuisine = "";
            if (count($Restaurant_Cuisine) > 0) {
                $restCuisine = implode(", ", $Restaurant_Cuisine);
            }
            //echo "<pre>";print_r($storeDetails);die;
            $Data[$i]['Restaurant_Cuisine'] = $restCuisine;
            $restaurantstatus = "Closed";
            if (isset($storeDetails['restaurantStatusArr'][$iCompanyId]['status'])) {
                $restaurantstatus = $storeDetails['restaurantStatusArr'][$iCompanyId]['status'];
            }
            $Data[$i]['restaurantstatus'] = $restaurantstatus; // closed or open
            $Restaurant_OfferMessage_short = $Restaurant_OfferMessage = $noOfferTxt;
            if (isset($storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage_short'])) {
                $Restaurant_OfferMessage_short = $storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage_short'];
            }
            if (isset($storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage'])) {
                $Restaurant_OfferMessage = $storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage'];
            }
            if ($Restaurant_OfferMessage_short == "") {
                $Restaurant_OfferMessage_short = $Restaurant_OfferMessage = $noOfferTxt;
            }
            $Restaurant_OrderPrepareTime = "0 mins";
            if (isset($storeDetails[$iCompanyId]['Restaurant_OrderPrepareTime'])) {
                $Restaurant_OrderPrepareTime = $storeDetails[$iCompanyId]['Restaurant_OrderPrepareTime'];
            }
            $Data[$i]['Restaurant_OrderPrepareTime'] = str_replace('mins', '<img src="' . $tsite_url . 'assets/img/custome-store/delivery_time.png" class="delivery_time_ico" alt=' . $Data[$i]['Restaurant_OrderPrepareTime'] . '><br>mins', $Restaurant_OrderPrepareTime);
            //$Data[$i]['Restaurant_OrderPrepareTime'] = $Restaurant_OrderPrepareTime;

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
            $restPricePerPerson = $restMinOrdValue = 1;
            if (isset($storeDetails['restaurantPricePerPerson'][$iCompanyId])) {
                $restPricePerPerson = $storeDetails['restaurantPricePerPerson'][$iCompanyId];
            }
            $Data[$i]['Restaurant_fPricePerPer'] = $currencySymbol . $restPricePerPerson . ' <div>' . ucfirst(strtolower($languageLabelsArr['LBL_PER_PERSON_TXT'])) . "</div>";
            if (isset($storeDetails['restaurantMinOrdValue'][$iCompanyId])) {
                $restMinOrdValue = $storeDetails['restaurantMinOrdValue'][$iCompanyId];
            }
            $Data[$i]['Restaurant_fMinOrderValue'] = $currencySymbol . $restMinOrdValue . " <div>" . ucfirst(strtolower($languageLabelsArr['LBL_MIN_SMALL'])) . ". " . ucfirst(strtolower($languageLabelsArr['LBL_ORDER'])) . "</div>";
            ?>
            <li <?php if (strtolower($restaurantstatus) == "closed") { ?> class="rest-closed" <?php } ?>>
                <a href="<?= $tsite_url; ?>store-items?id=<?= $iCompanyId; ?>&order=<?= $fromOrder; ?>" data-status="<?= $Data[$i]['Restaurant_Open_And_Close_time']; ?>">
                    <?php
                    if ((strtolower($checkUser) == 'rider' || strtolower($checkUser) == 'user') && $checkFavStore == 1) {
                        ?>
                        <div class="add-favorate">
                            <span class="fav-check">
                                <input id="favouriteManualStore" name="favouriteManualStore" class="favouriteManualStore" type="checkbox" value="Yes" <?php
                                if (isset($Data[$i]['eFavStore']) && !empty($Data[$i]['eFavStore']) && $Data[$i]['eFavStore'] == 'Yes') {
                                    echo "checked";
                                }
                                ?>>
                                <span class="custom-check"></span>
                            </span>
                        </div>
                    <?php } ?> 
                    <div class="rest-pro" style="background-image:url(<?= ($Data[$i]['vImage']); ?>);" ></div>
                    <div class="procapt">
                        <strong title="<?= $Data[$i]['vCompany']; ?>">
                            <span class="item-list-name"><?= $Data[$i]['vCompany']; ?></span><span class="rating"><img src="<?= $tsite_url; ?>assets/img/star.svg" alt=""> <?= $Data[$i]['vAvgRatingOrig']; ?></span><?php
                            if (strtolower($restaurantstatus) == "closed") {
                                ?>&nbsp;
                                    <?php
                                }
                                ?>    <div class="food-detail" title="<?= $Data[$i]['Restaurant_Cuisine']; ?>"><?= $Data[$i]['Restaurant_Cuisine']; ?></div></strong>
                        <div class="span-row">
                            <span class="timing"><?= $Data[$i]['Restaurant_OrderPrepareTime']; ?></span>
                            <span class="on-nin"><?= $Data[$i]['Restaurant_fMinOrderValue']; ?></span>
                            <?php if ($iServiceId == 1) { ?>
                                <span class="on-nos"><?= $Data[$i]['Restaurant_fPricePerPer']; ?></span>
                            <?php } ?>
                        </div>
                        <span class="discount-txt">
                            <img src="<?= $tsite_url; ?>assets/img/discount.svg" alt="">
                            <?= $Restaurant_OfferMessage; ?>
                        </span>
                    </div>
                </a>
            </li>
            <?php
        }
    }
    ?>
    <?php
    if (empty($Data)) {
        ?><div  align="center" class="show-msg-restaurant-listing">
            <h4><span style="color:#343434;"><b  style="margin:20px 0 0; padding:0px; float:left; width:100%;">
                        <?php /* if ($iServiceId != 1) { ?>
                          <img src="assets/img/custome-store/deliveryall-detail-holder.png" alt="" width="250px;">
                          <?php } else { ?>
                          <img src="assets/img/custome-store/food-detail-holder.png" alt="" width="250px;">
                          <?php } */ ?>
                    </b><strong><?= $languageLabelsArr['LBL_NO_RECORD_FOUND']; ?></strong>
                    <!--<div><button class="btn" onclick="window.location.href = '<?= $redirect_location; ?>'"><?= $languageLabelsArr['LBL_CHANGE_LOCATION']; ?></button></div>-->
                </span> </h4></div>
    <?php } ?>
</ul>
                    </div>
            </div>
            </div>
<script>
    $(document).ready(function () {
        $.fn.equalHeight = function () {
            var maxHeight = 0;
            return this.each(function (index, box) {
                var boxHeight = $(box).height();
                maxHeight = Math.max(maxHeight, boxHeight);
            }).height(maxHeight);
        }
        function EQUAL_HEIGHT() {
            $('ul.rest-listing li .food-detail').css('height', 'auto');
            $('ul.rest-listing li .food-detail').equalHeight();
            $(window).resize(function () {
                $('ul.rest-listing li .food-detail').css('height', 'auto');
                $('ul.rest-listing li .food-detail').equalHeight();
            });
        }
        $(window).load(function () {
            EQUAL_HEIGHT();
        });
        function EQUAL_HEIGHT_() {
            $('ul.rest-listing li .discount-txt').css('height', 'auto');
            $('ul.rest-listing li .discount-txt').equalHeight();
            $(window).resize(function () {
                $('ul.rest-listing li .discount-txt').css('height', 'auto');
                $('ul.rest-listing li .discount-txt').equalHeight();
            });
        }
        EQUAL_HEIGHT_();
    });
</script>