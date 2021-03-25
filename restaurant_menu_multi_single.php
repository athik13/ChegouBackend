<?php
    include_once("common.php");
    $vLang = "EN";
    $confirlAlert = 0;
    
    if (isset($_SESSION['sess_lang'])) {
        $vLang = $_SESSION['sess_lang'];
    }
    $confirlAlert = 0;
        
    include_once ('include_generalFunctions_dl.php');
    
    check_type_wise_mr('restaurant_menu');
    
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
    $orderDataSession = "MANUAL_ORDER_DATA_" . strtoupper($fromOrder);
    $orderStoreIdSession = "MANUAL_ORDER_STORE_ID_" . strtoupper($fromOrder);
    $script = "Restaurant menu";
    $meta = $generalobj->getStaticPage(1, $vLang);
    unset($_SESSION[$orderCouponNameSession]);
    unset($_SESSION[$orderCouponSession]);
    unset($_SESSION[$orderDataSession]);
    $_SESSION['sess_language'] = $vLang;
    //include_once ('include_generalFunctions_dl.php');
    $iCompanyId = isset($_REQUEST["id"]) ? $_REQUEST["id"] : '';
    $checkUser = check_user_mr();
    $iServiceId = $whereServiceId = $sql_query = "";
    $companyServiceId = 0;
    if (isset($_SESSION[$orderServiceSession]) && $_SESSION[$orderServiceSession] > 0) {
        $iServiceId = $selServiceId = $_SESSION[$orderServiceSession];
    } else if (strtolower($checkUser) == 'store') {
        $companyServiceId = 1;
    }
    if ($selServiceId > 0) {
        $whereServiceId = " and iServiceId='" . $selServiceId . "'";
    }
    $checkFavStore = checkFavStoreModule();
    $vLatitude = $iUserId = $iUserAddressId = $vLongitude = "";
    if (isset($_SESSION[$orderUserIdSession])) {
        $iUserId = $_SESSION[$orderUserIdSession];
    }
    if ((strtolower($checkUser) == 'rider' || strtolower($checkUser) == "user") && !empty($iUserId) && !empty($iCompanyId) && $checkFavStore == 1) {
        include "include/features/include_fav_store.php";
        $sql_query = getFavSelectQuery($iCompanyId, $iUserId);
    }
    $db_company = $obj->MySQLSelect("SELECT * " . $sql_query . " FROM company WHERE iCompanyId = '" . $iCompanyId . "' $whereServiceId");
    if ($companyServiceId > 0 && $whereServiceId == "") {
        $_SESSION[$orderServiceSession] = $db_company[0]['iServiceId'];
    }
    if (isset($_SESSION[$orderServiceSession]) && $_SESSION[$orderServiceSession] > 0) {
        $iServiceId = $selServiceId = $_SESSION[$orderServiceSession];
    }
    global $intervalmins;
    //$vTimeZone = "Asia/Kolkata";
    $vTimeZone = date_default_timezone_get();
    if(isset($db_company[0]['vTimeZone']) && $db_company[0]['vTimeZone'] != ""){
       $vTimeZone =  $db_company[0]['vTimeZone'];
    }
    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
    $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
    //$LIST_RESTAURANT_LIMIT_BY_DISTANCE = $generalobj->getConfigurations("configurations", "LIST_RESTAURANT_LIMIT_BY_DISTANCE");
    //$DRIVER_REQUEST_METHOD = $generalobj->getConfigurations("configurations", "DRIVER_REQUEST_METHOD");
    $param = ($DRIVER_REQUEST_METHOD == "Time") ? "tOnline" : "tLastOnline";
    $vServiceAddress = 0;
    
    if (isset($_SESSION[$orderAddressIdSession])) {
        $iUserAddressId = $_SESSION[$orderAddressIdSession];
    }
    if (isset($_SESSION[$orderAddressSession])) {
        $vServiceAddress = $_SESSION[$orderAddressSession];
    }
    if (isset($_SESSION[$orderLatitudeSession])) {
        $vLatitude = $_SESSION[$orderLatitudeSession];
    }
    if (isset($_SESSION[$orderLongitudeSession])) {
        $vLongitude = $_SESSION[$orderLongitudeSession];
    }
    if (!empty($iUserId) && empty($vLongitude) && empty($vLatitude) && !empty($iUserAddressId)) {
        if (empty($iUserId) || empty($iUserAddressId)) {
            header("location:user-order-information");
            exit;
        }
        $Dataua = $obj->MySQLSelect("SELECT *  FROM `user_address`  WHERE iUserAddressId = '" . $iUserAddressId . "' AND iUserId = '" . $iUserId . "'");
        if (count($Dataua) > 0) {
            $vServiceAddress = ucfirst($Dataua[0]['vServiceAddress']);
            $vBuildingNo = $Dataua[0]['vBuildingNo'];
            $vLandmark = $Dataua[0]['vLandmark'];
            $vAddressType = $Dataua[0]['vAddressType'];
            $vLatitude = $Dataua[0]['vLatitude'];
            $vLongitude = $Dataua[0]['vLongitude'];
            $vTimeZone = $Dataua[0]['vTimeZone'];
        }
    }
    $sourceLocationArr = array($vLatitude, $vLongitude);
    $iToLocationId = GetUserGeoLocationId($sourceLocationArr);
    //$allowed_ans = checkAllowedAreaNew($sourceLocationArr, "No");
    
    $CheckNonVegFoodType = isset($_REQUEST["CheckNonVegFoodType"]) ? $_REQUEST["CheckNonVegFoodType"] : 'No';
    $fDeliverytime = 0;
    $passengerLat = isset($_REQUEST["PassengerLat"]) ? $_REQUEST["PassengerLat"] : '';
    $passengerLon = isset($_REQUEST["PassengerLon"]) ? $_REQUEST["PassengerLon"] : '';
    $searchword = isset($_REQUEST["searchword"]) ? $_REQUEST["searchword"] : '';
    $searchword = strtolower(trim($searchword));
    if ($searchword == "" || $searchword == NULL) {
        $searchword = "";
    }
    if ($CheckNonVegFoodType == "" || $CheckNonVegFoodType == NULL) {
        $CheckNonVegFoodType = "";
    }
    if (empty($db_company)) {
        header("location:store-listing?success=0&error=LBL_NO_RESTAURANT_FOUND_TXT&order=" . $fromOrder);
        exit;
    }
    //echo count($_SESSION[$orderDetailsSession]);die;
    if (isset($_SESSION[$orderStoreIdSession]) && $_SESSION[$orderStoreIdSession] != $iCompanyId) {
        unset($_SESSION[$orderDetailsSession]);
    }
    if (strtolower($checkUser) == 'store') {
        if ($iCompanyId != $_SESSION['sess_iCompanyId']) {
            header("location:store-items?id=" . $_SESSION[$orderStoreIdSession] . "&order=" . $fromOrder);
            exit;
        }
    }
    $Recomendation_Arr = array();
    $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $selServiceId);
    //echo "<pre>";print_r($selServiceId);die;
    $CompanyDetails_Arr = getCompanyDetails($iCompanyId, $iUserId, $CheckNonVegFoodType, $searchword, $selServiceId, $vLang);
    //echo "<pre>";print_r($CompanyDetails_Arr);die;
    $storeIdArr[] = $iCompanyId;
    $storeDetails = getStoreDetails($storeIdArr, $iUserId, $iToLocationId, $languageLabelsArr);
    //echo "<pre>";print_r($storeDetails);die;
    $storeCuisins = implode(", ", $storeDetails['cuisineArr']);
    $restaurantstatus = "Closed";
    $Restaurant_OrderPrepareTime = "0 mins";
    $Restaurant_OfferMessage_short = $Restaurant_OfferMessage = "";
    $timeSlotArr = array();
    if (isset($CompanyDetails_Arr['monfritimeslot_TXT']) && $CompanyDetails_Arr['monfritimeslot_TXT'] != "") {
        $monToFri = array();
        $monToFri['head'] = $CompanyDetails_Arr['monfritimeslot_TXT'];
        $monToFri['time'] = $CompanyDetails_Arr['monfritimeslot_Time'];
        $timeSlotArr[] = $monToFri;
    }
    if (isset($CompanyDetails_Arr['satsuntimeslot_TXT']) && $CompanyDetails_Arr['satsuntimeslot_TXT'] != "") {
        $satToSun = array();
        $satToSun['head'] = $CompanyDetails_Arr['satsuntimeslot_TXT'];
        $satToSun['time'] = $CompanyDetails_Arr['satsuntimeslot_Time'];
        $timeSlotArr[] = $satToSun;
    }
    //echo "<pre>";print_r($timeSlotArr);die;
    if (isset($storeDetails['restaurantStatusArr'][$iCompanyId]['status'])) {
        $restaurantstatus = $storeDetails['restaurantStatusArr'][$iCompanyId]['status'];
    }
    if (isset($storeDetails[$iCompanyId]['Restaurant_OrderPrepareTime'])) {
        $Restaurant_OrderPrepareTime = $storeDetails[$iCompanyId]['Restaurant_OrderPrepareTime'];
    }
    if (isset($storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage_short'])) {
        $Restaurant_OfferMessage_short = $storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage_short'];
    }
    if (isset($storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage'])) {
        $Restaurant_OfferMessage = $storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage'];
    }
    //echo $Restaurant_OfferMessage;die;
    $currencySymbol = "$";
    if (isset($storeDetails['currencySymbol']) && $storeDetails['currencySymbol'] != "") {
        $currencySymbol = $storeDetails['currencySymbol'];
    }
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
    $restPricePerPerson = $restMinOrdValue = 1;
    if (isset($storeDetails['restaurantPricePerPerson'][$iCompanyId])) {
        $restPricePerPerson = $storeDetails['restaurantPricePerPerson'][$iCompanyId];
    }
    $db_company[0]['vCompany'] = stripslashes(ucfirst($db_company[0]['vCompany']));
    $db_company[0]['fPricePerPerson'] = "";
    if ($selServiceId == '1') {
        $db_company[0]['fPricePerPerson'] = $currencySymbol . $restPricePerPerson . ' ' . $languageLabelsArr['LBL_PER_PERSON_TXT'];
    }
    if (isset($storeDetails['restaurantMinOrdValue'][$iCompanyId])) {
        $restMinOrdValue = $storeDetails['restaurantMinOrdValue'][$iCompanyId];
    }
    //echo '<pre>';print_r($CompanyDetails_Arr);
    $db_company[0]['fMinOrderValue'] = $restMinOrdValue;
    $db_company[0]['Restaurant_MinOrderValue'] = $currencySymbol . $restMinOrdValue . " Min order";
    $db_company[0]['RatingCounts'] = $CompanyDetails_Arr['RatingCounts'];
    $db_company[0]['CompanyDetails'] = $CompanyDetails_Arr;
    $siteUrl = $tconfig['tsite_url'];
    if ($db_company[0]['vImage'] != "") {
        $db_company[0]['vImage'] = $tconfig['tsite_upload_images_compnay'] . '/' . $iCompanyId . '/' . $db_company[0]['vImage'];
    } else {
        /* if ($selServiceId == 1) {
          $db_company[0]['vImage'] = $siteUrl . '/assets/img/custome-store/food-restaurent-menu-place.png';
          } else {
          $db_company[0]['vImage'] = $siteUrl . '/assets/img/custome-store/deliveryall-restaurent-menu-place.png';
          } */
        $db_company[0]['vImage'] = $siteUrl . 'assets/img/custome-store/food-menu-order-list.png';
    }
    //$restaurant_status_arr = calculate_restaurant_time_span($iCompanyId, $iUserId);
    if ($db_company[0]['vCoverImage'] != "") {
        $db_company[0]['vCoverImage'] = $tconfig['tsite_upload_images_compnay'] . '/' . $iCompanyId . '/' . $db_company[0]['vCoverImage'];
    }
    //Added By HJ On 26-06-2019 For Get And Display Store Demo Image Start
    if (isset($storeDetails['storeDemoImageArr'][$iCompanyId]) && $storeDetails['storeDemoImageArr'][$iCompanyId] != "" && SITE_TYPE == "Demo") {
        $demoImgPath = $tconfig['tsite_upload_demo_compnay_doc_path'] . $storeDetails['storeDemoImageArr'][$iCompanyId];
        if (file_exists($demoImgPath)) {
            $demoImgUrl = $tconfig['tsite_upload_demo_compnay_doc'] . $storeDetails['storeDemoImageArr'][$iCompanyId];
            $db_company[0]['vImage'] = $demoImgUrl;
        }
    }
    //Added By HJ On 26-06-2019 For Get And Display Store Demo Image End
    $vAvgRating = $db_company[0]['vAvgRating'];
    $db_company[0]['vAvgRating'] = ($vAvgRating > 0) ? number_format($db_company[0]['vAvgRating'], 1) : 0;
    //$Recomendation_Arr = getRecommendedBestSellerMenuItems($iCompanyId, $iUserId, "Recommended", $CheckNonVegFoodType, $searchword, $selServiceId, $vLang); // Commented By HJ On 16-05-2019 Because Data Alraedy Found In Company Details Array
    // echo '<pre>';print_r($Recomendation_Arr);
    //Added By HJ For Get Recomendation Data Start On 16-05-2019
    $RecomendationArray = $Recomendation_Arr = array();
    if (isset($CompanyDetails_Arr['Recomendation_Arr'])) {
        $RecomendationArray = $CompanyDetails_Arr['Recomendation_Arr'];
    }
    for ($g = 0; $g < count($RecomendationArray); $g++) {
        if ($RecomendationArray[$g]['eRecommended'] == "Yes") {
            $Recomendation_Arr[] = $RecomendationArray[$g];
        }
    }
    //echo "<pre>";print_r($Recomendation_Arr);die;
    //Added By HJ For Get Recomendation Data End On 16-05-2019
    //echo '<pre>';print_r($Recomendation_Arr);
    $CompanyFoodData = $db_company[0]['CompanyDetails']['CompanyFoodData'];
    //echo "<pre>";print_r($CompanyFoodData);die;
    $_SESSION[$orderDataSession] = $CompanyFoodData;
    //echo '<pre>';print_r($CompanyFoodData);
    $Data[0]['Restaurant_Status'] = $Data[0]['restaurantstatus'] = $restaurantstatus;
    $Data[0]['Restaurant_Opentime'] = $restOpenTime;
    $Data[0]['Restaurant_Closetime'] = $restCloseTime;
    $Data[0]['timeslotavailable'] = $timeSlotAvailable;
    if (isset($Data[0]['Restaurant_Opentime']) && !empty($Data[0]['Restaurant_Opentime'])) {
        $Data[0]['Restaurant_Open_And_Close_time'] = $languageLabelsArr['LBL_CLOSED_TXT'] . ' ' . $Data[0]['Restaurant_Opentime'];
    } else {
        $Data[0]['Restaurant_Open_And_Close_time'] = $languageLabelsArr['LBL_CLOSED_TXT'];
    }
    
    if (isset($Data[0]['timeslotavailable']) && !empty($Data[0]['timeslotavailable']) && $Data[0]['timeslotavailable'] == 'Yes') {
        $Data[0]['Restaurant_Open_And_Close_time'] = $languageLabelsArr['LBL_NOT_ACCEPT_ORDERS_TXT'];
    }
    $addFavStoreLbl = $languageLabelsArr['LBL_FAVOURITE_MANUAL_STORE'];
    $favStoreLbl = $languageLabelsArr['LBL_FAVOURITE_TXT'];
    $confirmLabel = $languageLabelsArr['LBL_DELETE_CART_ITEM'];
    $openHourTxt = $languageLabelsArr['LBL_OPEN_HOURS_MANUAL_TXT'];
    $closeTxt = $languageLabelsArr['LBL_CLOSE_TXT'];
    $notFoundTxt = $languageLabelsArr['LBL_NOT_FOUND'];
    $pageHead = $SITE_NAME . " | " . $languageLabelsArr['LBL_STORE_ITEMS_MANUAL_TXT'];
    ?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= $pageHead; ?></title>
        <meta name="keywords" value="<?= $meta['meta_keyword']; ?>"/>
        <meta name="description" value="<?= $meta['meta_desc']; ?>"/>
        <!-- Default Top Script and css -->
        <?php include_once("top/top_script.php"); ?>
        <?php include_once("store_css_include.php"); ?>
        <?php include_once("top/validation.php"); ?>
        <link href="assets/css/apptype/<?= $template ?>/custom-order/OverlayScrollbars.css" rel="stylesheet">
        <script src="assets/js/custom-order/OverlayScrollbars.min.js"></script>
        <script src="assets/js/custom-order/script-new.js"></script>
        <style>
            .loader-default {
            position: fixed;
            left: 0px;
            top: 0px;
            width: 100%;
            height: 100%;
            z-index: 9999;
            background: url(<?= $tsite_url; ?>assets/img/loading.gif) 50% 50% no-repeat rgb(249,249,249);
            }
            .closetxt{
            color: #98441ef5;
            font-size: 18px;
            font-weight: 600;
            margin-top: 15px;
            }
            .showClear{
            visibility:inherit !important;opacity: 1 !important;
            }
            .add-favorate {
                margin: 5px 0 0 15px;
            }
            .add-favorate:hover {
                transform: scale(1.2);
            }
            .rest-name {
                display: flex;
                align-items: center;
            }
        </style>
        <!-- End: Default Top Script and css-->
    </head>
    <body>
        <div id="main-uber-page">
            <!-- Left Menu -->
            <?php include_once("top/left_menu.php"); ?>
            <!-- End: Left Menu-->
            <!-- home page -->
            <!-- Top Menu -->
            <?php include_once("top/header_topbar.php"); ?>
            <!-- End: Top Menu-->
            <!-- contact page-->
            <div class="page-contant page-contant-av">
                <div class="loader-default" style="display: none;"></div>
                <div class="restaurent-detail-banner">
                    <div class="restaurent-detail-left">
                        <div class="proImg-block">
                            <div class="proImage" style="background-image:url(<?= $siteUrl . "resizeImg.php?w=375&h=208&src=" . $db_company[0]['vImage']; ?>)">
                                <?php if (strtolower($restaurantstatus) == 'closed') { ?><span class="opening-time"><?= $Data[0]['Restaurant_Open_And_Close_time']; ?></span><?php } ?>
                            </div>
                        </div>
                        <div class="rest-caption">
                            <div class="rest-name">
                                <h3 class="pull-left"><?= $db_company[0]['vCompany']; ?></h3>
                                <?php if ((strtolower($checkUser) == 'rider' || strtolower($checkUser) == "user") && $checkFavStore == 1) { ?>
                                    <div class="add-favorate pull-left">
                                        <span class="fav-check">
                                        <input id="favouriteManualStore" name="favouriteManualStore" class="favouriteManualStore" type="checkbox" value="Yes" <?php
                                            $favLabel = $addFavStoreLbl;
                                            if (isset($db_company[0]['eFavStore']) && !empty($db_company[0]['eFavStore']) && $db_company[0]['eFavStore'] == 'Yes') {
                                                echo "checked";
                                                $favLabel = $favStoreLbl;
                                            }
                                            ?> />
                                        <span class="custom-check"></span>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="clearfix"></div>
                            <span class="rest-location"><?= $db_company[0]['vRestuarantLocation']; ?>
                            <span class="open-popup" data-id="time-info-model"><img onClick="displayStoreTime();" src="assets/img/info.svg" alt="" class="time_info"></span>
                            </span>
                            <div class="food-type"><?= trim($CompanyDetails_Arr['Restaurant_Cuisine'], ","); ?></div>
                            <div class="regard-rest">
                                <div class="review-column">
                                    <span class="rating"><img src="assets/img/star.svg" alt=""> <?= $db_company[0]['vAvgRating']; ?></span>
                                    <label><?= $CompanyDetails_Arr['RatingCounts'] . ' ' . $languageLabelsArr['LBL_REVIEWS']; ?> </label>
                                </div>
                                <div class="deltime-column">
                                    <span class="del-duration"><?= $Restaurant_OrderPrepareTime; ?></span>
                                    <label><?= $languageLabelsArr['LBL_DELIVERY_TIME']; ?></label>
                                </div>
                                <?php if ($selServiceId == 1) { ?>
                                <div class="costing-column">
                                    <span class="couple-amt"><?= $CompanyDetails_Arr['fPricePerPersonWithCurrency']; ?></span>
                                    <!--<label><?= $languageLabelsArr['LBL_PER_PERSON_TXT']; ?></label>!--->
                                    <label><?= $languageLabelsArr['LBL_FOR_ONE']; ?></label>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="restaurent-detail-right">
                        <?php if (!empty($Restaurant_OfferMessage_short)) { ?>
                        <fieldset>
                            <label><?= $languageLabelsArr['LBL_OFFER'] ?></label>
                            <div class="discount-promos">
                                <div class="discount-capt">
                                    <img src="assets/img/discount_w.svg" alt="">
                                    <?= $Restaurant_OfferMessage; ?>
                                </div>
                            </div>
                        </fieldset>
                        <?php } ?>
                        <?php if ($selServiceId == 1) { ?>
                        <div class="food-cat-row">
                            <div class="food-cat-type vegType">
                                <span class="checkbox-holding">
                                <input value="Veg" type="checkbox" class="filer-non-veg-foodType" />
                                <span class="custom-check"></span>
                                </span
                                    ><label for="Veg"><?= $languageLabelsArr['LBL_VEGETARIAN']; ?></label>
                            </div>
                            <div class="food-cat-type nonvageType">
                                <span class="checkbox-holding">
                                <input value="NonVeg" type="checkbox" class="filer-non-veg-foodType" />
                                <span class="custom-check"></span>
                                </span
                                    ><label for="NonVeg"><?= $languageLabelsArr['LBL_NONVEGETARIAN']; ?></label>
                            </div>
                        </div>
                        <?php } ?>
                             
                    </div>
                </div>
                <div class="flex-row" id="flex-row-error" align="center" style="display:none;">
                    <h3 class="closetxt"><?= $Data[0]['Restaurant_Open_And_Close_time']; ?></h3>
                </div>
                <div class="rest-menu-main">
                    <div class="rest-menu-left">
                        <div class="search-main">
                            <div class="search-holder">
                                <input type="text" placeholder="<?= $languageLabelsArr['LBL_MANUAL_STORE_MENU_ITEM_SEARCH'] . " " . $languageLabelsArr['LBL_ITEM']; ?>" id="magicsearchingg"   class="magicsearch" name="magicsearching" onKeyUp="searching(this.value)"/>
                                <img src="<?= $siteUrl; ?>assets/img/cancel.svg" alt="" onClick="clearSearchBox();" class="close_ico" id="clearbutton"/>
                            </div>
                        </div>
                        <div class="leftbar-filter fixed_ele">
                            <div class="filter-data fixed_ele">
                                <nav>
                                    <ul>
                                        <?php if (count($Recomendation_Arr > 0)) { ?>
                                        <li><a class="active" id="activeTab_1" onclick="enableActiveTab('1');" href="#cat1"><?= $languageLabelsArr['LBL_RECOMMENDED']; ?></a></li>
                                        <?php } ?>
                                        <?php
                                            if (count($CompanyFoodData > 0)) {
                                                for ($ia = 0; $ia < count($CompanyFoodData); $ia++) {
                                                    if ($ia < 7) {
                                                        $vMenu = $CompanyFoodData[$ia]['vMenu'];
                                                        $iFoodMenuId = $CompanyFoodData[$ia]['iFoodMenuId'];
                                                        ?>
                                        <li><a onclick="enableActiveTab('<?= $iFoodMenuId; ?>');" id="activeTab_<?= $iFoodMenuId; ?>" href="#cat<?= $iFoodMenuId; ?>"><?= ucfirst($vMenu); ?></a></li>
                                        <?php
                                            }
                                            }
                                            ?>
                                        <?php } ?>
                                    </ul>
                                </nav>
                                <?php
                                    if (count($CompanyFoodData) > 7) {
                                        ?>
                                <div class="more-menu">
                                    <nav>
                                        <!-- <button>More...</button> -->
                                        <ul>
                                            <?php
                                                for ($ia = 7; $ia <= count($CompanyFoodData); $ia++) {
                                                
                                                    $vMenu = $CompanyFoodData[$ia]['vMenu'];
                                                    $iFoodMenuId = $CompanyFoodData[$ia]['iFoodMenuId'];
                                                    ?>
                                            <li><a href="#cat<?= $iFoodMenuId; ?>"><?= ucfirst($vMenu); ?></a></li>
                                            <?php } ?>
                                        </ul>
                                    </nav>
                                </div>
                                <?php } ?>
                            </div>
                        </div
                            >
                        <div class="product-list-right filter-menu-tem">
                            <?php if (count($Recomendation_Arr) <= 0 && count($CompanyFoodData) <= 0) { ?>
                            <section class="rest-menu-cat"  id="cat1">
                                <div class="hold-cat-title">
                                    <h3><?= $languageLabelsArr['LBL_MANUAL_STORE_NO_MATCH_ITEM']; ?></h3>
                                </div>
                            </section>
                            <?php } ?>  
                            <?php if (count($Recomendation_Arr) > 0) { ?>   
                            <section class="rest-menu-cat" id="cat1">
                                <div class="hold-cat-title">
                                    <h3><?= $languageLabelsArr['LBL_RECOMMENDED']; ?></h3>
                                    <span><?= count($Recomendation_Arr); ?> <?= $languageLabelsArr['LBL_MANUAL_STORE_MENU_LISTING_ITEMS'] ?></span>
                                </div>
                                <div class="flex-row mit" >
                                    <?php for ($i = 0; $i < count($Recomendation_Arr); $i++) { ?>
                                    <div class="menu-item-block ab-123" id="menuitem"   onclick="showMenuTypes(<?= $Recomendation_Arr[$i]['iMenuItemId']; ?>, 'add', '')">
                                        <?php if (!empty($Recomendation_Arr[$i]['vHighlightName'])) { ?>
                                        <h2 id="ribbon-container"><a id="ribbon" href="javascript:;"><?= $languageLabelsArr[$Recomendation_Arr[$i]['vHighlightName']]; ?></a></h2>
                                        <?php } ?>
                                        <?php
                                            if (empty($Recomendation_Arr[$i]['vImage'])) {
                                                /* if ($selServiceId == 1) {
                                                  $Recomendation_Arr[$i]['vImage'] = $siteUrl . '/assets/img/custome-store/food-menu-order-list.png';
                                                  } else {
                                                  $Recomendation_Arr[$i]['vImage'] = $siteUrl . '/assets/img/custome-store/deliveryall-menu-order-list.png';
                                                  } */
                                                $Recomendation_Arr[$i]['vImage'] = $siteUrl . 'assets/img/custome-store/food-menu-order-list.png';
                                            }
                                            ?>
                                        <div class="menu-item-image" style="background-image:url(<?= $Recomendation_Arr[$i]['vImage']; ?>);">
                                            <?php if (isset($Recomendation_Arr[$i]['vImage']) && !empty($Recomendation_Arr[$i]['vImage']) && $Recomendation_Arr[$i]['eFoodType'] == 'Veg') { ?>
                                            <img src="assets/img/veg.jpg" alt="" class="food-type-sym">
                                            <?php } else if (isset($Recomendation_Arr[$i]['vImage']) && !empty($Recomendation_Arr[$i]['vImage']) && $Recomendation_Arr[$i]['eFoodType'] == 'NonVeg') { ?>
                                            <img src="assets/img/non-veg.jpg" alt="" class="food-type-sym">
                                            <?php } ?>
                                        </div>
                                        <div class="menu-item-caption">
                                            <strong title="<?= $Recomendation_Arr[$i]['vItemType']; ?>"><?= $Recomendation_Arr[$i]['vItemType']; ?></strong>
                                            <span class="menu-item-desc"><?= $Recomendation_Arr[$i]['vCategoryName']; ?></span>
                                            <?php if ($Recomendation_Arr[$i]['fOfferAmt'] != "0" && $Recomendation_Arr[$i]['fPrice'] > $Recomendation_Arr[$i]['fDiscountPrice']) { ?><span style="text-decoration: line-through;"><?= $Recomendation_Arr[$i]['StrikeoutPrice']; ?></span><?php } ?>
                                            <div class="price-with-add">
                                                <span class="menu-item-price"><?= $Recomendation_Arr[$i]['fDiscountPricewithsymbol']; ?></span>
                                                <button class="add_cart"><?= $languageLabelsArr['LBL_ACTION_ADD']; ?></button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php } ?>
                                </div>
                            </section>
                            <?php } ?>
                            <?php
                                for ($ia = 0; $ia < count($CompanyFoodData); $ia++) {
                                
                                    $vMenuItemCount = $CompanyFoodData[$ia]['vMenuItemCount'];
                                    if ($vMenuItemCount > 0) {
                                        $vMenu = $CompanyFoodData[$ia]['vMenu'];
                                        $iFoodMenuId = $CompanyFoodData[$ia]['iFoodMenuId'];
                                        $menu_items = $CompanyFoodData[$ia]['menu_items'];
                                        //  echo '<pre>';
                                        // print_r($CompanyFoodData);
                                        ?>  
                            <section class="rest-menu-cat"  id="cat<?= $iFoodMenuId; ?>">
                                <div class="hold-cat-title">
                                    <h3><?= $vMenu; ?></h3>
                                    <span><?= $vMenuItemCount; ?> <?= $languageLabelsArr['LBL_MANUAL_STORE_MENU_LISTING_ITEMS'] ?></span>
                                </div>
                                <div class="flex-row" > 
                                    <?php for ($ii = 0; $ii < $vMenuItemCount; $ii++) { ?>  
                                    <?php } ?> 
                                </div>
                                <div class="flex-row" >
                                    <?php for ($ii = 0; $ii < $vMenuItemCount; $ii++) { ?>
                                    <div class="menu-item-block box-style" id="menuitem"  onclick="showMenuTypes(<?= $menu_items[$ii]['iMenuItemId']; ?>, 'add', '')" >
                                        <?php if (!empty($menu_items[$ii]['vHighlightName'])) { ?>
                                        <div class="mi-work"><a id="ribbon-category" href="javascript:;"> <?= $languageLabelsArr[$menu_items[$ii]['vHighlightName']]; ?></a></div>
                                        <?php } ?>
                                        <div class="menu-item-caption">
                                            <?php if (isset($menu_items[$ii]['vImage']) && !empty($menu_items[$ii]['vImage']) && $menu_items[$ii]['eFoodType'] == 'Veg') { ?>
                                            <img src="assets/img/veg.jpg" alt="" class="food-type-sym">
                                            <?php } else if (isset($menu_items[$ii]['vImage']) && !empty($menu_items[$ii]['vImage']) && $menu_items[$ii]['eFoodType'] == 'NonVeg') { ?>
                                            <img src="assets/img/non-veg.jpg" alt="" class="food-type-sym">
                                            <?php } ?>
                                            <strong title="<?= $menu_items[$ii]['vItemType']; ?>"><?= $menu_items[$ii]['vItemType']; ?></strong>
                                            <span class="menu-item-desc"><?= $menu_items[$ii]['vCategoryName']; ?></span>
                                            <!--<span class="menu-item-desc" title="<?= $menu_items[$ii]['vItemDesc']; ?>"><?= $menu_items[$ii]['vItemDesc']; ?></span>-->
                                            <?php if ($menu_items[$ii]['fOfferAmt'] != "0" && $menu_items[$ii]['fPrice'] > $menu_items[$ii]['fDiscountPrice']) { ?><span style="text-decoration: line-through;"><?= $menu_items[$ii]['StrikeoutPrice']; ?></span><?php } ?>
                                            <div class="price-with-add">
                                                <span class="menu-item-price"><?= $menu_items[$ii]['fDiscountPricewithsymbol']; ?></span>
                                                <button class="add_cart"><?= $languageLabelsArr['LBL_ACTION_ADD']; ?></button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php } ?>
                                </div>
                            </section>
                            <?php } ?>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="rest-menu-right">
                        <div class="checkout-block">
                            <div id="cart-data"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- home page end-->
            <!-- footer part -->
            <div class="product-model-overlay" id="myModal">
                <div class="product-model">
                    <div class="close-icon">
                        <svg width="16" height="16" viewBox="0 0 14 14">
                            <path fill='#fff' d="M7 5.586L2.05.636.636 2.05 5.586 7l-4.95 4.95 1.414 1.414L7 8.414l4.95 4.95 1.414-1.414L8.414 7l4.95-4.95L11.95.636 7 5.586z"></path>
                        </svg>
                    </div>
                    <div class="product-model-left" ></div>
                    <div class="product-model-right">
                        <div class="procutcarting-data">
                            <div class="pro-title-hold">
                                <h4 id="menuitename"></h4>
                                <input id="id"  name="id" value="<?= $iCompanyId; ?>" type="hidden">
                                <span class="stock-cost" id="menuitePrice"></span>
                                <span class="stock-cost" id="menuitemdesc"></span>
                            </div>
                            <div class="scroll-data">
                                <div  id="optionsvalue"></div>
                                <div class="special-instruct" id="special_instruct"></div>
                            </div>
                        </div>
                        <div class="increment-cart">
                            <div class="count-block" id="counter-block">
                                <button class="plus" id="numbercart_minus">-</button>
                                <span id="count-block"></span>
                                <button class="minus" id="numbercart_plus" >+</button>
                            </div>
                            <div  id="leading"></div>
                            <button class="addCart-button" id="addtocart" name="addtocart"><?= $languageLabelsArr['LBL_ADD_ITEM']; ?></button><span id="subtotalchange"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="product-model-overlay small-model" id="time-info-model">
                <div class="product-model payment-block-row">
                    <form class="add-new-card-data" name="frmcreditcard" id="frmcreditcard" onSubmit="return false;" novalidate="novalidate">
                        <div class="close-icon">
                            <svg width="16" height="16" viewBox="0 0 14 14">
                                <path fill="#fff" d="M7 5.586L2.05.636.636 2.05 5.586 7l-4.95 4.95 1.414 1.414L7 8.414l4.95 4.95 1.414-1.414L8.414 7l4.95-4.95L11.95.636 7 5.586z"></path>
                            </svg>
                        </div>
                        <h3><?= $openHourTxt; ?></h3>
                        <?php
                            if (count($timeSlotArr) > 0) {
                                for ($t = 0; $t < count($timeSlotArr); $t++) {
                                    ?>
                        <div class="flex-row time_info-data">
                            <div><?= $timeSlotArr[$t]['head']; ?>:</div>
                            <div><?= str_replace("&", "<br>", str_replace("-", " To ", $timeSlotArr[$t]['time'])); ?></div>
                        </div>
                        <?php
                            }
                            } else {
                            ?>
                        <div class="flex-row time_info-data">
                            <div><?= $notFoundTxt; ?></div>
                            <!--<div>8:06 am - 11:07pm</div>-->
                            <?php } ?>
                        </div>
                    </form>
                </div>
            </div>
            <?php include_once('footer/footer_home.php'); ?>    
            <?php
                $lang = get_langcode($vLang);
                $_SESSION['sess_language'] = $lang;
                ?>
            <script type="text/javascript" src="assets/js/validation/jquery.validate.min.js" ></script>
            <?php if ($lang != 'en') { ?>
                <!-- <script type="text/javascript" src="assets/js/validation/localization/messages_<?= $lang; ?>.js" ></script> -->
                <? include_once('otherlang_validation.php');?>
            <?php } ?>
            <script type="text/javascript" src="assets/js/validation/additional-methods.js" ></script>
            <!-- End:contact page-->
            <div style="clear:both;"></div>
        </div>
        <script type="text/javascript">
            $(document).on('click', '.open-popup', function (e) {
                var DATAID = $(this).attr('data-id');
                $('.small-model').removeClass('active');
                $(document).find('#' + DATAID).addClass('active');
            });
            
            var optionData = [];
            var isPriceShow;
            var optionscountervalue;
            var jsonaddon = [];
            var addoncountervalue;
            var otherData = [];
            var othercountervalue;
            var subresult;
            var subresultvalues;
            var subresultvaluesconter;
            var otherinputType;
            var OptionMinSelection, OptionMaxSelection;
            var radioValueother;
            var eOptionInputType;
            var checkeddata = "No";
            var ItemId;
            var MenuId;
            var id;
            var result;
            var typed, no, qty;
            var restaurant_status = '<?= (strtolower($restaurantstatus)); ?>';
            var typeofitem;
            var lastEnabledTab = 1;
            deliveryAddressCount = "";
            if (restaurant_status == 'closed')
            {
                $('#counter-block').hide();
            }
            var radioValue = '';
            function enableActiveTab(foodMenuId) {
                $("#activeTab_" + lastEnabledTab).removeClass("active");
                lastEnabledTab = foodMenuId;
                $("#activeTab_" + foodMenuId).addClass("active");
            }
            function showMenuTypes(id, typed, no)
            {
                var tInst = '';
                var oselected, tselected;
                var valsother = '';
                $(".loader-default").fadeIn("slow");
                var Company = '<?= $iCompanyId; ?>';
                var fromOrder = '<?= $fromOrder; ?>';
                $.ajax({
                    type: "POST",
                    url: "ajax_load_model_cart.php",
                    data: {id: id, Company: Company, typed: typed, no: no, fromorder: fromOrder},
                    dataType: "json",
                    success: function (dataHtml)
                    {
                        if (dataHtml.counter == "1")
                        {
                            result = dataHtml.responce;
                            isPriceShow = dataHtml.ispriceshow;
                            var vImage = result.vImage;
                            qty = parseInt(result.Qty);
                            typeofitem = result.type;
                            if (result.vImage1 == '')
                            {
                                //$('.product-model').addClass('no-img-availabel');
                                $('.product-model-left').addClass('hasPlaceHolder');
                                $('.product-model-left').css('background-image', 'url(' + vImage + ')');
                            } else {
                                //$('.product-model-left').addClass('hasPlaceHolder');
                                $('.product-model-left').css('background-image', 'url(' + vImage + ')');
                            }
                            optionData = result.options;
                            optionscountervalue = result.optionscounter;
                            jsonaddon = result.addon;
                            addoncountervalue = result.addoncounter;
                            othercountervalue = result.otherAddonscounter;
                            subresult = result.otherAddons;
                            $('#menuitename').html(result.vItemType);
                            var PriceData = "";
                            if (result.discountoption == 'Yes')
                            {
                                PriceData += result.LBL_PRICE_FOR_MENU_ITEM + ': ' + result.fdiscountedPrice + '&nbsp;&nbsp;<span style="text-decoration: line-through">' + result.fmainPrice + '</span> ';
                            } else {
                                PriceData += result.LBL_PRICE_FOR_MENU_ITEM + ': ' + result.fmainPrice;
                            }
                            $('#menuitePrice').html(PriceData);
                            var itemDescription = result.vItemDesc;
                            $('#menuitemdesc').html("");
                            if (itemDescription.trim() != "") {
                                //$('#menuitemdesc').html(result.LBL_DESCRIPTION + ": " + result.vItemDesc); // Removed Lable As Per Discuss with CD sir On 27-06-2019
                                $('#menuitemdesc').html(result.vItemDesc);
                            }
                            $('#optionsvalue').html("");
                            if (result.optionscounter > 0)
                            {
                                $('#optionsvalue').html('<div class="extra-det" ><div><strong>' + result.LBL_SELECT_OPTIONS + '</strong><span>(<?= $languageLabelsArr['LBL_MANUAL_STORE_CHOOSE_MIN_ONE']; ?>)</span></div><label><?= $languageLabelsArr['LBL_MANUAL_STORE_POPUP_REQUIRED']; ?></label></div>');
                                var resultData = "<ul class='what-extra'>";
                                resultData += '<span id="optionserror" style="margin-left: 10px; color: #fb0000; display:none;"></span>';
                                for (var i = 0; i < result.optionscounter; i++)
                                {
                                    oselected = result.options[i].selected;
                                    resultData += '<li>';
                                    resultData += '<div class="label-data-hold">';
                                    resultData += '<span class="radio-holder">';
                                    if (oselected == 'Yes')
                                    {
                                        resultData += '<input type="radio" name="options" checked id="options" onChange="options(this.value)" value="' + result.options[i].iOptionId + '"/>';
                                    } else {
                                        var checked = "";
                                        var iServiceId = '<?= $selServiceId ?>';
                                        if (i == 0) {
                                            checked = 'checked';
                                        }
                                        resultData += '<input type="radio" ' + checked + ' name="options" id="options" onChange="options(this.value)" value="' + result.options[i].iOptionId + '"/>';
                                    }
            
                                    resultData += '<input type="radio" name="options" id="options" onChange="options(this.value)" value="' + result.options[i].iOptionId + '"/>';
                                    resultData += '<span class="radio-box"></span>';
                                    resultData += '</span>';
                                    resultData += '<label for="1">' + result.options[i].vOptionName + '</label>';
                                    resultData += '</div>';
                                    resultData += '<div class="costing">' + result.options[i].fUserPriceWithSymbol + '</div>';
                                    resultData += '</li>';
                                }
                                resultData += '</ul>';
                                $('#optionsvalue').append(resultData);
                            }
                            if (result.addoncounter > 0)
                            {
                                $('#optionsvalue').append('<div class="extra-det"><div><strong>' + result.LBL_SELECT_TOPPING + '</strong></div></div>');
                                var resultAddonData = "<ul class='what-extra'>";
                                resultAddonData += '<span id="addonerror" style="margin-left: 10px; color: #fb0000; display:none;"></span>';
                                for (var ii = 0; ii < result.addoncounter; ii++)
                                {
                                    tselected = result.addon[ii].selected;
                                    resultAddonData += '<li>';
                                    resultAddonData += '<div class="label-data-hold">';
                                    resultAddonData += '<span class="check-holder">';
                                    if (tselected == 'Yes')
                                    {
                                        resultAddonData += '<input type="checkbox" id="addon" checked name="addon[]" onChange="addon(this.value)" value="' + result.addon[ii].iOptionId + '"/>';
                                    } else {
            
                                        resultAddonData += '<input type="checkbox" id="addon" name="addon[]" onChange="addon(this.value)" value="' + result.addon[ii].iOptionId + '"/>';
                                    }
                                    resultAddonData += '<span class="check-box"></span>';
                                    resultAddonData += '</span>';
                                    resultAddonData += '<label for="1">' + result.addon[ii].vOptionName + '</label>';
                                    resultAddonData += '</div>';
                                    resultAddonData += '<div class="costing">' + result.addon[ii].fUserPriceWithSymbol + '</div>';
                                    resultAddonData += '</li>';
                                }
                                resultAddonData += '</ul>';
                                $('#optionsvalue').append(resultAddonData);
                            }
            
                            $('#count-block').html('<input id="numbercart"  name="numbercart" value="' + qty + '" type="text" readonly>');
                            $('#count-block').append('<input id="typeofitem"  name="typeofitem" value="' + typeofitem + '" type="hidden" readonly><input id="no"  name="no" value="' + no + '" type="hidden" readonly>');
                            $('#subtotalchange').html(result.fDiscountPricewithsymbol);
                            $('#leading').html('<input id="price"  name="price" value="' + result.fDiscountPricest + '" type="hidden"/>');
                            $('#leading').append('<input id="currencySymbol"  name="currencySymbol" value="' + result.currencySymbol + '" type="hidden"/>');
                            $('.product-model-overlay#myModal').addClass('active');
                        }
                        $(".loader-default").fadeOut("slow");
                    }
            
                });
            }
        </script>
        <script>
            var numbers;
            var price;
            var currencySymbol;
            var newnumber;
            loadingtime();
            function loadingtime() {
                $('#cart-data').html('');
                id = $('#id').val();
                var fromOrder = '<?= $fromOrder; ?>';
                $.ajax({type: "POST",
                    url: "ajax_view_cart_to_restaurant.php",
                    data: {id: id, fromorder: fromOrder},
                    dataType: "html",
                    success: function (dataHtml)
                    {
                        $('#cart-data').show();
                        $('#cart-data').html(dataHtml);
                    }
                });
            }
            function remove_item(ids,menuItemId)
            {
                var cart_id_update = ids;
                //id = $('#id').val();
                var fromOrder = '<?= $fromOrder; ?>';
                $.ajax({type: "POST",
                    url: "remove_item_cart_to_restaurant.php",
                    data: {removeid: cart_id_update, id: menuItemId, fromorder: fromOrder},
                    dataType: "json",
                    success: function (data)
                    {
            
                        $.ajax({
                            type: "POST",
                            url: "ajax_get_values_cart_to_restaurant.php",
                            data: {id: menuItemId, cart_id: cart_id_update, fromorder: fromOrder},
                            dataType: "JSON",
                            success: function (dataHtml)
                            {
            
                                if (dataHtml.TotaliQty > dataHtml.iMaxItemQty) {
                                    $('.msgmaxquty').removeClass('hide').addClass('show');
                                    $('.btnstatus').html('<button  disabled><?= $languageLabelsArr['LBL_CHECKOUT']; ?></button>');
                                } else {
                                    $('.msgmaxquty').removeClass('show').addClass('hide');
                                    $('.btnstatus').html('<form  action="store-order?order=<?= $fromOrder; ?>" method="post"><input type="hidden" id="id" value="<?= $iCompanyId ?>" name="id"><button type="submit" id="checkout-block" onClick="return changeValidation(' + dataHtml.fFinalTotal + ',' + dataHtml.fMinOrderValue + ')"><?= $languageLabelsArr['LBL_CHECKOUT']; ?></button></form>');
                                }
            
                                $("#number_update" + cart_id_update).hide();
                                if (dataHtml.TotaliQty == 0)
                                {
                                    $('#usershoppingcart').html('<i class="fa fa-shopping-cart  fa-2x"style="color: #ffffff !important;"></i><span style="float:right;"></span>');
                                    $('#cart-data').html('<div class="btn-hold"><button  disabled><?= $languageLabelsArr['LBL_CHECKOUT']; ?></button></div><div class="note_"id="note_cart"><?= $languageLabelsArr['LBL_MANUAL_STORE_ADD_ITEMS']; ?></div>');
                                } else {
                                    $('#usershoppingcart').html('<a href="store-order?order=<?= $fromOrder; ?>" ><i class="fa fa-shopping-cart  fa-2x"style="color: #1e9698 !important;"></i>&nbsp;<span style="float:right; color: #1e9698 !important;">' + dataHtml.TotaliQty + '</span></a>');
                                    $("#checkout-block").prop('disabled', false);
                                    if (dataHtml.TotaliQty == 1)
                                    {
                                        $('#total-row').html('<div class="total-row" style="display:flex;"> <div><?= $languageLabelsArr['LBL_SUBTOTAL_TXT'] ?> (' + dataHtml.TotaliQty + ' <?= $languageLabelsArr['LBL_MANUAL_STORE_MENU_LISTING_ITEM'] ?>)</div> <div id="subtotalamount">' + dataHtml.fSubTotal + '</div></div>');
                                    } else {
                                        $('#total-row').html('<div class="total-row" style="display:flex;"> <div><?= $languageLabelsArr['LBL_SUBTOTAL_TXT'] ?> (' + dataHtml.TotaliQty + ' <?= $languageLabelsArr['LBL_MANUAL_STORE_MENU_LISTING_ITEMS'] ?>)</div><div id="subtotalamount">' + dataHtml.fSubTotal + '</div> </div>');
                                    }
                                }
            
                            }
                        });
                    }
                });
            }
            
            function numbercart_minus_update(ids, cart_id)
            {
                var minusnumber = 1;
                var cart_id_update = cart_id;
                numbers = ids;
                if (numbers != 1) {
                    newnumber = parseInt(numbers) - minusnumber;
                    id = $('#id').val();
                    var fromOrder = '<?= $fromOrder; ?>';
                    $.ajax({
                        type: "POST",
                        url: "update_qty_item_cart_restaurant.php",
                        data: {id: id, cart_id_update: cart_id_update, numbercart_update: newnumber, fromorder: fromOrder},
                        dataType: "json",
                        success: function (data)
                        {
            
            
                            $('.msgminimumtotal').removeClass('show').addClass('hide');
                            $("#number_update" + cart_id).html('<button class="plus" onClick="numbercart_minus_update(' + newnumber + ',' + cart_id + ')"></button><input  id="cart_id_update"  value="' + cart_id + '" type="hidden">                                      <input id="numbercart_update' + cart_id + '" class="numbercart_update"  name="numbercart_update" value="' + newnumber + '" type="text" readonly><button class="minus" onClick="numbercart_plus_update(' + newnumber + ',' + cart_id + ')"></button>');
                            getCartDataUpdate(cart_id_update);
                        }
                    });
                }
            }
            function numbercart_plus_update(ids, cart_id)
            {
                var plusnumber = 1;
                var cart_id_update = cart_id;
                numbers = ids;
                newnumber = parseInt(numbers) + plusnumber;
                id = $('#id').val();
                var fromOrder = '<?= $fromOrder; ?>';
                $.ajax({type: "POST",
                    url: "update_qty_item_cart_restaurant.php",
                    data: {id: id, cart_id_update: cart_id_update, numbercart_update: newnumber, fromorder: fromOrder},
                    dataType: "json",
                    success: function (data)
                    {
                        $('.msgminimumtotal').removeClass('show').addClass('hide');
                        $("#number_update" + cart_id).html('<button class="plus" onClick="numbercart_minus_update(' + newnumber + ',' + cart_id + ')"></button><input  id="cart_id_update"  value="' + cart_id + '" type="hidden">                                      <input id="numbercart_update' + cart_id + '" class="numbercart_update"  name="numbercart_update" value="' + newnumber + '" type="text" readonly><button class="minus" onClick="numbercart_plus_update(' + newnumber + ',' + cart_id + ')"></button>');
                        getCartDataUpdate(cart_id_update);
                    }
                });
            }
            function getCartDataUpdate(cart_id_update){
                var id = $('#id').val();
                var fromOrder = '<?= $fromOrder; ?>';
                $.ajax({    
                    type: "POST",
                    url: "ajax_get_values_cart_to_restaurant.php",
                    data: {id: id, cart_id_update: cart_id_update, fromorder: fromOrder},
                    dataType: "JSON",
                    success: function (dataHtml)
                    {
                        if (dataHtml.TotaliQty > dataHtml.iMaxItemQty) {
                            $('.msgmaxquty').removeClass('hide').addClass('show');
                            $('.btnstatus').html('<button  disabled><?= $languageLabelsArr['LBL_CHECKOUT']; ?></button>');
                        } else {
                            $('.msgmaxquty').removeClass('show').addClass('hide');
                            $('.btnstatus').html('<form  action="store-order?order=<?= $fromOrder; ?>" method="post"><input type="hidden" id="id" value="<?= $iCompanyId ?>" name="id"><button type="submit" id="checkout-block"  onClick="return changeValidation(' + dataHtml.fFinalTotal + ',' + dataHtml.fMinOrderValue + ')"><?= $languageLabelsArr['LBL_CHECKOUT']; ?></button></form>');
                        }
                        $('#show_price_update' + cart_id_update).html('<span class="cart-item-cost" >' + dataHtml.showfPrice + '</span>');
                        $('#usershoppingcart').html('<a href="store-order?order=<?= $fromOrder; ?>" ><i class="fa fa-shopping-cart  fa-2x"style="color: #1e9698 !important;"></i>&nbsp;<span style="float:right; color: #1e9698 !important;">' + dataHtml.TotaliQty + '</span></a>');
                        if (dataHtml.TotaliQty == 1)
                        {
                            $('#total-row').html('<div class="total-row" style="display:flex;"> <div><?= $languageLabelsArr['LBL_SUBTOTAL_TXT'] ?> (' + dataHtml.TotaliQty + ' <?= $languageLabelsArr['LBL_MANUAL_STORE_MENU_LISTING_ITEM'] ?>)</div> <div id="subtotalamount">' + dataHtml.fSubTotal + '</div></div>');
                        } else {
            
                            $('#total-row').html('<div class="total-row" style="display:flex;"> <div><?= $languageLabelsArr['LBL_SUBTOTAL_TXT'] ?> (' + dataHtml.TotaliQty + ' <?= $languageLabelsArr['LBL_MANUAL_STORE_MENU_LISTING_ITEMS'] ?>)</div> <div id="subtotalamount">' + dataHtml.fSubTotal + '</div></div>');
                        }
            
            
                    }
                });
                loadingtime();
            }
            $('#numbercart_minus').on('click', function ()
            {
                var minusnumber = 1;
                numbers = $('#numbercart').val();
                price = $('#price').val();
                currencySymbol = $('#currencySymbol').val();
                if (numbers != 1) {
            
                    newnumber = parseInt(numbers) - minusnumber;
                    $("#numbercart").val(newnumber);
                    grandtotal(newnumber, price, currencySymbol);
                }
            });
            function otheraddon(id)
            {
                var numbers = $('#numbercart').val();
                var price = $('#price').val();
                var currencySymbol = $('#currencySymbol').val();
                grandtotal(numbers, price, currencySymbol);
            }
            
            var valsother;
            var valsothertotal = '';
            var radioValueotherother;
            function grandtotal(numbers, price, currencySymbol)
            {
                console.log(numbers + "==" + price + "==" + currencySymbol);
                price = parseFloat(price.replace(/,/g, ''));
                valsother = '';
                radioValueotherother = '';
                var radioValueother = '';
                var arr = new Array();
                arr = optionData;
                var arraddon = new Array();
                arraddon = jsonaddon;
                var newnumber = parseInt(numbers);
                var subprice = parseFloat(price) * newnumber;
                var OptionPrice;
                var OptionPriceother;
                var sumaddon = 0;
                var sumTotaladdon = 0;
                var itemtoptions = 0;
                var sumother = 0;
                var sumTotalother = 0;
                var suboptionother = 0;
                var totalsumother = 0;
                var suboption = 0;
                radioValue = $("input[name='options']:checked").val();
                if (radioValue || undefined)
                {
                    document.getElementById("optionserror").style.display = "none";
                    document.getElementById("optionserror").innerHTML = "";
                }
            
                if (optionscountervalue > 0)
                {
                    for (var i = 0; i < optionscountervalue; i++) {
                        var iOptionId = arr[i].iOptionId;
                        if (radioValue == iOptionId)
                        {
                            OptionPrice = arr[i].fPrice;
                            suboption += (parseFloat(OptionPrice));
                            console.log(radioValue + "==" + OptionPrice + "==" + suboption);
                        }
                    }
                }
                var vals = '';
                if (addoncountervalue > 0) {
                    var checkboxes = document.getElementsByName('addon[]');
                    for (var i = 0, n = checkboxes.length; i < n; i++)
                    {
                        if (checkboxes[i].checked)
                        {
                            vals += "," + checkboxes[i].value;
                        }
                    }
                    if (vals) {
                        //vals = vals.substring(1); //Commented By HJ On 07-06-2019 For Solved When Edit Item at that tiem wrong Calculation issue
                        vals = vals.replace(/(^,)|(,$)/g, ""); //Added By HJ On 07-06-2019 For Solved When Edit Item at that tiem wrong Calculation issue
                    }
                    for (var i = 0; i < addoncountervalue; i++) {
                        var iOptionIdaddon = arraddon[i].iOptionId;
                        if (vals.indexOf(iOptionIdaddon) !== -1) {
                            OptionPrice = arraddon[i].fPrice;
                            sumaddon += parseFloat(OptionPrice);
                        }
                    }
                }
                if (isPriceShow != '' && isPriceShow == 'separate') {
                    var subtotals = parseFloat(totalsumother) + parseFloat(suboption) + parseFloat(sumaddon);
                    if (subtotals == 0) {
                        subtotals = price;
                    }
                } else {
                    var subtotals = price + parseFloat(totalsumother) + parseFloat(suboption) + parseFloat(sumaddon);
                }
                var full = (parseFloat(subtotals) * parseInt(numbers));
                var fullprice = currencySymbol + " " + full.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                $("#subtotalchange").html(fullprice);
            }
            $(window).scroll(function (event) {
                var scroll = $(window).scrollTop();
                // Do something
            });
            function options(id) {
                numbers = $('#numbercart').val();
                price = $('#price').val();
                currencySymbol = $('#currencySymbol').val();
                grandtotal(numbers, price, currencySymbol);
            }
            function addon(id) {
                numbers = $('#numbercart').val();
                price = $('#price').val();
                currencySymbol = $('#currencySymbol').val();
                grandtotal(numbers, price, currencySymbol);
            }
            $('#numbercart_plus').on('click', function () {
                var plusnumber = 1;
                numbers = $('#numbercart').val();
                price = $('#price').val();
                currencySymbol = $('#currencySymbol').val();
                var newnumber = (plusnumber + parseInt(numbers));
                $("#numbercart").val(newnumber);
                grandtotal(newnumber, price, currencySymbol);
            });
        </script>
        <script>
            $("#addtocart").on('click', function ()
            {
                var counters;
                counters = 0;
                var totalcounters = 0;
                var onscounters = 0;
                var addcounters = 0;
                var othecounters = 0;
                var onscs = 1;
                var no = $('#no').val();
                var typeofitem = $('#typeofitem').val();
                var id = $('#id').val();
                if (optionscountervalue > 0)
                {
                    var radioValue = '';
                    var radioValues = '';
                    radioValues = document.getElementsByName('options');
                    for (var i = 0, length = radioValues.length; i < length; i++) {
                        if (radioValues[i].checked)
                        {
                            onscs = 0;
                        }
                    }
                    if (onscs == '1')
                    {
                        document.getElementById("optionserror").style.display = "block";
                        document.getElementById("optionserror").innerHTML = "<?= $languageLabelsArr['LBL_MANUAL_STORE_VALIDATION_ATLEAST_ONE']; ?>";
                        onscounters = 1;
                    } else {
                        onscounters = 0;
                    }
                    radioValue = $("input[name='options']:checked").val();
                }
                if (addoncountervalue > 0)
                {
                    var vals = '';
                    var checkboxes = document.getElementsByName('addon[]');
                    for (var i = 0, n = checkboxes.length; i < n; i++)
                    {
                        if (checkboxes[i].checked)
                        {
                            vals += "," + checkboxes[i].value;
                        }
                    }
            
                    if (vals) {
                        //vals = vals.substring(1); //Commented By HJ On 07-06-2019 For Solved When Edit Item at that tiem wrong Calculation issue
                        vals = vals.replace(/(^,)|(,$)/g, ""); //Added By HJ On 07-06-2019 For Solved When Edit Item at that tiem wrong Calculation issue
                    }
                    addcounters = 0;
                }
                totalcounters = othecounters + addcounters + onscounters;
                if (totalcounters == 0) {
                    if (restaurant_status == 'closed') {
                        $('#flex-row-error').show();
                        $(window).scrollTop($('#flex-row-error').offset().top);
                        $('.product-model-overlay#myModal').removeClass('active');
                        return true;
                    }
                    var fromOrder = '<?= $fromOrder; ?>';
                    if (typeofitem == "Add")
                    {
                        var id = $('#id').val();
                        var inst = $('#inst').val();
                        var numberss = $('#numbercart').val();
                        ItemId = result.ItemId;
                        MenuId = result.MenuId;
                        eFoodType = result.eFoodType;
                        $.ajax({
                            type: "POST",
                            url: "add_cart_to_restaurant.php",
                            data: {MenuItemId: ItemId, FoodMenuId: MenuId, id: id, numbers: numberss, addon: vals, option: radioValue, addonother: valsother, optionother: radioValueotherother, inst: inst, eFoodType: eFoodType, fromorder: fromOrder}, dataType: "json",
                            success: function (dataHtml)
                            {
                                $('#usershoppingcart').html('<a href="store-order?order=<?= $fromOrder; ?>" ><i class="fa fa-shopping-cart  fa-2x"style="color: #1e9698 !important;"></i>&nbsp;<span style="float:right; color: #1e9698 !important;">' + dataHtml.totalcounter + '</span></a>');
                                $.ajax({
                                    type: "POST",
                                    url: "ajax_view_cart_to_restaurant.php",
                                    data: {MenuItemId: ItemId, FoodMenuId: MenuId, id: id, numbers: numberss, addon: vals, option: radioValue, fromorder: fromOrder},
                                    dataType: "html",
                                    success: function (dataHtml)
                                    {
                                        $('#cart-data').show();
                                        $('#cart-data').html(dataHtml);
                                        $('.product-model-overlay#myModal').removeClass('active');
                                    }
                                });
                            }
                        });
                    } else {
            
                        var id = $('#id').val();
                        var inst = $('#inst').val();
                        var numberss = $('#numbercart').val();
                        ItemId = result.ItemId;
                        MenuId = result.MenuId;
                        eFoodType = result.eFoodType;
                        $.ajax({
                            type: "POST",
                            url: "update_item_cart_restaurant.php",
                            data: {MenuItemId: ItemId, FoodMenuId: MenuId, no: no, id: id, numbers: numberss, addon: vals, option: radioValue, addonother: valsother, optionother: radioValueotherother, inst: inst, eFoodType: eFoodType, fromorder: fromOrder}, dataType: "json",
                            success: function (dataHtml)
                            {
            
                                $('#usershoppingcart').html('<a href="store-order?order=<?= $fromOrder; ?>" ><i class="fa fa-shopping-cart  fa-2x"style="color: #1e9698 !important;"></i>&nbsp;<span style="float:right; color: #1e9698 !important;">' + dataHtml.totalcounter + '</span></a>');
                                $.ajax({
                                    type: "POST",
                                    url: "ajax_view_cart_to_restaurant.php",
                                    data: {MenuItemId: ItemId, FoodMenuId: MenuId, id: id, numbers: numberss, addon: vals, option: radioValue, fromorder: fromOrder},
                                    dataType: "html",
                                    success: function (dataHtml)
                                    {
                                        $('#cart-data').show();
                                        $('#cart-data').html(dataHtml);
                                        $('.product-model-overlay#myModal').removeClass('active');
                                    }
                                });
                            }
                        });
                    }
                }
            });
            $('.filer-non-veg-foodType').on('click', function (ev) {
                var filternonveg = '';
                var id = '<?= $iCompanyId ?>';
                var fromOrder = '<?= $fromOrder; ?>';
                $('.filer-non-veg-foodType').each(function () {
                    if (this.checked) {
                        filternonveg += this.value;
                    }
                });
                var search = $('#magicsearchingg').val();
                $.ajax({
                    type: "POST",
                    url: "ajax_filter_restaurant_menu_item.php",
                    data: {id: id, CheckNonVegFoodType: filternonveg, searchword: search, fromorder: fromOrder},
                    success: function (dataHtml)
                    {
                        if (dataHtml != "") {
            
                            $('.filter-menu-tem').html(dataHtml);
                        }
                    }
                });
            });
            $('.favouriteManualStore').on('click', function (ev) {
                var favStore = '';
                var id = '<?= $iCompanyId ?>';
                var iServiceId = '<?= $selServiceId ?>';
                var iUserId = '<?= $iUserId ?>';
                var fromOrder = '<?= $fromOrder; ?>';
                favStore = 'No';
                $('.favouriteManualStore').each(function () {
                    if (this.checked) {
                        favStore = this.value;
                    }
                });
                $.ajax({
                    type: "POST",
                    url: "ajax_fav_manual_store.php",
                    data: {iCompanyId: id, iUserId: iUserId, iServiceId: iServiceId, eFavStore: favStore, fromorder: fromOrder},
                    success: function (dataHtml)
                    {
                        if (dataHtml == "sucess") {
                            $("#favlabel").text('<?= $favStoreLbl; ?>');
                            //$(".favouriteManualStore").prop('checked', true);
                        } else {
                            $("#favlabel").text('<?= $addFavStoreLbl; ?>');
                            //$(".favouriteManualStore").prop('checked', false);
                        }
                    }
                });
            });
            
            setTimeout(function () {
                $('#flex-row-error').hide();
            }, 5000);
            
            function clearSearchBox() {
                $("#magicsearchingg").val("");
                searching();
            }
            function searching() {
                var filternonveg = '';
                var id = '<?= $iCompanyId ?>';
                var fromOrder = '<?= $fromOrder; ?>';
            <?php if ($selServiceId == 1) { ?>
                    $('.filer-non-veg-foodType').each(function () {
                        if (this.checked) {
                            filternonveg += this.value;
                        }
                    });
            <?php } ?>
                var search = $('#magicsearchingg').val();
                if (search == "") {
                    $('#clearbutton').removeClass('showClear');
                } else {
                    $('#clearbutton').addClass('showClear');
                }
            
                $.ajax({
                    type: "POST",
                    url: "ajax_filter_restaurant_menu_item.php",
                    data: {id: id, CheckNonVegFoodType: filternonveg, searchword: search, fromorder: fromOrder},
                    success: function (dataHtml)
                    {
                        if (dataHtml != "") {
                            $('.filter-menu-tem').html(dataHtml);
                        }
                    }
                });
            }
            ;
            function changeValidation(itemtotal, minordervalue) {
                if (itemtotal < minordervalue) {
                    $('.msgminimumtotal').removeClass('hide').addClass('show');
                    return false;
                } else {
                    $('.msgminimumtotal').removeClass('show').addClass('hide');
                    return true;
                }
            
            }
            function resetServiceCatagory() {
                var e = document.getElementById("servicename");
                var serviceId = e.options[e.selectedIndex].value;
                var serviceName = e.options[e.selectedIndex].text;
                var cartAmount = $("#subtotalamount").text();
                var cartTotItems = "<?= $confirlAlert; ?>";
                var fromOrder = '<?= $fromOrder; ?>';
                if (cartTotItems > 0 || cartAmount.trim() != "") {
                    if (confirm("<?= $confirmLabel; ?>")) {
                        window.location.href = 'store-listing?sid=' + serviceId + '&order=' + fromOrder;
                    }
                } else {
                    window.location.href = 'store-listing?sid=' + serviceId + '&order=' + fromOrder;
                }
            }
            function displayStoreTime() {
                $('#time-info-model').addClass('active');
            }
        </script>
        <!-- footer part end -->
        <!-- Footer Script -->
        <?php include_once('top/footer_script.php'); ?>
        <!-- End: Footer Script -->
    </body>
</html>
<script>
    var sections = $('section');
    var nav = $('nav');
    var nav_height = nav.outerHeight();
    $(window).on('scroll', function () {
        var cur_pos = $(this).scrollTop();
        sections.each(function () {
            var top = $(this).offset().top - nav_height-200;
            var bottom = top + $(this).outerHeight();
            if (cur_pos >= top && cur_pos <= bottom) {
                nav.find('a').removeClass('active');
                sections.removeClass('active');
                $(this).addClass('active');
                nav.find('a[href="#' + $(this).attr('id') + '"]').addClass('active');
            }
        });
    });
</script>