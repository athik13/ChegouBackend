<?php
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
//ini_set("display_errors", 1);
//error_reporting(E_ALL);

$generalobjAdmin->check_member_login();
/*
  if (!$userObj->hasPermission('passenger-request-report')) {
  $userObj->redirect();
  }

  ini_set("display_errors", 1);
  error_reporting(E_ALL);
 */
$script = $langage_lbl_admin['LBL_PASSENGER_TXT'] . "Request report";
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
//End Sorting
// Start Search Parameters
$ssql = '';
$Today = Date('Y-m-d');
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
$searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : $Today;
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : $Today;
$date1 = $startDate . ' ' . date("H").":".(date("i") - 20).":00";
$date2 = $endDate . ' ' . "23:59:59";
if ($startDate != '' && $endDate != '') {
    $ssql .= "DR.tDate between '$date1' and '$date2'";
}
if ($searchRider != '') {
    $ssql .= " AND DR.iUserId ='" . $searchRider . "'";
}
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT count(DR.iDriverRequestId) AS Total FROM driver_request DR INNER JOIN register_driver RD ON DR.iDriverId=RD.iDriverId INNER JOIN register_user RU ON DR.iUserId=RU.iUserId WHERE $ssql ORDER BY iDriverRequestId DESC";
$totalData1 = $obj->MySQLSelect($sql);
$total_results = $totalData1[0]['Total'];
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
$totalData = $obj->MySQLSelect("SELECT DR.iUserId,DR.iTripId,DR.tDate,CONCAT(RD.vName,' ',RD.vLastName) AS driverName,CONCAT(RU.vName,' ',RU.vLastName) AS riderName,DR.iDriverRequestId,DR.eStatus, DR.eReceived, DR.eOpened,DR.eAccept,DR.eDecline, DR.eTimeOut,DR.eLaterBooking,DR.vAvailability,DR.tLastOnline,DR.tOnline,DR.tLocationUpdateDate FROM driver_request DR INNER JOIN register_driver RD ON DR.iDriverId=RD.iDriverId INNER JOIN register_user RU ON DR.iUserId=RU.iUserId WHERE $ssql ORDER BY DR.iUserId ASC,DR.iDriverRequestId DESC LIMIT $start, $per_page");
$endRecord = count($totalData);
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page')
        $var_filter .= "&$key=" . stripslashes($val);
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
//echo "<pre>"; print_r($totalData); exit;
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
$db_rider = $obj->MySQLSelect("SELECT iUserId,CONCAT(vName,' ',vLastName) AS riderName,vEmail FROM register_user WHERE eStatus != 'Deleted' order by vName");
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | <?php echo $langage_lbl_admin['LBL_PASSENGER_TXT']; ?> Request report</title>
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
                            <h2><?php echo $langage_lbl_admin['LBL_PASSENGER_TXT']; ?> Request report</h2>
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
                                                <select class="form-control filter-by-text" name = 'searchRider' data-text="Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>">
                                                    <option value="">Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?></option>
                                                    <?php foreach ($db_rider as $dbr) { ?>
                                                        <option value="<?php echo $dbr['iUserId']; ?>" <?php
                                                        if ($searchRider == $dbr['iUserId']) {
                                                            echo "selected";
                                                        }
                                                        ?>><?php echo $generalobjAdmin->clearName($dbr['riderName']); ?> - ( <?php echo $generalobjAdmin->clearEmail($dbr['vEmail']); ?> )</option>
                                                            <?php } ?>
                                                </select>
                                            </div>
                                        </span>
                                    </div>
                                    <div class="tripBtns001"><b>
                                            <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                            <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'passenger_request_report.php'"/>
                                            <!--<button type="button" onClick="reportExportTypes('trips_statistics_report')" class="export-btn001" >Export</button>-->
                                        </b>
                                    </div>
                                </form>
                                <div class="table-responsive">
                                    <form name="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                        <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                            <thead>
                                                <tr>
                                                    <th>Request Date</th>
                                                    <th><?php echo $langage_lbl_admin['LBL_RIDER']; ?> Name</th>     
                                                    <th><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Name</th>
                                                    <th>Trip Details</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                for ($d = 0; $d < count($totalData); $d++) {
                                                    $driverName = $totalData[$d]['driverName'];
                                                    $riderName = $totalData[$d]['riderName'];
                                                    $requestDate = $totalData[$d]['tDate'];

                                                    $link_page = "invoice.php";
                                                    $vAvailability = $totalData[$d]['vAvailability'];
                                                    if($vAvailability == ""){
                                                        $vAvailability = "No";
                                                    }
                                                    $UnExprtedCase = "No";
                                                    if($totalData[$d]['eOpened'] == "Yes" && $totalData[$d]['eReceived'] == "Yes" && $totalData[$d]['eAccept'] == "No" && $totalData[$d]['eDecline'] == "No" && $totalData[$d]['eTimeOut'] == "No" && $totalData[$d]['eAcceptAttempted'] == "No"){
                                                        $UnExprtedCase = "Ye";
                                                    }
                                                    $tLocationUpdateDate = $totalData[$d]['tLocationUpdateDate'];
                                                    if($tLocationUpdateDate == "0000-00-00 00:00:00"){
                                                        $tLocationUpdateDate = "-----";
                                                    }
                                                    ?>
                                                    <tr class="gradeA">
                                                        <td>Request Date: <?= $requestDate; ?><br>
                                                            Request Id: <?= $totalData[$d]['iDriverRequestId']; ?><br>
                                                            Request Received In <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Device?: <?= $totalData[$d]['eReceived']; ?><br>
                                                            Dial Screen Opened In <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Device?: <?= $totalData[$d]['eOpened']; ?><br>
                                                            <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Accepted Request?: <?= $totalData[$d]['eAccept']; ?><br>
                                                            <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Declined Request?: <?= $totalData[$d]['eDecline']; ?><br>
                                                            Request Timeout?: <?= $totalData[$d]['eTimeOut']; ?><br>
                                                            UnExpected Close by <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> ?: <?= $UnExprtedCase; ?><br>
                                                            <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Available ?: <?= $vAvailability; ?><br>
                                                            <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Last Online Time : <?= $tLocationUpdateDate; ?><br>

                                                        </td>
                                                        <td><?= $riderName; ?></td>
                                                        <td><?= $driverName; ?></td>
                                                        <td><? if ($totalData[$d]['iTripId'] != 0) { ?><button class="btn btn-primary" onclick='return !window.open("<?= $link_page ?>?iTripId=<?= $totalData[$d]['iTripId'] ?>", "_blank")'>
                                                                    <i class="icon-th-list icon-white"><b>View Trip</b></i>
                                                                </button><? } ?></td>
                                                    </tr>
                                                    <?
                                                }
                                                ?>                                                              
                                            </tbody>
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