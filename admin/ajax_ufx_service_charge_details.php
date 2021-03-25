<?
include_once('../common.php');


if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}

$iVehicleTypeId = isset($_REQUEST['iVehicleTypeId']) ? $_REQUEST['iVehicleTypeId'] : '';
$iUserIdNew = isset($_REQUEST['iUserId']) ? $_REQUEST['iUserId'] : '';

$sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();

$sql = "SELECT vt.iVehicleCategoryId,vc.iParentId,vt.eFareType,vt.fFixedFare,vt.fPricePerKM,vt.fPricePerMin,vt.fPricePerHour,vt.iMinFare,vt.iBaseFare FROM vehicle_type as vt LEFT JOIN ".$$sql_vehicle_category_table_name." as vc on vc.iVehicleCategoryId = vt.iVehicleCategoryId WHERE vt.iVehicleTypeId = '".$iVehicleTypeId."' AND vt.eType = 'UberX'";
$db_vType = $obj->MySQLSelect($sql);

$iVehicleCategoryId = $db_vType[0]['iVehicleCategoryId'];
$iParentId= $db_vType[0]['iParentId'];
$eFareType = $db_vType[0]['eFareType'];
$fFixedFare = $db_vType[0]['fFixedFare'];
$fPricePerHour= $db_vType[0]['fPricePerHour'];
$fPricePerKM= $db_vType[0]['fPricePerKM'];
$fPricePerMin= $db_vType[0]['fPricePerMin'];
$iBaseFare= $db_vType[0]['iBaseFare'];
$iMinFare= $db_vType[0]['iMinFare'];

$csql = "SELECT ePriceType FROM ".$sql_vehicle_category_table_name." WHERE iVehicleCategoryId = '".$iParentId."'";
$db_vCatData = $obj->MySQLSelect($csql);
$ePriceType = $db_vCatData[0]['ePriceType'];

if(!empty($iUserIdNew)){
	$pSql = "SELECT vCurrencyPassenger,vCountry FROM register_user WHERE iUserId = '".$iUserIdNew."'";
	$db_UserData = $obj->MySQLSelect($pSql);
	$vCurrencyPassenger = $db_UserData[0]['vCurrencyPassenger'];
	$vCountry= $db_UserData[0]['vCountry'];

	$cSql = "SELECT eUnit FROM country WHERE vCountryCode='".$vCountry."'";
	$db_UnitData = $obj->MySQLSelect($cSql);
	$eUnit = $db_UnitData[0]['eUnit'];
	if($eUnit == 'KMs'){
		$distancelabel = $langage_lbl['LBL_PRICE_PER_KM'];
	} else {
		$distancelabel = $langage_lbl['LBL_PRICE_PER_MILES'];
	}

	$rSql = "SELECT Ratio,vSymbol FROM currency WHERE vName='".$vCurrencyPassenger."'";
	$db_ratioData = $obj->MySQLSelect($rSql);
	$Ratio = $db_ratioData[0]['Ratio'];
	$vSymbol= $db_ratioData[0]['vSymbol'];
}

if($ePriceType != 'Provider'){
	if($eFareType == 'Hourly') {
		echo '<h3>'.$langage_lbl['LBL_FARE_DETAILS'].'</h3>';
		echo  '<div>'.$vSymbol.round($fPricePerHour*$Ratio,2)." / ". $langage_lbl['LBL_HOUR'].'</div>';
	} else if ($eFareType == 'Fixed'){
		echo '<h3>'.$langage_lbl['LBL_FARE_DETAILS'].'</h3>';
		echo  '<div>'.$vSymbol.round($fFixedFare,2)." Fixed</div>";
	} else {
		echo '<h3>'.$langage_lbl['LBL_FARE_DETAILS'].'</h3>';
		echo '<div>'.$vSymbol.round($fPricePerMin,2)." ".$langage_lbl['LBL_PRICE_PER_MINUTE'].'</div>';
		echo '<div>'.$vSymbol.round($fPricePerKM,2)." ".$distancelabel.'</div>';
		echo '<div>'.$vSymbol.round($iBaseFare,2)." ".$langage_lbl['LBL_BASE_FARE_SMALL_TXT'].'</div>';
		echo '<div>'.$vSymbol.round($iMinFare,2)." ".$langage_lbl['LBL_MIN_FARE'].'</div>';
	}
}
exit();