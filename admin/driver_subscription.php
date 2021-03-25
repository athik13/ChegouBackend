<?php
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
if (!$userObj->hasPermission('view-driver-subscription')) {
    $userObj->redirect();
}
if ($default_lang == "") {
    $default_lang = "EN";
}
$script = 'DriverSubscriptionPlan';
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
$dri_ssql = "";
if (SITE_TYPE == 'Demo') {
    $dri_ssql = " And rd.tRegistrationDate > '" . WEEK_DATE . "'";
}

// Start Search Parameters
$option = isset($_REQUEST['option']) ? $_REQUEST['option'] : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";

$curDetails = $obj->MySQLSelect("SELECT vSymbol FROM `currency` WHERE `eDefault` ='Yes' AND eStatus = 'Active'");
$CurrSymbol = $curDetails[0]['vSymbol'];
$langDetails = $obj->MySQLSelect("SELECT vCode FROM `language_master` WHERE `eDefault` ='Yes' AND eStatus = 'Active'"); 
$langCode = $langDetails[0]['vCode'];

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
        if($option_new=='vPlanName') {
            $option_new = $option_new.'_'.$default_lang;
        }
        if ($eStatus != '') {
            $ssql .= " AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";
        }
        if($option_new=='fPrice') {
             $ssql .= " AND " . stripslashes($option_new) . " = '" . $generalobjAdmin->clean($keyword_new) . "'";
        } else if($option_new=='ePlanValidity') {
             $ssql .= " AND CONCAT(vPlanPeriod,' ',ePlanValidity) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%'";
        } else {
            $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%'";
        }
    } else { 
        if ($eStatus != '') {
            $ssql .= " AND (vPlanName_$default_lang LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR ePlanValidity LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR fPrice = '" . $generalobjAdmin->clean($keyword_new) . "') AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";
        } else {
            $ssql .= " AND (vPlanName_$default_lang LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR CONCAT(vPlanPeriod,' ',ePlanValidity) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR fPrice = '" . $generalobjAdmin->clean($keyword_new) . "')";
        }
    }
} else if ($eStatus != '' && $keyword == '') {
    $ssql .= " AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";
}
// End Search Parameters
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
if (!empty($eStatus)) {
    $eStatus_sql = "";
} else {
    $eStatus_sql = " AND eStatus != 'Deleted'";
}

$sql = "SELECT COUNT(iDriverSubscriptionPlanId) AS Total FROM $tblname WHERE 1 = 1 $eStatus_sql $ssql";
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
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


$sql = "SELECT * FROM $tblname WHERE 1 = 1 $eStatus_sql $ssql LIMIT $start, $per_page";
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
        <title><?= $SITE_NAME ?> | <?= $langage_lbl_admin['LBL_DRIVER_SUBSCRIPTION_PLAN'] ?></title>
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
                                <h2><?php echo $langage_lbl_admin['LBL_DRIVER_SUBSCRIPTION_PLAN']; ?></h2>
                                <!--<input type="button" id="" value="ADD A DRIVER" class="add-btn">-->
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
                                            <option value="">All</option>
                                            <option  value="vPlanName" <?php if ($option == "vPlanName") { echo "selected";} ?>><?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_NAME']; ?></option>
                                            <option  value="ePlanValidity" <?php if ($option == "ePlanValidity") { echo "selected"; } ?>><?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_TYPE']; ?></option>
                                            <option  value="fPrice" <?php if ($option == "fPrice") { echo "selected"; } ?>><?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_PRICE']; ?></option>
                                            <!--<option value="eStatus" <?php if ($option == 'eStatus') { echo "selected"; } ?>>Status</option>-->
                                        </select>
                                    </td>
                                    <td width="15%" class="searchform"><input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"  class="form-control" /></td>
                                    <td width="12%" class="estatus_options" id="eStatus_options" >
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
                                    </td>
                                    <td>
                                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'driver_subscription.php'"/>
                                    </td>
                                    <td width="30%"><a class="add-btn" href="driver_subscription_action.php" style="text-align: center;"><?php echo $langage_lbl_admin['LBL_ADD_SUBSCRIPTION_PLAN']; ?></a></td>
                                </tr>
                            </tbody>
                        </table>

                    </form>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="admin-nir-export">
                                    <div class="changeStatus col-lg-12 option-box-left">
                                        <span class="col-lg-2 new-select001">
                                    <?php //if ($userObj->hasPermission(['update-status-company', 'delete-company'])) { ?>
                                                    <select name="changeStatus" id="changeStatus" class="form-control" onChange="ChangeStatusAll(this.value);">
                                                        <option value="" >Select Action</option>
                                        
                                                                <option value='Active' <?php
                                    if ($option == 'Active') {
                                        echo "selected";
                                    }
                                    ?> >Activate</option>
                                                                <option value="Inactive" <?php
                                    if ($option == 'Inactive') {
                                        echo "selected";
                                    }
                                    ?> >Deactivate</option>
                                        <?php if ($eStatus != 'Deleted') { ?>
                                                                                                        <option value="Deleted" <?php
                                            if ($option == 'Delete') {
                                                echo "selected";
                                            }
                                            ?> >Delete</option>
    <?php } ?>
                                                    </select>
<?php //} ?>
                                        </span>
                                    </div>
<?php if (!empty($data_drv)) { ?>

                                        <!--<div class="panel-heading">
                                            <form name="_export_form" id="_export_form" method="post" >
                                                <button type="button" onClick="showExportTypes('company')">Export</button>
                                            </form>
                                        </div>-->
                                                <?php } ?>
                                </div>
                                <div style="clear:both;"></div>
                                <div class="table-responsive">
                                    <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th align="center" width="3%" style="text-align:center;"><input type="checkbox" id="setAllCheck" ></th>
                                                    <th><?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_NAME']; ?></th>
                                                    <!--<th><?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_PERIOD']; ?></th>-->
                                                    <th><?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_TYPE']; ?></th>
                                                    <th><?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_PRICE']; ?></th>
                                                    <th width="6%" class='align-center' style="text-align:center;">Status</th>
                                                    <th width="6%" align="center" style="text-align:center;">Action</th>
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
                                                                $durTxt = '';
                                                                if($data_drv[$i]['ePlanValidity']=='Weekly') {
                                                                    $durTxt = $langage_lbl_admin['LBL_SUB_WEEKS'];
                                                                }
                                                                if($data_drv[$i]['ePlanValidity']=='Monthly') {
                                                                    $durTxt = $langage_lbl_admin['LBL_SUB_MONTH'];
                                                                } 
                                                                ?>
                                                        <tr class="gradeA">
                                                            <td align="center" style="text-align:center;"><input type="checkbox" id="checkbox" name="checkbox[]" <?php echo $default; ?> value="<?php echo $data_drv[$i]['iDriverSubscriptionPlanId']; ?>" />&nbsp;</td>
                                                            <td><?= $data_drv[$i]['vPlanName_'.$langCode]; ?></td>
                                                            <!--<td><?= $data_drv[$i]['vPlanPeriod']; ?></td>-->
                                                            <td><?= $data_drv[$i]['vPlanPeriod']." ".$durTxt; ?></td>
                                                            <td><?= $CurrSymbol.$data_drv[$i]['fPrice']; ?></td>
                                                            <td align="center" style="text-align:center;">
        <?php
        if ($data_drv[$i]['eStatus'] == 'Active') {
            $dis_img = "img/active-icon.png";
        } else if ($data_drv[$i]['eStatus'] == 'Inactive') {
            $dis_img = "img/inactive-icon.png";
        } else if ($data_drv[$i]['eStatus'] == 'Deleted') {
            $dis_img = "img/delete-icon.png";
        }
        ?>
                                                                <img src="<?= $dis_img; ?>" alt="image" data-toggle="tooltip" title="<?php echo $data_drv[$i]['eStatus']; ?>">
                                                            </td>
                                                            <td align="center" style="text-align:center;" class="action-btn001">

                                                                <div class="share-button share-button4 openHoverAction-class" style="display:block;">
                                                                    <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>
                                                                    <div class="social show-moreOptions openPops_<?= $data_drv[$i]['iDriverSubscriptionPlanId']; ?>">
                                                                        <ul>
                                                                            <li class="entypo-twitter" data-network="twitter"><a href="driver_subscription_action.php?id=<?= $data_drv[$i]['iDriverSubscriptionPlanId']; ?>" data-toggle="tooltip" title="Edit">
                                                                                    <img src="img/edit-icon.png" alt="Edit">
                                                                                </a></li>
        
                                                                            <li class="entypo-facebook" data-network="facebook"><a href="javascript:void(0);" onClick="changeStatus('<?php echo $data_drv[$i]['iDriverSubscriptionPlanId']; ?>', 'Inactive')"  data-toggle="tooltip" title="Activate">
                                                                                        <img src="img/active-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >
                                                                                    </a></li>
                                                                            <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onClick="changeStatus('<?php echo $data_drv[$i]['iDriverSubscriptionPlanId']; ?>', 'Active')" data-toggle="tooltip" title="Deactivate">
                                                                                        <img src="img/inactive-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >
                                                                                    </a></li>
        
                                                        <?php if ($eStatus != 'Deleted') { ?>
                                                                            <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onClick="changeStatusDelete('<?php echo $data_drv[$i]['iDriverSubscriptionPlanId']; ?>')"  data-toggle="tooltip" title="Delete">
                                                                                        <img src="img/delete-icon.png" alt="Delete" >
                                                                                    </a></li>
                                            <?php } ?>
                                                                        </ul>
                                                                    </div>
                                                                </div>

                                                            </td>
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
                    <div class="admin-notes">
                        <h4>Notes:</h4>
                        <ul>
                            <li>Driver Subscription module will list all Plans on this page.</li>
                            <li>Admin can Activate , Deactivate , Delete any plans.</li>
                            <li>In the case of inactive or deleted plan, if the inactive/deleted plan contains any subscribed members then his plan will continue till it's expiry date. However, the new members will not able to register for the inactivated/deleted plans.</li>
                        </ul>
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
    </body>
    <!-- END BODY-->
</html>
