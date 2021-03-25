<?php
include_once("common.php");
$vLang = "EN";
if (isset($_SESSION['sess_lang'])) {
    $vLang = $_SESSION['sess_lang'];
}
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
$fromOrder = "guest";
if (isset($_REQUEST['fromorder']) && $_REQUEST['fromorder'] != "") {
    $fromOrder = $_REQUEST['fromorder'];
}
$orderDetailsSession = "ORDER_DETAILS_" . strtoupper($fromOrder);
$orderServiceSession = "MAUAL_ORDER_SERVICE_" . strtoupper($fromOrder);
$orderUserIdSession = "MANUAL_ORDER_USERID_" . strtoupper($fromOrder);
$orderAddressIdSession = "MANUAL_ORDER_ADDRESSID_" . strtoupper($fromOrder);
$orderAddressSession = "MANUAL_ORDER_ADDRESS_" . strtoupper($fromOrder);
$orderLatitudeSession = "MANUAL_ORDER_LATITUDE_" . strtoupper($fromOrder);
$orderLongitudeSession = "MANUAL_ORDER_LONGITUDE_" . strtoupper($fromOrder);
$orderCouponSession = "MANUAL_ORDER_PROMOCODE_" . strtoupper($fromOrder);
$orderCouponNameSession = "MANUAL_ORDER_PROMOCODE_NAME_" . strtoupper($fromOrder);
include_once ('include_generalFunctions_dl.php');
$meta = $generalobj->getStaticPage(1, $vLang);
unset($_SESSION[$orderCouponSession]);
unset($_SESSION[$orderCouponNameSession]);
$_SESSION['sess_language'] = $vLang;
//include_once ('include_generalFunctions_dl.php');
$iServiceId = $_SESSION[$orderServiceSession];
global $intervalmins;
$cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
$str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
//$LIST_RESTAURANT_LIMIT_BY_DISTANCE = $generalobj->getConfigurations("configurations", "LIST_RESTAURANT_LIMIT_BY_DISTANCE");
//$DRIVER_REQUEST_METHOD = $generalobj->getConfigurations("configurations", "DRIVER_REQUEST_METHOD");
$param = ($DRIVER_REQUEST_METHOD == "Time") ? "tOnline" : "tLastOnline";
$vServiceAddress = 0;
$vLatitude = $iUserId = $iUserAddressId = $vLongitude = "";
if (isset($_SESSION[$orderUserIdSession])) {
    $iUserId = $_SESSION[$orderUserIdSession];
}
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
    $sql = "SELECT *  FROM `user_address`  WHERE iUserAddressId = '" . $iUserAddressId . "' AND iUserId = '" . $iUserId . "'";
    $Dataua = $obj->MySQLSelect($sql);
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
$iCompanyId = isset($_REQUEST["id"]) ? $_REQUEST["id"] : '';
$CheckNonVegFoodType = isset($_REQUEST["CheckNonVegFoodType"]) ? $_REQUEST["CheckNonVegFoodType"] : '';
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
// updatecompanylatlong($passengerLat,$passengerLon,$iCompanyId);
$sqlr = "SELECT * FROM company WHERE iCompanyId = '" . $iCompanyId . "'";
$db_company = $obj->MySQLSelect($sqlr);
if (empty($db_company)) {
    header("location:store-listing?success=0&error=LBL_NO_RESTAURANT_FOUND_TXT");
    exit;
}
if (empty($iServiceId)) {
    $_SESSION[$orderServiceSession] = $db_company[0]['iServiceId'];
    $iServiceId = $_SESSION[$orderServiceSession];
}
$Recomendation_Arr = $Recomendation_Arr = array();
$CompanyDetails_Arr = getCompanyDetails($iCompanyId, $iUserId, $CheckNonVegFoodType, $searchword, $iServiceId, $vLang);
$db_company[0]['CompanyDetails'] = $CompanyDetails_Arr;
$storeIdArr[] = $iCompanyId;
$storeDetails = getStoreDetails($storeIdArr, $iUserId, $iToLocationId, $languageLabelsArr);
//$db_company[0]['MenuItemsDetails'] = $CompanyDetails_Arr['MenuItemsDataArr'];
//$db_company[0]['RegistrationDate'] = date("Y-m-d", strtotime($db_company[0]['tRegistrationDate'] . ' -1 day '));
if ($db_company[0]['vImage'] != "") {
    $db_company[0]['vImage'] = $tconfig['tsite_upload_images_compnay'] . '/' . $db_company[0]['iCompanyId'] . '/' . $db_company[0]['vImage'];
}
//$restaurant_status_arr = calculate_restaurant_time_span($iCompanyId, $iUserId);
//$restaurantstatus = $restaurant_status_arr['restaurantstatus'];
if ($db_company[0]['vCoverImage'] != "") {
    $db_company[0]['vCoverImage'] = $tconfig['tsite_upload_images_compnay'] . '/' . $db_company[0]['iCompanyId'] . '/' . $db_company[0]['vCoverImage'];
}
$vAvgRating = $db_company[0]['vAvgRating'];
$db_company[0]['vAvgRating'] = ($vAvgRating > 0) ? number_format($db_company[0]['vAvgRating'], 1) : 0;
//$Recomendation_Arr = getRecommendedBestSellerMenuItems($iCompanyId, $iUserId, "Recommended", $CheckNonVegFoodType, $searchword, $iServiceId, $vLang);
//$db_company[0]['Recomendation_Arr'] = $Recomendation_Arr;
// echo '<pre>';print_r($Recomendation_Arr);
//Added By HJ For Get Recomendation Data Start On 16-05-2019
$RecomendationArray = $Recomendation_Arr = array();
$firstCategoryId = "";
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
// $Bestseller_Arr = getRecommendedBestSellerMenuItems($iUserId,"BestSeller");
if ((!empty($db_company))) {
    $returnArr['Action'] = "1";
    $returnArr['message'] = $db_company[0];
} else {
    $returnArr['Action'] = "0";
    $returnArr['message'] = "LBL_NO_RESTAURANT_FOUND_TXT";
}
json_encode($returnArr);
$CompanyFoodData = $db_company[0]['CompanyDetails']['CompanyFoodData'];
$languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
$siteUrl = $tconfig['tsite_url'];
//echo '<pre>';print_r($CompanyFoodData);
?>
<?php if (count($Recomendation_Arr) <= 0 && count($CompanyFoodData) <= 0) { ?>
    <section class="rest-menu-cat"  id="cat1">
        <div class="hold-cat-title">
            <strong><?= $languageLabelsArr['LBL_MANUAL_STORE_NO_MATCH_ITEM']; ?></strong>
        </div>
    </section>    
<?php } ?>    

<?php
if (count($Recomendation_Arr) > 0) {
    if ($firstCategoryId == "") {
        $firstCategoryId = "1";
    }
    ?>   
    <section class="rest-menu-cat"  id="cat1">
        <div class="hold-cat-title">
            <h3><?= $langage_lbl_admin['LBL_RECOMMENDED']; ?></h3>
            <span><?= count($Recomendation_Arr); ?> <?= $languageLabelsArr['LBL_MANUAL_STORE_MENU_LISTING_ITEMS'] ?></span>
        </div>
        <div class="flex-row" > 
            <?php for ($i = 0; $i < count($Recomendation_Arr); $i++) { ?>
                <div class="menu-item-block" id="menuitem"   onclick="showMenuTypes(<?= $Recomendation_Arr[$i]['iMenuItemId']; ?>, 'add', '')">
                    <?php if (!empty($Recomendation_Arr[$i]['vHighlightName'])) { ?>
                        <h2 id="ribbon-container"><a id="ribbon" href="javascript:;"><?= $languageLabelsArr[$Recomendation_Arr[$i]['vHighlightName']]; ?></a></h2>
                    <?php } ?>
                    <div class="menu-item-image" style="background-image:url(<?= $Recomendation_Arr[$i]['vImage']; ?>);">
                        <?php if (isset($Recomendation_Arr[$i]['vImage']) && !empty($Recomendation_Arr[$i]['vImage']) && $Recomendation_Arr[$i]['eFoodType'] == 'Veg') { ?>
                            <img src="<?= $siteUrl; ?>assets/img/veg.jpg" alt="" class="food-type-sym">
                        <?php } else if (isset($Recomendation_Arr[$i]['vImage']) && !empty($Recomendation_Arr[$i]['vImage']) && $Recomendation_Arr[$i]['eFoodType'] == 'NonVeg') { ?>
                            <img src="<?= $siteUrl; ?>assets/img/non-veg.jpg" alt="" class="food-type-sym">
                        <?php } ?>
                    </div>
                    <div class="menu-item-caption">
                        <strong><?= $Recomendation_Arr[$i]['vItemType']; ?></strong>
                        <span class="menu-item-desc"><?= $Recomendation_Arr[$i]['vCategoryName']; ?></span>
                        <?php if ($Recomendation_Arr[$i]['fOfferAmt'] != "0" && $Recomendation_Arr[$i]['fPrice'] > $Recomendation_Arr[$i]['fDiscountPrice']) { ?><span style="text-decoration: line-through;"><?= $Recomendation_Arr[$i]['StrikeoutPrice']; ?></span><?php } ?>
                        <div class="price-with-add">
                            <span class="menu-item-price"><?= $Recomendation_Arr[$i]['fDiscountPricewithsymbol']; ?></span>
                            <button class="add_cart">Add</button>
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
        if ($firstCategoryId == "") {
            $firstCategoryId = $iFoodMenuId;
        }
        ?>  
        <section class="rest-menu-cat"  id="cat<?= $iFoodMenuId; ?>">          
            <div class="hold-cat-title">
                <h3><?= $vMenu; ?></h3>  
                <span><?= $vMenuItemCount; ?> <?= $languageLabelsArr['LBL_MANUAL_STORE_MENU_LISTING_ITEMS'] ?></span>
            </div>          
            <div class="flex-row" > 
                <?php for ($ii = 0; $ii < $vMenuItemCount; $ii++) { ?>
                    <div class="menu-item-block box-style" id="menuitem"  onclick="showMenuTypes(<?= $menu_items[$ii]['iMenuItemId']; ?>, 'add', '')" >
                        <?php if (!empty($menu_items[$ii]['vHighlightName'])) { ?>
                            <div class="mi-work"><a id="ribbon-category" href="javascript:;"> <?= $languageLabelsArr[$menu_items[$ii]['vHighlightName']]; ?></a></div>
                        <?php } ?>
                        <div class="menu-item-caption">
                            <?php if (isset($menu_items[$ii]['vImage']) && !empty($menu_items[$ii]['vImage']) && $menu_items[$ii]['eFoodType'] == 'Veg') { ?>
                                <img src="<?= $siteUrl; ?>assets/img/veg.jpg" alt="" class="food-type-sym">
                            <?php } else if (isset($menu_items[$ii]['vImage']) && !empty($menu_items[$ii]['vImage']) && $menu_items[$ii]['eFoodType'] == 'NonVeg') { ?>
                                <img src="<?= $siteUrl; ?>assets/img/non-veg.jpg" alt="" class="food-type-sym">
                            <?php } ?>
                            <strong><?= $menu_items[$ii]['vItemType']; ?></strong>
                            <span class="menu-item-desc"><?= $menu_items[$ii]['vCategoryName']; ?></span>
                            <?php if ($menu_items[$ii]['fOfferAmt'] != "0" && $menu_items[$ii]['fPrice'] > $menu_items[$ii]['fDiscountPrice']) { ?><span style="text-decoration: line-through;"><?= $menu_items[$ii]['StrikeoutPrice']; ?></span><?php } ?>
                            <div class="price-with-add">
                                <span class="menu-item-price"><?= $menu_items[$ii]['fDiscountPricewithsymbol']; ?></span>
                                <button class="add_cart">Add</button>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </section>
    <?php } ?>
<?php } ?>
<script>
    var sections = $('section');
    var nav = $('nav');
    var nav_height = nav.outerHeight();
    $(window).on('scroll', function () {
        var cur_pos = $(this).scrollTop();
        sections.each(function () {
            var top = $(this).offset().top - nav_height - 200;
            var bottom = top + $(this).outerHeight();
            if (cur_pos >= top && cur_pos <= bottom) {
                nav.find('a').removeClass('active');
                sections.removeClass('active');
                $(this).addClass('active');
                nav.find('a[href="#' + $(this).attr('id') + '"]').addClass('active');
            }
        });
    });
    $("#activeTab_" + lastEnabledTab).removeClass("active");
    lastEnabledTab = '<?= $firstCategoryId; ?>';
    $("#activeTab_" + <?= $firstCategoryId; ?>).addClass("active");
</script>