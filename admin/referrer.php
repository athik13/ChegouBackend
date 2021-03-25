<?php

include_once('../common.php');





if ($REFERRAL_SCHEME_ENABLE == "No") {

    header('Location: dashboard.php');

    exit;

}

//ini_set('display_errors', 1);

//error_reporting(E_ALL);

if (!isset($generalobjAdmin)) {

    require_once(TPATH_CLASS . "class.general_admin.php");

    $generalobjAdmin = new General_admin();

}

if (!$userObj->hasPermission('manage-referral-report')) {

    $userObj->redirect();

}

$script = 'referrer';

$type = (isset($_REQUEST['reviewtype']) && $_REQUEST['reviewtype'] != '') ? $_REQUEST['reviewtype'] : 'Driver';

$tableName = "register_user";

$refTableName = "register_driver";

$primaryId = "iUserId";

$userType = "Rider";

$refId = "ru.iDriverId";

if ($type == 'Driver') {

    $tableName = "register_driver";

    $refTableName = "register_user";

    $primaryId = "iDriverId";

    $userType = "Driver";

    $refId = "ru.iUserId";

}

$getMemberData = $obj->MySQLSelect("SELECT CONCAT(vName,' ',vLastName) AS memberName,eStatus,$primaryId FROM " . $tableName);

$getDriverRefData = $obj->MySQLSelect("SELECT CONCAT(rd.vName,' ',rd.vLastName) AS OrgdriverName,rd.eRefType as eRefType,rd1.$primaryId,rd1.iRefUserId FROM " . $tableName . " as rd LEFT JOIN " . $tableName . " as rd1 on rd1.iRefUserId=rd.$primaryId WHERE rd1.eRefType = '" . $userType . "'");

$refDataArr = array();

for ($r = 0; $r < count($getDriverRefData); $r++) {

    $refDataArr[$getDriverRefData[$r]['iRefUserId']][] = $getDriverRefData[$r];

}

$getUserRefData = $obj->MySQLSelect("SELECT CONCAT(ru.vName,' ',ru.vLastName) AS passangerName,CONCAT(rd1.vName,' ',rd1.vLastName) AS OrgdriverName,ru.eRefType as eRefType,$refId,ru.iRefUserId FROM " . $refTableName . " as ru LEFT JOIN " . $tableName . " as rd1 on rd1.$primaryId=ru.iRefUserId WHERE ru.eRefType = '" . $userType . "'");

for ($ru = 0; $ru < count($getUserRefData); $ru++) {

    $refDataArr[$getUserRefData[$ru]['iRefUserId']][] = $getUserRefData[$ru];

}

$totalRecord = $result = $refUserDataArr = array();

//echo "<pre>";print_R($refDataArr);die;

foreach ($getMemberData as $key => $value) {

    //echo "<pre>";print_R($refDataArr[$value[$primaryId]]);die;

    if (isset($refDataArr[$value[$primaryId]])) {

        $result[$value[$primaryId]][] = $refDataArr[$value[$primaryId]];

    }

}

//echo "<pre>";print_R($result);die;

foreach ($result as $key => $value) {

    //echo "<pre>";print_R($value);die;

    foreach ($value as $ky => $ve) {

        foreach ($ve as $k => $v) {

            $totalRecord[$key][] = $v;

        }

    }

}

//echo "<pre>";print_r($totalRecord);exit;

$refWalletData = $obj->MySQLSelect("SELECT sum(iBalance) as totalbalance,iUserId,eUserType,eFor from `user_wallet` WHERE  eFor = 'Referrer' GROUP BY iUserId,eUserType");

for ($r = 0; $r < count($refWalletData); $r++) {

    $refUserDataArr[$refWalletData[$r]['iUserId']][$refWalletData[$r]['eUserType']] = $refWalletData[$r]['totalbalance'];

}

//echo "<pre>";print_R($refUserDataArr);die;

//echo "<pre>";print_R($refDataArr);die;

//echo "<pre>";print_r($total_no_driver);die;

//$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;

$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : '';

?>

<!DOCTYPE html>

<head>

    <meta charset="UTF-8" />

    <title><?= $SITE_NAME ?> | Referral Report</title>

    <meta content="width=device-width, initial-scale=1.0" name="viewport" />

    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />

    <? include_once('global_files.php'); ?>

</head>

<body class="padTop53">

    <!-- MAIN WRAPPER -->

    <div id="wrap">

        <? include_once('header.php'); ?>

        <? include_once('left_menu.php'); ?>

        <!--PAGE CONTENT -->

        <div id="content">

            <div class="inner">

                <div id="add-hide-show-div">

                    <div class="row">

                        <div class="col-lg-12">

                            <h2>Referral Report</h2>

                        </div>

                    </div>

                    <hr />

                </div>

                <?php include('valid_msg.php'); ?>

                <div class="table-list">

                    <div class="row">

                        <div class="col-lg-12">

                            <div class="panel panel-default">

                                <div class="panel-heading referrer-page-tab">

                                    <ul class="nav nav-tabs">

                                        <li <?php if ($type == 'Driver') { ?> class="active" <?php } ?>>

                                            <a data-toggle="tab"  onclick="getReview('Driver')"  href="#home" ><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></a></li>

                                        <li <?php if ($type == 'Rider') { ?> class="active" <?php } ?>>

                                            <a data-toggle="tab" onClick="getReview('Rider')"  href="#menu1"><?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?></a></li>

                                    </ul>

                                </div>

                                <div class="panel-body">

                                    <div class="table-responsive">

                                        <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">

                                            <table class="table table-striped table-bordered table-hover" id="dataTables-example">

                                                <thead>

                                                    <tr>

                                                        <th width="35%">Member Name</th>

                                                        <th width="25%">Total Members Referred</th>

                                                        <?php 

                                                            if(ONLYDELIVERALL == "Yes") { 

                                                                $ref_text = "order";

                                                            } else if (DELIVERALL == "Yes") {

                                                                $ref_text = "trip/order";

                                                            } else if ($APP_TYPE == "Delivery") {

                                                                $ref_text = "Delivery";

                                                            } else {

                                                                $ref_text = "trip";

                                                            } 

                                                        ?>

                                                        <th width="25%">Total Amount Earned <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Amount earned in wallet once refferal do a successful first <?= $ref_text ?>.'></i></th>                                                             

                                                        <th width="15%">Detail</th>

                                                    </tr>

                                                </thead>

                                                <tbody>                                                            

                                                    <?php

                                                    if (count($totalRecord) > 0) {

                                                        $i = 0;

                                                        foreach ($totalRecord as $k => $val) {

                                                            //$totalbalance = $generalobj->getTotalbalance($k, $userType);

                                                            $totalbalance = $generalobj->setTwoDecimalPoint(0);

                                                            if (isset($refUserDataArr[$k][$userType])) {

                                                                $totalbalance = $generalobj->setTwoDecimalPoint($refUserDataArr[$k][$userType]);

                                                            }

                                                            ?>

                                                            <tr>

                                                                <td><?= $generalobjAdmin->clearName($val[0]['OrgdriverName']) ?></td>

                                                                <td><?= count($val); ?></td>

                                                                <td><?= ($totalbalance > 0) ? $generalobj->trip_currency($totalbalance) : '--'; ?></td>

                                                                <td> <a href="referrer_action.php?id=<?php echo $k; ?>&eUserType=<?= $userType; ?>" data-toggle="tooltip" title="View Details">

                                                                        <img src="img/view-details.png" alt="View Details">

                                                                    </a>

                                                                </td>

                                                            </tr>

                                                            <?

                                                            $i++;

                                                        }

                                                    }

                                                    ?>

                                                </tbody>

                                            </table>

                                        </form>

                                        <form name="frmreview" id="frmreview" method="post" action="">

                                            <input type="hidden" name="reviewtype" value="" id="reviewtype">

                                            <input type="hidden" name="action" value="" id="action">

                                        </form>

                                    </div>

                                </div>

                            </div>

                        </div> <!--TABLE-END-->

                    </div>

                </div>

            </div>

        </div>

        <!--END PAGE CONTENT -->

    </div>

    <!--END MAIN WRAPPER -->

    <? include_once('footer.php'); ?>

    <script src="../assets/plugins/dataTables/jquery.dataTables.js"></script>

    <script src="../assets/plugins/dataTables/dataTables.bootstrap.js"></script>

    <script>

                                                $(document).ready(function () {

                                                    $('#dataTables-example').dataTable({

                                                        "order": [[1, "desc"]],

                                                        "iDisplayLength": 25

                                                    });

                                                });

    </script>

    <script>

        $("#setAllCheck").on('click', function () {

            if ($(this).prop("checked")) {

                jQuery("#_list_form input[type=checkbox]").each(function () {

                    if ($(this).attr('disabled') != 'disabled') {

                        this.checked = 'true';

                    }

                });

            } else {

                jQuery("#_list_form input[type=checkbox]").each(function () {

                    this.checked = '';

                });

            }

        });

        $("#Search").on('click', function () {

            var action = $("#_list_form").attr('action');

            var formValus = $("#frmsearch").serialize();

            window.location.href = action + "?" + formValus;

        });

        $('.entypo-export').click(function (e) {

            e.stopPropagation();

            var $this = $(this).parent().find('div');

            $(".openHoverAction-class div").not($this).removeClass('active');

            $this.toggleClass('active');

        });

        $(document).on("click", function (e) {

            if ($(e.target).is(".openHoverAction-class,.show-moreOptions,.entypo-export") === false) {

                $(".show-moreOptions").removeClass("active");

            }

        });

        function getReview(type)

        {

            $('#reviewtype').val(type);

            document.frmreview.submit();

        }

    </script>

</body>

<!-- END BODY-->

</html>



