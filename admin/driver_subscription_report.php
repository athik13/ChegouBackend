<?php
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
if (!$userObj->hasPermission('manage-driver-subscription-report')) {
    $userObj->redirect();
}
if ($default_lang == "") {
    $default_lang = "EN";
}
$script = 'DriverSubscriptionReport';
$tblname = 'driver_subscription_plan';
//$eSystem = " AND  c.eSystem ='General'";
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY iDriverSubscriptionPlanId DESC';

/* if ($sortby == 1) {
  if ($order == 0)
  $ord = " ORDER BY c.vCompany ASC";
  else
  $ord = " ORDER BY c.vCompany DESC";
  }

  if ($sortby == 2) {

  if ($order == 0)
  $ord = " ORDER BY c.vEmail ASC";
  else
  $ord = " ORDER BY c.vEmail DESC";
  }

  if ($sortby == 3) {
  if ($order == 0)
  $ord = " ORDER BY `count` ASC";
  else
  $ord = " ORDER BY `count` DESC";
  }

  if ($sortby == 4) {
  if ($order == 0)
  $ord = " ORDER BY c.eStatus ASC";
  else
  $ord = " ORDER BY c.eStatus DESC";
  } */
//End Sorting

$cmp_ssql = "";
// if (SITE_TYPE == 'Demo') {
// $cmp_ssql = " And c.tRegistrationDate > '" . WEEK_DATE . "'";
// }
//$dri_ssql = "";
//if (SITE_TYPE == 'Demo') {
//    $dri_ssql = " And rd.tRegistrationDate > '" . WEEK_DATE . "'";
//}

$sql = "SELECT iDriverId,CONCAT(vName,' ',vLastName) AS driverName,vEmail FROM register_driver WHERE eStatus != 'Deleted' order by vName";
$db_drivers = $obj->MySQLSelect($sql);

// Start Search Parameters
$option = isset($_REQUEST['option']) ? $_REQUEST['option'] : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$searchDriver = isset($_REQUEST['searchDriver']) ? stripslashes($_REQUEST['searchDriver']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";

$defaultDetails = $obj->MySQLSelect("SELECT * FROM `language_master` WHERE `eDefault` ='Yes' AND eStatus = 'Active'");
//$currencySymbol = $obj->MySQLSelect("SELECT vSymbol FROM currency WHERE eDefault = 'Yes'")[0]['vSymbol'];
$vcode = $defaultDetails[0]['vCode'];
$currencySymbol = $defaultDetails[0]['vCurrencySymbol'];

$ssql = '';
if ($keyword != '') {
    $keyword_new = $keyword;
    $chracters = array("(", "+", ")");
    $removespacekeyword = preg_replace('/\s+/', '', $keyword);
    $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));
    if (is_numeric($keyword_new)) {
        $keyword_new = $keyword_new;
    } else {
        $keyword_new = $keyword;
    }
    $keyword_new = explode('/',$keyword_new);
    $keyword_new = $keyword_new[0];
    if ($option != '') {
        $option_new = $option;
        if($option_new=='providerName') {
            $ssql .= " AND (rd.vName LIKE '%".$generalobjAdmin->clean($keyword_new)."%' OR rd.vLastName LIKE '%".$generalobjAdmin->clean($keyword_new)."%' OR CONCAT( vName,  ' ', vLastName ) LIKE  '%".$generalobjAdmin->clean($keyword_new)."%' )";
        } else if($option_new=='ePlanValidity') {
             $ssql .= " AND CONCAT(d.vPlanPeriod,' ',d.ePlanValidity) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%'";
        } else {
            $ssql .= " AND d." . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%'";
        }
    } else { 
            $ssql .= " AND (d.vPlanName LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR CONCAT(d.vPlanPeriod,' ',d.ePlanValidity) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%')";
            $ssql .= " OR (rd.vName LIKE '%".$generalobjAdmin->clean($keyword_new)."%' OR rd.vLastName LIKE '%".$generalobjAdmin->clean($keyword_new)."%' OR CONCAT( vName,  ' ', vLastName ) LIKE  '%".$generalobjAdmin->clean($keyword_new)."%')";
            
    }
}
if($searchDriver!='') {
    $ssql .= " AND rd.iDriverId = $searchDriver";
}
// End Search Parameters
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$tblPlan = 'driver_subscription_plan';
$tblDetails = 'driver_subscription_details';

//$getField = "eSubscriptionStatus, p.vPlanName, p.vPlanDescription,p.vPlanPeriod,p.ePlanValidity,CONCAT('$currencySymbol',p.fPrice) as fPlanPrice,d.tSubscribeDate,d.tExpiryDate,IFNULL(DATEDIFF(d.tExpiryDate,CURDATE()),'0') AS planLeftDays, d.iDriverId,CONCAT(rd.vName,' ',rd.vLastName) as name";
$getField = "d.eSubscriptionStatus, d.vPlanName, d.vPlanDescription,d.vPlanPeriod,d.ePlanValidity,CONCAT('$currencySymbol',d.fPrice) as fPlanPrice,d.tSubscribeDate,d.tExpiryDate,d.tClosedDate,IFNULL(DATEDIFF(d.tExpiryDate,CURDATE()),'0') AS planLeftDays,d.tSubscribeDate, d.iDriverId,CONCAT(rd.vName,' ',rd.vLastName) as name";
//$sql = "SELECT $getField FROM $tblDetails d INNER JOIN $tblPlan p ON d.iDriverSubscriptionPlanId = p.iDriverSubscriptionPlanId  LEFT JOIN register_driver rd ON rd.iDriverId=d.iDriverId WHERE 1 $ssql ORDER BY d.tSubscribeDate DESC, d.tExpiryDate DESC";
$sql = "SELECT $getField FROM $tblDetails d LEFT JOIN register_driver rd ON rd.iDriverId=d.iDriverId WHERE 1 $ssql ORDER BY d.tSubscribeDate DESC, d.tExpiryDate DESC";

//$sql = "SELECT COUNT(iDriverSubscriptionPlanId) AS Total FROM $tblname WHERE 1 = 1 $eStatus_sql $ssql";
$totalData = $obj->MySQLSelect($sql);
$total_results = count($totalData);
//$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;

//-------------if page is setcheck------------------//
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    } else {
        // error - show first set of results
        $start = 0;
        $end = $per_page;
    }
} else {
    // if page isn't set, show first set of results
    $start = 0;
    $end = $per_page;
}
// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages = $total_pages;
if ($page <= 0)
    $page = 1;
//Pagination End


$sql = $sql." LIMIT $start, $per_page";
$data_drv = $obj->MySQLSelect($sql);

$endRecord = count($data_drv);

$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page')
        $var_filter .= "&$key=" . stripslashes($val);
}

$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | <?= $langage_lbl_admin['LBL_DRIVER_SUBSCRIPTION_REPORT'] ?></title>
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
                                <h2><?php echo $langage_lbl_admin['LBL_DRIVER_SUBSCRIPTION_REPORT']; ?></h2>
                            </div>
                        </div>
                        <hr />
                    </div>
                    <?php include('valid_msg.php'); ?>
                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                            <tbody>
                                <tr>
                                    <td width="5%"><label for="textfield"><strong>Search:</strong></label></td>
                                    <td width="10%" class="padding-right10">
                                        <select name="option" id="option" class="form-control">
                                            <!--<option value="">All</option>-->
                                            <option  value="vPlanName" <?php if ($option == "vPlanName") { echo "selected";} ?>><?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_NAME']; ?></option>
                                            <option  value="ePlanValidity" <?php if ($option == "ePlanValidity") { echo "selected"; } ?>><?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_TYPE']; ?></option>
<!--                                            <option  value="providerName" <?php if ($option == "providerName") { echo "selected"; } ?>><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'].' Name'; ?></option>-->
                                        </select>
                                    </td>
                                    <td width="15%" class="searchform padding-right10"><input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"  class="form-control" /></td>
                                    <td width="20%" class="padding-right10">
                                    <div class="select001">
                                    <select class="form-control filter-by-text driver_container" name = 'searchDriver' data-text="Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>">
                                        <option value="">Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></option>
                                        <?php foreach ($db_drivers as $dbd) { ?>
                                            <option value="<?php echo $dbd['iDriverId']; ?>" <?php
                                            if ($searchDriver == $dbd['iDriverId']) {
                                                echo "selected";
                                            }
                                            ?>><?php echo $generalobjAdmin->clearName(" " . $dbd['driverName']); ?> - ( <?php echo $generalobjAdmin->clearEmail($dbd['vEmail']); ?> )</option>
<?php } ?>
                                    </select>
                                    </div>
                                    </td>
                                    <!--<td width="12%" class="estatus_options" id="eStatus_options" >
                                        <select name="eStatus" id="estatus_value" class="form-control">
                                            <option value="" >Select Status</option>
                                            <option value='Active' <?php
                                                    if ($eStatus == 'Active') {
                                                        echo "selected";
                                                    }
                                                    ?> >Active</option>
                                            <option value="Inactive" <?php
                                                    if ($eStatus == 'Inactive') {
                                                        echo "selected";
                                                    }
                                                    ?> >Inactive</option>
                                            <option value="Deleted" <?php
                                    if ($eStatus == 'Deleted') {
                                        echo "selected";
                                    }
                                                    ?> >Delete</option>
                                        </select>
                                    </td>-->
                                    <td>
                                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'driver_subscription_report.php'"/>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </form>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="admin-nir-export">
                                <?php if (!empty($data_drv)) { ?>
                                        <div class="panel-heading">
                                            <form name="_export_form" id="_export_form" method="post" >
                                                <input type="hidden" name="searchDriver" value="<?php echo $_REQUEST['searchDriver']; ?>">
                                                <button type="button" onClick="showExportTypes('driversubscription')">Export</button>
                                            </form>
                                        </div>
                                <?php } ?>
                                </div>
                                <div style="clear:both;"></div>
                                <div class="table-responsive">
                                    <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr>
<!--                                                    <th align="center" width="3%" style="text-align:center;">
                                                        <input type="checkbox" id="setAllCheck" >
                                                    </th>-->
                                                    <th><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'].' Name'; ?></th>
                                                    <th><?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_NAME']; ?></th>
                                                    <!--<th><?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_PERIOD']; ?></th>-->
                                                    <th><?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_TYPE']; ?></th>
                                                    <th><?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_PRICE']; ?></th>
                                                    <th><?php echo $langage_lbl_admin['LBL_DRIVER_SUBSCRIBE_DATE']; ?></th>
                                                    <th><?php echo $langage_lbl_admin['LBL_DRIVER_EXPIRE_DATE']; ?></th>
                                                    <th><?php echo $langage_lbl_admin['LBL_DRIVER_CANCEL_DATE']; ?></th>
                                                    <!--<th>Status</th>-->
                                                </tr>
                                            </thead>
                                            <tbody>
                                                        <?php
                                                        if (!empty($data_drv)) {
                                                            for ($i = 0; $i < count($data_drv); $i++) {
                                                                $default = '';
//                                                                if ($data_drv[$i]['iCompanyId'] == 1) {
//                                                                    $default = 'disabled';
//                                                                }
                                                                $driverId = $data_drv[$i]['iDriverId'];
                                                                $driverName = $obj->MySQLSelect("SELECT CONCAT(vName,' ',vLastName) as name FROM register_driver WHERE iDriverId = $driverId");
                                                                $durTxt = '';
                                                                if($data_drv[$i]['ePlanValidity']=='Weekly') {
                                                                    $durTxt = $langage_lbl_admin['LBL_SUB_WEEKS'];
                                                                }
                                                                if($data_drv[$i]['ePlanValidity']=='Monthly') {
                                                                    $durTxt = $langage_lbl_admin['LBL_SUB_MONTH'];
                                                                } 
                                                                ?>
                                                        <tr class="gradeA">
<!--                                                            <td align="center" style="text-align:center;"><input type="checkbox" id="checkbox" name="checkbox[]" <?php echo $default; ?> value="<?php echo $data_drv[$i]['iDriverSubscriptionPlanId']; ?>" />&nbsp;</td>-->
                                                            <td><?= $generalobj->clearName($data_drv[$i]['name']); ?></td>
                                                            <td><?= $data_drv[$i]['vPlanName']; ?></td>
                                                            <!--<td><?= $data_drv[$i]['vPlanPeriod']; ?></td>-->
                                                            <td><?= $data_drv[$i]['vPlanPeriod']." ".$durTxt; ?></td>
                                                            <td><?= $data_drv[$i]['fPlanPrice']; ?></td>
                                                            <td><?= date('Y-m-d',strtotime($data_drv[$i]['tSubscribeDate'])); ?></td>
                                                            <td><?= date('Y-m-d',strtotime($data_drv[$i]['tExpiryDate'])); ?></td>
                                                            <td><?php if($data_drv[$i]['eSubscriptionStatus']=='Cancelled') echo date('Y-m-d',strtotime($data_drv[$i]['tClosedDate'])); else echo '-'; ?></td>
                                                            <!--<td><?= $data_drv[$i]['eSubscriptionStatus']; ?></td>-->
                                                        </tr>
        <?php
    }
} else {
    ?>
                                                    <tr class="gradeA">
                                                        <td colspan="8"> No Records Found.</td>
                                                    </tr>
<?php } ?>
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
        <form name="pageForm" id="pageForm" action="action/driver_subscription.php" method="post" >
            <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
            <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
            <input type="hidden" name="iDriverSubscriptionPlanId" id="iMainId01" value="" >
            <input type="hidden" name="eStatus" id="eStatus" value="<?php echo $eStatus; ?>" >
            <input type="hidden" name="status" id="status01" value="" >
            <input type="hidden" name="statusVal" id="statusVal" value="" >
            <input type="hidden" name="option" value="<?php echo $option; ?>" >
            <input type="hidden" name="keyword" value="<?php echo $keyword; ?>" >
            <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
            <input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
            <input type="hidden" name="method" id="method" value="" >
        </form>
<?php
include_once('footer.php');
?>
        <script>
            $(function () {
                $("select.filter-by-text").each(function () {
                    $(this).select2({
                        placeholder: $(this).attr('data-text'),
                        allowClear: true
                    }); //theme: 'classic'
                });
            });
            
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
                //$('html').addClass('loading');
                var action = $("#_list_form").attr('action');
                //alert(action);
                var formValus = $("#frmsearch").serialize();
//                alert(action+formValus);
                window.location.href = action + "?" + formValus;
            });

            $('.entypo-export').click(function (e) {
                e.stopPropagation();
                var $this = $(this).parent().find('div');
                $(".openHoverAction-class div").not($this).removeClass('active');
                $this.toggleClass('active');
            });


        </script>
        <link rel="stylesheet" href="css/select2/select2.min.css" />
        <script src="js/plugins/select2.min.js"></script>
    </body>
    <!-- END BODY-->
</html>
