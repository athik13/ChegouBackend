<?php
include_once("common.php");
$vLang = "EN";
if (isset($_SESSION['sess_lang'])) {
    $vLang = $_SESSION['sess_lang'];
}
$fromOrder = "guest";
if (isset($_REQUEST['order']) && $_REQUEST['order'] != "") {
    $fromOrder = $_REQUEST['order'];
}
include_once ('include_generalFunctions_dl.php');
$usertTypeSesstion = "MANUAL_ORDER_" . strtoupper($fromOrder);
$orderAddressIdSession = "MANUAL_ORDER_ADDRESSID_" . strtoupper($fromOrder);
$orderUserIdSession = "MANUAL_ORDER_USERID_" . strtoupper($fromOrder);
$chkusertype = check_user_mr();
$_SESSION[$usertTypeSesstion] = $chkusertype;
$iCompanyId = '';
ob_start();
$phone = isset($_REQUEST['phone']) ? $_REQUEST['phone'] : '';
$phone = ltrim($phone, '0');
$phoneCode = isset($_REQUEST['phoneCode']) ? $_REQUEST['phoneCode'] : '';
$cont = $workAddress = $vLatitude = $vLongitude = $vAddressType = $phonQr = '';
$iUserAddressId = 0;
//added by SP for cubex on 2-10-2019 start
if($generalobj->checkXThemOn() == 'Yes') {
    $workAddress = '<table class="table table-bordered address-table order-item-table">';
} else {
    $workAddress = '<table class="table table-bordered address-table">';
}
//added by SP for cubex on 2-10-2019 end
if ($phone != '') {
    if ($phoneCode != "") {
        $phonQr = " AND ru.vPhoneCode='" . $phoneCode . "'";
    }
    $sql = "select ru.vName,ru.vLastName,ru.vEmail,ru.iUserId,ru.eStatus,ru.iUserId from register_user ru where ru.vPhone = '" . $phone . "'$phonQr AND  ru.eStatus='Active' ORDER BY  `ru`.`iUserId` DESC ";
    //$sql = "select register_user.vName,register_user.vLastName,register_user.vEmail,register_user.iUserId,register_user.eStatus,ua.vServiceAddress,ua.iUserAddressId,ua.vBuildingNo,ua.vLandmark,ua.vAddressType,ua.vLatitude,ua.vLongitude from register_user INNER JOIN user_address as ua ON register_user.iUserId=ua.iUserId where vPhone = '" . $phone . "'$phonQr AND  ua.eStatus='Active' ORDER BY  `ua`.`iUserAddressId` DESC ";
    $db_model = $obj->MySQLSelect($sql);
    $count = count($db_model);
    if (count($db_model) > 0) {
        $getUserAddress = $obj->MySQLSelect("SELECT u.iUserId as iUserId ,u.vServiceAddress as vServiceAddress, u.iUserAddressId as iUserAddressId,u.vBuildingNo as vBuildingNo ,u.vLandmark as vLandmark,u.vAddressType as vAddressType, u.vLatitude as vLatitude , u.vLongitude as vLongitude FROM user_address as u LEFT JOIN register_user as ru on ru.iUserId=u.iUserId WHERE u.eStatus='Active' AND ru.vPhone = '" . $phone . "' $phonQr");
        $userAddressArr = array();
        for ($r = 0; $r < count($getUserAddress); $r++) {
            $userAddressArr[$getUserAddress[$r]['iUserId']][] = $getUserAddress[$r];
        }
        $cont .= $generalobj->clearName($db_model[0]['vName']) . ":";
        $cont .= " " . $generalobj->clearName($db_model[0]['vLastName']) . ":";
        $cont .= $db_model[0]['vEmail'] . ":";
        $cont .= $db_model[0]['iUserId'] . ":";
        $cont .= $db_model[0]['eStatus'];
        if (count($db_model) > 0) {
        	if($generalobj->checkXThemOn() == 'Yes') {
        		$workAddress .= '<strong>User Delivery Address</strong>';
        	} else {
                $workAddress .= '<b>User Delivery Address</b><br>';
        	}
        }
        $workAddress .= '<input type="hidden"   name="iUserId" id="iUserId" value=' . $db_model[0]['iUserId'] . ' >';
        if (strtolower($chkusertype) == 'store') {
            if (!empty($_SESSION[$usertTypeSesstion]) && strtolower($_SESSION[$usertTypeSesstion]) == 'store') {
                $iCompanyId = $_SESSION[$orderUserIdSession];
            }
            $sql = "select vRestuarantLocationLat,vRestuarantLocationLong,iServiceId from `company` where iCompanyId = '" . $iCompanyId . "'";
            $db_companydata = $obj->MySQLSelect($sql);
            $vRestuarantLocationLat = $db_companydata[0]['vRestuarantLocationLat'];
            $vRestuarantLocationLong = $db_companydata[0]['vRestuarantLocationLong'];
            $iServiceId = $db_companydata[0]['iServiceId'];
            $languageArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
        }
        for ($i = 0; $i < count($db_model); $i++) {
            $vLatitude = $vLongitude = $vBuildingNo = $vLandmark = $vServiceAddress = '';
            $iUserAddressId = 0;
            if (isset($userAddressArr[$db_model[$i]['iUserId']])) {
                $addressData = $userAddressArr[$db_model[$i]['iUserId']];
                for ($u = 0; $u < count($addressData); $u++) {
                    $vBuildingNo = $addressData[$u]['vBuildingNo'];
                    $vLandmark = $addressData[$u]['vLandmark'];
                    $vAddressType = $addressData[$u]['vAddressType'];
                    $vLatitude = $addressData[$u]['vLatitude'];
                    $vLongitude = $addressData[$u]['vLongitude'];
                    $vServiceAddress = $addressData[$u]['vServiceAddress'];
                    $iUserAddressId = $addressData[$u]['iUserAddressId'];
                    if (strtolower($chkusertype) == 'store') {
                        $distance = distanceByLocation($vLatitude, $vLongitude, $vRestuarantLocationLat, $vRestuarantLocationLong, "K");
                        if ($distance > $LIST_RESTAURANT_LIMIT_BY_DISTANCE) {
                            continue;
                        }
                    }

                    $a = $b = '';
                    if ($vBuildingNo != '') {
                        $a = ucfirst($vBuildingNo) . ", ";
                    }
                    if ($vLandmark != '') {
                        $b = ucfirst($vLandmark) . ", ";
                    }
                    $fulladdress = $a . "" . $b . "" . $vServiceAddress;
                    if ($vAddressType != "") {
                        $fulladdress .= ",(" . $vAddressType . ")";
                    }
                    //added by SP for cubex on 2-10-2019 start
                    if($generalobj->checkXThemOn() == 'Yes') {
                        $workAddress .= '<tr><td>
                            <div class="radio-combo">
                                <div class="radio-main">
                                    <span class="radio-hold">
                                        <input type="radio"   checked name="iUserAddressId" onclick="changelocation(' . $iUserAddressId . ',' . $vLatitude . ',' . $vLongitude . ')" id="iUserAddressId" value=' . $iUserAddressId . ' >
                                        <span class="radio-button"></span>
                                    </span>
                                </div><label for="r4_eRideType">'.$fulladdress.'</label>
                                <input type="hidden" class="no-border" name="vUserAddressLatitude" id="vUserAddressLatitude" value=' . $vLatitude . ' ><input type="hidden" class="no-border" name="vUserAddressLongitude" id="vUserAddressLongitude" value=' . $vLongitude . ' >
                            </div>
                    </td></tr>';
                    } else {
                        $workAddress .= '<tr><td width="50px"><span class="radio-holder"><input type="radio"   checked name="iUserAddressId" onclick="changelocation(' . $iUserAddressId . ',' . $vLatitude . ',' . $vLongitude . ')" id="iUserAddressId" value=' . $iUserAddressId . ' ><span class="radio-box"></span></span><input type="hidden" class="no-border" name="vUserAddressLatitude" id="vUserAddressLatitude" value=' . $vLatitude . ' ><input type="hidden" class="no-border" name="vUserAddressLongitude" id="vUserAddressLongitude" value=' . $vLongitude . ' ></td><td width="100%">' . $fulladdress . '</td></tr>';
                    }
                    //added by SP for cubex on 2-10-2019 end
                }
            }
        }
    }
    $workAddress .= '</table>';

    echo json_encode(array('cont' => $cont, 'workAddress' => $workAddress, 'vLatitude' => $vLatitude, 'vLongitude' => $vLongitude, 'count' => $count));
    exit;
}
?>