<?
include_once('common.php');
include_once('generalFunctions.php');

$script = "Trips";
$tbl_name = 'trips';
$generalobj->check_member_login();
//ini_set("display_errors", 1);
//error_reporting(E_ALL);
$abc = 'organization';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$generalobj->setRole($abc, $url);

//added by SP for cubex changes on 07-11-2019
if ($generalobj->checkXThemOn() == 'Yes') {
    include_once("cx-organization_trip.php");
    exit;
}

$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');
$ssql = $searchRider = $searchDriverPayment = $startDate = $endDate = '';
if ($action != '') {
    $startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
    $endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
    $searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '';
    $searchDriverPayment = isset($_REQUEST['searchDriverPayment']) ? $_REQUEST['searchDriverPayment'] : '';
    if ($startDate != '') {
        $ssql .= " AND Date(d.tTripRequestDate) >='" . $startDate . "'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(d.tTripRequestDate) <='" . $endDate . "'";
    }
    if ($searchDriverPayment != '') {
        $ssql .= " AND d.eOrganizationPaymentStatus ='" . $searchDriverPayment . "'";
    }
    if ($searchRider != '') {
        $ssql .= " AND d.iUserId ='" . $searchRider . "'";
    }
}
$sql = "SELECT d.*,u.vName, u.vLastName,d.tEndDate, d.tTripRequestDate, d.vRideNo, d.iActive, d.fOutStandingAmount, d.iFare, d.iDriverId, d.tSaddress, d.tDaddress,d.fTripGenerateFare,d.ePaymentBy, d.iRentalPackageId,d.eType, d.eHailTrip, d.fHotelCommision, d.vReceiverName AS name,d.eCarType,d.vTimezone,d.fTax2,d.fTax1, d.iTripId,vt.vVehicleType_" . $_SESSION['sess_lang'] . " as vVehicleType,vt.vRentalAlias_" . $_SESSION['sess_lang'] . " as vRentalVehicleTypeName, d.fCommision,d.fTripGenerateFare,d.fTipPrice, d.fCancellationFare, d.eCancelled,d.eOrganizationPaymentStatus,CONCAT(rd.vName,' ',rd.vLastName) AS name, rd.vLastName AS lname,rd.vAvgRating,rd.iDriverId,d.vTripPaymentMode FROM " . $tbl_name . " d LEFT JOIN vehicle_type vt ON vt.iVehicleTypeId = d.iVehicleTypeId LEFT JOIN  register_user u ON d.iUserId = u.iUserId LEFT JOIN register_driver rd ON d.iDriverId = rd.iDriverId WHERE d.iOrganizationId = '" . $_SESSION['sess_iOrganizationId'] . "'" . $ssql . " AND d.eSystem = 'General' ORDER BY d.iTripId DESC";
$db_trip = $obj->MySQLSelect($sql);

$sql = "SELECT * FROM  organization WHERE iOrganizationId='" . $_SESSION['sess_iOrganizationId'] . "'";
$dbOrganization = $obj->MySQLSelect($sql);

$sql = "SELECT fThresholdAmount, Ratio, vName, vSymbol FROM currency WHERE vName='" . $dbOrganization[0]['vCurrency'] . "'";
$dbOrganizationRatio = $obj->MySQLSelect($sql);

$orgCursymbol = $dbOrganizationRatio[0]['vSymbol'];
$orgCurRatio = $dbOrganizationRatio[0]['Ratio'];
$orgCurName = $dbOrganizationRatio[0]['vName'];
//$tripcurthholsamt=$dbOrganizationRatio[0]['fThresholdAmount'];

$sql = "SELECT UP.iUserId,CONCAT(RU.vName,' ',RU.vLastName) AS riderName,RU.vEmail AS vEmail FROM user_profile UP LEFT JOIN register_user RU ON UP.iUserId=RU.iUserId  WHERE RU.eStatus != 'Deleted' AND UP.eStatus != 'Deleted' AND iOrganizationId='" . $_SESSION['sess_iOrganizationId'] . "' order by RU.vName";
$db_rider = $obj->MySQLSelect($sql);
//echo "<pre>";
//print_r($db_rider);die;
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
if ($host_system == 'cubetaxiplus') {
    $canceled_icon = "canceled-invoice.png";
    $invoice_icon = "driver-view-icon.png";
} else if ($host_system == 'ufxforall') {
    $canceled_icon = "ufxforall-canceled-invoice.png";
    $invoice_icon = "ufxforall-driver-view-icon.png";
} else if ($host_system == 'uberridedelivery4') {
    $canceled_icon = "ride-delivery-canceled-invoice.png";
    $invoice_icon = "ride-delivery-driver-view-icon.png";
} else if ($host_system == 'uberdelivery4') {
    $canceled_icon = "delivery-canceled-invoice.png";
    $invoice_icon = "delivery-driver-view-icon.png";
} else {
    $invoice_icon = "driver-view-icon.png";
    $canceled_icon = "canceled-invoice.png";
}
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_ORGANIZATION_TRIP_REPORT_WEB']; ?></title>
        <!-- Default Top Script and css -->
        <?php include_once("top/top_script.php"); ?>
        <!-- <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" /> -->
        <!-- End: Default Top Script and css-->
        <style>
            .datetimerange.active{
                font-weight: 700;
                color:#300544;
            }
        </style>
    </head>
    <style>
        td.details-control {
            background: url('assets/img/details_open.png') no-repeat center center !important;
            cursor: pointer;
        }
        tr.shown td.details-control {
            background: url('assets/img/details_close.png') no-repeat center center !important;
        }
    </style>
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
                    <h2 class="header-page"><?= $langage_lbl['LBL_ORGANIZATION_TRIP_REPORT_WEB']; ?></h2>
                    <!-- trips page -->
                    <div class="trips-page">
                        <form name="search" action="" method="post" onSubmit="return checkvalid()">
                            <input type="hidden" name="action" value="search" />
                            <div class="Posted-date organization-trip">
                                <h3><?= $langage_lbl['LBL_COMPANY_TRIP_SEARCH_RIDES_POSTED_BY_DATE']; ?></h3>
                                <span>
                                    <b><input type="text" id="dp4" name="startDate" placeholder="<?= $langage_lbl['LBL_WALLET_FROM_DATE']; ?>" class="form-control" value="" readonly style="cursor:default; background-color: #fff"/>
                                        <select class="form-control filter-by-text" name = 'searchRider' data-text="Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>">
                                            <option value="">Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?></option>
                                            <?php foreach ($db_rider as $dbr) { ?>
                                                <option value="<?php echo $dbr['iUserId']; ?>" <?php
                                                if ($searchRider == $dbr['iUserId']) {
                                                    echo "selected";
                                                }
                                                ?>><?php echo $generalobj->clearName($dbr['riderName']); ?> - ( <?php echo $generalobj->clearEmail($dbr['vEmail']); ?> )</option>
                                                    <?php } ?>
                                        </select>
                                    </b>


                                    <b><input type="text" id="dp5" name="endDate" placeholder="<?= $langage_lbl['LBL_WALLET_TO_DATE']; ?>" class="form-control" value="" readonly style="cursor:default; background-color: #fff"/>
                                        <select class="form-control" name='searchDriverPayment' data-text="Select Payment Status">
                                            <option value="">Select Payment Status</option>
                                            <option value="Settelled" <?php if ($searchDriverPayment == "Settelled") { ?>selected <?php } ?>>Settled</option>
                                            <option value="Unsettelled" <?php if ($searchDriverPayment == "Unsettelled") { ?>selected <?php } ?>>Unsettled</option>
                                        </select>
                                    </b>
                                </span>
                            </div>

                            <div class="time-period">
                                <h3><?= $langage_lbl['LBL_COMPANY_TRIP_SEARCH_RIDES_POSTED_BY_TIME_PERIOD']; ?></h3>
                                <span>
                                    <a class="datetimerange" onClick="return todayDate('dp4', 'dp5');"><?= $langage_lbl['LBL_COMPANY_TRIP_Today']; ?></a>
                                    <a class="datetimerange" onClick="return yesterdayDate('dFDate', 'dTDate');"><?= $langage_lbl['LBL_COMPANY_TRIP_Yesterday']; ?></a>
                                    <a class="datetimerange" onClick="return currentweekDate('dFDate', 'dTDate');"><?= $langage_lbl['LBL_COMPANY_TRIP_Current_Week']; ?></a>
                                    <a class="datetimerange" onClick="return previousweekDate('dFDate', 'dTDate');"><?= $langage_lbl['LBL_COMPANY_TRIP_Previous_Week']; ?></a>
                                    <a class="datetimerange" onClick="return currentmonthDate('dFDate', 'dTDate');"><?= $langage_lbl['LBL_COMPANY_TRIP_Current_Month']; ?></a>
                                    <a class="datetimerange" onClick="return previousmonthDate('dFDate', 'dTDate');"><?= $langage_lbl['LBL_COMPANY_TRIP_Previous Month']; ?></a>
                                    <a class="datetimerange" onClick="return currentyearDate('dFDate', 'dTDate');"><?= $langage_lbl['LBL_COMAPNY_TRIP_Current_Year']; ?></a>
                                    <a class="datetimerange" onClick="return previousyearDate('dFDate', 'dTDate');"><?= $langage_lbl['LBL_COMPANY_TRIP_Previous_Year']; ?></a>
                                </span> 
                                <b><button class="driver-trip-btn"><?= $langage_lbl['LBL_COMPANY_TRIP_Search']; ?></button>
                                    <input type="button" value="<?= $langage_lbl['LBL_MYTRIP_RESET']; ?>" class="driver-trip-btn" onClick="window.location.href = 'organization-trip'"/>
                                </b> 
                            </div>
                        </form>
                        <div class="trips-table"> 
                            <div class="trips-table-inner">
                                <div class="driver-trip-table">
                                    <table width="100%" border="0" cellpadding="0" cellspacing="1" id="dataTables-example">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <?php if ($APP_TYPE != 'UberX' && $APP_TYPE != 'Delivery') { ?>
                                                    <th><?= $langage_lbl_admin['LBL_TRIP_JOB_TYPE_FRONT']; ?></th>
                                                <?php } ?>
                                                <th width="17%"><?= $langage_lbl['LBL_MYTRIP_RIDE_NO_TXT']; ?></th>
                                                <th><?= $langage_lbl['LBL_Pick_Up']; ?></th>
                                                <th><?= $langage_lbl['LBL_COMPANY_TRIP_DRIVER']; ?></th>
                                                <th><?= $langage_lbl['LBL_COMPANY_TRIP_RIDER']; ?></th>
                                                <th style="width: 86px !important;text-align: right;"><?= $langage_lbl['LBL_COMPANY_TRIP_FARE_TXT']; ?></th>
                                                <th><?= $langage_lbl['LBL_COMPANY_TRIP_View_Invoice']; ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?
                                            $fareTotal = 0;
                                            for ($i = 0; $i < count($db_trip); $i++) {
                                                $eType = $db_trip[$i]['eType'];
                                                $poolTxt = "";
                                                //echo "<pre>";
                                                //print_r($db_trip);
                                                //die;
                                                if ($db_trip[$i]['ePoolRide'] == "Yes") {
                                                    $poolTxt = " (Pool)";
                                                }
                                                $link_page = "invoice.php";
                                                if ($eType == 'Ride') {
                                                    $trip_type = 'Ride';
                                                } else if ($eType == 'UberX') {
                                                    $trip_type = 'Other Services';
                                                } else if ($eType == 'Multi-Delivery') {
                                                    $trip_type = 'Multi-Delivery';
                                                    $link_page = "invoice_multi_delivery.php";
                                                } else {
                                                    $trip_type = 'Delivery';
                                                }
                                                $trip_type .= $poolTxt;
                                                //echo $trip_type;die;
                                                $systemTimeZone = date_default_timezone_get();
                                                if ($db_trip[$i]['tTripRequestDate'] != "" && $db_trip[$i]['vTimezone'] != "") {
                                                    $dBookingDate = converToTz($db_trip[$i]['tTripRequestDate'], $db_trip[$i]['vTimezone'], $systemTimeZone);
                                                } else {
                                                    $dBookingDate = $db_trip[$i]['tTripRequestDate'];
                                                }

                                                if ($db_trip[$i]['iRentalPackageId'] > 0) {
                                                    $vehicleType = $db_trip[$i]['vRentalVehicleTypeName'];
                                                } else {
                                                    $vehicleType = $db_trip[$i]['vVehicleType'];
                                                }
                                                $tripType = "";
                                                if ($APP_TYPE != 'UberX' && $APP_TYPE != 'Delivery') {
                                                    if ($db_trip[$i]['eHailTrip'] == "Yes" && $db_trip[$i]['iRentalPackageId'] > 0) {
                                                        $tripType = "Rental " . $trip_type . "<br/> ( Hail )";
                                                    } else if ($db_trip[$i]['iRentalPackageId'] > 0) {
                                                        $tripType = "Rental " . $trip_type;
                                                    } else if ($db_trip[$i]['eHailTrip'] == "Yes") {
                                                        $tripType = "Hail " . $trip_type;
                                                    } else {
                                                        $tripType = $trip_type;
                                                    }
                                                }
                                                $trip_type .= $poolTxt;
                                                ?>
                                                <tr role="row">
                                                    <td class="details-control" id="details_<?= $db_trip[$i]['vRideNo']; ?>" data-paystatus="<?= $db_trip[$i]['eOrganizationPaymentStatus']; ?>" data-payby="<?= ($db_trip[$i]['ePaymentBy'] == "Passenger") ? $langage_lbl['LBL_PASSANGER_TXT_ADMIN'] : $db_trip[$i]['ePaymentBy']; ?>" data-bookdate="<?= date('d-M-Y', strtotime($dBookingDate)); ?>" data-type="<?= $vehicleType; ?>" data-triptype="<?= $tripType ?>"></td>
                                                    <?php if ($APP_TYPE != 'UberX' && $APP_TYPE != 'Delivery') { ?>
                                                        <td ><?= $tripType; ?></td>
                                                    <?php } ?>
                                                    <td align="center"><?= $db_trip[$i]['vRideNo']; ?></td>
                                                    <?php if ($APP_TYPE == 'UberX') { ?>
                                                        <td width="25%"><?= $db_trip[$i]['tSaddress']; ?></td>
                                                        <?php
                                                    } else {
                                                        if (!empty($db_trip[$i]['tDaddress'])) {
                                                            ?>
                                                            <td width="25%"><?= $db_trip[$i]['tSaddress'] . ' -> ' . $db_trip[$i]['tDaddress']; ?></td>
                                                        <?php } else { ?>
                                                            <td width="25%"><?= $db_trip[$i]['tSaddress']; ?></td>
                                                            <?php
                                                        }
                                                    }
                                                    ?> 
                                                    <td>
                                                        <?= $generalobj->clearName($db_trip[$i]['name']); ?>
                                                    </td>
                                                    <td>
                                                        <?= $generalobj->clearName($db_trip[$i]['vName'] . " " . $db_trip[$i]['vLastName']); ?>
                                                    </td>
                                                    <td align="center" style="text-align: right;">
                                                        <?php
                                                        //$total_main_price = ($db_trip[$i]['fTripGenerateFare'] + $db_trip[$i]['fTipPrice'] - $db_trip[$i]['fCommision'] - $db_trip[$i]['fTax2'] - $db_trip[$i]['fTax1'] - $db_trip[$i]['fOutStandingAmount'] - $db_trip[$i]['fHotelCommision']); //Comment By Hasmukh On 04-10-2018 As Per Discuss with Mrunal Sir
                                                        $total_main_price = $db_trip[$i]['fTripGenerateFare'] - $db_trip[$i]['fDiscount']; //Added By Hasmukh On 04-10-2018 As Per Discuss with Mrunal Sir ,Subtract Discount By HJ On 08-01-2019 As Per Bug - 6008
                                                        ?>
                                                        <?php
                                                        //$generalobj->trip_currency($total_main_price);
                                                        $fare = $generalobj->trip_currency_payment($total_main_price, $db_trip[$i]['fRatio_' . $orgCurName]);
                                                        $fare = round($total_main_price * $db_trip[$i]['fRatio_' . $orgCurName], 2);
                                                        echo $generalobj->formateNumAsPerCurrency($fare, $orgCurName);
                                                        $fareTotal += $fare;
                                                        ?>
                                                    </td>
                                                    <?php if ($db_trip[$i]['iActive'] == 'Canceled' && $db_trip[$i]['fTripGenerateFare'] <= 0) { ?>
                                                        <td class="center">
                                                            <img src="assets/img/<?php echo $canceled_icon; ?>" title="<?= $langage_lbl['LBL_MYTRIP_CANCELED_TXT']; ?>">
                                                        </td>
                                                    <?php } else if (($db_trip[$i]['iActive'] == 'Finished' && $db_trip[$i]['eCancelled'] == "Yes") || ($db_trip[$i]['iActive'] == 'Canceled' && $db_trip[$i]['fTripGenerateFare'] > 0)) { ?>
                                                        <td align="center" width="10%">
                                                            <a target = "_blank" href="<?= $link_page ?>?iTripId=<?= base64_encode(base64_encode($db_trip[$i]['iTripId'])) ?>">
                                                                <img alt="" src="assets/img/<?php echo $invoice_icon; ?>">
                                                            </a>
                                                            <div style="font-size: 12px;">Cancelled</div>
                                                        </td>
                                                    <? } else { ?>	
                                                        <td align="center" width="10%">
                                                            <a target = "_blank" href="<?= $link_page ?>?iTripId=<?= base64_encode(base64_encode($db_trip[$i]['iTripId'])) ?>">
                                                                <img alt="" src="assets/img/<?php echo $invoice_icon; ?>">
                                                            </a>
                                                        </td>
                                                    <?php } ?>
                                                </tr>
                                            <? } ?>		
                                        </tbody>
                                        <tfoot>
                                            <tr class="last_row_record">
                                                <td colspan="6" style="text-align: right;"><b>Total Fare Amount</b></td>
                                                <td style="width: 120px; text-align: right;" class="last_record_row text-right">
                                                    <?php
                                                    echo $generalobj->formateNumAsPerCurrency($fareTotal, $orgCurName);
                                                    ?>

                                                </td>
                                                <td></td>

                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>	
                            </div>
                        </div>
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
<?php if ($APP_TYPE == 'UberX' || $APP_TYPE == 'Delivery') { ?>
                                                $('#dataTables-example').DataTable({
                                                    "oLanguage": langData,
                                                    "order": [[3, "desc"]]
                                                });
<?php } else { ?>
                                                $('#dataTables-example').DataTable({
                                                    "oLanguage": langData,
                                                    "order": [[5, "desc"]]
                                                });
<?php } ?>
                                            //$('#dataTables-example').dataTable();
                                            // formInit();
                                        });
                                        function reset() {
                                            window.location.href = window.location.href;
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
                var table = $('#dataTables-example').DataTable({"oLanguage": langData});
                $("[name='dataTables-example_length']").each(function () {
                    $(this).wrap("<em class='select-wrapper'></em>");
                    $(this).after("<em class='holder'></em>");
                });
                $("[name='dataTables-example_length']").change(function () {
                    var selectedOption = $(this).find(":selected").text();
                    $(this).next(".holder").text(selectedOption);
                }).trigger('change');
                $('#dataTables-example tbody').on('click', 'td.details-control', function () {
                    var tr = $(this).closest('tr');
                    var row = table.row(tr);
                    if (row.child.isShown()) {
                        // This row is already open - close it
                        row.child.hide();
                        tr.removeClass('shown');
                    } else {
                        // Open this row
                        row.child(showRowDataTable(this)).show();
                        tr.addClass('shown');
                    }
                });
            })
            function showRowDataTable(elem) {
                var payStatus = $(elem).attr("data-paystatus");
                var payBY = $(elem).attr("data-payby");
                var bookDate = $(elem).attr("data-bookdate");
                var vehicleType = $(elem).attr("data-type");
                var tripType = $(elem).attr("data-tripType");
                var rowDataHtml = '<table><tr>';
                if (tripType != "") {
                    rowDataHtml += '<td><b>Trip / Job Type: </b>' + tripType + '</td>';
                }
                rowDataHtml += '<td><b>Booking Date: </b>' + bookDate + '</td>';
                rowDataHtml += '<td><b>Type: </b>' + vehicleType + '</td>';
                rowDataHtml += '<td><b>Payment Status: </b>' + payStatus + '</td>';
                rowDataHtml += '<td><b>Payment By: </b>' + payBY + '</td>';
                rowDataHtml += '</tr></table>';
                return rowDataHtml;
            }
        </script>
            <script type="text/javascript">
            $(document).ready(function () {

                $(".datetimerange").on('click', function () {
                    $(".datetimerange.active").removeClass("active");
                    // adding classname 'active' to current click li 
                    $(this).addClass("active");
                });

                $("#dp4").on('change', function () {
                    $(".datetimerange.active").removeClass("active");
                });

                  $("#dp5").on('change', function () {
                    $(".datetimerange.active").removeClass("active");
                });

            });
    </script>
        <!-- End: Footer Script -->
    </body>
</html>
