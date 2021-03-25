<?php
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}

$generalobjAdmin->check_member_login();
ini_set("display_errors", 1);
error_reporting(E_ALL);
$script = 'Driver_Movement_Arrivinig_Report';

function cleanNumber($num) {
    return str_replace(',', '', $num);
}

$sql = "select iDriverId,CONCAT(vName,' ',vLastName) AS driverName from register_driver WHERE eStatus != 'Deleted'";
$db_drivers = $obj->MySQLSelect($sql);
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY iTripId DESC, iTripLocationId ';
if ($sortby == 1) {
    if ($order == 0)
        $ord = " ORDER BY rd.vName ASC";
    else
        $ord = " ORDER BY rd.vName DESC";
}
//End Sorting
// Start Search Parameters
$ssql = '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : '';
$serachTripNo = isset($_REQUEST['serachTripNo']) ? $_REQUEST['serachTripNo'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
if ($action == 'search') {
    if ($startDate != '') {
        $ssql .= " AND Date(tDate) >='" . $startDate . " 00:00:00'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(tDate) <='" . $endDate . " 23:59:59'";
    }
    if ($searchDriver != '') {
        $ssql .= " AND t.iDriverId ='" . $searchDriver . "'";
    }
}
$trp_ssql = "";
if (SITE_TYPE == 'Demo') {
    $trp_ssql = " And tr.tTripRequestDate > '" . WEEK_DATE . "'";
}
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT * FROM trips_locations tl, register_driver as rd, trips as t WHERE  t.iDriverId = rd.iDriverId AND tl.iTripId = t.iTripId  AND t.iActive = 'Arrived'" . $ssql . $trp_ssql;
$totalData = $obj->MySQLSelect($sql);
//echo $sql;die;
$total_results = 0;
if (isset($totalData[0]['Total'])) {
    $total_results = $totalData[0]['Total'];
}
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;
//-------------if page is setcheck------------------//
$start = 0;
$end = $per_page;
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    }
}
// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages = $total_pages;
if ($page <= 0)
    $page = 1;
//Pagination End
$sql = "SELECT tl.*,rd.vName, rd.vLastName, t.vRideNo,t.iDriverId,t.fDistance,t.tStartDate AS dStartTime,t.tEndDate AS dEndTime FROM trips_locations tl, register_driver as rd, trips as t WHERE  t.iDriverId = rd.iDriverId AND tl.iTripId = t.iTripId AND t.iActive= 'Arrived'" . $ssql . $trp_ssql . " " . $ord . " LIMIT " . $start . " , " . $per_page;
$db_trip = $obj->MySQLSelect($sql);
//echo "<pre>";
//print_r($db_trip);die;
$endRecord = count($db_trip);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page')
        $var_filter .= "&$key=" . stripslashes($val);
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
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
?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | Movement Report For Period 2</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php include_once('global_files.php'); ?>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >
        <!-- Main LOading -->
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?php include_once('header.php'); ?>
            <?php include_once('left_menu.php'); ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div id="add-hide-show-div">
                        <div class="row">
                            <div class="col-lg-12">
                                <h2>Movement Report For Period 2</h2>
                                <!--<input type="button" id="" value="ADD A DRIVER" class="add-btn">-->
                            </div>
                        </div>
                        <hr />
                    </div>
                    <?php include('valid_msg.php'); ?>
                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post" >
                        <div class="Posted-date mytrip-page payment-report">
                            <input type="hidden" name="action" value="search" />
                            <h3>Search </h3>
                            <span>
                                <a style="cursor:pointer" onClick="return todayDate('dp4', 'dp5');"><?= $langage_lbl['LBL_MYTRIP_Today']; ?></a>
                                <a style="cursor:pointer" onClick="return yesterdayDate('dFDate', 'dTDate');"><?= $langage_lbl['LBL_MYTRIP_Yesterday']; ?></a>
                                <a style="cursor:pointer" onClick="return currentweekDate('dFDate', 'dTDate');"><?= $langage_lbl['LBL_MYTRIP_Current_Week']; ?></a>
                                <a style="cursor:pointer" onClick="return previousweekDate('dFDate', 'dTDate');"><?= $langage_lbl['LBL_MYTRIP_Previous_Week']; ?></a>
                                <a style="cursor:pointer" onClick="return currentmonthDate('dFDate', 'dTDate');"><?= $langage_lbl['LBL_MYTRIP_Current_Month']; ?></a>
                                <a style="cursor:pointer" onClick="return previousmonthDate('dFDate', 'dTDate');"><?= $langage_lbl['LBL_MYTRIP_Previous Month']; ?></a>
                                <a style="cursor:pointer" onClick="return currentyearDate('dFDate', 'dTDate');"><?= $langage_lbl['LBL_MYTRIP_Current_Year']; ?></a>
                                <a style="cursor:pointer" onClick="return previousyearDate('dFDate', 'dTDate');"><?= $langage_lbl['LBL_MYTRIP_Previous_Year']; ?></a>
                            </span> 
                            <span>
                                <input type="text" id="dp4" name="startDate" placeholder="From Date" class="form-control" value="" readonly=""style="cursor:default; background-color: #fff" />
                                <input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control" value="" readonly="" style="cursor:default; background-color: #fff"/>
                                <div class="col-lg-3 select001">
                                    <select class="form-control filter-by-text" name = 'searchDriver' data-text="Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>">
                                        <option value="">Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></option>
                                        <?php foreach ($db_drivers as $dbd) { ?>
                                            <option value="<?php echo $dbd['iDriverId']; ?>" <?php
                                            if ($searchDriver == $dbd['iDriverId']) {
                                                echo "selected";
                                            }
                                            ?>><?php echo $generalobjAdmin->clearName($dbd['driverName']); ?></option>
                                                <?php } ?>
                                    </select>
                                </div>
                            </span>
                        </div>
                        <div class="row payment-report payment-report1 payment-report2">
                        </div>
                        <div class="tripBtns001"><b>
                                <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'driver_movement_arriving_report.php'"/>
                            </b>
                        </div>
                    </form>
                    <div class="admin-nir-export">
                        <div class="panel-heading">
                            <form name="movement_export_form" id="movement_export_form" method="post" >
                                <input type="hidden" name="exportType" value="XLS">
                                <button type="button" onClick="showExportTypes_movement('movement_report_arriving')" >Export</button>
                            </form>
                        </div>
                    </div>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="table-responsive">
                                    <form name="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                        <input type="hidden" id="actionpayment" name="actionpayment" value="pay_driver">
                                        <input type="hidden"  name="iTripId" id="iTripId" value="">
                                        <input type="hidden"  name="ePayDriver" id="ePayDriver" value="">
                                        <table class="table table-striped table-bordered table-hover" id="dataTables-example123" >
                                            <thead>
                                                <tr>
                                                    <th><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></th>
                                                    <th>Trip No.</th>
                                                    <th>Distance (Mile)</th>
                                                    <th>Date</th>
                                                    <th>Total Time</th>
                                                    <th>Location</th>
                                                    <th>View Map</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?
                                                if (count($db_trip) > 0) {
                                                    for ($i = 0; $i < count($db_trip); $i++) {
                                                        ?>
                                                        <tr class="gradeA">
                                                            <td><a target="blank" href="driver_action.php?id=<?= $db_trip[$i]['iDriverId'] ?>"><?= $db_trip[$i]['vName'] . " " . $db_trip[$i]['vLastName']; ?></a></td>
                                                            <td><?= $db_trip[$i]['vRideNo']; ?></td>
                                                            <?php
                                                            if ($db_trip[$i]['fDistance'] > 0.1) {
                                                                $fDistance = $db_trip[$i]['fDistance'];
                                                            } else {
                                                                $fDistance = round($db_trip[$i]['fDistance']);
                                                            }
                                                            $fDistance = $generalobjAdmin->getUnitToMiles($db_trip[$i]['fDistance'], 'Miles');
                                                            ?>
                                                            <td><?= $fDistance; ?></td>
                                                            <td><?= $generalobjAdmin->DateTime($db_trip[$i]['tDate']); ?></td>
                                                            <td>
                                                                <?
                                                                echo $generalobjAdmin->TimeDifference($db_trip[$i]['dStartTime'], $db_trip[$i]['dEndTime']);
                                                                ?></td>
                                                            <?php
                                                            $tPlatitudes = explode(",", $db_trip[$i]['tPlatitudes']);
                                                            $tPlongitudes = explode(",", $db_trip[$i]['tPlongitudes']);
                                                            $lat = $tPlatitudes[0];
                                                            $lng = $tPlongitudes[0];
                                                            //$address = $generalobjAdmin->getaddress($lat, $lng);
                                                            $address = "---";
                                                            if ($address) {
                                                                ?>
                                                                <td> <?php echo $address; ?> </td>
                                                                <?php
                                                            } else {
                                                                ?>
                                                                <td> <?php echo "--"; ?></td>
                                                                <?php
                                                            }

                                                            if ($address) {
                                                                ?>
                                                                <td><button type="button" class="btn btn-primary btn-new" onclick='javascript:window.open("viewmap.php?iTripLocationId=<?= $db_trip[$i]['iTripLocationId'] ?>", "_blank")';""><b>View Location</b></button></td>
                                                            <?php } else { ?>
                                                                <td> <?php echo "--"; ?></td>
                                                            <?php } ?>
                                                        </tr>
                                                    <? } ?>
                                                <?php } else { ?>
                                                    <tr><td colspan="7" style="text-align: center;">Trip Data not found</td></tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </form>
                                    <?php include('pagination_n.php'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
        <?php include_once('footer.php'); ?>
        <link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css" />
        <link rel="stylesheet" href="css/select2/select2.min.css" />
        <script src="js/plugins/select2.min.js"></script>
        <script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
        <script>
                                                        $('#dp4').datepicker()
                                                                .on('changeDate', function (ev) {
                                                                    var endDate = $('#dp5').val();
                                                                    if (ev.date.valueOf() < endDate.valueOf()) {
                                                                        $('#alert').show().find('strong').text('The start date can not be greater then the end date');
                                                                    } else {
                                                                        $('#alert').hide();
                                                                        startDate = new Date(ev.date);
                                                                        $('#startDate').text($('#dp4').data('date'));
                                                                    }
                                                                    $('#dp4').datepicker('hide');
                                                                });
                                                        $('#dp5').datepicker()
                                                                .on('changeDate', function (ev) {
                                                                    var startDate = $('#dp4').val();
                                                                    if (ev.date.valueOf() < startDate.valueOf()) {
                                                                        $('#alert').show().find('strong').text('The end date can not be less then the start date');
                                                                    } else {
                                                                        $('#alert').hide();
                                                                        endDate = new Date(ev.date);
                                                                        $('#endDate').text($('#dp5').data('date'));
                                                                    }
                                                                    $('#dp5').datepicker('hide');
                                                                });
                                                        function showExportTypes_movement(section) {
                                                            var action = "main_export.php";
                                                            var formValus = $("#movement_export_form").serialize();
                                                            var formValus1 = $("#frmsearch").serialize();
                                                            window.location.href = action + '?section=' + section + '&' + formValus + '&' + formValus1;
                                                            return false;
                                                        }
                                                        $(document).ready(function () {
                                                            if ('<?= $startDate ?>' != '') {
                                                                $("#dp4").val('<?= $startDate ?>');
                                                                $("#dp4").datepicker('update', '<?= $startDate ?>');
                                                            }
                                                            if ('<?= $endDate ?>' != '') {
                                                                $("#dp5").datepicker('update', '<?= $endDate; ?>');
                                                                $("#dp5").val('<?= $endDate; ?>');
                                                            }
                                                        });
                                                        function todayDate() {
                                                            $("#dp4").val('<?= $Today; ?>');
                                                            $("#dp5").val('<?= $Today; ?>');
                                                        }
                                                        function reset() {
                                                            location.reload();
                                                        }
                                                        function yesterdayDate()
                                                        {
                                                            $("#dp4").val('<?= $Yesterday; ?>');
                                                            $("#dp4").datepicker('update', '<?= $Yesterday; ?>');
                                                            $("#dp5").datepicker('update', '<?= $Yesterday; ?>');
                                                            $("#dp4").change();
                                                            $("#dp5").change();
                                                            $("#dp5").val('<?= $Yesterday; ?>');
                                                        }
                                                        function currentweekDate(dt, df)
                                                        {
                                                            $("#dp4").val('<?= $monday; ?>');
                                                            $("#dp4").datepicker('update', '<?= $monday; ?>');
                                                            $("#dp5").datepicker('update', '<?= $sunday; ?>');
                                                            $("#dp5").val('<?= $sunday; ?>');
                                                        }
                                                        function previousweekDate(dt, df)
                                                        {
                                                            $("#dp4").val('<?= $Pmonday; ?>');
                                                            $("#dp4").datepicker('update', '<?= $Pmonday; ?>');
                                                            $("#dp5").datepicker('update', '<?= $Psunday; ?>');
                                                            $("#dp5").val('<?= $Psunday; ?>');
                                                        }
                                                        function currentmonthDate(dt, df)
                                                        {
                                                            $("#dp4").val('<?= $currmonthFDate; ?>');
                                                            $("#dp4").datepicker('update', '<?= $currmonthFDate; ?>');
                                                            $("#dp5").datepicker('update', '<?= $currmonthTDate; ?>');
                                                            $("#dp5").val('<?= $currmonthTDate; ?>');
                                                        }
                                                        function previousmonthDate(dt, df)
                                                        {
                                                            $("#dp4").val('<?= $prevmonthFDate; ?>');
                                                            $("#dp4").datepicker('update', '<?= $prevmonthFDate; ?>');
                                                            $("#dp5").datepicker('update', '<?= $prevmonthTDate; ?>');
                                                            $("#dp5").val('<?= $prevmonthTDate; ?>');
                                                        }
                                                        function currentyearDate(dt, df)
                                                        {
                                                            $("#dp4").val('<?= $curryearFDate; ?>');
                                                            $("#dp4").datepicker('update', '<?= $curryearFDate; ?>');
                                                            $("#dp5").datepicker('update', '<?= $curryearTDate; ?>');
                                                            $("#dp5").val('<?= $curryearTDate; ?>');
                                                        }
                                                        function previousyearDate(dt, df)
                                                        {
                                                            $("#dp4").val('<?= $prevyearFDate; ?>');
                                                            $("#dp4").datepicker('update', '<?= $prevyearFDate; ?>');
                                                            $("#dp5").datepicker('update', '<?= $prevyearTDate; ?>');
                                                            $("#dp5").val('<?= $prevyearTDate; ?>');
                                                        }
                                                        $("#Search").on('click', function () {
                                                            if ($("#dp5").val() < $("#dp4").val()) {
                                                                alert("From date should be lesser than To date.")
                                                                return false;
                                                            } else {
                                                                var action = $("#_list_form").attr('action');
                                                                var formValus = $("#frmsearch").serialize();
                                                                window.location.href = action + "?" + formValus;
                                                            }
                                                        });
                                                        $(function () {
                                                            $("select.filter-by-text").each(function () {
                                                                $(this).select2({
                                                                    placeholder: $(this).attr('data-text'),
                                                                    allowClear: true
                                                                });
                                                            });
                                                        });
        </script>
    </body>
    <!-- END BODY-->
</html>