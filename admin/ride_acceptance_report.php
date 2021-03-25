<?php
include_once('../common.php');
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
if (!$userObj->hasPermission('manage-trip-job-request-acceptance-report')) {
    $userObj->redirect();
}
$driverDataArr = array();
$script = "Driver Accept Report";
$sql = "select iDriverId, CONCAT(vName,' ',vLastName) AS driverName,vEmail from register_driver WHERE eStatus != 'Deleted'  order by vName";
$db_drivers = $obj->MySQLSelect($sql);

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
$iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$date1 = $startDate . ' ' . "00:00:00";
$date2 = $endDate . ' ' . "23:59:59";

if ($startDate != '' && $endDate != '') {
    $ssql .= " AND rs.tDate between '$date1' and '$date2'";
    $ssql_trip .= " AND t.tTripRequestDate between '$date1' and '$date2'";
}
if ($iDriverId != '') {
    $ssql .= " AND rs.iDriverId = '" . $iDriverId . "'";
}

//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
//Added By HJ On 19-06-2020 For Optimized Query Start
$reqTotalData = $obj->MySQLSelect("SELECT COUNT( DISTINCT rs.iDriverId ) AS Total,rs.iDriverId FROM driver_request rs WHERE 1=1 $ssql GROUP by rs.iDriverId");
$requestArr = $totalData = array();
for ($r = 0; $r < count($reqTotalData); $r++) {
    $requestArr[$reqTotalData[$r]['iDriverId']] = $reqTotalData[$r]['Total'];
}
$DriverData = $obj->MySQLSelect("SELECT iDriverId FROM register_driver");
for ($h = 0; $h < count($DriverData); $h++) {
    $countRequest = 0;
    if (isset($requestArr[$DriverData[$h]['iDriverId']]) && $requestArr[$DriverData[$h]['iDriverId']] > 0) {
        $countRequest = $requestArr[$DriverData[$h]['iDriverId']];
        $tmpArr = array();
        $tmpArr['Total'] = $countRequest;
        $tmpArr['iDriverId'] = $DriverData[$h]['iDriverId'];
        $totalData[] = $tmpArr;
    }
}
//Added By HJ On 19-06-2020 For Optimized Query End
//echo "<pre>";print_r($totalData);die;
//Commented By HJ On 19-06-2020 For Optimized Query As Per Above Start
/* $sql = "SELECT COUNT( DISTINCT rs.iDriverId ) AS Total,rs.iDriverId FROM register_driver rd 
  left join driver_request rs on rd.iDriverId=rs.iDriverId
  WHERE rs.iDriverId > 0 $ssql GROUP by rs.iDriverId";
  $totalData = $obj->MySQLSelect($sql); */
//Commented By HJ On 19-06-2020 For Optimized Query As Per Above End
$total_results = count($totalData);
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;

//-------------if page is setcheck------------------//+
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

//$sql = "SELECT rd.iDriverId , rd.vLastName ,rd.vName ,COUNT(case when rs.eStatus = 'Accept' then 1 else NULL end) `Accept` ,COUNT(case when rs.eStatus != '' then 1 else NULL  end) `Total Request` ,COUNT(case when (rs.eStatus  = 'Decline' AND rs.eAcceptAttempted  = 'No') then 1 else NULL end) `Decline` ,COUNT(case when rs.eAcceptAttempted  = 'Yes' then 1 else NULL end) `Missed` ,COUNT(case when ((rs.eStatus  = 'Timeout' OR rs.eStatus  = 'Received') AND rs.eAcceptAttempted  = 'No' AND  rs.dAddedDate < '" . $chk_str_date . "')  then 1 else NULL end) `Timeout`,COUNT(case when ((rs.eStatus  = 'Timeout' OR rs.eStatus  = 'Received') AND rs.eAcceptAttempted  = 'No' AND rs.dAddedDate > '" . $chk_str_date . "' ) then 1 else NULL end) `inprocess`FROM register_driver rd left join driver_request rs on rd.iDriverId=rs.iDriverId WHERE 1=1 $ssql GROUP by rs.iDriverId $ord LIMIT $start, $per_page"; // Commented By HJ On 12-06-2019 For Optimized Query This Query Time 167 seconds

$sql = "SELECT rs.iDriverId,COUNT(case when rs.eStatus = 'Accept' then 1 else NULL end) `Accept` ,COUNT(case when rs.eStatus != '' then 1 else NULL  end) `Total Request` ,COUNT(case when (rs.eStatus  = 'Decline' AND rs.eAcceptAttempted  = 'No') then 1 else NULL end) `Decline` ,COUNT(case when rs.eAcceptAttempted  = 'Yes' then 1 else NULL end) `Missed` ,COUNT(case when ((rs.eStatus  = 'Timeout' OR rs.eStatus  = 'Received') AND rs.eAcceptAttempted  = 'No' AND  rs.dAddedDate < '" . $chk_str_date . "')  then 1 else NULL end) `Timeout`,COUNT(case when ((rs.eStatus  = 'Timeout' OR rs.eStatus  = 'Received') AND rs.eAcceptAttempted  = 'No' AND rs.dAddedDate > '" . $chk_str_date . "' ) then 1 else NULL end) `inprocess`FROM driver_request rs  WHERE 1=1 $ssql GROUP by rs.iDriverId $ord LIMIT $start, $per_page"; // Added By HJ On 12-06-2019 For Optimized Query This Query Time 3.9265 seconds
$db_res = $obj->MySQLSelect($sql);

$endRecord = count($db_res);

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
        <title><?= $SITE_NAME ?> | Ride Acceptance Report</title>
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
                            <h2><?php echo $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> Request Acceptance Report</h2>

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
                                            <!-- changed by me --> 
                                            <input type="text" id="dp4" name="startDate" placeholder="From Date" class="form-control" value=""  readonly="" style="cursor:default; background-color: #fff" />
                                            <input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control" value=""  readonly="" style="cursor:default; background-color: #fff" />
                                            <div class="col-lg-3 select001">
                                                <select class="form-control filter-by-text" name = 'iDriverId' data-text="Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>">
                                                    <option value="">Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></option>
                                                    <?php foreach ($db_drivers as $dbd) { ?>
                                                        <option value="<?php echo $dbd['iDriverId']; ?>" <?php
                                                    if ($iDriverId == $dbd['iDriverId']) {
                                                        echo "selected";
                                                    }
                                                    $driverDataArr[$dbd['iDriverId']] = $dbd['driverName'];
                                                        ?>><?php echo $generalobjAdmin->clearName($dbd['driverName']); ?> - ( <?php echo $generalobjAdmin->clearEmail($dbd['vEmail']); ?> )</option>
                                                            <?php } ?>
                                                </select>
                                            </div>
                                        </span>
                                    </div>
                                    <div class="tripBtns001"><b>
                                            <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                            <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'ride_acceptance_report.php'"/>
                                            <button type="button" onClick="reportExportTypes('ride_acceptance_report')" class="export-btn001" >Export</button>
                                        </b>
                                    </div>
                                </form>
                                <div class="table-responsive">
                                    <form name="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                        <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                            <thead>
                                                <tr>
                                                    <th><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Name</th>
                                                    <th>Total <?php echo $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> Requests <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='<?= htmlspecialchars('Total trip request (i.e 30 second timout screen) received from ' . strtolower($langage_lbl_admin['LBL_RIDERS_ADMIN']) . ' to ' . strtolower($langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN']), ENT_QUOTES, 'UTF-8') ?>'></i></th>     
                                                    <th>Requests Accepted <?php echo $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> Requests <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='<?= htmlspecialchars('Total trip request accepted by ' . strtolower($langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN']), ENT_QUOTES, 'UTF-8') ?>'></i></th>
                                                    <th>Requests Declined <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='<?= htmlspecialchars('Total trip request declined by ' . strtolower($langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN']), ENT_QUOTES, 'UTF-8') ?>'></i></th>
                                                    <th>Requests Timeout <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='<?= htmlspecialchars('Total trip request ' . strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']) . ' has missed to attend. Neither Accepted not Declined.', ENT_QUOTES, 'UTF-8') ?>'></i></th>
                                                    <th>Missed Attempts <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='<?= htmlspecialchars('Total trip request ' . strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']) . ' has tried to accept but due to competitive algorithm, another ' . strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']) . ' has accepted it.', ENT_QUOTES, 'UTF-8') ?>'></i></th>
                                                    <th>In Process Requests <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='<?= htmlspecialchars('Requests sent from ' . strtolower($langage_lbl_admin['LBL_RIDER']) . ' but no ' . strtolower($langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN']) . ' have accepted it yet. Values in this column will remain for some seconds until any ' . strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']) . ' accept it.', ENT_QUOTES, 'UTF-8') ?>'></i></th>
                                                                                                                                                    <!-- <th><?php echo $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN']; ?> Cancelled</th> -->
                                                                                                                                                    <!-- <th>Requests Completed</th> -->
                                                    <th>Acceptance Percentage <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='<?= htmlspecialchars('Ratio of Total Requests Accepted + Total Missed Attempts out of Total Requests sent.', ENT_QUOTES, 'UTF-8') ?>'></i></th> 
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $total_trip_req = $total_trip_acce_req = $total_trip_dec_req = "";
                                                if (count($db_res) > 0) {
                                                    $tAccept = $tRequest = $tDecline = $tTimeout = $tmissed = $tinprocess = 0;
                                                    for ($i = 0; $i < count($db_res); $i++) {
                                                        //$sql_acp = "SELECT COUNT(case when t.eCancelled = 'Yes' then 1 else NULL end) `Cancel`,COUNT(case when t.eCancelled = 'No' then 1 else NULL  end) `Finish` FROM trips t  where t.iDriverId='" . $db_res[$i]['iDriverId'] . "'" . $ssql_trip;
                                                        // echo $sql_acp;
                                                        //$db_acp = $obj->MySQLSelect($sql_acp);
                                                        $Accept = $db_res[$i]['Accept'];
                                                        $tAccept = $tAccept + $Accept;
                                                        $Request = $db_res[$i]['Total Request'];
                                                        $tRequest = $tRequest + $Request;
                                                        $Decline = $db_res[$i]['Decline'];
                                                        $tDecline = $tDecline + $Decline;
                                                        $Timeout = $db_res[$i]['Timeout'];
                                                        $tTimeout = $tTimeout + $Timeout;
                                                        $missed = $db_res[$i]['Missed'];
                                                        $tmissed = $tmissed + $missed;
                                                        $inprocess = $db_res[$i]['inprocess'];
                                                        $tinprocess = $tinprocess + $inprocess;
                                                        $aceptance_percentage = (100 * ($Accept + $missed));
                                                        if ($Request > 0) {
                                                            $aceptance_percentage = (100 * ($Accept + $missed)) / $Request;
                                                        }
                                                        $driverName = "";
                                                        if (isset($driverDataArr[$db_res[$i]['iDriverId']])) {
                                                            $driverName = $driverDataArr[$db_res[$i]['iDriverId']];
                                                        }
                                                        ?>
                                                        <tr class="gradeA">
                                                            <td><?= $generalobjAdmin->clearName($driverName); ?></td>
                                                            <td><?= $Request; ?></td>
                                                            <td><?= $Accept; ?></td>
                                                            <td><?= $Decline; ?></td>
                                                            <td><?= $Timeout; ?></td>
                                                            <td><?= $missed; ?></td>
                                                            <td><?= $inprocess; ?></td>
                                                            <td><?= round($aceptance_percentage, 2) . ' %'; ?></td>      
                                                        </tr>

                                                        <?
                                                    }
                                                }
                                                ?>                                                              
                                            </tbody>
                                            <tr class="gradeA">
                                                <td><b>TOTAL</b></td>
                                                <td><?= $tRequest; ?></td>
                                                <td><?= $tAccept; ?></td>
                                                <td><?= $tDecline; ?></td>
                                                <td><?= $tTimeout; ?></td>
                                                <td><?= $tmissed; ?></td>
                                                <td><?= $tinprocess; ?></td>
                                                <td></td>
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
            <input type="hidden" name="iDriverId" value="<?php echo $iDriverId; ?>" >
            <input type="hidden" name="startDate" value="<?php echo $startDate; ?>" >
            <input type="hidden" name="endDate" value="<?php echo $endDate; ?>" >
            <input type="hidden" name="vStatus" value="<?php echo $vStatus; ?>" >
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

                                                function setRideStatus(actionStatus) {
                                                    window.location.href = "trip.php?type=" + actionStatus;
                                                }
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