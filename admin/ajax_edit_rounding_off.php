<?php
include_once('common.php');

$iCurrencyId = isset($_POST['iCurrencyId']) ? $_POST['iCurrencyId'] : '0'; 
$fMiddleRangeValue = isset($_POST['fMiddleRangeValue']) ? $_POST['fMiddleRangeValue'] : '0'; 
$fMiddleRangeValue = isset($_POST['iFirstRangeValue1']) ? $_POST['iFirstRangeValue1'] : ''; 
$iFirstRangeValue = isset($_POST['iFirstRangeValue']) ? $_POST['iFirstRangeValue'] : ''; 
$iSecRangeValue = isset($_POST['iSecRangeValue']) ? $_POST['iSecRangeValue'] : '';

$tbl_name = 'currency';

$q = "UPDATE ";
$where = " WHERE `iCurrencyId` = '" . $iCurrencyId . "'";
$query = $q . " `" . $tbl_name . "` SET
			`fMiddleRangeValue` = '" . $fMiddleRangeValue . "',
            `fFirstRangeValue` = '" . $iFirstRangeValue . "', 
            `fSecRangeValue` = '" . $iSecRangeValue . "'"
            . $where;

$obj->sql_query($query);
$id = ($id != '') ? $id : $obj->GetInsertId();                
echo $id; exit;                
?>