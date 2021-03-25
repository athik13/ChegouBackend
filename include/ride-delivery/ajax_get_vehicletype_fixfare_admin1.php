<?
if(!empty($ilocation_id)) {
	$sql2 = "SELECT lm.vLocationName,vt.iLocationId,vt.vVehicleType,vt.iVehicleTypeId FROM  `vehicle_type` as vt LEFT JOIN location_master as lm on lm.iLocationId = vt.iLocationid  WHERE (vt.iLocationid = '-1' OR vt.iLocationid IN ('".$ilocation_id."')) AND vt.eStatus='Active' AND (vt.eType = 'Ride') AND (vt.ePoolStatus = 'No')";
} else {
	$sql2 = "SELECT lm.vLocationName,vt.iLocationId,vt.vVehicleType,vt.iVehicleTypeId FROM  `vehicle_type` as vt LEFT JOIN location_master as lm on lm.iLocationId = vt.iLocationid  WHERE (vt.iLocationid = '-1') AND vt.eStatus='Active' AND (vt.eType = 'Ride') AND (vt.ePoolStatus = 'No')";
}
?>