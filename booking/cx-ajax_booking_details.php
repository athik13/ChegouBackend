<?php

include_once('../common.php');
//$generalobj->check_member_login();
require_once("../app_common_functions.php"); //added by SP for get vehicles/services according to the pickup location on 02-08-2019
include_once ('../include_generalFunctions_shark.php');
//include_once ('../app_common_functions.php');
include_once ('../include/include_webservice_enterprisefeatures.php');
if (checkFlyStationsModule(1)) {
    include_once ('../include/features/include_fly_stations.php');
}
$countryId = isset($_REQUEST['countryId']) ? $_REQUEST['countryId'] : '';
$iVehicleTypeId = isset($_REQUEST['iVehicleTypeId']) ? $_REQUEST['iVehicleTypeId'] : '';
$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : 'Ride';

$sql = "SELECT iCountryId FROM country WHERE vCountryCode = '" . $countryId . "'";
$countryarray = $obj->MySQLSelect($sql);
$countryid = $countryarray[0]['iCountryId'];

//added by SP for get vehicles/services according to the pickup location on 02-08-2019 start
$from_lat = isset($_REQUEST['from_lat']) ? $_REQUEST['from_lat'] : '';
$from_long = isset($_REQUEST['from_long']) ? $_REQUEST['from_long'] : '';
$to_lat = isset($_REQUEST['to_lat']) ? $_REQUEST['to_lat'] : '';
$to_long = isset($_REQUEST['to_long']) ? $_REQUEST['to_long'] : '';
$distance = isset($_REQUEST['distance']) ? $_REQUEST['distance'] : '1';
$duration = isset($_REQUEST['duration']) ? $_REQUEST['duration'] : '1';
$promoCode = isset($_REQUEST['promoCode']) ? $_REQUEST['promoCode'] : '';
$iFromStationId = isset($_REQUEST['iFromStationId']) ? $_REQUEST['iFromStationId'] : '';
$iToStationId = isset($_REQUEST['iToStationId']) ? $_REQUEST['iToStationId'] : '';
$userType = isset($_REQUEST['userType']) ? $_REQUEST['userType'] : '';
// added by sunita 11-01-2020
$booking_date = isset($_REQUEST['booking_date']) ? $_REQUEST['booking_date'] : '';

$admin = 0;
if ($userType == 'Admin') {
    $admin = 1;
}
$fareEstimate = 0;
if(empty($userType)) {
    $fareEstimate = 1;
}

if (strtoupper($userType) == 'RIDER') {
    $table_name = 'register_user';
    $field = 'iUserId';
} else if (strtoupper($userType) == 'COMPANY') {
    $table_name = 'company';
    $field = 'iCompanyId';
}
$iUserId = $_SESSION['sess_iUserId'];
//this is for cubex only but not put in the condition bc this variable is used in qry and here condition is not put...
$getMemberData = $obj->MySQLSelect("SELECT vLang,vTimeZone FROM " . $table_name . " WHERE $field='" . $iUserId . "'");
$vTimeZone = "Asia/Kolkata";
$vLang = "EN";
if (count($getMemberData) > 0) {
    $vLang = $getMemberData[0]['vLang'];
    $vTimeZone = $getMemberData[0]['vTimeZone'];
}
date_default_timezone_set($vTimeZone);

$vLang = get_value($table_name, 'vLang', $field, $_SESSION['sess_iUserId'], 'true'); //get language code of driver

if ($vLang == "" || $vLang == NULL) {
    $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
}
//if(empty($iUserId)){
if (isset($_SESSION['sess_lang']) && trim($_SESSION['sess_lang']) != "") {
    $vLang = $_SESSION['sess_lang'];
}

if (!empty($from_lat)) {
    $vSelectedLatitude = $from_lat;
    $vSelectedLongitude = $from_long;
    $pickuplocationarr = array($vSelectedLatitude, $vSelectedLongitude);
    $GetVehicleIdfromGeoLocation = GetVehicleTypeFromGeoLocation($pickuplocationarr);
    if (!empty($GetVehicleIdfromGeoLocation)) {
        $locations_where = " AND vt.iLocationid IN(-1, {$GetVehicleIdfromGeoLocation}) ";
    }

    $vSelectedLatitude = $to_lat;
    $vSelectedLongitude = $to_long;
    $dropofflocationarr = array($vSelectedLatitude, $vSelectedLongitude);
}
//added by SP for get vehicles/services according to the pickup location on 02-08-2019 end
$returnarr = '';
$sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();
$getAllVehicleData = $obj->MySQLSelect("SELECT iVehicleCategoryId,eStatus FROM " . $sql_vehicle_category_table_name);
$vehicleStatusArr = array();
for ($r = 0; $r < count($getAllVehicleData); $r++) {
    $vehicleStatusArr[$getAllVehicleData[$r]['iVehicleCategoryId']] = $getAllVehicleData[$r]['eStatus'];
}

if ($type == 'getVehicles') {
    $eFly = '';
    if ($eType == "UberX") {
        $whereParentId = "";
        if ($parent_ufx_catid > 0) {
            $whereParentId = " AND vc.iParentId='" . $parent_ufx_catid . "'";
        }
        //$sql23 = "SELECT vt.*,vc.vCategory_EN,lm.vLocationName FROM `vehicle_type` AS vt LEFT JOIN `country` AS c ON c.iCountryId=vt.iCountryId LEFT JOIN vehicle_category as vc on vc.iVehicleCategoryId = vt.iVehicleCategoryId left join location_master as lm ON lm.iLocationId = vt.iLocationid WHERE (lm.iCountryId='" . $countryid . "' OR vt.iLocationid = '-1') $whereParentId AND vt.eType='" . $eType . "' AND vc.eStatus = 'Active' AND ePoolStatus = 'No' $locations_where ORDER BY vt.iVehicleTypeId ASC";
        //$sql23 = "SELECT vt.*,vc.vCategory_EN,lm.vLocationName FROM `vehicle_type` AS vt LEFT JOIN `country` AS c ON c.iCountryId=vt.iCountryId LEFT JOIN vehicle_category as vc on vc.iVehicleCategoryId = vt.iVehicleCategoryId left join location_master as lm ON lm.iLocationId = vt.iLocationid WHERE 1 $whereParentId AND vt.eType='" . $eType . "' AND vc.eStatus = 'Active' AND ePoolStatus = 'No' $locations_where ORDER BY vt.iVehicleTypeId ASC";
        $sql23 = "SELECT vt.*,vc.iParentId,vc.vCategory_$vLang,lm.vLocationName FROM `vehicle_type` AS vt LEFT JOIN `country` AS c ON c.iCountryId=vt.iCountryId LEFT JOIN " . $sql_vehicle_category_table_name . " as vc on vc.iVehicleCategoryId = vt.iVehicleCategoryId left join location_master as lm ON lm.iLocationId = vt.iLocationid WHERE 1 $whereParentId AND vt.eType='" . $eType . "' AND vt.ePoolStatus='No' AND vc.eStatus = 'Active' AND vt.eStatus = 'Active' $locations_where ORDER BY vt.iVehicleTypeId ASC";
    } else {
        if ($eType == 'Ride') {
            $sql_other = " AND vt.eFly='0' AND vt.eIconType != 'Bike' AND vt.eIconType != 'Cycle'";
        } else if ($eType == 'Fly') {
            $sql_other = " AND vt.eFly='1'";
            $eFly = 'Yes';
            $eType = 'Ride';
        } else if ($eType == 'Moto') {
            $sql_other .= " AND (vt.eIconType = 'Bike' OR vt.eIconType = 'Cycle')";
            $eType = 'Ride';
        } else {
            $sql_other = '';
        }
        //$sql23 = "SELECT vt.*,lm.vLocationName FROM `vehicle_type` AS vt LEFT JOIN `country` AS c ON c.iCountryId=vt.iCountryId left join location_master as lm ON lm.iLocationId = vt.iLocationid WHERE (lm.iCountryId='" . $countryid . "' OR vt.iLocationid = '-1') AND vt.eType='" . $eType . "'".$sql_other." AND vt.eStatus = 'Active' AND ePoolStatus = 'No' $locations_where ORDER BY vt.iVehicleTypeId ASC";
        $sql23 = "SELECT vt.*,lm.vLocationName FROM `vehicle_type` AS vt LEFT JOIN `country` AS c ON c.iCountryId=vt.iCountryId left join location_master as lm ON lm.iLocationId = vt.iLocationid WHERE vt.eType='" . $eType . "'" . $sql_other . " AND vt.eStatus = 'Active' AND ePoolStatus = 'No' $locations_where ORDER BY vt.iDisplayOrder ASC";
    }

    $db_carType = $obj->MySQLSelect($sql23);

    //added by SP for fly stations on 19-08-2019, its bc fly vehicles are shown only if price in location wise fare is entered
    if ($eFly == 'Yes') {
        $ssql1 = '';
        $iFromLocationId = $iFromStationId;
        $iToLocationId = $iToStationId;
        //fly_location_wise_fare.iFromLocationId
        if (!empty($iFromLocationId)) {
            $ssql1 .= " AND fl.iFromLocationId = $iFromLocationId AND fl.iToLocationId = $iToLocationId"; //becoz vehicles are shown of source location only..if enter iscon then show vehicles which have from station iscon, and also add for it destination
        } /* else {
          exit;
          } */
        $db_carType = array();
        $FlylocationData = $obj->MySQLSelect("SELECT GROUP_CONCAT(DISTINCT(vt.iVehicleTypeId)) as vehicle FROM fly_location_wise_fare as fl LEFT JOIN vehicle_type as vt on vt.iVehicleTypeId = fl.iVehicleTypeId WHERE 1 $ssql1 AND vt.eStatus = 'Active' AND fl.eStatus='Active' AND vt.eFly = 1");

        foreach ($FlylocationData as $row) {
            $FlyVehicleIds = $row['vehicle'];
        }

        if (!empty($FlyVehicleIds)) {
            $db_carType = $obj->MySQLSelect("SELECT vt.*,lm.vLocationName FROM vehicle_type as vt left join location_master as lm ON lm.iLocationId = vt.iLocationid WHERE 1 $locations_where AND vt.eStatus = 'Active' AND vt.iVehicleTypeId IN (" . $FlyVehicleIds . ")"); 
        } else {
            if(!empty($iFromLocationId) && !empty($iToLocationId)) {
                echo -1; exit;
            }
        }

        //$db_carType = $obj->MySQLSelect("SELECT DISTINCT(vt.iVehicleTypeId),vt.*,lm.vLocationName FROM vehicle_type as vt RIGHT JOIN fly_location_wise_fare ON vt.iVehicleTypeId = fly_location_wise_fare.iVehicleTypeId left join location_master as lm ON lm.iLocationId = vt.iLocationid WHERE 1 $locations_where $ssql AND vt.eStatus = 'Active' AND fly_location_wise_fare.eStatus = 'Active'");
    }

    if ($eType == 'UberX') {
        $returnarr .= '<div class="general-form"><div class="form-group"><select name="iVehicleTypeId" id="iVehicleTypeId" required onChange="showAsVehicleType(this.value)">';
        $returnarr .= '<option value="" >Select ' . $langage_lbl['LBL_MYTRIP_TRIP_TYPE'] . '</option>';
    } else {
        $returnarr .= '<ul id="iVehicleTypeId">';
    }
    //$kk = 0; //to get first vehicle as selected
    foreach ($db_carType as $db_car) {
        $selected = '';
        //if ($db_car['iVehicleTypeId'] == $iVehicleTypeId || ($kk==0 && empty($iVehicleTypeId))) {
        if ($db_car['iVehicleTypeId'] == $iVehicleTypeId) {
            //$selected = "selected=selected";
            $selected = "checked";
        }
        //$kk++;
        $location = "";
        if ($db_car['vLocationName'] != '') {
            $location = " (" . $db_car['vLocationName'] . ")";
        } else {
            $location = " (" . $langage_lbl['LBL_ALL_LOCATIONS'] . ")"; //added by SP when all location is selected then show ALl on 31-07-2019
        }
        if ($eType == 'UberX') {
            $iParentId = $db_car['iParentId'];
            $enableVehile = 1;
            if ($iParentId > 0) {
                $enableVehile = 0;
                if (isset($vehicleStatusArr[$iParentId]) && $vehicleStatusArr[$iParentId] == "Active") {
                    $enableVehile = 1;
                }
            }
            //Added By HJ On 06-06-2019 For Check Vehicle Category Parent Id Status End
            if ($enableVehile == 1) {
                $selected = '';
                if ($db_car['iVehicleTypeId'] == $iVehicleTypeId) {
                    $selected = "selected=selected";
                }
                $returnarr .= "<option value=" . $db_car['iVehicleTypeId'] . " " . $selected . ">" . $db_car['vCategory_' . $vLang] . "-" . $db_car['vVehicleType_' . $vLang] . $location . "</option>";
            }
        } else {
            $eFlatTrip = "No";
            $fFlatTripPrice = 0;
            if (!empty($pickuplocationarr) && !empty($dropofflocationarr)) {
                $data_flattrip = checkFlatTripnew($pickuplocationarr, $dropofflocationarr, $db_car['iVehicleTypeId']);
                $eFlatTrip = $data_flattrip['eFlatTrip'];
                $fFlatTripPrice = $data_flattrip['Flatfare'];
            }

            if (!empty($from_lat)) {
                $iUserId = $_SESSION['sess_iUserId'];
                $sourceLocationArr = array($from_lat, $from_long);
                $destinationLocationArr = array($to_lat, $to_long);
                $duration = round(($duration), 2);
                $distance = round(($distance), 2);
                $Fare_data[0]['total_fare_amount'] = 0;
                // added by sunita 11-01-2020
                if ($booking_date == "") {
                    $booking_date = date("Y-m-d H:i:s");
                }
                //$booking_date = date("Y-m-d H:i:s");

                if ($userType == 'Admin')
                    $iUserId = 0;
                //added for admin country code on 11-01-2020
                if ($iUserId <= 0 || $iUserId == "") {
                    $countryCodeAdmin = $countryId;
                }

                //added for Company country code on 11-01-2020
                if ($userType == "Company") {
                    $countryCodeAdmin = $countryId;
                }

                if ($userType == 'Rider' || $userType == 'Admin')
                    $userType = 'Passenger';
                $Fare_data = calculateFareEstimateAll($duration, $distance, $db_car['iVehicleTypeId'], $iUserId, 1, "", "", $promoCode, 1, 0, 0, 0, "DisplySingleVehicleFare", $userType, '', $db_car['iVehicleTypeId'], 'Yes', $eFlatTrip, $fFlatTripPrice, $sourceLocationArr, $destinationLocationArr, '', '', $booking_date, $eFly, $iFromLocationId, $iToLocationId);
                
                if($admin==1) $userType = 'Admin';
                $totalFare = 0;
                /* if ($default_lang != 'EN') {
                  //$languageLabelsArr = $general->getLanguageLabelsArr("EN", "1");
                  $subtotalLbl = $langage_lbl['LBL_SUBTOTAL_TXT'];
                  $nettotalLbl = $langage_lbl['LBL_ROUNDING_NET_TOTAL_TXT'];
                  } else { */
                $subtotalLbl = $langage_lbl['LBL_SUBTOTAL_TXT'];
                $nettotalLbl = $langage_lbl['LBL_ROUNDING_NET_TOTAL_TXT'];
                //}

                $getSymbol = '';
                $roundoff = $totalnetFare = 0;

                $totalFareData = end($Fare_data);
                $totalFare = current(array_slice($totalFareData, -1));

                for ($r = 0; $r < count($Fare_data); $r++) {
                    foreach ($Fare_data[$r] as $key => $val) {
                        if ($getSymbol == "") {
                            $getSymbol = explode(" ", $val);
                        }
                        if ($key == "total_fare_amount" || $key == "eDisplaySeperator") {
                            if ($key == "total_fare_amount") {
                                // $totalFare = $getSymbol[0] . " " . $val;
								$totalFare = $val;
                            }
                        } else {
                            $fareArr = array();
                            $fareArr['key'] = $key;
                            $fareArr['value'] = $val;
                            $estimateArr[] = $fareArr;
                        }
                        if ($key == $subtotalLbl) {
                            $totalFare = $val;
                        }
                        if ($key == $langage_lbl['LBL_ROUNDING_DIFF_TXT']) {
                            $roundoff = 1;
                        }
                        if ($key == $nettotalLbl) {
                            $totalnetFare = $val;
                        }
                    }
                }

                if ($roundoff == 1) {
                    $totalFare = $totalnetFare;
                }

                //$total_fare_amount_sub = array_column($Fare_data, 'Subtotal');
                $total_fare_amount_sub = array_column($Fare_data, 'total_fare_amount');
                //$total_fare_amount = $Fare_data[count($Fare_data) - 2]['Subtotal'];
                //$total_fare_amount = $total_fare_amount_sub[0];
                $total_fare_amount = $totalFare;
            } else {
                $total_fare_amount = 0;
            }
            
            $Photo_Gallery_folder = $tconfig["tsite_upload_images_vehicle_type_path"] . '/' . $db_car['iVehicleTypeId'] . '/android/' . $db_car['vLogo'];
            if ($db_car['vLogo'] != "" && file_exists($Photo_Gallery_folder)) {
                $db_car['vLogo'] = $tconfig["tsite_upload_images_vehicle_type"] . '/' . $db_car['iVehicleTypeId'] . '/android/' . $db_car['vLogo'];
                $db_car['vLogo1'] = $tconfig["tsite_upload_images_vehicle_type"] . '/' . $db_car['iVehicleTypeId'] . '/android/' . $db_car['vLogo1'];

                $logo = "<img src=" . $db_car['vLogo'] . " width='60' height='60' data-selcetedLogo = " . $db_car['vLogo1'] . " class='logoImgCar'>";
            } else {
                $db_car['vLogo'] = "";
                $logo = "";
                //$db_car['vLogo'] = $tconfig["tsite_url"]."/webimages/icons/DefaultImg/ic_car.png";
            }

            $price_display = 'style="display:none"';
            if (!empty($from_lat) && !empty($to_lat)) {
                $price_display = '';
            }
            if($fareEstimate==1) {
                $returnarr .= "<li>
                        <div class='veh-left'>
                            <div class='radio-main'>
                                <span class='radio-hold'>
                                    
                                </span>
                            </div
                            ><i class='vehicle-ico'>$logo</i
                            ><span class='vehicle-name'>" . $db_car['vVehicleType_' . $vLang] . "<small>" . $location . "</small></span>
                            
                        </div>
                        <div class='price-caption' " . $price_display . ">
                            <strong>" . $total_fare_amount . "</strong>
                            <i onclick='showAsVehicleType_all(this);' data-val=" . $db_car['iVehicleTypeId'] . " class='icon-information'></i>
                            
                        </div>
                    </li>";
                
            } else {
                $returnarr .= "<li>
                        <div class='veh-left'>
                            <div class='radio-main'>
                                <span class='radio-hold'>
                                    <input type='radio' name='iVehicleTypeId' required onChange='showAsVehicleType(this.value)' value=" . $db_car['iVehicleTypeId'] . " " . $selected . ">
                                    <span class='radio-button'></span>
                                </span>
                            </div
                            ><i class='vehicle-ico'>$logo</i
                            ><span class='vehicle-name'>" . $db_car['vVehicleType_' . $vLang] . "<small>" . $location . "</small></span>
                            
                        </div>
                        <div class='price-caption' " . $price_display . ">
                            <strong>" . $total_fare_amount . "</strong>
                            <i onclick='showAsVehicleType_all(this);' data-val=" . $db_car['iVehicleTypeId'] . " class='icon-information'></i>
                            
                        </div>
                    </li>";
            }
        }
    }
    if ($eType == 'UberX') {
        $returnarr .= '</ul>';
    } else {
        $returnarr .= '</select></div></div>';
    }
    echo $returnarr;
    exit;
}
?>
