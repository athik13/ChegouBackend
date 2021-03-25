<?php

include_once("common.php");
$iTripId = isset($_REQUEST['iTripId']) ? $_REQUEST['iTripId'] : '';
$eForInvoice = isset($_REQUEST['eForInvoice']) ? $_REQUEST['eForInvoice'] : 'No'; //Added By HJ On 22-02-2020 For Solved 141 Mantis Bug #3676 As Per Discuss With KS Sir
if ($iTripId != '') {
    $driver = array();
    //Added By HJ On 22-02-2020 For Solved 141 Mantis Bug #3676 As Per Discuss With KS Sir Start 
    $whereCondo  = "AND (t.iActive = 'Active' OR t.iActive = 'On Going Trip' OR t.iActive = 'Arrived')";
    if($eForInvoice == "Yes"){
        $whereCondo  = "AND t.fTripGenerateFare  > 0";
    }
    //Added By HJ On 22-02-2020 For Solved 141 Mantis Bug #3676 As Per Discuss With KS Sir End
    $sql = "SELECT t.*,d.* FROM trips t LEFT JOIN register_driver d ON t.iDriverId = d.iDriverId WHERE t.iTripId =" . $iTripId . " $whereCondo ORDER BY t.iTripId DESC";
    $db_dtrip = $obj->MySQLSelect($sql);
    if (!empty($db_dtrip)) {
        echo json_encode($db_dtrip[0]);
        exit;
    } else {
        $returnArr = "1";
        echo json_encode($returnArr);
        exit;
    }
}
?>
