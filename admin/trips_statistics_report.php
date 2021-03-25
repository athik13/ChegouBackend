<?php
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();
/*
if (!$userObj->hasPermission('trips-statistics-report')) {
    $userObj->redirect();
}
ini_set("display_errors", 1);
error_reporting(E_ALL);

function dateFR($datetime) {
    $weekDay = array("Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche");
    $dayNumber = date("N", strtotime($datetime));
    $dayName = "";
    for ($y = 0; $y < count($weekDay); $y++) {
        $n = $y + 1;
        if ($dayNumber == $n) {
            $dayName = $weekDay[$y];
        }
    }
    
    setlocale(LC_ALL, 'fr_FR');
    return strftime($dayName.' %d/%m/%Y &agrave; %H:%M', strtotime($datetime));
}

$datetime = date('Y-m-d H:i');
$sdfsd = dateFR($datetime);
print_r($sdfsd);
die;
*/
$script = "Trip Statistics Report";
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY rs.iDriverRequestId DESC';
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
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : date('Y-m-d', strtotime("-30 days"));
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : date('Y-m-d');
$date1 = $startDate . ' ' . "00:00:00";
$date2 = $endDate . ' ' . "23:59:59";
if ($startDate != '' && $endDate != '') {
    $ssql .= "TR.tTripRequestDate between '$date1' and '$date2'";
}
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$totalData = $obj->MySQLSelect("SELECT iActive,DATE_FORMAT(TR.tTripRequestDate, '%Y-%m-%d') AS REQUEST_DATE FROM trips TR WHERE $ssql ORDER BY tTripRequestDate DESC");
//echo "<pre>";
//print_r($totalData);die;
$finalTripArr = array();
for ($r = 0; $r < count($totalData); $r++) {
    $date = $totalData[$r]['REQUEST_DATE'];
    $tripStatus = $totalData[$r]['iActive'];
    $finalTripArr[$date]['date'] = $date;
    if (isset($finalTripArr[$date]['total'])) {
        $finalTripArr[$date]['total'] += 1;
    } else {
        $finalTripArr[$date]['total'] = 1;
    }
    if (isset($finalTripArr[$date][$tripStatus])) {
        $finalTripArr[$date][$tripStatus] += 1;
    } else {
        $finalTripArr[$date][$tripStatus] = 1;
    }
}
$total_results = $endRecord = count($finalTripArr);
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
$chk_str_date = @date('Y-m-d H:i:s', strtotime('-' . $RIDER_REQUEST_ACCEPT_TIME . ' second'));
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page')
        $var_filter .= "&$key=" . stripslashes($val);
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
//echo "<pre>"; print_r($db_log_report); exit;
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
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | <?php echo $langage_lbl_admin['LBL_HEADER_TRIPS_TXT']; ?> Statistics Report</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
<? include_once('global_files.php'); ?>         
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >
        <!-- MAIN WRAPPER -->
        <div id="wrap">
<? include_once('header.php'); ?>
<? include_once('left_menu.php'); ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2><?php echo $langage_lbl_admin['LBL_HEADER_TRIPS_TXT']; ?> Statistics report</h2>
                        </div>
                    </div>
                    <hr />
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                                    <div class="Posted-date mytrip-page mytrip-page-select payment-report">
                                        <input type="hidden" name="action" value="search" />
                                        <h3>Search by Date...</h3>
                                        <span>
                                            <a onClick="return todayDate('dp4', 'dp5');"><?= $langage_lbl_admin['LBL_MYTRIP_Today']; ?></a>
                                            <a onClick="return yesterdayDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Yesterday']; ?></a>
                                            <a onClick="return currentweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Week']; ?></a>
                                            <a onClick="return previousweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Week']; ?></a>
                                            <a onClick="return currentmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Month']; ?></a>
                                            <a onClick="return previousmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous Month']; ?></a>
                                            <a onClick="return currentyearDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Year']; ?></a>
                                            <a onClick="return previousyearDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Year']; ?></a>
                                        </span> 
                                        <span>
                                            <input type="text" id="dp4" name="startDate" placeholder="From Date" class="form-control" value=""/>
                                            <input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control" value=""/>
                                        </span>
                                    </div>
                                    <div class="tripBtns001"><b>
                                            <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                            <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'trips_statistics_report.php'"/>
                                            <button type="button" onClick="reportExportTypes('trips_statistics_report')" class="export-btn001" >Export</button>
                                        </b>
                                    </div>
                                </form>
                                <div class="table-responsive">
                                    <form name="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                        <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                            <thead>
                                                <tr>
                                                    <th>Trip Date</th>
                                                    <th style="text-align: right;">Total Trips</th>     
                                                    <th style="text-align: right;">Active Trips</th>
                                                    <th style="text-align: right;">Ongoing Trips</th>
                                                    <th style="text-align: right;">Completed Trips</th>
                                                    <th style="text-align: right;">Cancelled Trips</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $totTrips = $totCompleted = $totCancelled = $totOngoing = $totActive = 0;
                                                foreach ($finalTripArr as $key => $val) {
                                                    $totalTrips = $cancelledTrips = $completedTrips = $ongoingTrips = $activeTrips = 0;
                                                    $tripDate = $generalobjAdmin->DateTime($val['date']);
                                                    if (isset($val['total']) && $val['total'] > 0) {
                                                        $totalTrips = $val['total'];
                                                    }
                                                    $totTrips += $totalTrips;
                                                    if (isset($val['Active']) && $val['Active'] > 0) {
                                                        $activeTrips = $val['Active'];
                                                    }
                                                    $totActive += $activeTrips;
                                                    if (isset($val['Finished']) && $val['Finished'] > 0) {
                                                        $completedTrips = $val['Finished'];
                                                    }
                                                    $totCompleted += $completedTrips;
                                                    if (isset($val['Canceled']) && $val['Canceled'] > 0) {
                                                        $cancelledTrips = $val['Canceled'];
                                                    }
                                                    $totCancelled += $cancelledTrips;
                                                    if (isset($val['On Going Trip']) && $val['On Going Trip'] > 0) {
                                                        $ongoingTrips = $val['On Going Trip'];
                                                    }
                                                    $totOngoing += $ongoingTrips;
                                                    ?>
                                                    <tr class="gradeA">
                                                        <td><?= $tripDate; ?></td>
                                                        <td style="text-align: right;"><?= $totalTrips; ?></td>
                                                        <td style="text-align: right;"><?= $activeTrips; ?></td>
                                                        <td style="text-align: right;"><?= $ongoingTrips; ?></td>
                                                        <td style="text-align: right;"><?= $completedTrips; ?></td>
                                                        <td style="text-align: right;"><?= $cancelledTrips; ?></td>     
                                                    </tr>
    <?
}
?>                                                              
                                            </tbody>
                                            <tr class="gradeA">
                                                <td><b>TOTAL</b></td>
                                                <td style="text-align: right;"><?= $totTrips; ?></td>
                                                <td style="text-align: right;"><?= $totActive; ?></td>
                                                <td style="text-align: right;"><?= $totOngoing; ?></td>
                                                <td style="text-align: right;"><?= $totCompleted; ?></td>
                                                <td style="text-align: right;"><?= $totCancelled; ?></td>
                                            </tr>
                                        </table>
                                    </form>
<?php include('pagination_n.php'); ?>
                                </div>
                            </div> <!--TABLE-END-->
                        </div>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
        <form name="pageForm" id="pageForm" action="" method="post" >
            <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
            <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
            <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
            <input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
            <input type="hidden" name="action" value="<?php echo $action; ?>" >
            <input type="hidden" name="startDate" value="<?php echo $startDate; ?>" >
            <input type="hidden" name="endDate" value="<?php echo $endDate; ?>" >
            <input type="hidden" name="method" id="method" value="" >
        </form>
<? include_once('footer.php'); ?>
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
                                                                var startDate = new Date(ev.date);
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
                                                                var endDate = new Date(ev.date);
                                                                $('#endDate').text($('#dp5').data('date'));
                                                            }
                                                            $('#dp5').datepicker('hide');
                                                        });
                                                $(document).ready(function () {
                                                    $("#dp5").click(function () {
                                                        $('#dp5').datepicker('show');
                                                        $('#dp4').datepicker('hide');
                                                    });

                                                    $("#dp4").click(function () {
                                                        $('#dp4').datepicker('show');
                                                        $('#dp5').datepicker('hide');
                                                    });
                                                    if ('<?= $startDate ?>' != '') {
                                                        $("#dp4").val('<?= $startDate ?>');
                                                        $("#dp4").datepicker('update', '<?= $startDate ?>');
                                                    }
                                                    if ('<?= $endDate ?>' != '') {
                                                        $("#dp5").datepicker('update', '<?= $endDate; ?>');
                                                        $("#dp5").val('<?= $endDate; ?>');
                                                    }

                                                    $("select.filter-by-text").each(function () {
                                                        $(this).select2({
                                                            placeholder: $(this).attr('data-text'),
                                                            allowClear: true
                                                        }); //theme: 'classic'
                                                    });
                                                });
                                                function todayDate()
                                                {
                                                    //alert('sa');
                                                    $("#dp4").val('<?= $Today; ?>');
                                                    $("#dp5").val('<?= $Today; ?>');
                                                }
                                                function resetform()
                                                {
                                                    //location.reload();
                                                    document.search.reset();
                                                    document.getElementById("iDriverId").value = " ";
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
                                                function checkvalid() {
                                                    if ($("#dp5").val() < $("#dp4").val()) {
                                                        alert("From date should be lesser than To date.")
                                                        return false;
                                                    }
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
        </script>
    </body>
</html>