<?php
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();
$script = "Driver Accept Report";
$sql = "select iDriverId, CONCAT(vName,' ',vLastName) AS driverName from register_driver WHERE eStatus != 'Deleted' order by vName";
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
$defaultDate = date("Y-m-d") . ' ' . "00:00:00";
if ($startDate != '' && $endDate != '') {
    $ssql .= " AND rs.tDate between '$date1' and '$date2'";
} else {
    $ssql .= " AND rs.tDate > '" . $defaultDate . "'";
}
if ($iDriverId != '') {
    $ssql .= " AND rd.iDriverId = '" . $iDriverId . "'";
}
//echo $ssql;die;
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT COUNT( DISTINCT rs.iDriverId ) AS Total FROM register_driver rd left join driver_request rs on rd.iDriverId=rs.iDriverId WHERE 1=1 $ssql GROUP by rs.iDriverId";
$totalData = $obj->MySQLSelect($sql);
$total_results = count($totalData);
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
/* $sql = "SELECT rd.iDriverId , rd.vLastName ,rd.vName ,
  COUNT(case when rs.eStatus = 'Accept' then 1 else NULL end) `Accept` ,
  COUNT(case when rs.eStatus != '' then 1 else NULL  end) `Total Request` ,
  COUNT(case when rs.eStatus  = 'Decline' then 1 else NULL end) `Decline` ,
  COUNT(case when rs.eStatus  = 'Timeout' then 1 else NULL end) `Timeout`
  FROM register_driver rd
  left join driver_request rs on rd.iDriverId=rs.iDriverId
  WHERE 1=1 $ssql GROUP by rs.iDriverId $ord LIMIT $start, $per_page";
  $db_res = $obj->MySQLSelect($sql); */
$sql = "SELECT rd.iDriverId , rd.vLastName ,rd.vName ,
COUNT(case when rs.iDriverId != '' then 1 else NULL  end) `Total Request` ,
COUNT(case when rs.eTimeOut  = 'Yes' then 1 else NULL end) `Timeout` ,
COUNT(case when rs.eOpened  = 'Yes' then 1 else NULL end) `Opened` ,
COUNT(case when rs.eAccept  = 'Yes' then 1 else NULL end) `Accept` ,
COUNT(case when rs.eDecline  = 'Yes' then 1 else NULL end) `Decline` ,
COUNT(case when rs.eDiscardByApp  = 'Yes' AND rs.eAcceptAttempted  = 'No' AND  rs.eReceived ='No' 
AND  rs.eReceivedByPubSub  = 'No' AND rs.eReceivedByPush  = 'No' AND rs.eReceivedByScript  = 'No' AND rs.eOpened ='No' AND rs.eOpened  = 'No' AND rs.eDecline ='No' AND rs.eTimeOut ='No' AND rs.eAccept  = 'No' then 1 else NULL end) `DiscardByApp` ,
COUNT(case when (rs.eDiscard  = 'Yes'  AND  rs.eReceived ='No' 
AND  rs.eReceivedByPubSub  = 'No' AND rs.eReceivedByPush  = 'No' AND rs.eReceivedByScript  = 'No' AND rs.eDiscardByApp  = 'No') then 1 else NULL end) `Discard` ,
COUNT(case when rs.eAcceptAttempted  = 'Yes' then 1 else NULL end) `eAcceptAttempted` ,
COUNT(case when rs.eReceived ='Yes' AND (rs.eReceivedByPubSub  = 'Yes' OR rs.eReceivedByPush  = 'Yes' OR rs.eReceivedByScript  = 'Yes' ) then 1 else NULL end) `Received`,
COUNT(case when rs.eOpened ='No' AND rs.eTimeOut  = 'No' AND  rs.eDecline  = 'No' AND rs.eAccept  = 'No' AND  rs.eReceived ='Yes' AND  (rs.eReceivedByPubSub  = 'Yes' OR rs.eReceivedByPush  = 'Yes' OR rs.eReceivedByScript  = 'Yes' ) then 1 else NULL end) `NotProcessByDriver`,
COUNT(case when rs.eOpened ='Yes' AND rs.eTimeOut  = 'No' AND  rs.eDecline  = 'No' AND rs.eAccept  = 'No' AND  rs.eReceived ='Yes' AND rs.eAcceptAttempted  = 'No' AND  (rs.eReceivedByPubSub  = 'Yes' OR rs.eReceivedByPush  = 'Yes' OR rs.eReceivedByScript  = 'Yes' ) then 1 else NULL end) `UnExprtedCase`
FROM  driver_request rs
left join register_driver rd   on rd.iDriverId=rs.iDriverId  
WHERE 1=1 $ssql GROUP by rs.iDriverId $ord LIMIT $start, $per_page";
$db_res = $obj->MySQLSelect($sql);
//echo "<pre>"; print_r($db_res); exit;
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
                            <h2><?php echo $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> Acceptance Report</h2>

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
                                            <div class="col-lg-3 select001">
                                                <select class="form-control filter-by-text" name = 'iDriverId' data-text="Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>">
                                                    <option value="">Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></option>
                                                    <?php foreach ($db_drivers as $dbd) { ?>
                                                        <option value="<?php echo $dbd['iDriverId']; ?>" <?php
                                                        if ($iDriverId == $dbd['iDriverId']) {
                                                            echo "selected";
                                                        }
                                                        ?>><?php echo $generalobjAdmin->clearName($dbd['driverName']); ?></option>
                                                            <?php } ?>
                                                </select>
                                            </div>
                                        </span>
                                    </div>
                                    <div class="tripBtns001"><b>
                                            <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                            <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'trip_acceptance_report.php'"/>
                                            <button type="button" onClick="reportExportTypes('trip_acceptance_report')" class="export-btn001" >Export</button>
                                        </b>
                                    </div>
                                </form>
                                <div class="table-responsive">
                                    <form name="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                        <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                            <thead>
                                                <tr>
                                                    <th><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Name</th>
                                                    <th>Total <?php echo $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> Requests</th>     
                                                    <th>Requests Received </th>
                                                    <th>Requests Open </th>
                                                    <th>Requests Accept</th>
                                                    <th>Requests Decline</th>
                                                    <th>Requests Timeout</th>
                                                    <th>Requests Discard by Application</th>
                                                    <th>Requests Discard by Server</th>
                                                    <th>Not Processed by <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></th>
                                                    <th>UnExpected Close by <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></th>
                                                    <th>Miss Attempts</th>
                                                    <!--<th><?php echo $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN']; ?> Accepted</th>
                                                    <th><?php echo $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN']; ?> Cancelled</th>
                                                    <th><?php echo $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN']; ?> Finished</th>-->
                                                    <th>Acceptance Percentage</th> 
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $total_trip_req = $total_trip_acce_req = $total_trip_dec_req = "";
                                                for ($i = 0; $i < count($db_res); $i++) {
                                                    $sql_acp = "SELECT COUNT(case when t.eCancelled = 'Yes' then 1 else NULL end) `Cancel` ,
									COUNT(case when t.eCancelled = 'No' then 1 else NULL  end) `Finish` 
									FROM trips t  where t.iDriverId='" . $db_res[$i]['iDriverId'] . "'";
                                                    // echo $sql_acp;
                                                    $db_acp = $obj->MySQLSelect($sql_acp);


                                                    $Accept = $db_res[$i]['Accept'];
                                                    $tAccept = $tAccept + $Accept;

                                                    $eAcceptAttempted = $db_res[$i]['eAcceptAttempted'];
                                                    $teAcceptAttempted = $teAcceptAttempted + $eAcceptAttempted;
                                                    $Request = $db_res[$i]['Total Request'];
                                                    $tRequest = $tRequest + $Request;
                                                    $Decline = $db_res[$i]['Decline'];
                                                    $tDecline = $tDecline + $Decline;
                                                    $Timeout = $db_res[$i]['Timeout'];
                                                    $tTimeout = $tTimeout + $Timeout;
                                                    $Cancel = $db_acp[0]['Cancel'];
                                                    $tCancel = $tCancel + $Cancel;
                                                    $Opened = $db_res[$i]['Opened'];
                                                    $tOpened = $tOpened + $Opened;
                                                    $Received = $db_res[$i]['Received'];
                                                    $tReceived = $tReceived + $Received;
                                                    $DiscardByApp = $db_res[$i]['DiscardByApp'];
                                                    $tDiscardByApp = $tDiscardByApp + $DiscardByApp;
                                                    $Discard = $db_res[$i]['Discard'];
                                                    $tDiscard = $tDiscard + $Discard;

                                                    $NotProcessByDriver = $db_res[$i]['NotProcessByDriver'];
                                                    $tNotProcessByDriver = $tNotProcessByDriver + $NotProcessByDriver;


                                                    $UnExprtedCase = $db_res[$i]['UnExprtedCase'];
                                                    $tUnExprtedCase = $tUnExprtedCase + $UnExprtedCase;


                                                    //$Finish = $db_acp[0]['Finish'];
                                                    $Finish = $Accept - $Cancel;
                                                    if ($Finish < 0) {
                                                        $Finish = 0;
                                                    }
                                                    $tFinish = $tFinish + $Finish;
                                                    $aceptance_percentage = (100 * ($Accept + $eAcceptAttempted)) / $Request;

                                                    //$aceptance_percentage= (100 * ($Accept))/$Request;
                                                    ?>

                                                    <tr class="gradeA">
                                                        <td><?= $generalobjAdmin->clearName($db_res[$i]['vName'] . ' ' . $db_res[$i]['vLastName']); ?></td>
                                                        <td><?= $Request; ?></td>
                                                        <td><?= $Received; ?></td>
                                                        <td><?= $Opened; ?></td>
                                                        <td><?= $Accept; ?></td>
                                                        <td><?= $Decline; ?></td>
                                                        <td><?= $Timeout; ?></td>
                                                        <td><?= $DiscardByApp; ?></td>
                                                        <td><?= $Discard; ?></td>                                      
                                                        <td><?= $NotProcessByDriver; ?></td>                                      
                                                        <td><?= $UnExprtedCase; ?></td>                                      
                                                        <td><?= $eAcceptAttempted; ?></td>                                      
                                                        <!--<td><?= $Cancel; ?></td>-->
                                                        <!--<td><?= $Finish; ?></td>-->
                                                        <td><?= round($aceptance_percentage) . ' %'; ?></td>      
                                                    </tr>

                                                    <?
                                                }
                                                ?>                                                              
                                            </tbody>
                                            <?php if ($endRecord > 0) { ?>
                                                <tr class="gradeA">
                                                    <td><b>TOTAL</b></td>
                                                    <td><?= $tRequest; ?></td>
                                                    <td><?= $tReceived; ?></td>
                                                    <td><?= $tOpened; ?></td>
                                                    <td><?= $tAccept; ?></td>
                                                    <td><?= $tDecline; ?></td>
                                                    <td><?= $tTimeout; ?></td>	                               
                                                    <td><?= $tDiscardByApp; ?></td>
                                                    <td><?= $tDiscard; ?></td>
                                                    <td><?= $tNotProcessByDriver; ?></td>
                                                    <td><?= $tUnExprtedCase; ?></td>
                                                    <td><?= $teAcceptAttempted; ?></td>
                                                    <td></td>
                                                </tr>
                                            <?php } else { ?>
                                                <tr class="gradeA">
                                                    <td colspan="13" style="text-align: center;">Record not found</td>
                                                </tr>
                                            <?php } ?>
                                        </table>
                                    </form>
                                    <?php include('pagination_n.php'); ?>
                                </div>
                            </div> <!--TABLE-END-->
                        </div>
                    </div>
                    <div class="admin-notes">
                        <h4>Notes:</h4>
                        <ul>
                            <li>
                                Total Viaje Requests : These are total requests sent by <?php echo strtolower($langage_lbl_admin['LBL_RIDER']); ?> to listed <?php echo strtolower($langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN']); ?>
                            </li>
                            <li>
                                Requests Received : These are total requests received by <?php echo strtolower($langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN']); ?>' devices
                            <li>
                                Requests Open : These are total requests opened into <?php echo strtolower($langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN']); ?>' devices	
                            </li>
                            <li>
                                Requests Accept : These are total trip requests accepted by <?php echo strtolower($langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN']); ?>
                            </li>
                            <li>
                                Requests Decline : These are total trip requests declined by <?php echo strtolower($langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN']); ?>
                            </li>
                            <li>
                                Requests Timeout : These are total trip requests <?php echo strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']); ?> has missed to attend. <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> neither accepted nor declined these requests.
                            </li>
                            <li>
                                Requests Discard by Application : These are total trip requests discard by <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> App.(In case of many requests received by app and app is not running in background OR app is not in a state to process request.)
                            </li>

                            <li>
                                Requests Discard by Server : These are total trip requests rejected by server. (In case of many requests, script will deliver only last new requests.)
                            </li>
                            <li>
                                Not Processed by <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> : These are total trip requests which are not processed by <?php echo strtolower($langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN']); ?>.(App opens request page but <?php echo strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']); ?> closes the app.)
                            </li>
                            <li>
                                Unexpected Close by <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> : These are total trip requests which is not handled by <?php echo strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']); ?>.(Like: Request received by app but <?php echo strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']); ?> does not open application. and <?php echo strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']); ?> closes the app) 
                            </li>
                            <li>
                                Miss Attempts : These are total trip requests <?php echo strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']); ?> has tried to accept but due to competitive algorithm, another <?php echo strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']); ?> has accepted it.
                            </li>
                            <li>
                                Acceptance Percentage : Ratio of Total Requests Accepted + Total Missed Attempts out of Total Requests sent.
                            </li>
                        </ul> 
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
                                                            if (ev.date.valueOf() < startDate.valueOf()) {
                                                                $('#alert').show().find('strong').text('The end date can not be less then the start date');
                                                            } else {
                                                                $('#alert').hide();
                                                                endDate = new Date(ev.date);
                                                                $('#endDate').text($('#dp5').data('date'));
                                                            }
                                                            $('#dp5').datepicker('hide');
                                                        });
                                                $(document).ready(function () {
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