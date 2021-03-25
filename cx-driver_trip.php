<?
include_once('common.php');
include_once('generalFunctions.php');
include_once('include/config.php');
$tbl_name = 'register_driver';
$script = "Trips";
$generalobj->check_member_login();
$abc = 'driver';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$generalobj->setRole($abc, $url);
$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');


$ssql = '';
if ($action != '') {
    $startDate = $_REQUEST['startDate'];
    $endDate = $_REQUEST['endDate'];
    $dateRange = isset($_REQUEST['dateRange']) ? $_REQUEST['dateRange'] : '';

    if ($startDate != '') {
        $ssql .= " AND Date(t.tTripRequestDate) >='" . $startDate . "'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(t.tTripRequestDate) <='" . $endDate . "'";
    }
}
if ($_SESSION['sess_user'] == "driver") {
    $sql = "SELECT * FROM register_" . $_SESSION['sess_user'] . " WHERE iDriverId='" . $_SESSION['sess_iUserId'] . "'";
    $db_booking = $obj->MySQLSelect($sql);
    $sql = "SELECT fThresholdAmount, Ratio, vName, vSymbol FROM currency WHERE vName='" . $db_booking[0]['vCurrencyDriver'] . "'";
    $db_curr_ratio = $obj->MySQLSelect($sql);
} else {
    $sql = "SELECT * FROM register_" . $_SESSION['sess_user'] . " WHERE iUserId='" . $_SESSION['sess_iUserId'] . "'";
    $db_booking = $obj->MySQLSelect($sql);
    $sql = "SELECT fThresholdAmount, Ratio, vName, vSymbol FROM currency WHERE vName='" . $db_booking[0]['vCurrencyPassenger'] . "'";
    $db_curr_ratio = $obj->MySQLSelect($sql);
}
$tripcursymbol = $db_curr_ratio[0]['vSymbol'];
$tripcur = $db_curr_ratio[0]['Ratio'];
$tripcurname = $db_curr_ratio[0]['vName'];
$tripcurthholsamt = $db_curr_ratio[0]['fThresholdAmount'];
$deafultLang = $_SESSION['sess_lang'];

$ufxEnable = isUberXModuleAvailable() ? "Yes" : "No"; //add function to modules availibility
$rideEnable = isRideModuleAvailable() ? "Yes" : "No";
$deliveryEnable = isDeliveryModuleAvailable() ? "Yes" : "No";
$deliverallEnable = isDeliverAllModuleAvailable() ? "Yes" : "No";

if($ufxEnable != "Yes") {
	$ssql .= " AND t.eType != 'UberX'";
}
if(!checkFlyStationsModule()) {
    $ssql.= " AND t.iFromStationId = '0' AND t.iToStationId = '0'";
}
if($rideEnable != "Yes") {
    $ssql .= " AND t.eType != 'Ride'";
}
if($deliveryEnable != "Yes") {
    $ssql .= " AND t.eType != 'Deliver' AND t.eType != 'Multi-Delivery'";
}

$sql = "SELECT t.*, u.vName, u.vLastName,t.tEndDate, t.tTripRequestDate, t.iActive, d.vAvgRating, t.iFare, d.iDriverId,t.fRatioDriver,t.vCurrencyDriver,t.fTripGenerateFare,t.fHotelCommision,t.iRentalPackageId, t.vRideNo, t.tSaddress,t.eType, t.eHailTrip, d.vName AS name, d.vLastName AS lname,t.eCarType,t.iTripId,vt.vVehicleType_" . $deafultLang . " as vVehicleType,vt.vRentalAlias_" . $deafultLang . " as vRentalVehicleTypeName FROM register_driver d RIGHT JOIN trips t ON d.iDriverId = t.iDriverId LEFT JOIN vehicle_type vt ON vt.iVehicleTypeId = t.iVehicleTypeId LEFT JOIN  register_user u ON t.iUserId = u.iUserId WHERE d.iDriverId = '" . $_SESSION['sess_iUserId'] . "' AND eSystem = 'General' AND t.fTripGenerateFare > 0 " . $ssql . " ORDER BY t.iTripId DESC";
$db_dtrip = $obj->MySQLSelect($sql);
$sql = "SELECT vName FROM currency WHERE eDefault='Yes'";
$db_currency = $obj->MySQLSelect($sql);
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
if (file_exists($logogpath . "driver-view-icon.png")) {
    $invoice_icon = $logogpath . "driver-view-icon.png";
} else {
    $invoice_icon = "assets/img/driver-view-icon.png";
}
if (file_exists($logogpath . "canceled-invoice.png")) {
    $canceled_icon = $logogpath . "canceled-invoice.png";
} else {
    $canceled_icon = "assets/img/canceled-invoice.png";
}
$vehilceTypeArr = array();
$getVehicleTypes = $obj->MySQLSelect("SELECT iVehicleTypeId,vVehicleType_" . $deafultLang . " AS vehicleType FROM vehicle_type WHERE 1=1");
for ($r = 0; $r < count($getVehicleTypes); $r++) {
    $vehilceTypeArr[$getVehicleTypes[$r]['iVehicleTypeId']] = $getVehicleTypes[$r]['vehicleType'];
}
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_HEADER_TRIPS_TXT']; ?></title>
        <?php include_once("top/top_script.php"); ?>
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
                            <h1><?= $langage_lbl['LBL_HEADER_TRIPS_TXT']; ?></h1>
                        </div>

                        <form class="tabledata-filter-block filter-form" name="search"  method="post" onSubmit="return checkvalid()">
                            <input type="hidden" name="action" value="search" />
                            <div class="filters-column mobile-full">
                                <label><?= $langage_lbl['LBL_MYTRIP_SEARCH_RIDES_POSTED_BY_DATE']; ?></label>
                                <select id="timeSelect" name="dateRange">
                                    <option value=""><?= $langage_lbl['LBL_SELECT_TXT']; ?></option>
                                    <option value="today" <?php if ($dateRange == 'today') {
                echo 'selected';
            } ?> ><?= $langage_lbl['LBL_MYTRIP_Today']; ?></option>
                                    <option value="yesterday" <?php if ($dateRange == 'yesterday') {
                echo 'selected';
            } ?> ><?= $langage_lbl['LBL_MYTRIP_Yesterday']; ?></option>
                                    <option value="currentWeek" <?php if ($dateRange == 'currentWeek') {
                echo 'selected';
            } ?> ><?= $langage_lbl['LBL_MYTRIP_Current_Week']; ?></option>
                                    <option value="previousWeek" <?php if ($dateRange == 'previousWeek') {
                echo 'selected';
            } ?> ><?= $langage_lbl['LBL_MYTRIP_Previous_Week']; ?></option>
                                    <option value="currentMonth" <?php if ($dateRange == 'currentMonth') {
                echo 'selected';
            } ?> ><?= $langage_lbl['LBL_MYTRIP_Current_Month']; ?></option>
                                    <option value="previousMonth" <?php if ($dateRange == 'previousMonth') {
                echo 'selected';
            } ?> ><?= $langage_lbl['LBL_MYTRIP_Previous Month']; ?></option>
                                    <option value="currentYear" <?php if ($dateRange == 'currentYear') {
                echo 'selected';
            } ?> ><?= $langage_lbl['LBL_MYTRIP_Current_Year']; ?></option>
                                    <option value="previousYear" <?php if ($dateRange == 'previousYear') {
                echo 'selected';
            } ?> ><?= $langage_lbl['LBL_MYTRIP_Previous_Year']; ?></option>

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
                                <button class="driver-trip-btn"><?= $langage_lbl['LBL_Search']; ?></button>
                                <!-- <button onClick="reset();" class="driver-trip-btn"><?= $langage_lbl['LBL_MYTRIP_RESET']; ?></button> -->
                                <a href="provider-job" class="gen-btn"><?= $langage_lbl['LBL_MYTRIP_RESET']; ?></a>
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
                                <?php if ($APP_TYPE != 'UberX' && $APP_TYPE != 'Delivery') { ?> 
                                        <th width="17%"><?= $langage_lbl['LBL_TRIP_JOB_TYPE_FRONT']; ?></th>
                                <?php } ?>
                                    <th style="text-align: right;" width="17%"><?= $langage_lbl['LBL_MYTRIP_RIDE_NO_TXT']; ?></th>
                                    <th width="18%"><?= $langage_lbl['LBL_MYTRIP_TRIP_RIDER']; ?></th>
                                    <th width="15%"><?= $langage_lbl['LBL_MYTRIP_TRIPDATE']; ?></th>
                                    <th style="text-align: right;" width="15%"><?= $langage_lbl['LBL_DRIVER_TRIP_FARE_TXT']; ?></th>
                                    <th width="15%"><?= $langage_lbl['LBL_MYTRIP_CAR_TYPE']; ?></th>
                                    <th width="16%"><?= $langage_lbl['LBL_View_Invoice']; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?
                                for ($i = 0; $i < count($db_dtrip); $i++) {
                                    $poolTxt = $seriveJson = "";
                                    if ($db_dtrip[$i]['ePoolRide'] == "Yes") {
                                        $poolTxt = " (Pool)";
                                    }
                                    $pickup = $db_dtrip[$i]['tSaddress'];
                                    $iFare = $db_dtrip[$i]['fTripGenerateFare'];
                                    $fDiscount = $db_dtrip[$i]['fDiscount'];
                                    if (($iFare == "" || $iFare == 0) && $db_dtrip[$i]['fDiscount'] > 0) {
                                        $total_main_price = ($db_dtrip[$i]['fDiscount'] - $db_dtrip[$i]['fCommision'] - $db_dtrip[$i]['fTax1'] - $db_dtrip[$i]['fTax2'] - $db_dtrip[$i]['fOutStandingAmount'] - $db_dtrip[$i]['fAddedOutstandingamt']) + $db_dtrip[$i]['fTipPrice'] - $db_dtrip[$i]['fHotelCommision'];
                                    } else if ($iFare != "" && $iFare > 0) {
                                        $total_main_price = ($iFare - $db_dtrip[$i]['fCommision'] - $db_dtrip[$i]['fTax1'] - $db_dtrip[$i]['fTax2'] - $db_dtrip[$i]['fOutStandingAmount'] - $db_dtrip[$i]['fAddedOutstandingamt']) + $db_dtrip[$i]['fTipPrice'] - $db_dtrip[$i]['fHotelCommision'];
                                    }
                                    $fare = $generalobj->trip_currency_payment($total_main_price, $db_dtrip[$i]['fRatio_' . $tripcurname]);
                                    if ($db_dtrip[$i]['iRentalPackageId'] > 0 && $db_dtrip[$i]['vRentalVehicleTypeName'] != "") {
                                        $car = $db_dtrip[$i]['vRentalVehicleTypeName'];
                                    } else {
                                        $car = $db_dtrip[$i]['vVehicleType'];
                                    }
                                    //Added By HJ On 08-02-2019 For Get Main Category Name When Multiple Service Selected Start
                                    $viewService = 0;
                                    if (isset($db_dtrip[$i]['tVehicleTypeData']) && $db_dtrip[$i]['tVehicleTypeData'] != "" && $car == "") {
                                        $viewService = 1;
                                        $seriveJson = $db_dtrip[$i]['tVehicleTypeData'];
                                    }
                                    //Added By HJ On 08-02-2019 For Get Main Category Name When Multiple Service Selected End
                                    $name = $generalobj->clearName($db_dtrip[$i]['vName'] . ' ' . $db_dtrip[$i]['vLastName']);
                                    $eType = $db_dtrip[$i]['eType'];
                                    $link_page = "cx-invoice.php";
                                    if ($eType == 'Ride') {
                                        $trip_type = 'Ride';
                                    } else if ($eType == 'UberX') {
                                        $trip_type = 'Other Services';
                                    } else if ($eType == 'Multi-Delivery') {
                                        $trip_type = 'Multi-Delivery';
                                        $link_page = "cx-invoice_multi_delivery.php";
                                    } else {
                                        $trip_type = 'Delivery';
                                    }
                                    $trip_type .= $poolTxt;
                                    $systemTimeZone = date_default_timezone_get();
                                    if ($db_dtrip[$i]['tTripRequestDate'] != "" && $db_dtrip[$i]['vTimeZone'] != "") {
                                        $dBookingDate = converToTz($db_dtrip[$i]['tTripRequestDate'], $db_dtrip[$i]['vTimeZone'], $systemTimeZone);
                                    } else {
                                        $dBookingDate = $db_dtrip[$i]['tTripRequestDate'];
                                    }
                                    ?>
                                    <tr class="gradeA">
                                            <?php if ($APP_TYPE != 'UberX' && $APP_TYPE != 'Delivery') { ?> 
                                            <td><?
                                                if ($db_dtrip[$i]['eHailTrip'] == "Yes" && $db_dtrip[$i]['iRentalPackageId'] > 0) {
                                                    echo "Rental " . $trip_type . "<br/> ( Hail )";
                                                } else if ($db_dtrip[$i]['iRentalPackageId'] > 0) {
                                                    echo "Rental " . $trip_type;
                                                } else if ($db_dtrip[$i]['eHailTrip'] == "Yes") {
                                                    echo "Hail " . $trip_type;
                                                } else {
                                                    echo $trip_type;
                                                }
                                                ?></td>
                                            <?php } ?>
                                        <td style="text-align: right;" align="center"><?= $db_dtrip[$i]['vRideNo']; ?></td>
                                        <td ><?= $name; ?></td>
                                        <td align="center" data-order="<?= $db_dtrip[$i]['iTripId'] ?>"><?= $generalobj->DateTime1($dBookingDate, 'no'); ?></td>
                                        <td align="right" style="text-align: right;">
    <?= $tripcursymbol . ' ' . $fare; ?>
                                        </td>
                                        <td align="center" class="center">
    <?php if ($viewService == 1) { ?>
                                                <button class="btn btn-success gen-btn" data-trip="<?= $db_dtrip[$i]['vRideNo']; ?>" data-json='<?= $seriveJson; ?>' onclick="return showServiceModal(this);">
                                                    <i class="fa fa-certificate certi"><b> View Service</b></i>
                                                </button>
                                            <?php
                                        } else {
                                            echo $car;
                                        }
                                        ?>
                                        </td>
                                    <?php if ($db_dtrip[$i]['iActive'] == 'Canceled' && $db_dtrip[$i]['fTripGenerateFare'] <= 0) {
                                        ?>
                                            <td class="center">
                                                <img src="<?php echo $canceled_icon; ?>" title="<?= $langage_lbl['LBL_MYTRIP_CANCELED_TXT']; ?>">
                                            </td>
    <?php } else if (($db_dtrip[$i]['iActive'] == 'Canceled' && $db_dtrip[$i]['fTripGenerateFare'] > 0) || ($db_dtrip[$i]['iActive'] == 'Finished' && $db_dtrip[$i]['eCancelled'] == "Yes")) { ?>
                                            <td align="center" width="10%">
                                                <a  target = "_blank" href="<?= $link_page ?>?iTripId=<?= base64_encode(base64_encode($db_dtrip[$i]['iTripId'])) ?>"><strong><img src="<?php echo $invoice_icon; ?>"></strong></a>
                                                <div style="font-size: 12px;">Cancelled</div>
                                            </td>
    <? } else { ?>  
                                            <td class="center">
                                                <a  target = "_blank" href="<?= $link_page ?>?iTripId=<?= base64_encode(base64_encode($db_dtrip[$i]['iTripId'])) ?>"><strong><img src="<?php echo $invoice_icon; ?>"></strong></a>
                                            </td>
    <?php } ?>
                                    </tr>
<? } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>


            <div  class="custom-modal-main" id="service_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
                <div class="custom-modal">
                    <div class="modal-dialog" >
                        <div class="modal-content">
                            <div class="model-header">
                                <h4 id="servicetitle">
                                    <i style="margin:2px 5px 0 2px;"><img src="images/icon/driver-icon.png" alt=""></i>
                                    Service Details
                                    <!-- <button type="button" class="close" data-dismiss="modal">x</button> -->
                                </h4>
                                <i class="icon-close" data-dismiss="modal"></i>
                            </div>
                            <div class="model-body" style="max-height: 450px;overflow: auto;">
                                <div id="service_detail"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- add money-->

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
       /*  if ($('#my-trips-data').length > 0) {
            $('#my-trips-data').dataTable({
                "order": [[0, "desc"]],
                "aoColumns": [
                    null,
                    null,
                    null,
                    null,
                    null,
                    {"bSortable": false}
                ]
            });
        } */

           if ($('#my-trips-data').length > 0) {
               $('#my-trips-data').dataTable({"oLanguage": langData});
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
            var typeArr = '<?= json_encode($vehilceTypeArr); ?>';
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
                    $('#dataTables-example').dataTable({
                        "oLanguage": langData,
                        "order": [[2, "desc"]],
                        "aoColumns": [
                            null,
                            null,
                            null,
                            null,
                            null,
                            {"bSortable": false}
                        ]
                    });
<?php } else { ?>
                    $('#dataTables-example').dataTable({
                        "oLanguage": langData,
                        "order": [[3, "desc"]],
                        "aoColumns": [
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            {"bSortable": false}
                        ]
                    });
<?php } ?>
                // formInit();
            });
            function todayDate()
            {
                $("#dp4").val('<?= $Today; ?>');
                $("#dp5").val('<?= $Today; ?>');
            }
            function reset() {
                location.reload();
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
            function showServiceModal(elem) {
                var tripJson = JSON.parse($(elem).attr("data-json"));
                var rideNo = $(elem).attr("data-trip");
                var typeNameArr = JSON.parse(typeArr)
                var serviceHtml = "";
                var srno = 1;
                // added by sunita
                
                for (var g = 0; g < tripJson.length; g++) {
                    serviceHtml += "<p>" + srno + ") " + typeNameArr[tripJson[g]['iVehicleTypeId']] + "&nbsp;&nbsp;&nbsp;&nbsp;  <?=$langage_lbl_admin['LBL_QTY_TXT']?>: <b>"+ [tripJson[g]['fVehicleTypeQty']] + "</b></p>";
                    srno++;
                }
                $("#service_detail").html(serviceHtml);
                $("#servicetitle").text("Service Details : " + rideNo);
                //$("#service_modal").modal('show');
                $("#service_modal").addClass('active');
                return false;
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
            });
        </script>
        <!-- End: Footer Script -->
    </body>
</html>
