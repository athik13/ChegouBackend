<?php
include_once("common.php");
include_once ('include_generalFunctions_dl.php');
$fromOrder = "guest";
if (isset($_REQUEST['fromorder']) && $_REQUEST['fromorder'] != "") {
    $fromOrder = $_REQUEST['fromorder'];
}
$orderAddressIdSession = "MANUAL_ORDER_ADDRESSID_" . strtoupper($fromOrder);
$addressCount = $addressId = 0;

if (isset($_POST['type']) && $_POST['type'] != "") {
    $type = $_POST['type'];
    $vLang = $_SESSION['sess_lang'];
    $languageArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
    //$languageArr = $languageArr['LanguageLabels'];
    $siteUrl = $tconfig['tsite_url'];
    $iUserId = $_POST['sess_iUserId'];
    $iCompanyId = isset($_POST['iCompanyIds']) ? $_POST['iCompanyIds'] : '';
    if (isset($_POST['addressId'])) {
        $addressId = $_POST['addressId'];
    }
    if ($type == 'adddeliveryaddress' || $type == "removeAddress") {
        $sql = "select vRestuarantLocationLat,vRestuarantLocationLong,iServiceId from `company` where iCompanyId = '" . $iCompanyId . "'";
        $db_companydata = $obj->MySQLSelect($sql);
        $vRestuarantLocationLat = $vRestuarantLocationLong = "";
        if (count($db_companydata) > 0) {
            $vRestuarantLocationLat = $db_companydata[0]['vRestuarantLocationLat'];
            $vRestuarantLocationLong = $db_companydata[0]['vRestuarantLocationLong'];
            $iServiceId = $db_companydata[0]['iServiceId'];
        }
        if (isset($_POST['DeliveryAddress']) && $_POST['DeliveryAddress'] != "" && !empty($_POST['DeliveryAddress']) && $_POST['BuildingNo'] != "" && !empty($_POST['BuildingNo']) && $_POST['Landmark'] != "" && !empty($_POST['Landmark'])) {
            //Added By HJ On 07-01-2019 For Check Deliver Location to Store Location Distance As Per Bug  141 Mantis #2234 Start 
            $distance = distanceByLocation($_POST['from_lat'], $_POST['from_long'], $vRestuarantLocationLat, $vRestuarantLocationLong, "K");
            //echo $distance."====".$LIST_RESTAURANT_LIMIT_BY_DISTANCE."<br>";
            //echo $languageArr['LBL_LOCATION_FAR_AWAY_TXT'];die;
            if ($distance > $LIST_RESTAURANT_LIMIT_BY_DISTANCE && $type == 'adddeliveryaddress') {
                echo "";
                die;
            }
            //Added By HJ On 07-01-2019 For Check Deliver Location to Store Location Distance As Per Bug  141 Mantis #2234 End
            $UserCountryData = get_value('register_user', 'vCountry', 'iUserId', $iUserId);
            $UserCountryData1 = $UserCountryData[0]['vCountry'];
            $db_code = get_value('country', 'vTimeZone', 'vCountryCode', $UserCountryData1);
            $DataAddress['iUserId'] = $iUserId;
            $DataAddress['eUserType'] = 'Rider';
            $DataAddress['vServiceAddress'] = $_POST['DeliveryAddress'];
            $DataAddress['vBuildingNo'] = $_POST['BuildingNo'];
            $DataAddress['vLandmark'] = $_POST['Landmark'];
            $DataAddress['vAddressType'] = $_POST['vAddressType'];
            $DataAddress['vLatitude'] = $_POST['from_lat'];
            $DataAddress['vLongitude'] = $_POST['from_long'];
            $DataAddress['dAddedDate'] = date('Y-m-d H:i:s');
            $DataAddress['vTimeZone'] = $db_code[0]['vTimeZone'];
            $DataAddress['eStatus'] = 'Active';
            $iUserAddressId = $addressId = $obj->MySQLQueryPerform("user_address", $DataAddress, 'insert');
        }
        if ($type == "removeAddress") {
            if ($addressId == $_SESSION[$orderAddressIdSession]) {
                $_SESSION[$orderAddressIdSession] = 0;
                unset($_SESSION[$orderAddressIdSession]);
            }

            $obj->sql_query("delete from user_fave_address where iUserFavAddressId='" . $addressId . "'");
            $obj->sql_query("delete from user_address where iUserAddressId='" . $addressId . "'");
        }
        $sql = "select register_user.vName,register_user.vLastName,register_user.vEmail,register_user.iUserId,register_user.eStatus,ua.vServiceAddress,ua.iUserAddressId,ua.vBuildingNo,ua.vLandmark,ua.vAddressType,ua.vLatitude,ua.vLongitude from register_user INNER JOIN user_address as ua ON register_user.iUserId=ua.iUserId where register_user.iUserId = '" . $iUserId . "' AND  ua.eStatus='Active' ORDER BY  `ua`.`iUserAddressId` DESC";
        $db_model = $obj->MySQLSelect($sql);
        $workAddress1 = '<div class="address-wrap">';
        $workAddress = '<input type="hidden"   name="iUserId" id="iUserId" value=' . $iUserId . ' >';
        $iUserAddressId_data = '';
        //$languageArr = $languageArr['LanguageLabels'];
        if (count($db_model) > 0) {
            for ($i = 0; $i < count($db_model); $i++) {
                $distance = distanceByLocation($db_model[$i]['vLatitude'], $db_model[$i]['vLongitude'], $vRestuarantLocationLat, $vRestuarantLocationLong, "K");
                if ($distance > $LIST_RESTAURANT_LIMIT_BY_DISTANCE) {
                    continue;
                }
                $vBuildingNo = $db_model[$i]['vBuildingNo'];
                $vLandmark = $db_model[$i]['vLandmark'];
                $vAddressType = $db_model[$i]['vAddressType'];
                $vLatitude = $db_model[$i]['vLatitude'];
                $vLongitude = $db_model[$i]['vLongitude'];
                $iUserAddressId_data = $db_model[$i]['iUserAddressId'];
                $from_lat_long = '(' . $vLatitude . ', ' . $vLongitude . ')';
                $a = $b = '';
                if ($vBuildingNo != '') {
                    $a = ucfirst($vBuildingNo) . ", ";
                }
                if ($vLandmark != '') {
                    $b = ucfirst($vLandmark) . ", ";
                }
                $radio_address_sel = "";
                $addresshide = 'hide';
                if ($db_model[$i]['iUserAddressId'] == $_SESSION[$orderAddressIdSession]) {
                    $addresshide = 'selected-address show';
                    $radio_address_sel = "checked";
                    $workAddress1 = '<div class="address-wrap">';
                }
                $fulladdress = $a . "" . $b . "" . $db_model[$i]['vServiceAddress'] . " (" . $db_model[$i]['vAddressType'] . ")";
                $workAddress .= '<div class="address-block  ' . $addresshide . '"  id="address-id-' . $db_model[$i]['iUserAddressId'] . '" >
                    <img src="' . $siteUrl . 'assets/img/cancel.svg" alt="Remove" onclick="removeAddress(' . $db_model[$i]['iUserAddressId'] . ');" class="close_ico" />
                           <input type="radio" class="chkaddresssIds" ' . $radio_address_sel . ' name="iUserAddressId" onclick="changelocation(' . $db_model[$i]['iUserAddressId'] . ',' . $db_model[$i]['vLatitude'] . ',' . $db_model[$i]['vLongitude'] . ')" id="iUserAddressId" value=' . $db_model[$i]['iUserAddressId'] . ' >
                                <span><img src="' . $siteUrl . 'assets/img/locate.png" alt=""></span>
                                 <div class="address-caption">
                                            <b>' . $langage_lbl['LBL_PROFILE_ADDRESS'] . '</b>
                                            <address>' . $fulladdress . '</address>
                                            <span class="appr-deliverytime"></span>
                                             <a href="javascript:;">' . $languageArr["LBL_MANUAL_STORE_DELIVERY_HERE"] . '</a>';
                $addressCount++;
                $workAddress .= '</div>
                                    </div>';
            }
        }
        $workAddress2 = '</div>';
        $workAddress4 = '<div class="address-block hide">
                                        <span><img src="' . $siteUrl . 'assets/img/locate.png" alt=""></span>
                                        <div class="address-caption">
                                            <b>&nbsp;</b>
                                            <img src="assets/img/add-location.svg" />
                                            <a href="javascript:;" class="add-new open-model" data-id="delivery-address-model">' . $languageArr["LBL_ADD_NEW_ADDRESS_TXT"] . '</a>
                                        </div>
                                    </div>';
        //Added By HJ On 07-06-2019 For Get User Home and Work Address Start
        if ($iUserId > 0) {
            $getUserFavAddress = $obj->MySQLSelect("SELECT * FROM user_fave_address WHERE eStatus='Active' AND iUserId='" . $iUserId . "' AND eUserType='Passenger'");
            if (count($getUserFavAddress) > 0) {
                for ($r = 0; $r < count($getUserFavAddress); $r++) {
                    $addressType = $getUserFavAddress[$r]['eType'];
                    $radio_address_sel = "";
                    $addresshide = 'hide';
                    if ($getUserFavAddress[$r]['iUserFavAddressId'] == $_SESSION[$orderAddressIdSession]) {
                        $addresshide = 'selected-address show';
                        $radio_address_sel = "checked";
                    }
                    $addressCount++;
                    $workAddress .= '<div class="address-block ' . $addresshide . '" id="address-id-' . $getUserFavAddress[$r]['iUserFavAddressId'] . '">
                        <img src="' . $siteUrl . 'assets/img/cancel.svg" alt="Remove" onclick="removeAddress(' . $getUserFavAddress[$r]['iUserFavAddressId'] . ');" class="close_ico" />
                           <input type="radio" class="chkaddresssIds" ' . $radio_address_sel . ' name="iUserAddressId" onclick="changelocation(' . $getUserFavAddress[$r]['iUserFavAddressId'] . ',' . $getUserFavAddress[$r]['vLatitude'] . ',' . $getUserFavAddress[$r]['vLongitude'] . ')" id="iUserAddressId" value=' . $getUserFavAddress[$r]['iUserFavAddressId'] . '>
                                <span><img src="' . $siteUrl . 'assets/img/locate.png" alt=""></span>
                                 <div class="address-caption">
                                            <b>' . $addressType . ' ' . $langage_lbl['LBL_PROFILE_ADDRESS'] . '</b>
                                            <address>' . $getUserFavAddress[$r]['vAddress'] . '</address>
                                             <span class="appr-deliverytime"></span>
                                             <a href="#">' . $langage_lbl['LBL_MANUAL_STORE_DELIVERY_HERE'] . '</a></div>
                                    </div>';
                }
            }
        }
//Added By HJ On 07-06-2019 For Get User Home and Work Address End
        $workAddress = $workAddress1 . $workAddress . $workAddress4 . $workAddress2;
        echo $workAddress;
    } else if ($type == 'changeaddress') {
        $addressids = $_POST['addressids'];
        $_SESSION[$orderAddressIdSession] = $addressids;
    } else if ($type == 'removechangeaddress') {
        $_SESSION[$orderAddressIdSession] = 0;
        unset($_SESSION[$orderAddressIdSession]);
    } else {
        echo '';
    }
}
?>
