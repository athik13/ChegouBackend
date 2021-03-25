<?php
include_once('common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
include_once ('generalFunctions_addon.php');


global $generalobj, $obj, $LIST_RESTAURANT_LIMIT_BY_DISTANCE, $DRIVER_REQUEST_METHOD, $intervalmins;
$cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
$str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
$LIST_RESTAURANT_LIMIT_BY_DISTANCE = $generalobj->getConfigurations("configurations", "LIST_RESTAURANT_LIMIT_BY_DISTANCE");
$DRIVER_REQUEST_METHOD = $generalobj->getConfigurations("configurations", "DRIVER_REQUEST_METHOD");
$param = ($DRIVER_REQUEST_METHOD == "Time") ? "tOnline" : "tLastOnline";

$fromOrder = "guest";
if (isset($_REQUEST['order']) && $_REQUEST['order'] != "") {
    $fromOrder = $_REQUEST['order'];
}

$orderUserIdSession = "MANUAL_ORDER_USERID_" . strtoupper($fromOrder);
$iUserAddressId = "MANUAL_ORDER_ADDRESSID_" . strtoupper($fromOrder);
$orderServiceSession = "MAUAL_ORDER_SERVICE_" . strtoupper($fromOrder);
$iUserAddressId = $_SESSION[$iUserAddressId];
$iUserId = $_SESSION[$orderUserIdSession];

$sql = "SELECT *  FROM `user_address`  WHERE iUserAddressId = '" . $iUserAddressId . "' AND iUserId = '" . $iUserId . "'";
$Dataua = $obj->MySQLSelect($sql);

$vServiceAddress = ucfirst($Dataua[0]['vServiceAddress']);
$vBuildingNo = $Dataua[0]['vBuildingNo'];
$vLandmark = $Dataua[0]['vLandmark'];
$vAddressType = $Dataua[0]['vAddressType'];
$vLatitude = $Dataua[0]['vLatitude'];
$vLongitude = $Dataua[0]['vLongitude'];
$vTimeZone = $Dataua[0]['vTimeZone'];

$vBuildingNo = $Dataua[0]['vBuildingNo'];
$vLandmark = $Dataua[0]['vLandmark'];
$vAddressType = $Dataua[0]['vAddressType'];
$a = '';
$b = '';
if ($vBuildingNo != '') {
    $a = ucfirst($vBuildingNo) . ", ";
}
if ($vLandmark != '') {
    $b = ucfirst($vLandmark) . ", ";
}
$fulladdress = $a . "" . $b . "" . $Dataua[0]['vServiceAddress'];
$iServiceId = $_SESSION[$orderServiceSession];
$sourceLocationArr = array($vLatitude,$vLongitude);
$iToLocationId = GetUserGeoLocationId($sourceLocationArr);
$allowed_ans = checkAllowedAreaNew($sourceLocationArr, "No");

$ssql = "";
$searchid = isset($_POST['searchid']) ? $_POST['searchid'] : '';
$cuisine = isset($_POST['cuisine']) ? $_POST['cuisine'] : '';
$vLang = 'EN';
if ($searchid != "") {
    $ssql .= " AND ( company.vCompany like '%$searchid%')";
    $sql = "SELECT DISTINCT (company.iCompanyId),ROUND(( 6371 * acos( cos( radians(" . $vLatitude . ") ) 
		* cos( radians( vRestuarantLocationLat ) ) 
			* cos( radians( vRestuarantLocationLong ) - radians(" . $vLongitude . ") ) 
			+ sin( radians(" . $vLatitude . ") ) 
			* sin( radians( vRestuarantLocationLat ) ) ) ),2) AS distance, company.*  FROM `company`  LEFT JOIN favorite_store as fs on fs.iCompanyId = company.iCompanyId  
			WHERE vRestuarantLocationLat != '' AND vRestuarantLocationLong != '' AND company.eStatus='Active' AND eSystem = 'DeliverAll' AND company.iServiceId = '" . $iServiceId . "' $ssql
			HAVING distance < " . $LIST_RESTAURANT_LIMIT_BY_DISTANCE . " ORDER BY `company`.`iCompanyId` ASC";
} else if ($cuisine != "") {
    $ssql .= " AND (cu.cuisineName_" . $vLang . " like '%$cuisine%' AND cu.eStatus = 'Active')";
    $sql = "SELECT DISTINCT (company.iCompanyId),ROUND(( 6371 * acos( cos( radians(" . $vLatitude . ") ) 
		* cos( radians( vRestuarantLocationLat ) ) 
			* cos( radians( vRestuarantLocationLong ) - radians(" . $vLongitude . ") ) 
			+ sin( radians(" . $vLatitude . ") ) 
			* sin( radians( vRestuarantLocationLat ) ) ) ),2) AS distance, company.* , cu.* FROM `company`  LEFT JOIN favorite_store as fs on fs.iCompanyId = company.iCompanyId  LEFT JOIN company_cuisine as ccu ON ccu.iCompanyId=company.iCompanyId LEFT JOIN cuisine as cu ON ccu.cuisineId=cu.cuisineId
			WHERE vRestuarantLocationLat != '' AND vRestuarantLocationLong != '' AND company.eStatus='Active' AND eSystem = 'DeliverAll' AND company.iServiceId = '" . $iServiceId . "' $ssql
			HAVING distance < " . $LIST_RESTAURANT_LIMIT_BY_DISTANCE . " ORDER BY `company`.`iCompanyId` ASC";
} else {

    $sql = "SELECT DISTINCT (company.iCompanyId),ROUND(( 6371 * acos( cos( radians(" . $vLatitude . ") ) 
		* cos( radians( vRestuarantLocationLat ) ) 
			* cos( radians( vRestuarantLocationLong ) - radians(" . $vLongitude . ") ) 
			+ sin( radians(" . $vLatitude . ") ) 
			* sin( radians( vRestuarantLocationLat ) ) ) ),2) AS distance, company.*   FROM `company`  LEFT JOIN favorite_store as fs on fs.iCompanyId = company.iCompanyId
			WHERE vRestuarantLocationLat != '' AND vRestuarantLocationLong != '' AND company.eStatus='Active' AND eSystem = 'DeliverAll' AND company.iServiceId = '" . $iServiceId . "'  
			HAVING distance < " . $LIST_RESTAURANT_LIMIT_BY_DISTANCE . " ORDER BY `company`.`iCompanyId` ASC";
}


$Data = $obj->MySQLSelect($sql);
?> 
<h4><?php echo $fulladdress; ?></h4>
<ul class="rest-listing">
<?php
$languageLabelsArr = getLanguageLabelsArr($vLang, "1", "1");
if (count($Data) > 0) {

    for ($i = 0; $i < count($Data); $i++) {
        $fDeliverytime = 0;
        $iCompanyId = $Data[$i]['iCompanyId'];
        $vAvgRating = $Data[$i]['vAvgRating'];
        $Data[$i]['vAvgRating'] = ($vAvgRating > 0) ? number_format($Data[$i]['vAvgRating'], 1) : 0;
        $Data[$i]['vAvgRatingOrig'] = $Data[$i]['vAvgRating'];
        $Data[$i]['vCompany'] = $Data[$i]['vCompany'];
        $CompanyDetailsArr = getCompanyDetails($Data[$i]['iCompanyId'], $iUserId, "No", "");

        if ($Data[$i]['vImage'] != "") {
            $Data[$i]['vImage'] = $tconfig['tsite_upload_images_compnay'] . '/' . $Data[$i]['iCompanyId'] . '/' . $Data[$i]['vImage'];
        } else {
            $Data[$i]['vImage'] = $tconfig['tsite_url'] . '/assets/img/burger.jpg';
        }
        if ($Data[$i]['vCoverImage'] != "") {
            $Data[$i]['vCoverImage'] = $tconfig['tsite_upload_images_compnay'] . '/' . $Data[$i]['iCompanyId'] . '/' . $Data[$i]['vCoverImage'];
        }

        $Data[$i]['Restaurant_Cuisine'] = $CompanyDetailsArr['Restaurant_Cuisine'];
        $sql = "SELECT vRestuarantLocationLat as restaurantlat,vRestuarantLocationLong as restaurantlong FROM company WHERE iCompanyId = '" . $iCompanyId . "'";
        $datac = $obj->MySQLSelect($sql);
        $Rest_Address_Array = array(
            $datac[0]['restaurantlat'],
            $datac[0]['restaurantlong']
        );
        $iLocationId = GetUserGeoLocationId($Rest_Address_Array);

        $restaurant_status_arr = calculate_restaurant_time_span_web($Data[$i]['iCompanyId'], $iUserId);

        $Data[$i]['Restaurant_Status'] = $restaurant_status_arr['status'];
        $Data[$i]['Restaurant_Opentime'] = $restaurant_status_arr['opentime'];
        $Data[$i]['Restaurant_Closetime'] = $restaurant_status_arr['closetime'];
        $Data[$i]['restaurantstatus'] = $restaurant_status_arr['restaurantstatus']; // closed or open
        $Data[$i]['timeslotavailable'] = $restaurant_status_arr['timeslotavailable'];

        if ($iToLocationId != "0") {
            $sql = "SELECT * FROM  `delivery_charges` WHERE ";
            $iToLocationId = ltrim($iToLocationId, "0.,");
            $iLocationId = ltrim($iLocationId, "0.,");

            $iToLocationId = explode(",", $iToLocationId);
            $iLocationId = explode(",", $iLocationId);
            $countuser = count($iToLocationId);
            $countrest = count($iLocationId);

            $counttotal = $countrest * $countuser;
            $cott = 1;
            $cot = 1;
            if ($countuser >= $countrest) {

                for ($ui = 0; $ui < $countuser; $ui++) {
                    for ($ri = 0; $ri < $countrest; $ri++) {
                        $sql .= "  ( iToLocationId =  '" . $iToLocationId[$ui] . "' AND iLocationId =  '" . $iLocationId[$ri] . "' AND eStatus =  'Active') ";

                        if ($cot != $counttotal) {
                            $sql .= " OR ";
                        }
                        $cot++;
                    }
                }
            } else {

                for ($rri = 0; $rri < $countrest; $rri++) {
                    $cott;

                    for ($uui = 0; $uui < $countuser; $uui++) {
                        $sql .= "  ( iToLocationId =  '" . $iToLocationId[$uui] . "' AND iLocationId =  '" . $iLocationId[$rri] . "' AND eStatus =  'Active') ";

                        if ($cott != $counttotal) {
                            $sql .= " OR ";
                        }
                        $cott++;
                    }
                }
            }

            $sql .= " LIMIT 0,1";

            $datacharg = $obj->MySQLSelect($sql);
            if (count($datacharg)) {
                $fDeliverytime = $datacharg[0]['fDeliverytime'];
            } else {
                $fDeliverytime = "0";
            }
        } else {
            $fDeliverytime = "0";
        }
        $Restaurant_OfferMessage_short = $CompanyDetailsArr['Restaurant_OfferMessage_short'];
        $LBL_MINS_SMALL = $langage_lbl['LBL_MINS_SMALL'];
        $Data[$i]['Restaurant_OrderPrepareTime'] = $fDeliverytime . " " . $LBL_MINS_SMALL;
        ?>
            <li>
                <a href="<?php echo $tconfig['tsite_url']; ?>restaurant_menu.php?id=<?php echo $iCompanyId; ?>">
                    <div class="rest-pro" style="background-image:url(<?php echo ($Data[$i]['vImage']); ?>);"></div>
                    <strong><?php echo ucfirst($Data[$i]['vCompany']); ?><?php if ($restaurant_status_arr['restaurantstatus'] == "closed") { ?>&nbsp;<span style="color:#98441ef5;"><?php echo $languageLabelsArr['LBL_RESTAURANT_CLOSED_STATUS_TXT']; ?></span><?php } ?>	</strong>
                    <span class="food-type"><?php echo $Data[$i]['Restaurant_Cuisine']; ?></span>
                    <div class="span-row">
                        <span class="timing"><?php echo $Data[$i]['Restaurant_OrderPrepareTime']; ?></span>
                        <span class="rating"><?php echo $Data[$i]['vAvgRatingOrig']; ?> &#9733;</span>
                    </div>
                    <span class="discount-txt"><?php if ($Restaurant_OfferMessage_short != '') { ?><img src="assets/img/discount.svg" alt=""> <?php echo $Restaurant_OfferMessage_short; ?><?php } ?></span>
                </a>
            </li> 
        <?php
        }
    }
    ?>
</ul><?php
    if (count($Data) == 0) {
        ?><div  align="center" >
        <h4><span style="color:#98441ef5;"><strong><?php echo $languageLabelsArr['LBL_NO_RESTAURANT_FOUND_TXT']; ?></strong></span> </h4></div>
<?php } ?>