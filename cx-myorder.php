<?php
include_once('common.php');
//include_once('generalFunctions.php');

include_once('include/config.php');
$script = "Order";
//$tbl_name 	= 'register_driver';
$generalobj->check_member_login();
$abc = 'rider,company';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$generalobj->setRole($abc, $url);
$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');
$ssql = $startDate = $endDate = "";
if (isset($_REQUEST['startDate']) && $_REQUEST['startDate'] != "") {
    $startDate = $_REQUEST['startDate'];
}
if (isset($_REQUEST['endDate']) && $_REQUEST['endDate'] != "") {
    $endDate = $_REQUEST['endDate'];
}
if ($action != '') {
    if ($startDate != '') {
        $ssql .= " AND Date(ord.tOrderRequestDate) >='" . $startDate . "'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(ord.tOrderRequestDate) <='" . $endDate . "'";
    }
}


/* ADD BY PJ for handle the stripe payment  */
$cancelled = 0;
if (isset($_REQUEST['cancelled']) && $_REQUEST['cancelled'] == "true") {
    $cancelled = 1;
}
if ($_REQUEST['order'] && $_REQUEST['success'] == 1 && $cancelled == 0) {
    require_once ('assets/libraries/pubnub/autoloader.php');
    require_once ('assets/libraries/SocketCluster/autoload.php');
    include_once('app_common_functions.php');
    $ordrId = base64_decode(base64_decode(trim($_REQUEST['order'])));

    $queryOder = 'SELECT vOrderNo,iOrderId,eCheckUserWallet,iUserId,fNetTotal,iCompanyId,iServiceId FROM orders WHERE iOrderId = "' . $ordrId . '" AND iStatusCode != "1"';
    $unPlacedOrder = $obj->MySQLSelect($queryOder);
    if (count($unPlacedOrder) > 0) {
        $where = " iOrderId = '$ordrId'";
        $CheckUserWallet = $unPlacedOrder[0]['eCheckUserWallet'];
        $iUserId = $unPlacedOrder[0]['iUserId'];
        $fNetTotal = $unPlacedOrder[0]['fNetTotal'];
        $vOrderNo = $unPlacedOrder[0]['vOrderNo'];
        $iOrderId = $unPlacedOrder[0]['iOrderId'];
        if ($CheckUserWallet == "Yes") {
            $user_available_balance = $generalobj->get_user_available_balance($iUserId, "Rider");
            if ($fNetTotal > $user_available_balance) {
                $fNetTotal = $fNetTotal - $user_available_balance;
                $user_wallet_debit_amount = $user_available_balance;
            } else {
                $user_wallet_debit_amount = $fNetTotal;
                $fNetTotal = 0;
            }
            //echo $fNetTotal."=====".$user_wallet_debit_amount."=====".$user_available_balance;
            $data['fNetTotal'] = $fNetTotal;

            //Added By HJ On 13-02-2020 For Deduct Wallet Amount From User Wallet When Payment Flow 1 (Remain For Payment Flow 2 and 3) (SP Will Check then Put Conditiom For 2 and 3 In main) Dicuss With SP Start
            if ($user_wallet_debit_amount > 0 && $SYSTEM_PAYMENT_FLOW == "Method-1") {
                $dDate = date("Y-m-d H:i:s");
                $ePaymentStatus = "Unsettelled";
                $tDescription = "#LBL_DEBITED_BOOKING_DL#" . " " . $vOrderNo;
                $generalobj->InsertIntoUserWallet($iUserId, "Rider", $user_wallet_debit_amount, "Debit", 0, "Booking", $tDescription, $ePaymentStatus, $dDate, $iOrderId);
                // $obj->MySQLQueryPerform("user_wallet",$data_wallet,'insert');
            }
            //Added By HJ On 13-02-2020 For Deduct Wallet Amount From User Wallet When Payment Flow 1 (Remain For Payment Flow 2 and 3) (SP Will Check then Put Conditiom For 2 and 3 In main) Dicuss With SP End
        }
//echo "aaaaaaaaaaaa";exit;
        $data['ePaid'] = "Yes";
        $data['iStatusCode'] = 1;
        //$data['fNetTotal'] = 0;
        $data['fWalletDebit'] = $user_wallet_debit_amount;
        $id = $obj->MySQLQueryPerform("orders", $data, 'update', $where);
        //$OrderLogId = createOrderLog($ordrId, "1");

        $sql1 = "UPDATE order_status_logs SET iStatusCode = '1' WHERE iOrderId = '" . $ordrId . "'";
        $db_company = $obj->sql_query($sql1);

        /* Update Payment table as Successful Payment */
        $sql = 'SELECT iPaymentId FROM payments WHERE iOrderId = "' . $ordrId . '"';
        $paymentData = $obj->MySQLSelect($sql);
        if ($db_company == 1 && count($paymentData) == 0) {
            $pay_data = $_SESSION['pay_data'];

            /* Change tPaymentDetails for Flutterwave Transaction */
            if (isset($_REQUEST['flwref']) && $_REQUEST['flwref'] != '') {
                $transactionArray = array('flwref' => $_REQUEST['flwref'], 'txref' => $_REQUEST['txref']);
                $pay_data['tPaymentDetails'] = json_encode($transactionArray);
            }

            $obj->MySQLQueryPerform("payments", $pay_data, 'insert');
        }
        
        // # Send Notification To Company ##
        $iCompanyId = $unPlacedOrder[0]['iCompanyId'];
        $iServiceId = $unPlacedOrder[0]['iServiceId'];
        $CompanyMessage = "OrderRequested";
        
        $sql = 'SELECT vLang FROM company WHERE iCompanyId = "' . $iCompanyId . '"';
        $vLangCode = $obj->MySQLSelect($sql)[0]['vLang'];
        //$vLangCode = get_value('company', 'vLang', 'iCompanyId', $iCompanyId, '', 'true');
        if ($vLangCode == "" || $vLangCode == NULL) {
            //$vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
            $sql = 'SELECT vCode FROM language_master WHERE eDefault = "Yes"';
            $vLangCode = $obj->MySQLSelect($sql)[0]['vCode'];
        }
        
        //$languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
        $orderreceivelbl = $langage_lbl['LBL_NEW_ORDER_PLACED_TXT'] . " " . $vOrderNo;
        $alertMsg = $orderreceivelbl;
        if ($PUBNUB_DISABLED == "Yes") {
            $ENABLE_PUBNUB = "No";
        }
        
        $alertSendAllowed = true;
        /* For PubNub Setting */
        $tableName = "company";
        $iMemberId_VALUE = $iCompanyId;
        $iMemberId_KEY = "iCompanyId";
        //$AppData = get_value($tableName, 'iAppVersion,eDeviceType,iGcmRegId,tSessionId', $iMemberId_KEY, $iMemberId_VALUE);
        $sql = "SELECT iAppVersion,eDeviceType,iGcmRegId,tSessionId FROM $tableName WHERE $iMemberId_KEY = $iMemberId_VALUE";
        $AppData = $obj->MySQLSelect($sql);
        $iAppVersion = $AppData[0]['iAppVersion'];
        $eDeviceType = $AppData[0]['eDeviceType'];
        $iGcmRegId = $AppData[0]['iGcmRegId'];
        $tSessionId = $AppData[0]['tSessionId'];
        $registatoin_ids = $iGcmRegId;
        $deviceTokens_arr_ios = array();
        $registation_ids_new = array();
        
        $message_arr['tSessionId'] = $tSessionId;
        $message_arr['vTitle'] = $alertMsg;
        $message_arr['Message'] = $CompanyMessage;
        $message_arr['MsgCode'] = strval(time() . mt_rand(1000, 9999));
        $message_arr['iOrderId'] = $iOrderId;
        $message_arr['eSystem'] = "DeliverAll";
        $message_pub = json_encode($message_arr, JSON_UNESCAPED_UNICODE);
        /* For PubNub Setting Finished */
        if ($alertSendAllowed == true) {
            if ($eDeviceType == "Android") {
                array_push($registation_ids_new, $iGcmRegId);
                $Rmessage = array(
                    "message" => $message_pub
                );
                $result = send_notification($registation_ids_new, $Rmessage, 0);
            } else {
                array_push($deviceTokens_arr_ios, $iGcmRegId);
                sendApplePushNotificationOrder(2, $deviceTokens_arr_ios, $message_pub, $alertMsg, 0); 
            }
        
            $data_CompanyRequest = array();
            $data_CompanyRequest['iCompanyId'] = $iCompanyId;
            $data_CompanyRequest['iOrderId'] = $iOrderId;
            $data_CompanyRequest['tMessage'] = $message_pub;
            $data_CompanyRequest['vMsgCode'] = $message_arr['MsgCode'];
            $data_CompanyRequest['dAddedDate'] = @date("Y-m-d H:i:s");
            $requestId = addToCompanyRequest2($data_CompanyRequest);
        }
        
        $channelName = "COMPANY_" . $iCompanyId;
        
        if ($eDeviceType == "Ios") {
            sleep(3);
        }
        publishEventMessage($channelName, $message_pub);
    }
}
function addToCompanyRequest2($data) {
    global $obj;
    $data['dAddedDate'] = @date("Y-m-d H:i:s");
    $id = $obj->MySQLQueryPerform("company_request", $data, 'insert');
    return $id;
}
/* End handle the stripe payment  */
if (isset($_SESSION['sess_lang'])) {
    $vLang = $_SESSION['sess_lang'];
} else{
	  $vLang = $default_lang;
}


$sql = "SELECT ord.fTotalGenerateFare,ord.iOrderId,ord.vOrderNo,ord.vTimeZone,sc.vServiceName_" . $default_lang . " as vServiceName,ord.tOrderRequestDate,ord.fNetTotal,ord.iUserId, Concat(u.vName,' ',u.vLastName) as Username,cmp.vCompany,ordst.vStatus_".$vLang." as vStatus,(select count(orddetail.iOrderId) from order_details as orddetail where orddetail.iOrderId = ord.iOrderId) as TotalItem,ord.eTakeaway From orders as ord LEFT JOIN company as cmp ON cmp.iCompanyId = ord.iCompanyId LEFT JOIN order_status as ordst ON ordst.iStatusCode = ord.iStatusCode LEFT JOIN register_user as u ON u.iUserId = ord.iUserId LEFT JOIN service_categories as sc on sc.iServiceId=ord.iServiceId WHERE ord.iStatusCode NOT IN ('11','12') AND ord.iUserId = '" . $_SESSION['sess_iUserId'] . "' " . $ssql . " AND IF(ord.eTakeaway = 'Yes' && ordst.iStatusCode = 6, ordst.eTakeaway='Yes', ordst.eTakeaway != 'Yes') AND ord.iServiceId IN ($enablesevicescategory) ORDER BY ord.iOrderId DESC ";
// echo $sql;exit;
$db_order_detail = $obj->MySQLSelect($sql);
//echo "<pre>";print_r($db_order_detail);die;
$Today = Date('Y-m-d');
$tdate = date("d") - 1;
$mdate = date("d");
$Yesterday = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));

$curryearFDate = date("Y-m-d", mktime(0, 0, 0, '1', '1', date("Y")));
$curryearTDate = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y")));
$prevyearFDate = date("Y-m-d", mktime(0, 0, 0, '1', '1', date("Y") - 1));
$prevyearTDate = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y") - 1));

$currmonthFDate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $tdate, date("Y")));
$currmonthTDate = date("Y-m-d", mktime(0, 0, 0, date("m") + 1, date("d") - $mdate, date("Y")));
$prevmonthFDate = date("Y-m-d", mktime(0, 0, 0, date("m") - 1, date("d") - $tdate, date("Y")));
$prevmonthTDate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $mdate, date("Y")));

$monday = date('Y-m-d', strtotime('sunday this week -1 week'));
$sunday = date('Y-m-d', strtotime('saturday this week'));

$Pmonday = date('Y-m-d', strtotime('sunday this week -2 week'));
$Psunday = date('Y-m-d', strtotime('saturday this week -1 week'));

//$invoice_icon = "driver-view-icon.png";
//$canceled_icon = "canceled-invoice.png";
if (file_exists($logogpath . "driver-view-icon.png")) {
    $invoice_icon = $logogpath . "driver-view-icon.png";
} else {
    $invoice_icon = "assets/img/driver-view-icon.png";
}


$catdata = serviceCategories;
$allservice_cat_data = json_decode($catdata, true);

$serviceIdArray = array();
$serviceIdArray = array_column($allservice_cat_data, 'iServiceId');
$restaurant = '';
if(strtoupper(DELIVERALL) == "YES") {
    if (count($serviceIdArray) == 1 && $serviceIdArray[0]==1) {
        $restaurant = $langage_lbl['LBL_RESTAURANT'];
    } else {
        $restaurant = $langage_lbl['LBL_STORE'];
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
        <!--<title><?= $SITE_NAME ?></title>-->
        <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_ORDERS_TXT']; ?></title>
        <meta name="keywords" value="<?= $meta_arr['meta_keyword']; ?>"/>
        <meta name="description" value="<?= $meta_arr['meta_desc']; ?>"/>
        <!-- Default Top Script and css -->
        <?php include_once("top/top_script.php"); ?>
        <!-- End: Default Top Script and css-->
        <style type="text/css">
            .grey-color {
                color: grey !important
            }
        </style>
    </head>
     

    <body id="wrapper">
        <!-- home page -->
        <!-- home page -->
        <?php if ($template != 'taxishark') { ?>
            <div id="main-uber-page">
            <?php } ?>
            <!-- Left Menu -->
            <?php include_once("top/left_menu.php"); ?>
            <!-- End: Left Menu-->
            <!-- Top Menu -->
            <?php include_once("top/header_topbar.php"); ?>
            <!-- End: Top Menu-->
            <!-- First Section -->
            <?php include_once("top/header.php"); ?>
            <!-- End: First Section -->
            <section class="profile-section my-trips">
                <div class="profile-section-inner">
                    <div class="profile-caption">
                        <div class="page-heading">
                            <h1><?= $langage_lbl['LBL_ORDERS_TXT']; ?></h1>
                        </div>

                        <form class="tabledata-filter-block filter-form" name="search"  method="post" onSubmit="return checkvalid()">
                            <input type="hidden" name="action" value="search" />
                            <div class="filters-column mobile-full">
                                <label><?= $langage_lbl['LBL_ORDER_SEARCH_BY_DATE']; ?></label>
                                <select id="timeSelect" name="dateRange">
                                    <option value="">Select</option>
                                    <option value="today" <?php
                                    if ($_POST['dateRange'] == 'today') {
                                        echo 'selected';
                                    }
                                    ?>><?= $langage_lbl['LBL_COMPANY_TRIP_Today']; ?></option>
                                    <option value="yesterday" <?php
                                    if ($_POST['dateRange'] == 'yesterday') {
                                        echo 'selected';
                                    }
                                    ?>><?= $langage_lbl['LBL_COMPANY_TRIP_Yesterday']; ?></option>
                                    <option value="currentWeek" <?php
                                    if ($_POST['dateRange'] == 'currentWeek') {
                                        echo 'selected';
                                    }
                                    ?>><?= $langage_lbl['LBL_COMPANY_TRIP_Current_Week']; ?></option>
                                    <option value="previousWeek" <?php
                                    if ($_POST['dateRange'] == 'previousWeek') {
                                        echo 'selected';
                                    }
                                    ?>><?= $langage_lbl['LBL_COMPANY_TRIP_Previous_Week']; ?></option>
                                    <option value="currentMonth" <?php
                                    if ($_POST['dateRange'] == 'currentMonth') {
                                        echo 'selected';
                                    }
                                    ?>><?= $langage_lbl['LBL_COMPANY_TRIP_Current_Month']; ?></option>
                                    <option value="previousMonth" <?php
                                    if ($_POST['dateRange'] == 'previousMonth') {
                                        echo 'selected';
                                    }
                                    ?>><?= $langage_lbl['LBL_COMPANY_TRIP_Previous Month']; ?></option>
                                    <option value="currentYear" <?php
                                    if ($_POST['dateRange'] == 'currentYear') {
                                        echo 'selected';
                                    }
                                    ?>><?= $langage_lbl['LBL_COMAPNY_TRIP_Current_Year']; ?></option>
                                    <option value="previousYear" <?php
                                    if ($_POST['dateRange'] == 'previousYear') {
                                        echo 'selected';
                                    }
                                    ?>><?= $langage_lbl['LBL_COMPANY_TRIP_Previous_Year']; ?></option>

                                </select>
                            </div>
                            <div class="filters-column mobile-half">
                                <label><?= $langage_lbl['LBL_MYTRIP_FROM_DATE']; ?></label>
                                <input type="text" id="dp4" name="startDate" placeholder="<?= $langage_lbl['LBL_MYTRIP_FROM_DATE']; ?>" class="form-control" value="" readonly style="cursor:default; background-color: #fff"/>
                                <i class="icon-cal" id="from-date"></i>
                            </div>
                            <div class="filters-column mobile-half">
                                <label><?= $langage_lbl['LBL_MYTRIP_TO_DATE']; ?></label>
                                <input type="text" id="dp5" name="endDate" placeholder="<?= $langage_lbl['LBL_MYTRIP_TO_DATE']; ?>" class="form-control" value="" readonly style="cursor:default; background-color: #fff"/>
                                <i class="icon-cal" id="to-date"></i>
                            </div>
                            <div class="filters-column mobile-full">
                                <button class="driver-trip-btn"><?= $langage_lbl['LBL_MYTRIP_Search']; ?></button>
                                <!-- <button onClick="reset();" class="driver-trip-btn"><?= $langage_lbl['LBL_MYTRIP_RESET']; ?></button> -->
                                <a href="myorder" class="gen-btn"><?= $langage_lbl['LBL_RESET'] ?></a>
                            </div>
                        </form>

                    </div>
                </div>
            </section>
            <section class="profile-earning">
                <div class="profile-earning-inner">
                    <div class="table-holder">
                        <table id="my-trips-data" class="ui celled table custom-table" style="width:100%">
                            <thead>
                                <tr>
                                    <? if (count($allservice_cat_data) > 1) { ?>
                                        <th style="text-align: center;"><?= $langage_lbl['LBL_ORDER_TYPE'] ?></th>
                                    <? } ?>
                                    <th style="text-align: center;"><?= $langage_lbl['LBL_ORDER_NO_TXT']; ?></th>	
                                    <th width="17%" style="text-align: center;"><?= $langage_lbl['LBL_ORDER_DATE_TXT']; ?></th>
                                    <th style="text-align: center;"><?= $restaurant ?></th>
                                    <th style="text-align: center;"><?= $langage_lbl['LBL_TOTAL_ITEM_TXT']; ?></th>
                                    <th style="text-align: center;"><?= $langage_lbl['LBL_ORDER_TOTAL_TXT']; ?></th>
                                    <th style="text-align: center;"><?= $langage_lbl['LBL_ORDER_STATUS_TXT']; ?></th>
                                    <th style="text-align: center;"><?= $langage_lbl['LBL_VIEW_DETAIL_TXT']; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?
                                for ($i = 0; $i < count($db_order_detail); $i++) {
                                    $iOrderIdNew = $db_order_detail[$i]['iOrderId'];
                                    $getUserCurrencyLanguageDetails = $generalobj->getUserCurrencyLanguageDetailsWeb($_SESSION['sess_iUserId'], $iOrderIdNew);
                                    $currencySymbol = $getUserCurrencyLanguageDetails['currencySymbol'];
                                    $Ratio = $getUserCurrencyLanguageDetails['Ratio'];

                                    //$fNetTotalratio = $db_order_detail[$i]['fNetTotal'] * $Ratio; // Comment By HJ On 07-05-2020 As Per Discuss With KS
                                    $fNetTotalratio = $db_order_detail[$i]['fTotalGenerateFare'] * $Ratio; // Added By HJ On 07-05-2020 As Per Discuss With KS
                                    $systemTimeZone = date_default_timezone_get();
                                    if ($db_order_detail[$i]['tOrderRequestDate'] != "" && $db_order_detail[$i]['vTimeZone'] != "") {
                                        $tOrderRequestDate = converToTz($db_order_detail[$i]['tOrderRequestDate'], $db_order_detail[$i]['vTimeZone'], $systemTimeZone);
                                    } else {
                                        $tOrderRequestDate = $db_order_detail[$i]['tOrderRequestDate'];
                                    }

                                    ?>
                                    <tr class="gradeA">
                                        <? if (count($allservice_cat_data) > 1) { ?>
                                            <td><?= $db_order_detail[$i]['vServiceName']; ?></td>
                                        <? } ?>
                                        <td align="center" data-order="<?php echo $db_order_detail[$i]['iOrderId']; ?>">
                                            <?=$db_order_detail[$i]['vOrderNo'];?>
                                            <?= $db_order_detail[$i]['eTakeaway'] == 'Yes' ? '<br><span class="grey-color">'.$langage_lbl['LBL_TAKE_AWAY'].'</span>' : ''?>
                                        </td>
                                        <td><?= $generalobj->DateTime1($tOrderRequestDate, 'yes'); ?></td>
                                        <td align="center">
                                            <?php if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] != "company") { ?>
                                                <?= $generalobj->clearName($db_order_detail[$i]['vCompany']); ?>
                                            <?php } else { ?>
                                                <?= $db_order_detail[$i]['vCompany']; ?>
                                            <?php } ?>
                                        </td>
                                        <td align="center"><?= $db_order_detail[$i]['TotalItem']; ?></td>
                                        <td align="center"><?= $currencySymbol . " " . $generalobj->trip_currency_payment($fNetTotalratio); ?></td>
                                        <td align="center"><?= str_replace("#STORE#", $db_order_detail[$i]['vCompany'], $db_order_detail[$i]['vStatus']); ?></td>
                                        <td align="center" width="10%">
                                            <a target = "_blank" href="invoice_deliverall.php?iOrderId=<?= base64_encode(base64_encode($db_order_detail[$i]['iOrderId'])) ?>">
                                                <img alt="" src="<?php echo $invoice_icon; ?>">
                                            </a>
                                        </td>		
                                    </tr>
                                <? } ?>		
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
            <!-- home page end-->
            <!-- footer part -->
            <?php include_once('footer/footer_home.php'); ?>

            <div style="clear:both;"></div>
            <?php if ($template != 'taxishark') { ?>
            </div>
        <?php } ?>
        <!-- footer part end -->
        <!-- Footer Script -->
        <?php include_once('top/footer_script.php'); ?>
        <script src="assets/js/jquery-ui.min.js"></script>
        <script src="assets/plugins/dataTables/jquery.dataTables.js"></script>


        <script type="text/javascript">
                            if ($('#my-trips-data').length > 0) {
                                $('#my-trips-data').DataTable({"oLanguage": langData,"order": [1, "desc"]});
                            }




                            $(document).on('change', '#timeSelect', function (e) {
                                e.preventDefault();

                                var timeSelect = $(this).val();

                                if (timeSelect == 'today') {
                                    todayDate('dp4', 'dp5')
                                }
                                if (timeSelect == 'yesterday') {
                                    yesterdayDate('dFDate', 'dTDate')
                                }
                                if (timeSelect == 'currentWeek') {
                                    currentweekDate('dFDate', 'dTDate')
                                }
                                if (timeSelect == 'previousWeek') {
                                    previousweekDate('dFDate', 'dTDate')
                                }
                                if (timeSelect == 'currentMonth') {
                                    currentmonthDate('dFDate', 'dTDate')
                                }
                                if (timeSelect == 'previousMonth') {
                                    previousmonthDate('dFDate', 'dTDate')
                                }
                                if (timeSelect == 'currentYear') {
                                    currentyearDate('dFDate', 'dTDate')
                                }
                                if (timeSelect == 'previousYear') {
                                    previousyearDate('dFDate', 'dTDate')
                                }

                            });




        </script>

        <script type="text/javascript">
            $(document).ready(function () {
                $("#dp4").datepicker({
                    dateFormat: "yy-mm-dd",
                    changeYear: true,
                    changeMonth: true,
                    yearRange: "-100:+10"
                });
                $("#dp5").datepicker({
                    dateFormat: "yy-mm-dd",
                    changeYear: true,
                    changeMonth: true,
                    yearRange: "-100:+10"
                });
                if ('<?= $startDate ?>' != '') {
                    $("#dp4").val('<?= $startDate ?>');
                    $("#dp4").datepicker('refresh');
                }
                if ('<?= $endDate ?>' != '') {
                    $("#dp5").val('<?= $endDate; ?>');
                    $("#dp5").datepicker('refresh');
                }
                $('#dataTables-example').DataTable({
                    "oLanguage": langData,
                    "order": [[2, "desc"]]
                });
            });
            function reset() {
                location.reload();
            }
            function todayDate()
            {
                $("#dp4").val('<?= $Today; ?>');
                $("#dp5").val('<?= $Today; ?>');
            }
            function yesterdayDate()
            {
                $("#dp4").val('<?= $Yesterday; ?>');
                $("#dp5").val('<?= $Yesterday; ?>');
                $("#dp4").datepicker('refresh');
                $("#dp5").datepicker('refresh');
            }
            function currentweekDate(dt, df)
            {
                $("#dp4").val('<?= $monday; ?>');
                $("#dp5").val('<?= $sunday; ?>');
                $("#dp4").datepicker('refresh');
                $("#dp5").datepicker('refresh');
            }
            function previousweekDate(dt, df)
            {
                $("#dp4").val('<?= $Pmonday; ?>');
                $("#dp5").val('<?= $Psunday; ?>');
                $("#dp4").datepicker('refresh');
                $("#dp5").datepicker('refresh');
            }
            function currentmonthDate(dt, df)
            {
                $("#dp4").val('<?= $currmonthFDate; ?>');
                $("#dp5").val('<?= $currmonthTDate; ?>');
                $("#dp4").datepicker('refresh');
                $("#dp5").datepicker('refresh');
            }
            function previousmonthDate(dt, df)
            {
                $("#dp4").val('<?= $prevmonthFDate; ?>');
                $("#dp5").val('<?= $prevmonthTDate; ?>');
                $("#dp4").datepicker('refresh');
                $("#dp5").datepicker('refresh');
            }
            function currentyearDate(dt, df)
            {
                $("#dp4").val('<?= $curryearFDate; ?>');
                $("#dp5").val('<?= $curryearTDate; ?>');
                $("#dp4").datepicker('refresh');
                $("#dp5").datepicker('refresh');
            }
            function previousyearDate(dt, df)
            {
	    
                $("#dp4").val('<?= $prevyearFDate; ?>');
                $("#dp5").val('<?= $prevyearTDate; ?>');
                $("#dp4").datepicker('refresh');
                $("#dp5").datepicker('refresh');
            }
            function checkvalid() {
                if ($("#dp5").val() < $("#dp4").val()) {
                    //bootbox.alert("<h4>From date should be lesser than To date.</h4>");
                    bootbox.dialog({
                        message: "<h4><?php echo addslashes($langage_lbl['LBL_FROM_TO_DATE_ERROR_MSG']); ?></h4>",
                        buttons: {
                            danger: {
                                label: "OK",
                                className: "btn-danger"
                            }
                        }
                    });
                    return false;
                }
            }
        </script>
        

        <!-- End: Footer Script -->
    </body>
</html>
