<?php

include_once('../common.php');

$iLocationId = isset($_REQUEST['iLocationId']) ? $_REQUEST['iLocationId'] : '';
$selected = isset($_REQUEST['selected']) ? $_REQUEST['selected'] : '';
if (isset($iLocationId)) {
    if ($iLocationId != "") {
        $sql = "SELECT iCountryId FROM  `location_master` WHERE 1=1 AND eStatus = 'Active' AND iLocationId = '" . $iLocationId . "' AND eFor = 'AirportSurcharge'";
        $db_data = $obj->MySQLSelect($sql);
        //Added By HJ On 22-07-2019 For Check Pool Feature Status Start
        $poolCond = "";
        if ($POOL_ENABLE == "No") {
            $poolCond = " AND vt.ePoolStatus = 'No'";
        }
        //Added By HJ On 22-07-2019 For Check Pool Feature Status End
        if (!empty($db_data)) {
            $iCountryId = $db_data[0]['iCountryId'];
            if (!empty($iCountryId)) {
                $sql1 = "SELECT iLocationId FROM  `location_master` WHERE 1=1 AND eStatus = 'Active' AND iCountryId = '" . $iCountryId . "' AND eFor = 'VehicleType'";
                $db_data_vehicle = $obj->MySQLSelect($sql1);
                foreach ($db_data_vehicle as $key => $value) {
                    $iLocationId_array[] = $value['iLocationId'];
                }
                $ilocation_id = '';
                if (!empty($iLocationId_array)) {
                    $ilocation_id = implode("','", $iLocationId_array);
                }
                if ($APP_TYPE == 'Ride-Delivery') {
                    include_once('../include/ride-delivery/ajax_get_vehicletype_airportsurcharge_admin1.php');
                } else if ($APP_TYPE == 'Ride-Delivery-UberX') {
                    include_once('../include/ride-delivery-uberx/ajax_get_vehicletype_airportsurcharge_admin2.php');
                } else {
                    if (!empty($ilocation_id)) {
                        //$sql2 = "SELECT lm.vLocationName,vt.iLocationId,vt.vVehicleType,vt.iVehicleTypeId FROM  `vehicle_type` as vt LEFT JOIN location_master as lm on lm.iLocationId = vt.iLocationid  WHERE (vt.iLocationid = '-1' OR vt.iLocationid IN ('" . $ilocation_id . "')) AND vt.eStatus='Active' AND vt.eType = '" . $APP_TYPE . "' AND vt.ePoolStatus = 'No'"; // Commented By HJ On 22-07-2019 For Remove Static Pool Condition As Per Discuss With KS
                        $sql2 = "SELECT lm.vLocationName,vt.iLocationId,vt.vVehicleType,vt.iVehicleTypeId FROM  `vehicle_type` as vt LEFT JOIN location_master as lm on lm.iLocationId = vt.iLocationid  WHERE (vt.iLocationid = '-1' OR vt.iLocationid IN ('" . $ilocation_id . "')) AND vt.eStatus='Active' AND vt.eType = '" . $APP_TYPE . "' $poolCond";
                    } else {
                        //$sql2 = "SELECT lm.vLocationName,vt.iLocationId,vt.vVehicleType,vt.iVehicleTypeId FROM  `vehicle_type` as vt LEFT JOIN location_master as lm on lm.iLocationId = vt.iLocationid  WHERE (vt.iLocationid = '-1') AND vt.eStatus='Active' AND vt.eType = '" . $APP_TYPE . "' AND vt.ePoolStatus = 'No'"; // Commented By HJ On 22-07-2019 For Remove Static Pool Condition As Per Discuss With KS
                        $sql2 = "SELECT lm.vLocationName,vt.iLocationId,vt.vVehicleType,vt.iVehicleTypeId FROM  `vehicle_type` as vt LEFT JOIN location_master as lm on lm.iLocationId = vt.iLocationid  WHERE (vt.iLocationid = '-1') AND vt.eStatus='Active' AND vt.eType = '" . $APP_TYPE . "' $poolCond";
                    }
                }
                $sql2 .= ' AND eFly != 1'; //added by SP for fly on 7-9-2019
                $db_select_data = $obj->MySQLSelect($sql2);
                $cons = "<option value=''>Select Vehicle Type</option>";
                foreach ($db_select_data as $k => $val) {
                    if (!empty($val['vLocationName'])) {
                        $cons .= "<option value='" . $val['iVehicleTypeId'] . "'";
                        if ($val['iVehicleTypeId'] == $selected) {
                            $cons .= " selected";
                        }
                        $cons .= ">" . $val['vVehicleType'] . ' (' . $val['vLocationName'] . ')' . "</option>";
                    } else {
                        $cons .= "<option value='" . $val['iVehicleTypeId'] . "'";
                        if ($val['iVehicleTypeId'] == $selected) {
                            $cons .= " selected";
                        }
                        $cons .= ">" . $val['vVehicleType'] . "</option>";
                    }
                }
            }
        }
    } else {
        $cons = "<option value=''>Select Vehicle Type</option>";
    }
    echo $cons;
    exit;
}
?>