<?php
include_once('common.php');
//include_once('generalFunctions.php');
include_once('include/config.php');
include_once('app_common_functions.php');
$script = "Order";
//$tbl_name 	= 'register_driver';

//added by SP for cubex changes on 07-11-2019
if ($generalobj->checkXThemOn() == 'Yes') {
    include_once("cx-myorder.php");
    exit;
}
$generalobj->check_member_login();
$abc = 'rider,company';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$generalobj->setRole($abc, $url);
$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');
$ssql = $startDate = $endDate = '';
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

//payment gateway start
$cancelled = 0;
if (isset($_REQUEST['cancelled']) && $_REQUEST['cancelled'] == "true") {
    $cancelled = 1;
}
if ($_REQUEST['order'] && $_REQUEST['success'] == 1 && $cancelled == 0) {
    $ordrId = base64_decode(base64_decode(trim($_REQUEST['order'])));

    $queryOder = 'SELECT iOrderId FROM orders WHERE iOrderId = "' . $ordrId . '" AND iStatusCode != "1"';
    $unPlacedOrder = $obj->MySQLSelect($queryOder);


    if (count($unPlacedOrder) > 0) {
        $sql1 = "UPDATE orders SET iStatusCode = '1' WHERE iOrderId = '" . $ordrId . "'";
        $db_company = $obj->sql_query($sql1);

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
    }
}
//payment gateway end
if (isset($_SESSION['sess_lang'])) {
    $vLang = $_SESSION['sess_lang'];
} else{
	  $vLang = $default_lang;
}

$sql = "SELECT ord.iOrderId,ord.vOrderNo,ord.vTimeZone,sc.vServiceName_" . $default_lang . " as vServiceName,ord.tOrderRequestDate,ord.fNetTotal,ord.iUserId, ord.fRoundingAmount, ord.eRoundingType, Concat(u.vName,' ',u.vLastName) as Username,cmp.vCompany,ordst.vStatus_".$vLang." as vStatus,(select count(orddetail.iOrderId) from order_details as orddetail where orddetail.iOrderId = ord.iOrderId) as TotalItem,ord.eTakeaway From orders as ord LEFT JOIN company as cmp ON cmp.iCompanyId = ord.iCompanyId LEFT JOIN order_status as ordst ON ordst.iStatusCode = ord.iStatusCode LEFT JOIN register_user as u ON u.iUserId = ord.iUserId LEFT JOIN service_categories as sc on sc.iServiceId=ord.iServiceId WHERE ord.iStatusCode NOT IN ('11','12') AND ord.iUserId = '" . $_SESSION['sess_iUserId'] . "' " . $ssql . " AND IF(ord.eTakeaway = 'Yes' && ordst.iStatusCode = 6, ordst.eTakeaway='Yes', ordst.eTakeaway != 'Yes') ORDER BY ord.iOrderId DESC ";

$db_order_detail = $obj->MySQLSelect($sql);

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
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_ORDERS_TXT']; ?></title>
        <!-- Default Top Script and css -->
        <?php include_once("top/top_script.php"); ?>

        <!-- <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" /> -->
        <!-- End: Default Top Script and css-->
        <style type="text/css">
            .grey-color {
                color: grey !important
            }
        </style>
    </head>
    <body>
        <!-- home page -->
        <div id="main-uber-page">
            <!-- Left Menu -->
            <?php include_once("top/left_menu.php"); ?>
            <!-- End: Left Menu-->
            <!-- Top Menu -->
            <?php include_once("top/header_topbar.php"); ?>
            <!-- End: Top Menu-->
            <!-- contact page-->
            <div class="page-contant">
                <div class="page-contant-inner">
                    <h2 class="header-page"><?= $langage_lbl['LBL_ORDERS_TXT']; ?></h2>
                    <!-- trips page -->
                    <div class="trips-page">
                        <form name="search" action="" method="post" onSubmit="return checkvalid()">
                            <input type="hidden" name="action" value="search" />
                            <div class="Posted-date">
                                <h3><?= $langage_lbl['LBL_ORDER_SEARCH_BY_DATE']; ?></h3>
                                <span>
                                    <input type="text" id="dp4" name="startDate" placeholder="<?= $langage_lbl['LBL_WALLET_FROM_DATE']; ?>" class="form-control" value="" readonly style="cursor:default; background-color: #fff"/>
                                    <input type="text" id="dp5" name="endDate" placeholder="<?= $langage_lbl['LBL_WALLET_TO_DATE']; ?>" class="form-control" value="" readonly style="cursor:default; background-color: #fff"/>
                                </span>
                            </div>
                            <div class="time-period">
                                <h3><?= $langage_lbl['LBL_ORDER_SEARCH_BY_TIME_PERIOD']; ?></h3>
                                <span>
                                    <a onClick="return todayDate('dp4', 'dp5');"><?= $langage_lbl['LBL_COMPANY_TRIP_Today']; ?></a>
                                    <a onClick="return yesterdayDate('dFDate', 'dTDate');"><?= $langage_lbl['LBL_COMPANY_TRIP_Yesterday']; ?></a>
                                    <a onClick="return currentweekDate('dFDate', 'dTDate');"><?= $langage_lbl['LBL_COMPANY_TRIP_Current_Week']; ?></a>
                                    <a onClick="return previousweekDate('dFDate', 'dTDate');"><?= $langage_lbl['LBL_COMPANY_TRIP_Previous_Week']; ?></a>
                                    <a onClick="return currentmonthDate('dFDate', 'dTDate');"><?= $langage_lbl['LBL_COMPANY_TRIP_Current_Month']; ?></a>
                                    <a onClick="return previousmonthDate('dFDate', 'dTDate');"><?= $langage_lbl['LBL_COMPANY_TRIP_Previous Month']; ?></a>
                                    <a onClick="return currentyearDate('dFDate', 'dTDate');"><?= $langage_lbl['LBL_COMAPNY_TRIP_Current_Year']; ?></a>
                                    <a onClick="return previousyearDate('dFDate', 'dTDate');"><?= $langage_lbl['LBL_COMPANY_TRIP_Previous_Year']; ?></a>
                                </span> 
                                <b><button class="driver-trip-btn"><?= $langage_lbl['LBL_COMPANY_TRIP_Search']; ?></button>
                                    <button onClick="reset();" class="driver-trip-btn"><?= $langage_lbl['LBL_MYTRIP_RESET']; ?></button></b> 
                            </div>
                        </form>
                        <div class="trips-table"> 
                            <div class="trips-table-inner">
                                <div class="driver-trip-table">
                                    <table width="100%" border="0" cellpadding="0" cellspacing="1" id="dataTables-example">
                                        <thead>
                                            <tr>
                                                <? if (count($allservice_cat_data) > 1) { ?>
                                                    <th style="text-align: center;">Order Type</th>
                                                <? } ?>
                                                <th style="text-align: center;"><?= $langage_lbl_admin['LBL_ORDER_NO_TXT']; ?></th>	
                                                <th width="17%" style="text-align: center;"><?= $langage_lbl['LBL_ORDER_DATE_TXT']; ?></th>
                                                <th style="text-align: center;"><?= $langage_lbl['LBL_RESTAURANT_TXT_ADMIN']; ?></th>
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
                                                $currencycode = $getUserCurrencyLanguageDetails['currencycode'];

                                                $fNetTotalratio = $db_order_detail[$i]['fNetTotal'] * $Ratio;
                                                $systemTimeZone = date_default_timezone_get();
                                                if ($db_order_detail[$i]['tOrderRequestDate'] != "" && $db_order_detail[$i]['vTimeZone'] != "") {
                                                    $tOrderRequestDate = converToTz($db_order_detail[$i]['tOrderRequestDate'], $db_order_detail[$i]['vTimeZone'], $systemTimeZone);
                                                } else {
                                                    $tOrderRequestDate = $db_order_detail[$i]['tOrderRequestDate'];
                                                }

                                                if ($_SESSION['sess_user'] != "driver") {

                                                    $query = "SELECT vCurrencyPassenger FROM `trips` WHERE iOrderId = '" . $db_order_detail[$i]['iOrderId'] . "'";
                                                    $TripsData = $obj->MySQLSelect($query);

                                                    $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable, ru.vCurrencyPassenger, cu.ratio FROM register_user AS ru LEFT JOIN currency AS cu ON ru.vCurrencyPassenger = cu.vName WHERE ru.iUserId = '" . $db_order_detail[$i]['iUserId'] . "'";
                                                    $currData = $obj->MySQLSelect($sqlp);
                                                    $vCurrency = $currData[0]['vName'];
                                                    $samecur = ($TripsData[0]['vCurrencyPassenger'] == $currData[0]['vCurrencyPassenger']) ? 1 : 0;

                                                    if (isset($db_order_detail[$i]['fRoundingAmount']) && !empty($db_order_detail[$i]['fRoundingAmount']) && $db_order_detail[$i]['fRoundingAmount'] != 0 && $samecur == 1 && $currData[0]['eRoundingOffEnable'] == "Yes") {
                                                        //$roundingOffTotal_fare_amountArr = getRoundingOffAmount($returnArr['iFare'],$vCurrency);
                                                        //$roundingOffTotal_fare_amountArr['finalFareValue'] = formatNum(($iFare) - $db_trip[$i]['fRoundingAmount']);
                                                        $roundingOffTotal_fare_amountArr['method'] = $db_order_detail[$i]['eRoundingType'];
                                                        $roundingOffTotal_fare_amountArr['differenceValue'] = $db_order_detail[$i]['fRoundingAmount'];
                                                        $roundingOffTotal_fare_amountArr = getRoundingOffAmounttrip($fNetTotalratio, $db_order_detail[$i]['fRoundingAmount'], $db_order_detail[$i]['eRoundingType']);
                                                        //print_R($roundingOffTotal_fare_amountArr); exit;
                                                        //$returnArr['roundingOffAmountArr'] = $roundingOffTotal_fare_amount;
                                                        if ($roundingOffTotal_fare_amountArr['method'] == "Addition") {
                                                            $roundingMethod = "";
                                                        } else {
                                                            $roundingMethod = "-";
                                                        }
                                                        $fNetTotalratio = $roundingOffTotal_fare_amountArr['finalFareValue'];
                                                    }
                                                }
                                                $fNetTotalratio = $generalobj->trip_currency_payment($fNetTotalratio);
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
                                                    <td align="center"><?= $db_order_detail[$i]['vCompany']; ?></td>
                                                    <td align="center"><?= $db_order_detail[$i]['TotalItem']; ?></td>
                                                    <td align="center"><?= $generalobj->formateNumAsPerCurrency($fNetTotalratio, $currencycode); ?></td>
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
                                </div>	</div>
                        </div>
                        <!-- -->
                        <? //if(SITE_TYPE=="Demo"){ ?>
                        <!-- <div class="record-feature"> <span><strong>â€œEdit / Delete Record Featureâ€?</strong> has been disabled on the Demo Admin Version you are viewing now.
                          This feature will be enabled in the main product we will provide you.</span> </div>
                        <?php //} ?> -->
                        <!-- -->
                    </div>
                    <!-- -->
                    <div style="clear:both;"></div>
                </div>
            </div>
            <!-- footer part -->
            <?php include_once('footer/footer_home.php'); ?>
            <!-- footer part end -->
            <!-- End:contact page-->
            <div style="clear:both;"></div>
        </div>
        <!-- home page end-->
        <!-- Footer Script -->
        <?php include_once('top/footer_script.php'); ?>
        <script src="assets/js/jquery-ui.min.js"></script>
        <script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
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
                                                "order": [[1, "desc"]]
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

        <script type="text/javascript">
            $(document).ready(function () {
                $("[name='dataTables-example_length']").each(function () {
                    $(this).wrap("<em class='select-wrapper'></em>");
                    $(this).after("<em class='holder'></em>");
                });
                $("[name='dataTables-example_length']").change(function () {
                    var selectedOption = $(this).find(":selected").text();
                    $(this).next(".holder").text(selectedOption);
                }).trigger('change');
            })
        </script>
        <!-- End: Footer Script -->
    </body>
</html>
