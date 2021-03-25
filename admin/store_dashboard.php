<?php
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}

$script = 'Store Dashboard';
////$generalobjAdmin->check_member_login();
if (!$userObj->hasPermission('manage-store-dashboard')) {
    $userObj->redirect();
}
$company = $generalobjAdmin->getStoreDetails();
$driver = $generalobjAdmin->getDriverDetailsDashboard('');
$rider_count = $generalobjAdmin->getRiderCount();
$rider = $rider_count[0]['count(iUserId)'];
$totalEarns = $generalobjAdmin->getStoreTotalEarns();
$totalRides = $generalobjAdmin->getStoreTripStates('total');
$onRides = $generalobjAdmin->getStoreTripStates('on going order');
$finishRides = $generalobjAdmin->getStoreTripStates('Delivered');
$cancelRides = $generalobjAdmin->getStoreTripStates('Cancelled');
$actDrive = $generalobjAdmin->getStoreDetailsDashboard('active');
$inaDrive = $generalobjAdmin->getStoreDetailsDashboard('inactive');
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME; ?> | Store Dashboard</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <? include_once('global_files.php'); ?>
        <link rel="stylesheet" href="css/style.css" />
        <link rel="stylesheet" href="css/new_main.css" />
        <link rel="stylesheet" href="css/adminLTE/AdminLTE.min.css" />
        <script type="text/javascript" src="js/plugins/jquery/jquery.min.js"></script>
        <script type="text/javascript" src="js/plugins/morris/raphael-min.js"></script>
        <script type="text/javascript" src="js/plugins/morris/morris.min.js"></script> 
        <script type="text/javascript" src="js/actions.js"></script>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53">
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <? include_once('header.php'); ?>
            <? include_once('left_menu.php'); ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner" style="min-height:700px;">
                    <div class="row">
                        <div class="col-lg-12">
                            <h1> Store Dashboard </h1>
                        </div>
                    </div>
                    <hr />
                    <?php if (!$userObj->hasPermission(["store-dashboard-order-statistics", "store-dashboard-statistics", "store-dashboard-orders", "store-dashboard-latest-order"])) { ?>
                        <h3 class="text-center" style="margin-top: 200px">Welcome to Admin panel</h3>
                    <?php } ?>
                    <?php if ($userObj->hasPermission(['store-dashboard-order-statistics', 'store-dashboard-statistics'])) { ?>
                        <div class="row">
                            <?php if ($userObj->hasPermission('store-dashboard-order-statistics')) { ?>
                                <div class="col-lg-6">
                                    <div class="panel panel-primary bg-gray-light" >
                                        <div class="panel-heading" >
                                            <div class="panel-title-box">
                                                <i class="fa fa-area-chart"></i> <?php echo $langage_lbl_admin['LBL_ORDER_STATISTICS_ADMIN']; ?>
                                            </div>                                  
                                        </div>
                                        <div class="row padding_005" style="padding-bottom: 5px;">
                                            <div class="col-lg-6">
                                                <?php if ($userObj->hasPermission('view-all-orders')) { ?>
                                                    <a href="allorders.php?type=allorders">
                                                    <?php } ?>
                                                    <div class="info-box bg-aqua">
                                                        <span class="info-box-icon"><i class="fa fa-cubes"></i></span>

                                                        <div class="info-box-content">
                                                            <span class="info-box-text"><?php echo $langage_lbl_admin['LBL_TOTAL_ORDER_ADMIN']; ?> </span>
                                                            <span class="info-box-number"><?= number_format($totalRides); ?></span>
                                                        </div>
                                                        <!-- /.info-box-content -->
                                                    </div>
                                                    <?php if ($userObj->hasPermission('view-all-orders')) { ?>
                                                    </a>
                                                <?php } ?>
                                                <!-- /.info-box -->
                                            </div>
                                            <!-- /.col -->
                                            <div class="col-lg-6">
                                                <?php if ($userObj->hasPermission('view-processing-orders')) { ?>
                                                    <a href="allorders.php?type=processing">
                                                    <?php } ?>
                                                    <div class="info-box bg-yellow">
                                                        <span class="info-box-icon"><i class="fa fa-clone"></i></span>

                                                        <div class="info-box-content">
                                                            <span class="info-box-text"><?php echo $langage_lbl_admin['LBL_ON_ORDERS_ADMIN']; ?> </span>
                                                            <span class="info-box-number"><?= number_format($onRides); ?></span>
                                                        </div>
                                                        <!-- /.info-box-content -->
                                                    </div>
                                                    <?php if ($userObj->hasPermission('view-processing-orders')) { ?>
                                                    </a>
                                                <?php } ?>
                                                <!-- /.info-box -->
                                            </div>
                                            <div class="col-lg-6">
                                                <?php if ($userObj->hasPermission('view-cancelled-orders')) { ?>
                                                    <a href="cancelled_orders.php">
                                                    <?php } ?>
                                                    <div class="info-box bg-red">
                                                        <span class="info-box-icon"><i class="fa fa-times-circle-o"></i></span>

                                                        <div class="info-box-content">
                                                            <span class="info-box-text"><?php echo $langage_lbl_admin['LBL_CANCELLED_ORDERS_ADMIN']; ?> </span>
                                                            <span class="info-box-number"><?= number_format($cancelRides); ?></span>
                                                        </div>
                                                        <!-- /.info-box-content -->
                                                    </div>
                                                    <?php if ($userObj->hasPermission('view-cancelled-orders')) { ?>
                                                    </a>
                                                <?php } ?>
                                                <!-- /.info-box -->
                                            </div>
                                            <!-- /.col -->
                                            <div class="col-lg-6">
                                                <?php if ($userObj->hasPermission('view-all-orders')) { ?>
                                                    <a href="allorders.php?type=allorders&iStatusCode=6">
                                                    <?php } ?>
                                                    <div class="info-box bg-green">
                                                        <span class="info-box-icon"><i class="fa fa-check"></i></span>

                                                        <div class="info-box-content">
                                                            <span class="info-box-text"><?php echo $langage_lbl_admin['LBL_COMPLETED_ORDERS_ADMIN']; ?> </span>
                                                            <span class="info-box-number"><?= number_format($finishRides); ?></span>
                                                        </div>
                                                        <!-- /.info-box-content -->
                                                    </div>
                                                    <?php if ($userObj->hasPermission('view-all-orders')) { ?>
                                                    </a>
                                                <?php } ?>
                                                <!-- /.info-box -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php if ($userObj->hasPermission('store-dashboard-statistics')) { ?>
                                <div class="col-lg-6">
                                    <div class="panel panel-primary bg-gray-light" >
                                        <div class="panel-heading" >
                                            <div class="panel-title-box">
                                                <i class="fa fa-bar-chart"></i> <?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?>
                                            </div>                                  
                                        </div>
                                        <div class="panel-body padding-0">
                                            <div class="col-lg-6">
                                                <div class="chart-holder" id="dashboard-drivers" style="height: 200px;"></div>
                                            </div>
                                            <div class="col-lg-6">
                                                <h3><?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?>  Count : <?= number_format($company); ?></h3>
                                                <p>Today : <b><?= number_format(count($generalobjAdmin->getStoreDateStatus('today'))); ?></b></p>
                                                <p>This Month : <b><?= number_format(count($generalobjAdmin->getStoreDateStatus('month'))); ?></b></p>
                                                <p>This Year : <b><?= number_format(count($generalobjAdmin->getStoreDateStatus('year'))); ?></b></p>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- END VISITORS BLOCK -->
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                    <?php if ($userObj->hasPermission(['store-dashboard-orders', 'store-dashboard-latest-order'])) { ?>
                        <hr />
                        <div class="row">
                            <?php if ($userObj->hasPermission('store-dashboard-orders')) { ?>
                                <div class="col-lg-6">
                                    <div class="panel panel-primary bg-gray-light" >
                                        <div class="panel-heading" >
                                            <div class="panel-title-box">
                                                <i class="fa fa-bar-chart"></i> <?php echo $langage_lbl_admin['LBL_ORDERS_NAME_ADMIN']; ?>
                                            </div>                                  
                                        </div>
                                        <div class="panel-body padding-0">
                                            <div class="col-lg-6">
                                                <div class="chart-holder" id="dashboard-rides" style="height: 207px;"></div>
                                            </div>
                                            <div class="col-lg-6">
                                                <h3><?php echo $langage_lbl_admin['LBL_ORDERS_NAME_ADMIN']; ?>  Count : <?= number_format($totalRides); ?></h3>
                                                <p>Today : 
                                                    <b><?= number_format($generalobjAdmin->getOrderDateStates('today')); ?></b>
                                                </p>
                                                <p>This Month : 
                                                    <b><?= number_format($generalobjAdmin->getOrderDateStates('month')); ?></b>
                                                </p>
                                                <p>This Year : 
                                                    <b><?= number_format($generalobjAdmin->getOrderDateStates('year')); ?></b>
                                                </p>
                                                <br />
                                                <p>
                                                    * This is count for all <?= $langage_lbl_admin['LBL_ORDERS_NAME_ADMIN']; ?> (Finished, ongoing, cancelled.)
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php if ($userObj->hasPermission('store-dashboard-latest-order')) { ?>
                                <div class="col-lg-6">
                                    <div class="panel panel-primary bg-gray-light">
                                        <div class="panel-heading" >
                                            <div class="panel-title-box">
                                                <i class="icon-comments"></i> Latest <?php echo $langage_lbl_admin['LBL_ORDERS_NAME_ADMIN']; ?>
                                                <?php if ($userObj->hasPermission('view-all-orders')) { ?>
                                                    <a class="btn btn-info btn-sm ride-view-all001" href="allorders.php?type=allorders">View All</a>
                                                <?php } ?>
                                            </div>                                  
                                        </div>
                                        <div class="panel-body padding-0" style="padding-bottom: 0;">
                                            <?php if (!empty($db_finished_orders)) { ?>
                                                <?php for ($i = 0, $n = $i + 2; $i < count($db_finished_orders); $i++, $n++) { ?>
                                                    <!-- <div class="panel-heading" style="background:none;"> -->
                                                    <ul class="chat">
                                                        <?php if ($n % 2 == 0) { ?>
                                                            <?php if ($userObj->hasPermission('view-order-invoice')) { ?>
                                                                <a href=<? echo "order_invoice.php?iOrderId=" . $db_finished_orders[$i]['iOrderId']; ?> target="_blank"> 
                                                                <?php } ?>
                                                                <li class="left clearfix" style="margin-bottom: 0;">

                                                                    <span class="chat-img pull-left">
                                                                        <? if ($db_finished_orders[$i]['vImage'] != '' && $db_finished_orders[$i]['vImage'] != "NONE" && file_exists("../webimages/upload/Company/" . $db_finished_orders[$i]['iCompanyId'] . "/" . $db_finished_orders[$i]['vImage'])) { ?>
                                                                            <img src="../webimages/upload/Company/<?php echo $db_finished_orders[$i]['iCompanyId'] . "/" . $db_finished_orders[$i]['vImage']; ?>" alt="User Avatar" class="img-circle"  height="50" width="50"/>
                                                                        <? } else { ?>

                                                                            <img src="../assets/img/profile-user-img.png" alt="" class="img-circle"  height="50" width="50">
                                                                        <? } ?>
                                                                    </span>

                                                                    <div class="chat-body clearfix">
                                                                        <div class="header">
                                                                            <strong class="primary-font "> <?php echo $generalobjAdmin->clearName($db_finished_orders[$i]['vCompany']); ?> </strong>
                                                                            <small class="pull-right text-muted label label-danger">
                                                                                <i class="icon-time"></i>
                                                                                <?php
                                                                                $regDate = $db_finished_orders[$i]['tOrderRequestDate'];
                                                                                $dif = strtotime(Date('Y-m-d H:i:s')) - strtotime($regDate);
                                                                                if ($dif < 60) {
                                                                                    $time = floor($dif / (60));
                                                                                    echo "Just Now";
                                                                                } else if ($dif < 3600) {
                                                                                    $time = floor($dif / (60));
                                                                                    $texts = "Minute";
                                                                                    if ($time > 1) {
                                                                                        $texts = "Minutes";
                                                                                    }
                                                                                    echo $time . " $texts ago";
                                                                                } else if ($dif < 86400) {
                                                                                    $time = floor($dif / (60 * 60));
                                                                                    $texts = "Hour";
                                                                                    if ($time > 1) {
                                                                                        $texts = "Hours";
                                                                                    }
                                                                                    echo $time . " $texts ago";
                                                                                } else {
                                                                                    $time = floor($dif / (24 * 60 * 60));
                                                                                    $texts = "Day";
                                                                                    if ($time > 1) {
                                                                                        $texts = "Days";
                                                                                    }
                                                                                    echo $time . " $texts ago";
                                                                                }
                                                                                ?>
                                                                            </small>
                                                                        </div>
                                                                        <br />
                                                                        <p>
                                                                            <?php
                                                                            echo $db_finished_orders[$i]['vCaddress'] . " --> " . $db_finished_orders[$i]['vServiceAddress'] . "<br/>";
                                                                            echo "<b>Status: " . $db_finished_orders[$i]['vStatus'] . "</b>";
                                                                            echo "<b>&nbsp;&nbsp;&nbsp; Order No: " . $db_finished_orders[$i]['vOrderNo'] . "</b>";
                                                                            ?>
                                                                        </p>
                                                                    </div>
                                                                </li>
                                                                <?php if ($userObj->hasPermission('view-order-invoice')) { ?>
                                                                </a>
                                                            <?php } ?>
                                                        <?php } else { ?>
                                                            <li class="right clearfix" style="margin-bottom: 0;">
                                                                <?php if ($userObj->hasPermission('view-order-invoice')) { ?>
                                                                    <a href=<? echo "order_invoice.php?iOrderId=" . $db_finished_orders[$i]['iOrderId']; ?> target="_blank"> 
                                                                    <?php } ?>
                                                                    <span class="chat-img pull-right">



                                                                        <? if ($db_finished_orders[$i]['vImage'] != '' && $db_finished_orders[$i]['vImage'] != "NONE") { ?>
                                                                            <?php if (file_exists("../webimages/upload/Company/" . $db_finished_orders[$i]['iCompanyId'] . "/" . $db_finished_orders[$i]['vImage'])) { ?>
                                                                                <img src="../webimages/upload/Company/<?php echo $db_finished_orders[$i]['iCompanyId'] . "/" . $db_finished_orders[$i]['vImage']; ?>" alt="User Avatar" class="img-circle"  height="50" width="50"/>
                                                                            <?php } else { ?>
                                                                                <img src="../assets/img/profile-user-img.png" alt="" class="img-circle"  height="50" width="50">
                                                                            <?php } ?>
                                                                        <? } else { ?>

                                                                            <img src="../assets/img/profile-user-img.png" alt="" class="img-circle"  height="50" width="50">
                                                                        <? } ?>
                                                                    </span>
                                                                    <div class="chat-body clearfix">
                                                                        <div class="header">
                                                                            <small class=" text-muted label label-info">
                                                                                <i class="icon-time"></i> <?php
                                                                                $regDate = $db_finished_orders[$i]['tOrderRequestDate'];
                                                                                $dif = strtotime(Date('Y-m-d H:i:s')) - strtotime($regDate);
                                                                                if ($dif < 60) {
                                                                                    $time = floor($dif / (60));
                                                                                    echo "Just Now";
                                                                                } else if ($dif < 3600) {
                                                                                    $time = floor($dif / (60));
                                                                                    $texts = "Minute";
                                                                                    if ($time > 1) {
                                                                                        $texts = "Minutes";
                                                                                    }
                                                                                    echo $time . " $texts ago";
                                                                                } else if ($dif < 86400) {
                                                                                    $time = floor($dif / (60 * 60));
                                                                                    $texts = "Hour";
                                                                                    if ($time > 1) {
                                                                                        $texts = "Hours";
                                                                                    }
                                                                                    echo $time . " $texts ago";
                                                                                } else {
                                                                                    $time = floor($dif / (24 * 60 * 60));
                                                                                    $texts = "Day";
                                                                                    if ($time > 1) {
                                                                                        $texts = "Days";
                                                                                    }
                                                                                    echo $time . " $texts ago";
                                                                                }
                                                                                ?></small>

                                                                            <strong class="pull-right primary-font"> <?php echo $generalobjAdmin->clearName($db_finished_orders[$i]['vCompany']); ?></strong>

                                                                        </div>
                                                                        <br />
                                                                        <p>
                                                                            <?php
                                                                            echo $db_finished_orders[$i]['vCaddress'] . " --> " . $db_finished_orders[$i]['vServiceAddress'] . "<br/>";
                                                                            echo "<b>Status: " . $db_finished_orders[$i]['vStatus'] . "</b>";
                                                                            echo "<b>&nbsp;&nbsp;&nbsp; Order No: " . $db_finished_orders[$i]['vOrderNo'] . "</b>";
                                                                            ?>
                                                                        </p>
                                                                    </div>
                                                                    <?php if ($userObj->hasPermission('view-order-invoice')) { ?>
                                                                    </a>
                                                                <?php } ?>
                                                            </li>
                                                        <?php } ?>
                                                    </ul>
                                                    <!-- </div> -->
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <!-- END VISITORS BLOCK -->
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                    <!-- COMMENT AND NOTIFICATION  SECTION -->
                </div>

                <!--END PAGE CONTENT -->
            </div>

            <? include_once('footer.php'); ?>

    </body>
    <!-- END BODY-->
    <?
// if(SITE_TYPE=='Demo'){
// $generalobjAdmin->remove_unwanted();
// }
    ?>
</html>
<script>
    $(document).ready(function () {
        /* Donut dashboard chart */
        var total_ride = '<?= $totalRides; ?>';
        var complete_ride = '<?= $finishRides; ?>';
        var cancel_ride = '<?= $cancelRides; ?>';
        var on_ride = '<?= $onRides; ?>';
        if (complete_ride > 0 || cancel_ride > 0 || total_ride > 0)
        {
            Morris.Donut({
                element: 'dashboard-rides',
                data: [
                    {label: "On Going", value: on_ride},
                    {label: "Completed", value: complete_ride},
                    {label: "Cancelled", value: cancel_ride}
                ],

                formatter: function (x) {
                    return (x / total_ride * 100).toFixed(2) + '%' + ' (' + x + ')';
                },
                colors: ['#33414E', '#1caf9a', '#FEA223'],
                resize: true
            });
        } else
        {
            Morris.Donut({
                element: 'dashboard-rides',
                data: [
                    {label: "On Going", value: on_ride},
                    {label: "Completed", value: complete_ride},
                    {label: "Cancelled", value: cancel_ride}
                ],
                backgroundColor: '#f7f7f7',
                formatter: function (x) {
                    return (0) + ' %' + ' (' + x + ')';
                },
                colors: ['#33414E', '#1caf9a', '#FEA223'],
                resize: true
            });
        }
        var total_drive = '<?= $company; ?>';
        var active_drive = '<?= $actDrive; ?>';
        var inactive_drive = '<?= $inaDrive; ?>';
        Morris.Donut({
            element: 'dashboard-drivers',
            data: [
                {label: "Active", value: active_drive},
                {label: "Pending", value: inactive_drive},
            ],
            formatter: function (x) {
                return (x / total_drive * 100).toFixed(2) + '%' + '(' + x + ')';
            },
            colors: ['#33414E', '#1caf9a', '#FEA223'],
            resize: true
        });
        /* END Donut dashboard chart */
    });
</script>
