<?php

    include_once("common.php");
    //added by SP for cubex changes on 07-11-2019

    if(isStoreCategoriesEnable()) {
        include_once("cx-restaurant_listing.php");
        exit;
    }
 
    // include_once("cx-restaurant_listing.php");
    // exit;
    $confirlAlert = 0;
    include_once ('include_generalFunctions_dl.php');
    check_type_wise_mr('restaurant_listing');
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
    $vLang = "EN";
    if (isset($_SESSION['sess_lang'])) {
        $vLang = $_SESSION['sess_lang'];
    }
    if (isset($_REQUEST['sid']) && $_REQUEST['sid'] > 0) {
        unset($_SESSION[$orderDetailsSession]);
        unset($_SESSION[$orderDataSession]);
        unset($_SESSION[$orderServiceSession]);
        unset($_SESSION[$orderCouponSession]);
        unset($_SESSION[$orderCouponNameSession]);
        unset($_SESSION[$orderServiceNameSession]);
        $_SESSION[$orderServiceSession] = $_REQUEST['sid'];
        $service_categories = array();
        if (isset($serviceCategoriesTmp) && !empty($serviceCategoriesTmp)) {
            $service_categories = $serviceCategoriesTmp;
            $_SESSION[$orderServiceNameSession] = $service_categories[($_SESSION[$orderServiceSession] - 1)]['vServiceName'];
        }
        $_SESSION[$orderServiceNameSession] = $service_categories[($_SESSION[$orderServiceSession] - 1)]['vServiceName'];
    }
    //echo "<pre>";print_r($_SESSION);die;
    //echo $selServiceName;die;
    $meta = $generalobj->getStaticPage(1, $vLang);
    $_SESSION['sess_language'] = $vLang;
    if (!isset($_SESSION[$orderServiceSession]) || empty($_SESSION[$orderServiceSession]) || !in_array($_SESSION[$orderServiceSession], $service_categories_ids_arr)) {
        unset($_SESSION[$orderServiceSession]);
        header("location:user-order-information?order=" . $fromOrder);
        exit;
    } else {
        $iServiceId = $_SESSION[$orderServiceSession];
    }
   if(checkSystemStoreSelection() && $fromOrder!="admin") {
    $service_categories = array();
    if (isset($serviceCategoriesTmp) && !empty($serviceCategoriesTmp)) {
        $service_categories = $serviceCategoriesTmp;
    }
    
    //$cnt_sc = count($service_categories);
    //if($cnt_sc==1) {
        session_start();
        $store_data = $generalobj->getStoreDataForSystemStoreSelection($iServiceId);
        //$iCompanyId = $store_data[0]['iCompanyId'];
        $iCompanyId = $store_data['iCompanyId'];
        $_SESSION[$orderLongitudeSession] = $store_data['vRestuarantLocationLat'];
        $_SESSION[$orderLatitudeSession] = $store_data['vRestuarantLocationLong'];
        $_SESSION[$orderServiceSession] = $store_data['iServiceId'];
        $_SESSION[$orderAddressSession] = $store_data['vCaddress'];
        $_SESSION[$orderServiceNameSession] = $service_categories[($store_data['iServiceId'] - 1)]['vServiceName'];
        header("location: store-items?id=" . $iCompanyId . "&order=" . $fromOrder);
        exit;
    //}
}
    global $intervalmins;
    //$vTimeZone = "Asia/Kolkata";
    $vTimeZone = date_default_timezone_get();
    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
    $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
    //$LIST_RESTAURANT_LIMIT_BY_DISTANCE = $generalobj->getConfigurations("configurations", "LIST_RESTAURANT_LIMIT_BY_DISTANCE");
    //$DRIVER_REQUEST_METHOD = $generalobj->getConfigurations("configurations", "DRIVER_REQUEST_METHOD");
    $param = ($DRIVER_REQUEST_METHOD == "Time") ? "tOnline" : "tLastOnline";
    $script = "Restaurant";
    $vServiceAddress = 0;
    $vLatitude = $vLongitude = $iUserId = $iUserAddressId = "";
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
    $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
    $selServiceName = "";
    $atText = $languageLabelsArr['LBL_AT_TXT'];
    if (isset($_SESSION[$orderServiceNameSession]) && $_SESSION[$orderServiceNameSession] != "") {
        $selServiceName = $_SESSION[$orderServiceNameSession] . " " . $atText . " - ";
    }
    if (!empty($iUserId) && empty($vLongitude) && empty($vLatitude) && !empty($iUserAddressId)) {
        if (empty($iUserId) || empty($iUserAddressId)) {
            header("location:user-order-information?error=1&var_msg=" . $languageLabelsArr['LBL_NO_USER_ADDRESS_FOUND'] . "&order=" . $fromOrder);
            exit;
        }
        $Dataua = $obj->MySQLSelect("SELECT *  FROM `user_address`  WHERE iUserAddressId = '" . $iUserAddressId . "' AND iUserId = '" . $iUserId . "'");
        //print_r($iUserId);die;
        if (count($Dataua) > 0) {
            $vBuildingNo = $Dataua[0]['vBuildingNo'];
            $vLandmark = $Dataua[0]['vLandmark'];
            $vAddressType = $Dataua[0]['vAddressType'];
            $a = $b = '';
            if ($vBuildingNo != '') {
                $a = ucfirst($vBuildingNo) . ", ";
            }
            if ($vLandmark != '') {
                $b = ucfirst($vLandmark) . ", ";
            }
            $fulladdress = $a . "" . $b . "" . $Dataua[0]['vServiceAddress'];
            $vServiceAddress = ucfirst($Dataua[0]['vServiceAddress']);
            $vLatitude = $Dataua[0]['vLatitude'];
            $vLongitude = $Dataua[0]['vLongitude'];
            $vTimeZone = $Dataua[0]['vTimeZone'];
        }
    }
    $sourceLocationArr = array($vLatitude, $vLongitude);
    ///$allowed_ans = checkAllowedAreaNew($sourceLocationArr, "No");
    $checkUser = check_user_mr();
    $checkFavStore = checkFavStoreModule();
    
    
    $sql_query = $ssql = $leftjoinsql = "";
    if (($checkUser == 'rider' || strtolower($checkUser) == "user") && !empty($iUserId) && $checkFavStore == 1) {
        include "include/features/include_fav_store.php";
        $sql_query = getFavSelectQuery('', $iUserId);
    }
    $tsite_url = $tconfig['tsite_url'];
    $redirect_location = $tsite_url . 'order-items?order=' . $fromOrder;
    if (strtolower($checkUser) == 'store' || strtolower($checkUser) == 'admin') {
        $redirect_location = $tsite_url . 'user-order-information?order=' . $fromOrder;
    }
    $having_ssql = "";
    if (SITE_TYPE == "Demo" && $searchword == "") {
        $vAddress = $vServiceAddress;
        $having_ssql .= " OR company.eDemoDisplay = 'Yes'";
        if ($vAddress != "") {
            // $ssql .= " AND ( company.vRestuarantLocation like '%$vAddress%' OR company.vRestuarantLocation like '%India%' OR company.eDemoDisplay = 'Yes')";
        } else {
            // $ssql .= " AND ( company.vRestuarantLocation like '%India%' OR company.eDemoDisplay = 'Yes')";
        }
    }

    $postcuisineIds = (isset($_POST['cuisine_type'])) ? $_POST['cuisine_type'] : "";
    $postoffers = (isset($_POST['offers'])) ? $_POST['offers'] : "";
    $postfavStore = (isset($_POST['favStore'])) ? $_POST['favStore'] : "";
    $iServiceIdDef = $iServiceId;
    if(isset($_POST['filter']))
    {
        $filter = 1;
        $cuisine_types = (count($_POST['cuisine_type'])) ? $_POST['cuisine_type'] : "";
        if($cuisine_types != "")
        {
            $cuisine_types = "'".implode("','", $cuisine_types)."'";
            $cuisinesql = "select cuisineId from cuisine where cuisineName in (".$cuisine_types.")";
            $cuisineData = $obj->MySQLSelect($cuisinesql);
            $cuisineIds = array();
            foreach ($cuisineData as $cvalue) {
                $cuisineIds[] = $cvalue['cuisineId'];
            }
            $cuisineIds = implode(',', $cuisineIds);

            $leftjoinsql .= " LEFT JOIN company_cuisine on company.iCompanyId = company_cuisine.iCompanyId";
            $ssql .= " AND company_cuisine.cuisineId IN (".$cuisineIds.")";
        }
        if($postoffers != "")
        {
            $ssql .= " AND fOfferAppyType != 'None'";
        }
    }
    $sql = "SELECT DISTINCT (company.iCompanyId),ROUND(( 6371 * acos( cos( radians(" . $vLatitude . ") ) * cos( radians( vRestuarantLocationLat ) ) * cos( radians( vRestuarantLocationLong ) - radians(" . $vLongitude . ") ) + sin( radians(" . $vLatitude . ") ) * sin( radians( vRestuarantLocationLat ) ) ) ),2) AS distance, company.* " . $sql_query . " FROM `company` $leftjoinsql WHERE vRestuarantLocationLat != '' AND vRestuarantLocationLong != '' AND eStatus='Active' AND eSystem = 'DeliverAll' AND iServiceId = '" . $iServiceId . "' $ssql HAVING (distance < " . $LIST_RESTAURANT_LIMIT_BY_DISTANCE . $having_ssql . ") ORDER BY `company`.`iCompanyId` ASC";

    $Data = $obj->MySQLSelect($sql);
    $storeIdArr = array();
    if (count($Data) > 0) {
        for ($ii = 0; $ii < count($Data); $ii++) {
            $iCompanyId = $Data[$ii]['iCompanyId'];
            array_push($storeIdArr, $iCompanyId);
        }
    }
    $iToLocationId = GetUserGeoLocationId($sourceLocationArr);
    
    
    //$storeIdArr = array(288);
    $storeDetails = getStoreDetails($storeIdArr, $iUserId, $iToLocationId, $languageLabelsArr);
    //echo "<pre>";print_r($storeDetails);die;
    for ($rs = 0; $rs < count($Data); $rs++) {
        $restStatus = "closed";
        if (isset($storeDetails['restaurantStatusArr'][$Data[$rs]['iCompanyId']]['status']) && $storeDetails['restaurantStatusArr'][$Data[$rs]['iCompanyId']]['status'] != "") {
            $restStatus = strtolower($storeDetails['restaurantStatusArr'][$Data[$rs]['iCompanyId']]['status']);
        }
        $Data[$rs]['restaurantstatus'] = $restStatus;
        $vAvgRating = $Data[$rs]['vAvgRating'];
        $Data[$rs]['vAvgRating'] = ($vAvgRating > 0) ? number_format($Data[$rs]['vAvgRating'], 1) : 0;
        $Data[$rs]['vAvgRatingOrig'] = $Data[$rs]['vAvgRating'];
    }
    //echo "<pre>";print_r($storeDetails);die;
    //Added By HJ On 22-06-2019 For Get Store By Filter Start
    $sortby = isset($_POST["sortby"]) ? $_POST["sortby"] : 'relevance';
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

    if($postfavStore != "")
    {
        $favStoreData = array();
        foreach ($Data as $fkey => $fvalue) {
            if($fvalue['eFavStore'] == "Yes")
            {
                $favStoreData[] = $fvalue;
            }
        }
        $Data = $favStoreData;
    }


    //Added By HJ On 22-06-2019 For Get Store By Filter End
    //$allcuisine = getcuisinelist($storeIdArr, 0);
    //$cuisineArr = $storeDetails['cuisineArr'];
    $allStoreCuisin = array_values($storeDetails['companyCuisineArr']);
    $finalCuisineArr = $cuisineArr = array();
    foreach ($allStoreCuisin as $cuisineKey => $cuisineArr) {
        for ($f = 0; $f < count($cuisineArr); $f++) {
            $finalCuisineArr[] = $cuisineArr[$f];
        }
    }
    if (count($finalCuisineArr) > 0) {
        $cuisineoriginalarray = array_unique($finalCuisineArr);
        $cuisineArr = array_values($cuisineoriginalarray);
    }
    $cuisinecount = $storeDetails['cuisinecount'];
    $confirmLabel = $languageLabelsArr['LBL_DELETE_CART_ITEM'];
    $noOfferTxt = $languageLabelsArr['LBL_NO_OFFER_TXT'];
    $pageHead = $SITE_NAME . " | " . $languageLabelsArr['LBL_STORE_LISTING_MANUAL_TXT'];
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
        <style>
            .error {
            color:red;
            font-weight: normal;
            }
            .select2-container--default .select2-search--inline .select2-search__field{
            width:500px !important;
            }
            .full-width {
                width: 100%
            }
            ul.store-category-listing {
                min-height: auto
            }
            ul.store-category-listing li {
                width: 33.33%
            }
            ul.store-category-listing li.rest-closed a:before {
                padding: 0 0 20px 10px
            }
            .download-section {
                padding: 40px 0;
                margin: 20px -15px;
                background-color: #249302;
            }
            .rest-menu-main {
                padding-bottom: 0
            }
            .filter-label, .filter-main {
                margin: 0
            }
            .flex-row {
                align-items: center;
            }
            .search-main.ACTIVE .search-holder input {
                margin-bottom: 0
            }
            .search-main.ACTIVE .search-holder {
                background-position: 10px 26px;
            }

            .sidenav {
              height: 100%;
              width: 500px;
              position: fixed;
              z-index: 9999;
              top: 0;
              right: 0;
              background-color: #ffffff;
              overflow-x: hidden;
              padding-top: 0px;
              display: none;
            }

            .sidenav .filters {
                padding: 25px;
            text-decoration: none;
            font-size: 25px;
            color: #000000;
            display: block;
            overflow-y: auto;
            height: calc(100vh - 156px);
            box-sizing: border-box;
            margin-top: 70px;
            }

            .sidenav .filter-option-label {
                margin-top: 20px
            }
            .sidenav .filter-option-label, .sidenav .show-msg-restaurant-listing strong {
                color: #219201;
            }
            .sidenav .closebtn .closebtnI{
                cursor: pointer;
                width: 45px;
                height: 45px;
                background: #219201;
                display: flex;
                text-align: center;
                border-radius: 50%;
                position: relative;
                font-size: 25px;
                font-style: inherit;
                justify-content: center;
                align-items: center;
                color: #fff;
                padding:0px;
            }

                .sidenav .closebtn {
                    position: absolute;
                    font-size: 22px;
                    padding: 10px 25px 10px 25px;
                    display: flex;
                    justify-content: space-between;
                    flex-direction: row-reverse;
                    width: 100%;
                    top: 0;
                    border-bottom: 1px solid #d1d1d1;
                    margin: 0 0 0;
                }

            .sidenav .closebtn i {
                cursor: pointer;
                width: 45px;
                height: 45px;
                padding: 8px 0 0px 11px;
                background:#219201;
                display: flex;
                text-align: center;
                border-radius: 50%;
                left: 0;
                right: 0;
                position: relative;
                font-size: 30px;
            }

            .sidenav .filter-options {
                float: left;
                display: flex;
                align-items: center;
                margin: 0;
                cursor: pointer;
                width: 225px
            }

            .sidenav .check-holder [type="checkbox"] {
                top: 11px;
                left: 1px;
            }

            .sidenav .filter-options .filter-option-text, .sidenav .filter-options-radio .filter-option-text {
                text-transform: capitalize;
                font-size: 14px;
                font-weight: normal;
                margin: 6px 0 0 5px;
            }

            .sidenav .show-msg-restaurant-listing {
                float: left;
                display: flex;
                align-items: center;
            }

            .sidenav .show-msg-restaurant-listing img {
                margin: 0 15px 0 0;
                float: left;
                height: 50px;
                width: 50px;
            }
            .know-more-btn-new{padding: 12px 20px!important;}
            .know-more-btn{padding: 12px 50px;}


            @media screen and (max-height: 450px) {
              .sidenav {padding-top: 15px;}
              .sidenav a {font-size: 18px;}
              .know-more-btn {padding: 12px 18px;}
            }

            @media screen and (max-width:767px) {
                .sidenav{width:100%}
                .know-more-btn {padding: 12px 18px;}
            }

            .overlay {
                position: fixed;
                display: none;
                width: 100%;
                height: 100%;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0,0,0,0.5);
                z-index: 9998;
                cursor: pointer;
            }
            

            .sidenav .apply-filter-buttons {
                position: absolute;
                bottom: 0;
                left: 0;
                padding: 20px 20px 20px 20px;
                width: 100%;
                -webkit-box-shadow: 0 -2px 4px 0 #e9e9eb;
                -moz-box-shadow: 0 -2px 4px 0 #e9e9eb;
                box-shadow: 0 -2px 4px 0 #e9e9eb;
                background-color: #fff;
                box-sizing: border-box;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            .sidenav .apply-filter-buttons a {
                margin-top: 0;
            }

            ul.rest-listing li .add-favorate:hover {
                transform: scale(1.2);
            }

            .sidenav .filter-options-radio {
              display: inline;
              position: relative;
              padding-left: 31px;
              margin-top: 10px;
              cursor: pointer;
              font-size: 15px;
              -webkit-user-select: none;
              -moz-user-select: none;
              -ms-user-select: none;
              user-select: none;
              float: left;
              width: 160px;
              margin-right: 40px
            }

            .sidenav .filter-options-radio input {
              position: absolute;
              opacity: 0;
              cursor: pointer;
            }

            .sidenav .checkmark {
              position: absolute;
              top: 0;
              left: 0;
              height: 20px;
              width: 20px;
              background-color: #eee;
              border-radius: 50%;
            }

            .sidenav .filter-options-radio:hover input ~ .checkmark {
              background-color: #ccc;
            }

            .sidenav .filter-options-radio input:checked ~ .checkmark {
              background-color: #219201;
            }

            .sidenav .checkmark:after {
              content: "";
              position: absolute;
              display: none;
            }

            .sidenav .filter-options-radio input:checked ~ .checkmark:after {
              display: block;
            }

            .sidenav .filter-options-radio .checkmark:after {
                top: 6px;
                left: 6px;
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: white;
            }
            .intro-fixed{
                overflow:hidden;
            }

        </style>
        <script>
            var LISTHEIGHT
            deliveryAddressCount = "";
            $('.catlist-holder').css('height', LISTHEIGHT);
            $(document).on('click', '.search-holder input', function () {
                $(this).closest('.search-main').addClass('ACTIVE');
                $('.catlist-holder').slideDown(300);
                $('body,html').addClass('overflow-hide');
            })
            $(document).on('click', '.search-holder .close_ico', function () {
                $(this).closest('.search-main').removeClass('ACTIVE');
                $('.catlist-holder').slideUp(300);
                $('body,html').removeClass('overflow-hide');
                $("#loadstore").html('');
                $("#rest-listing").show();
                $('#magicsearchingg').val('');
            })
            $(document).on('blur', '.search-holder', function(){
                $(this).closest('.search-main').removeClass('ACTIVE');
                $('body,html').removeClass('overflow-hide');
            });
            $(document).click(function(event) {
                //if you click on anything except the modal itself or the "open modal" link, close the modal
                if (!$(event.target).closest(".search-holder input,.search-holder .close_ico,#cuisinlist,.catlist-row").length) {
                    $('.search-main').removeClass('ACTIVE');
                    $('.catlist-holder').slideUp(300);
                }
            });
        </script>
        <!-- End: Default Top Script and css-->
    </head>
    <body>
        <div id="main-uber-page">
            <?php
                include_once("top/left_menu.php");
                include_once("top/header_topbar.php");
                ?> 
            <div class="page-contant home-page-data" style="padding-bottom: 0">
                <div class="page-contant-inner _MLR0_  page-contant-inner-av" style="margin-bottom: 0">
                    <div class="search-main">
                        <div class="search-holder">
                            <input type="text" placeholder="<?= $languageLabelsArr['LBL_MANUAL_STORE_SEARCH_RESTAURANT']; ?>" id="magicsearchingg"   class="magicsearch" name="magicsearching" onKeyUp="searching(this.value)"/>
                            <img src="<?= $tsite_url; ?>assets/img/cancel.svg" alt="" class="close_ico" />
                        </div>
                        <?php /*
                        <div class="catlist-holder listing-main center-cont" id="cuisinlist">
                            <div class="catlist-row">
                                <span class="categoriestxt">Categories</span>
                                <ul class="cat-listing show show-msg-restaurant-listing" >
                                    <?php
                                        if ($cuisinecount > 0) {
                                            for ($w = 0; $w < $cuisinecount; $w++) {
                                                ?>
                                    <li>
                                        <a onClick ="return searchingcuisine('<?= $cuisineArr[$w]; ?>', 'cuisine')"><strong><?= $cuisineArr[$w]; ?></strong></a>
                                    </li>
                                    <?php
                                        }
                                        } else {
                                        $storenoimage = $tsite_url . 'assets/img/custome-store/no_service.png';
                                        ?>
                                    <div  align="center" class="show-msg-restaurant-listing">
                                        <h4><span style="">
                                            <b  style="margin:20px 0 0; padding:0px; float:left; width:100%;">
                                            <img src="<?= $storenoimage; ?>" alt="" width="250px;"></b>
                                            <strong><?php
                                                echo $languageLabelsArr['LBL_NO_CUISINE_FOUND_TXT'];
                                                ?></strong></span> 
                                        </h4>
                                    </div>
                                    <?php
                                        }
                                        ?>
                                </ul>
                            </div>
                        </div>
                        */ ?>
                    </div>
                    <div class="listing-main" id="restlisting">
                        <div class="flex-row">
                            <h4><img src="<?= $tsite_url; ?>assets/img/placeholder.svg" class="locate_ico" alt=""><?= $selServiceName . $fulladdress; ?></h4>
                           
                            <?php if ((count($Data) > 0 && !empty($Data)) || $filter == 1) { ?>
                            <label class="filter-main" onclick="openNav()">
                                <div class="filter-label">
                                    <img src="<?= $tsite_url; ?>assets/img/custome-store/controls.svg" alt="" width="30px;" >
                                </div>
                            </label>
                            <div id="mySidenav" class="sidenav">
                                
                                <form action="" method="POST" id="filter_form">
                                    <div class="closebtn"><span class="closebtnI" onclick="closeNav()">
                                    <img src="<?= $tsite_url; ?>assets/img/cancel-new.svg" width="20px;"></span> Filters</div>
                                    <input type="hidden" name="filter" value="filter">
                                    <div class="filters">
                                        <?php
                                            if ($cuisinecount > 0) { ?>
                                                <div class="filter-option-label"><?= $languageLabelsArr['LBL_CUISINES'] ?></div>
                                                <?php 
                                                $storenoimage = $tsite_url . 'assets/img/custome-store/no_service.png';
                                                for ($w = 0; $w < $cuisinecount; $w++) {
                                                    $checkbox_checked = "";
                                                    if(in_array($cuisineArr[$w], $postcuisineIds))
                                                        $checkbox_checked = "checked";
                                        ?>
                                        <label class="filter-options">
                                            <span class="check-holder">
                                                <input name="cuisine_type[]" class="filterFavStore" type="checkbox" value="<?= $cuisineArr[$w]; ?>" <?php echo $checkbox_checked; ?>>
                                                <span class="check-box"></span>
                                            </span>
                                            <span class="filter-option-text"><?= $cuisineArr[$w]; ?></span>
                                        </label>
                                        <?php
                                                }
                                            } else {
                                                $storenoimage = $tsite_url . 'assets/img/custome-store/no_service.png';
                                            ?>
                                            <div class="show-msg-restaurant-listing">
                                                <img src="<?= $storenoimage; ?>" alt="">
                                                <strong><?php echo $languageLabelsArr['LBL_NO_CUISINE_FOUND_TXT']; ?></strong> 
                                            </div>
                                        <?php } ?>
                                        <div class="clearfix"></div>
                                        <div class="filter-option-label"><?= $languageLabelsArr['LBL_SHOW_RESTAURANTS_WITH'] ?></div>
                                        <label class="filter-options">
                                            <span class="check-holder">
                                                <input name="offers" class="filterFavStore" type="checkbox" value="Offers" <?php if($postoffers != "") echo "checked"; ?>>
                                                <span class="check-box"></span>
                                            </span>
                                            <span class="filter-option-text">Offers</span>
                                        </label>
                                        <?php if(checkFavStoreModule() && (strtolower($checkUser) == 'rider' || strtolower($checkUser) == "user")) { ?>
                                        <label class="filter-options">
                                            <span class="check-holder">
                                                <input name="favStore" class="filterFavStore" type="checkbox" value="favStore" <?php if($postfavStore != "") echo "checked"; ?>>
                                                <span class="check-box"></span>
                                            </span>
                                            <span class="filter-option-text"><?= $languageLabelsArr['LBL_MANUAL_STORE_FILTER_FAVOURITE_STORE'] ?></span>
                                        </label>
                                        <?php } ?>

                                        <div class="clearfix"></div>
                                        <div class="filter-option-label">Sort By</div>
                                        <label class="filter-options-radio">
                                            <input type="radio" checked="checked" name="sortby" value="relevance" <?php if($sortby == "relevance") echo "checked"; ?>>
                                            <span class="checkmark"></span>
                                            <span class="filter-option-text">Relevance</span>
                                        </label>
                                        <label class="filter-options-radio">
                                            <input type="radio" name="sortby" value="rating" <?php if($sortby == "rating") echo "checked"; ?>>
                                            <span class="checkmark"></span>
                                            <span class="filter-option-text">Rating</span>
                                        </label>
                                        <label class="filter-options-radio">
                                            <input type="radio" name="sortby" value="time" <?php if($sortby == "time") echo "checked"; ?>>
                                            <span class="checkmark"></span>
                                            <span class="filter-option-text">Delivery Time</span>
                                        </label>
                                        <?php if($iServiceIdDef == 1) { ?>
                                        <label class="filter-options-radio">
                                            <input type="radio" name="sortby" value="costlth" <?php if($sortby == "costlth") echo "checked"; ?>>
                                            <span class="checkmark"></span>
                                            <span class="filter-option-text">Cost (Low to High)</span>
                                        </label>
                                        <label class="filter-options-radio">
                                            <input type="radio" name="sortby" value="costhtl" <?php if($sortby == "costhtl") echo "checked"; ?>>
                                            <span class="checkmark"></span>
                                            <span class="filter-option-text">Cost (High to Low)</span>
                                        </label>
                                        <?php } ?>
                                    </div>
                                    <div class="apply-filter-buttons">
                                        <a class="know-more-btn" style="margin-right: 15px" id="clear_filter">Clear</a>
                                        <a class="know-more-btn know-more-btn-new" id="apply_filter">Apply Filters</a>
                                    </div>
                                </form>
                            </div>
                            <div class="overlay" onclick="closeNav()" id="myOverlay"></div>
                            <?php } ?>
                        </div>
                        
                            
                        <?php if (count($Data) > 0 && !empty($Data)) { ?>
                        <div class="flex-row list-work catall" id="restaurantcount">
                            <?= $totalStore . ' ' . $languageLabelsArr['LBL_RESTAURANTS']; ?>
                        </div>
                        
                        <ul class="rest-listing" id="rest-listing">
                            <?php
                                $currencySymbol = "$";
                                if (isset($storeDetails['currencySymbol']) && $storeDetails['currencySymbol'] != "") {
                                    $currencySymbol = $storeDetails['currencySymbol'];
                                }
                                for ($i = 0; $i < count($Data); $i++) {
                                    $fDeliverytime = 0;
                                    $iCompanyId = $Data[$i]['iCompanyId'];
                                    $vAvgRating = $Data[$i]['vAvgRating'];
                                    $iServiceId = $Data[$i]['iServiceId'];
                                    $Data[$i]['vAvgRating'] = ($vAvgRating > 0) ? number_format($Data[$i]['vAvgRating'], 1) : 0;
                                    $Data[$i]['vAvgRatingOrig'] = $Data[$i]['vAvgRating'];
                                    $Data[$i]['vCompany'] = stripslashes(ucfirst($Data[$i]['vCompany']));
                                    //$CompanyDetailsArr = getCompanyDetails($iCompanyId, $iUserId, "No", "", $iServiceId, $vLang);
                                    //$restaurant_status_arr = calculate_restaurant_time_span($iCompanyId, $iUserId);
                                    //echo "<pre>";print_r($Data[$i]);die;
                                    if ($Data[$i]['vImage'] != "") {
                                        $Data[$i]['vImage'] = $tconfig['tsite_upload_images_compnay'] . '/' . $iCompanyId . '/3_' . $Data[$i]['vImage'];
                                    } else {
                                        /* if ($iServiceId != 1) {
                                          $Data[$i]['vImage'] = $tsite_url . 'assets/img/custome-store/deliveryall-menu-order-list.png';
                                          } else {
                                          $Data[$i]['vImage'] = $tsite_url . 'assets/img/custome-store/food-menu-order-list.png';
                                          } */
                                        $Data[$i]['vImage'] = $tsite_url . 'assets/img/custome-store/food-menu-order-list.png';
                                    }
                                    if ($Data[$i]['vCoverImage'] != "") {
                                        $Data[$i]['vCoverImage'] = $tconfig['tsite_upload_images_compnay'] . '/' . $iCompanyId . '/' . $Data[$i]['vCoverImage'];
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
                                    $Data[$i]['Restaurant_Cuisine'] = "";
                                    $Restaurant_OfferMessage_short = $Restaurant_OfferMessage = $noOfferTxt;
                                    $LBL_MINS_SMALL = $languageLabelsArr['LBL_MINS_SMALL'];
                                    $Data[$i]['Restaurant_OrderPrepareTime'] = "0 ".$LBL_MINS_SMALL;
                                    $Data[$i]['restaurantstatus'] = $restaurantstatus = "Closed";
                                    if (isset($storeDetails['companyCuisineArr'][$iCompanyId])) {
                                        $Data[$i]['Restaurant_Cuisine'] = implode(", ", $storeDetails['companyCuisineArr'][$iCompanyId]);
                                    }
                                    if (isset($storeDetails[$iCompanyId]['Restaurant_OrderPrepareTime'])) {
                                        $Data[$i]['Restaurant_OrderPrepareTime'] = $storeDetails[$iCompanyId]['Restaurant_OrderPrepareTime'];
                                    }
                                    $Data[$i]['Restaurant_OrderPrepareTime'] = str_replace($LBL_MINS_SMALL, '<img src="' . $tsite_url . 'assets/img/custome-store/delivery_time.png" class="delivery_time_ico" alt=' . $Data[$i]['Restaurant_OrderPrepareTime'] . '><br>'.$LBL_MINS_SMALL, $Data[$i]['Restaurant_OrderPrepareTime']);
                                    if (isset($storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage_short'])) {
                                        $Restaurant_OfferMessage_short = $storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage_short'];
                                    }
                                    if (isset($storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage'])) {
                                        $Restaurant_OfferMessage = $storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage'];
                                    }
                                    if ($Restaurant_OfferMessage_short == "") {
                                        $Restaurant_OfferMessage_short = $Restaurant_OfferMessage = $noOfferTxt;
                                    }
                                    if (isset($storeDetails['restaurantStatusArr'][$iCompanyId]['status'])) {
                                        $Data[$i]['restaurantstatus'] = $restaurantstatus = $storeDetails['restaurantStatusArr'][$iCompanyId]['status'];
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
                                    //echo "<pre>";print_r($storeDetails);die;
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

                                    ?>

                            <li <?php if (strtolower($restaurantstatus) == "closed") { ?> class="rest-closed" <?php } ?>>
                                <a href="<?= $tsite_url; ?>store-items?id=<?= $iCompanyId; ?>&order=<?= $fromOrder; ?>" data-status="<?= $Data[$i]['Restaurant_Open_And_Close_time']; ?>">
                                    <?php
                                        if ((strtolower($checkUser) == 'rider' || strtolower($checkUser) == "user") && $checkFavStore == 1) {
                                            ?>
                                    <div class="add-favorate">
                                        <span class="fav-check">
                                        <input id="favouriteManualStore" name="favouriteManualStore" data-company="<?= $iCompanyId; ?>" data-service="<?= $iServiceId; ?>" onclick="updateFavStoreStatus(this);" class="favouriteManualStore" type="checkbox" value="Yes" <?php
                                            if (isset($Data[$i]['eFavStore']) && !empty($Data[$i]['eFavStore']) && $Data[$i]['eFavStore'] == 'Yes') {
                                                echo "checked";
                                            }
                                            ?>>
                                        <span class="custom-check"></span>
                                        </span>
                                    </div>
                                    <?php } ?> 
                                    <div class="rest-pro" style="background-image:url(resizeImg.php?h=500&src=<?= ($Data[$i]['vImage']); ?>);" ></div>
                                    <div class="procapt">
                                        <strong title="<?= $Data[$i]['vCompany']; ?>">
                                            <span class="item-list-name"><?= $Data[$i]['vCompany']; ?></span><span class="rating"><img src="<?= $tsite_url; ?>assets/img/star.svg" alt=""> <?= $Data[$i]['vAvgRatingOrig']; ?></span><?php
                                                if (strtolower($restaurantstatus) == "closed") {
                                                    ?><?php
                                                }
                                                ?>
                                            <div class="food-detail" title="<?= $Data[$i]['Restaurant_Cuisine']; ?>"><?= $Data[$i]['Restaurant_Cuisine']; ?></div>
                                        </strong>
                                        <!-- <span class="food-type"><?php
                                            // echo $Data[$i]['Restaurant_Cuisine'];
                                            ?></span> -->
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
                            <?php }
                                ?>
                        </ul>
                       <?php } ?> 
                        <?php
                            $class = "hide";
                            $notFoundLabel = $languageLabelsArr['LBL_NO_RECORD_FOUND'];
                            if (empty($Data)) {
                                $class = "show";
                                $notFoundLabel = $languageLabelsArr['LBL_OUT_OF_DELIVERY_AREA'];
                            }
                            /* if ($iServiceId == 1) {
                              $storenoimage = $tsite_url . '/assets/img/custome-store/food-detail-holder.png';
                              } else {
                              $storenoimage = $tsite_url . '/assets/img/custome-store/no_service.png';
                              } */
                            $storenoimage = $tsite_url . 'assets/img/custome-store/no_service.png';
                            ?>
                        <div  align="center" class="<?= $class; ?> show-msg-restaurant-listing show-msg-restaurant-listing-msg" >
                            <h4>
                                <span style="color:#343434;">
                                    <b  style="margin:20px 0 0; padding:0px; float:left; width:100%;"><img src="<?= $storenoimage; ?>" alt="" width="250px;"></b><strong><?= $notFoundLabel; ?></strong>
                                    <div>
                                        <button class="btn" onClick="window.location.href = '<?= $redirect_location; ?>'"><?= $languageLabelsArr['LBL_CHANGE_LOCATION']; ?></button>
                                    </div>
                                    <!-- <p style="margin:20px 0; padding:0px; float:left; width:100%; font-size:15px; color:#7e808c;">We can't find anything related to your search.<br />Try a different search.</p> -->
                                </span>
                            </h4>
                        </div>
                        <!-- <div class="more-btn-block">
                            <a href="#" class="load-more-btn">load more</a>
                            </div> -->
                        <div id="loadstore"> </div>
                    </div>
                    
                </div>
                <!-- <div class="static-page">
                    <? // =$meta['page_desc']; 
                        ?>
                </div> -->
            </div>
        </div>
        <!-- home page end-->
        <!-- footer part -->
        <?php
            include_once('footer/footer_home.php');
            ?>
        <script type="text/javascript" src="assets/js/validation/jquery.validate.min.js" ></script>
        <script type="text/javascript" src="assets/js/validation/additional-methods.js" ></script>
        <script type="text/javascript" src="assets/js/jquery-ui.min.js" ></script>
        <!-- End:contact page-->
        <div style="clear:both;"></div>
        </div>
        <?php
            include_once('top/footer_script.php');
            ?>
        <!-- End: Footer Script --> 
        <script>
            $(document).ready(function () {
                $("ul.rest-listing li").each(function (index) {
                    var ELE = $(this).find('.span-row span')
                    if (ELE.length == 3) {
                        ELE.closest('.span-row').addClass('dual-val')
                    }
                });
            
                $(window).on('load', function() {
                    EQUAL_HEIGHT();
                    EQUAL_HEIGHT_();
                })

                $('[data-toggle="tooltip"]').tooltip();
            });
            $.fn.equalHeight = function () {
                var maxHeight = 0;
                return this.each(function (index, box) {
                    var boxHeight = $(box).height();
                    maxHeight = Math.max(maxHeight, boxHeight);
                }).height(maxHeight);
            };
            
            function EQUAL_HEIGHT() {
                $('ul.rest-listing li .food-detail').css('height', 'auto');
                $('ul.rest-listing li .food-detail').equalHeight();
                $(window).resize(function () {
                    $('ul.rest-listing li .food-detail').css('height', 'auto');
                    $('ul.rest-listing li .food-detail').equalHeight();
                });
            }
            
            function EQUAL_HEIGHT_() {
                $('ul.rest-listing li .discount-txt').css('height', 'auto');
                $('ul.rest-listing li .discount-txt').equalHeight();
                $(window).resize(function () {
                    $('ul.rest-listing li .discount-txt').css('height', 'auto');
                    $('ul.rest-listing li .discount-txt').equalHeight();
                });
            }
            EQUAL_HEIGHT_();
            var allrestorant = [];
            allrestorant = <?= json_encode($Data, JSON_UNESCAPED_UNICODE); ?>;
            
            function searchingcuisine(valautionId, userName) {
                var valautionId;
                $("#restlisting").hide();
            
                $(this).closest('.search-main').removeClass('ACTIVE');
            
                $('.catlist-holder').slideUp(500);
                $('body,html').removeClass('overflow-hide');
            
                $.ajax({
                    type: "POST",
                    url: "ajax_load_store.php",
                    data: {cuisine: valautionId, order: '<?php echo $fromOrder; ?>'},
                    dataType: "html",
                    success: function (dataHtml)
                    {
                        $("#loadstore").html(dataHtml);
                    }
                });
            }
            var myVar = null;
            function searching() {
                var search = $('#magicsearchingg').val();
                $("#rest-listing").hide();
                $(this).closest('.search-main').removeClass('ACTIVE');
                clearTimeout(myVar);
                $('.catlist-holder').slideUp(500);
                $('body,html').removeClass('overflow-hide');
                myVar = setTimeout(function () {
                    $.ajax({
                        type: "POST",
                        url: "ajax_load_store.php",
                        data: {searchid: search, order: '<?php echo $fromOrder; ?>'},
                        dataType: "html",
                        success: function (dataHtml)
                        {
                            $("#loadstore").html(dataHtml);
                        }
                    });
                }, 500);
            }
            function favStore(ele) {
                var FavStore = 'No';
                if ($(ele).is(":checked")) {
                    var FavStore = 'Yes';
                }
                $.ajax({
                    type: "POST",
                    url: "ajax_load_fav_store.php",
                    data: {eFavStore: FavStore, order: '<?php echo $fromOrder; ?>'},
                    dataType: "html",
                    success: function (dataHtml)
                    {
                        if (dataHtml != "") {
                            $(".rest-listing").html(dataHtml);
                            $(".rest-listing").show();
                            $(".show-msg-restaurant-listing-msg").removeClass('show').addClass('hide');
                        } else {
                            $(".rest-listing").html('');
                            $(".rest-listing").hide();
                            $(".show-msg-restaurant-listing-msg").removeClass('hide').addClass('show');
                        }
                        var restCount = $("#totalstorecount").val();
                        $("#restaurantcount").text(restCount);
                    }
                });
            }
            function updateFavStoreStatus(elem) {
                favStore = 'No';
                $('.favouriteManualStore').each(function () {
                    if (elem.checked) {
                        favStore = elem.value;
                    }
                });
                var iUserId = '<?= $iUserId ?>'
                var companyId = $(elem).attr("data-company");
                var iServiceId = $(elem).attr("data-service");
                $.ajax({
                    type: "POST",
                    url: "ajax_fav_manual_store.php",
                    data: {iCompanyId: companyId, iUserId: iUserId, iServiceId: iServiceId, eFavStore: favStore},
                    dataType: "text",
                    success: function (dataHtml)
                    {
                        return true;
                    }
                });
            }
            function resetServiceCatagory() {
                var e = document.getElementById("servicename");
                var serviceId = e.options[e.selectedIndex].value;
                var serviceName = e.options[e.selectedIndex].text;
                var cartAmount = $("#subtotalamount").text();
                var cartTotItems = "<?= $confirlAlert; ?>";
                var userType = '<?= $fromOrder; ?>';
                if (cartTotItems > 0 || cartAmount.trim() != "") {
                    if (confirm("<?= $confirmLabel; ?>")) {
                        window.location.href = 'store-listing?sid=' + serviceId + '&order=' + userType;
                    }
                } else {
                    window.location.href = 'store-listing?sid=' + serviceId + '&order=' + userType;
                }
            }

            function openNav() {
                $("body").addClass("intro-fixed");
                $('#mySidenav').toggle('slide', {
                    direction: 'right'
                }, 500);
                $('#myOverlay').fadeIn(500);
            }

            function closeNav() {
                $("body").removeClass("intro-fixed");
                $('#myOverlay').fadeOut(500);
                $('#mySidenav').toggle('slide', {
                    direction: 'right'
                }, 500);
            }

            $('#apply_filter').click(function(){
                $('#filter_form').submit();
            });

            $('#clear_filter').click(function() {
               $('#filter_form').find('input[type="checkbox"]').prop('checked', false); 
               $('#filter_form').find('input[type="radio"]').prop('checked', false);
               $('#filter_form').submit();
            });

        </script>
    </body>
</html>