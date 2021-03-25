<?php

include_once('../../common.php');
ob_clean();
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();

$reload = $_SERVER['REQUEST_URI'];
// echo "<pre>"; print_r($_REQUEST); die;

$urlparts = explode('?', $reload);
$parameters = $urlparts[1];

$action = isset($_REQUEST['actionpayment']) ? $_REQUEST['actionpayment'] : 'view';
$ePayDriver = isset($_REQUEST['ePayDriver']) ? $_REQUEST['ePayDriver'] : '';

if ($action == "pay_driver" && $_REQUEST['ePayDriver'] == "Yes") {
    if (SITE_TYPE != 'Demo') {
        $iTripId = $_REQUEST['iTripId'];
        //Added By HJ On 07-02-2020 FOr Optimized Code Start
        $db_pay = $obj->MySQLSelect("SELECT iTripId,ePaymentDriverStatus from payments WHERE ePaymentDriverStatus = 'UnPaid' AND iTripId > 0");
        $unPaidDataArr = $unPaidUserArr = array();
        for ($h = 0; $h < count($db_pay); $h++) {
            $unPaidDataArr[$db_pay[$h]['iTripId']][] = $db_pay[$h];
        }
        $fieldName = "eDriverPaymentStatus";
        $orgField = "ePaidByPassenger";
        if (isset($_REQUEST['orgpay']) && $_REQUEST['orgpay'] > 0) {
            $fieldName = "eOrganizationPaymentStatus";
            $orgField = "ePaidByOrganization";
        }
        //Added By HJ On 07-02-2020 FOr Optimized Code End
        //Added By HJ On 07-02-2020 For Update trip Payment Status Start 141 Mantis Bug #3307
        $getTripIds = $obj->MySQLSelect("SELECT iTripId,iUserId,vTripAdjusmentId,iTripOutstandId FROM trip_outstanding_amount WHERE $orgField = 'No'");
        for ($g = 0; $g < count($getTripIds); $g++) {
            $unPaidUserArr[$getTripIds[$g]['vTripAdjusmentId']][] = $getTripIds[$g];
        }
        $tripIdsRef = $outStandingIds = "";
        //ePaidToDriver
        //Added By HJ On 07-02-2020 For Update trip Payment Status End 141 Mantis Bug #3307
        for ($k = 0; $k < count($iTripId); $k++) {
            //Added By HJ On 07-02-2020 FOr Optimized Code Start
            $db_pay = array();
            if (isset($unPaidDataArr[$iTripId[$k]])) {
                $db_pay = $unPaidDataArr[$iTripId[$k]];
            }
            //Added By HJ On 07-02-2020 FOr Optimized Code End
            //Added By HJ On 07-02-2020 For Update trip Payment Status Start 141 Mantis Bug #3307
            if (isset($unPaidUserArr[$iTripId[$k]])) {
                //echo "<pre>";print_r($unPaidUserArr[$iTripId[$k]]);die;
                for ($n = 0; $n < count($unPaidUserArr[$iTripId[$k]]); $n++) {
                    $tripIdsRef .= $unPaidUserArr[$iTripId[$k]][$n]['iTripId'] . ",";
                    $outStandingIds .= $unPaidUserArr[$iTripId[$k]][$n]['iTripOutstandId'] . ",";
                }
            }
            //Added By HJ On 07-02-2020 For Update trip Payment Status End 141 Mantis Bug #3307
            if (count($db_pay) > 0) {
                $query = "UPDATE payments SET ePaymentDriverStatus = 'Paid' WHERE iTripId = '" . $iTripId[$k] . "'";
                $obj->sql_query($query);

                $query = "UPDATE trips SET $fieldName = 'Settelled', ePayment_request = 'Yes' WHERE iTripId = '" . $iTripId[$k] . "'";
                $obj->sql_query($query);
            } else {
                $query = "UPDATE trips SET $fieldName = 'Settelled', ePayment_request = 'Yes' WHERE iTripId = '" . $iTripId[$k] . "'";
                $obj->sql_query($query);
            }
        }
        //Added By HJ On 07-02-2020 For Update trip Payment Status Start 141 Mantis Bug #3307
        if ($tripIdsRef != "") {
            $tripIdsRef = trim($tripIdsRef, ",");
            $obj->sql_query("UPDATE trips SET $fieldName='Settelled' WHERE iTripId IN ($tripIdsRef)");
        }
        if ($outStandingIds != "") {
            $outStandingIds = trim($outStandingIds, ",");
            $obj->sql_query("UPDATE trip_outstanding_amount SET ePaidToDriver='Yes' WHERE iTripOutstandId IN ($outStandingIds)");
        }
        //Added By HJ On 07-02-2020 For Update trip Payment Status End 141 Mantis Bug #3307
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = 'Record(s) marked as settled successfully.';
    } else {
        $_SESSION['success'] = '2';
    }
    if (isset($_REQUEST['orgpay']) && $_REQUEST['orgpay'] > 0) {
        header("Location:" . $tconfig["tsite_url_main_admin"] . "org_payment_report.php?" . $parameters);
    } else {
        header("Location:" . $tconfig["tsite_url_main_admin"] . "payment_report.php?" . $parameters);
    }
    exit;
}
?>