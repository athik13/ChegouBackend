<?php
include_once('../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
include_once ('../app_common_functions.php'); //added by SP on 27-9-2019 for fly changes

////$generalobjAdmin->check_member_login();
if (!$userObj->hasPermission('view-cancel-reasons')) {
    $userObj->redirect();
}
$script = 'cancel_reason';

//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY iCancelReasonId DESC';
if ($sortby == 1) {
    if ($order == 0)
        $ord = " ORDER BY vTitle_" . $default_lang . " ASC";
    else
        $ord = " ORDER BY vTitle_" . $default_lang . " DESC";
}

if ($sortby == 3) {
    if ($order == 0)
        $ord = " ORDER BY eStatus ASC";
    else
        $ord = " ORDER BY eStatus DESC";
}
//End Sorting

$adm_ssql = "";
/* if (SITE_TYPE == 'Demo') {
  $adm_ssql = " And ad.tRegistrationDate > '" . WEEK_DATE . "'";
  } */

// Start Search Parameters
$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$eType = isset($_REQUEST['eType']) ? stripslashes($_REQUEST['eType']) : "";

$eFly = 0;
if($eType=='Fly') {
    $eFly = 1;
    $eType = 'Ride';
}
//Added BY HJ On 09-01-2020 For Set Option Name As Per Service Start
$serviceIds = $generalobj->getCurrentActiveServiceCategoriesIds();
//$ufxEnable = $generalobj->CheckUfxServiceAvailable();
$ufxEnable = isUberXModuleAvailable() ? "Yes" : "No"; //add function to modules availibility
$rideEnable = isRideModuleAvailable() ? "Yes" : "No";
$deliveryEnable = isDeliveryModuleAvailable() ? "Yes" : "No";
$deliverallEnable = isDeliverAllModuleAvailable() ? "Yes" : "No";

$optionName = "DeliverAll";
if ($serviceIds == 1) {
    $optionName = "Food";
}
//Added BY HJ On 09-01-2020 For Set Option Name As Per Service End
$ssql = '';
if ($keyword != '') {
    if ($option != '') {
        if (strpos($option, 'eStatus') !== false) {
            if ($eType != '') {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "' AND eType = '" . $eType . "' ";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            }
        } else {
            if ($eType != '') {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND eType = '" . $eType . "' ";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        }
    } else {
        if ($eType != '') {
            $ssql .= " AND ( vTitle_" . $default_lang . " LIKE '%" . $keyword . "%') AND  eType='" . $eType . "'";
        } else {
            $ssql .= " AND ( vTitle_" . $default_lang . " LIKE '%" . $keyword . "%')";
        }
    }
    $ssql.=" AND eFly = '".$eFly."'";
} else if ($eType != '' && $keyword == '') {
    $ssql .= " AND eType = '" . $generalobjAdmin->clean($eType) . "'";
    $ssql.=" AND eFly = '".$eFly."'";
}

if($ufxEnable != "Yes") {
	$ssql .= " AND eType != 'UberX'";
}
if(!checkFlyStationsModule()) {
    $ssql.= " AND eFly = '0'";
}
if($rideEnable != "Yes") {
    $ssql .= " AND eType != 'Ride'";
}
if($deliveryEnable != "Yes") {
    $ssql .= " AND eType != 'Deliver'";
}
if($deliverallEnable != "Yes") {
    $ssql .= " AND eType != 'DeliverAll'";
}
// End Search Parameters
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT count(iCancelReasonId) as Total FROM cancel_reason where 1=1 $ssql";
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;
$start = 0;
$end = $per_page;
//-------------if page is setcheck------------------//
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


$sql = "  SELECT * FROM cancel_reason where 1=1 AND eStatus !='Deleted' $ssql $ord LIMIT $start, $per_page";
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
        <title><?= $SITE_NAME ?> | Admin</title>
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
                                <h2><?= $langage_lbl_admin["LBL_CANCEL_REASON"]; ?></h2>
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
                                    <td width="10%" class=" padding-right10"><select name="option" id="option" class="form-control">
                                            <option value="">All</option>
                                            <option value="vTitle_<?= $default_lang; ?>" <?php
                                            if ($option == "vTitle_" . $default_lang) {
                                                echo "selected";
                                            }
                                            ?> >Reason</option>
                                            <option value="eStatus" <?php
                                            if ($option == 'eStatus') {
                                                echo "selected";
                                            }
                                            ?> >Status</option>
                                        </select>
                                    </td>
                                    <td width="15%"><input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"  class="form-control" /></td>
                                    <?
                                    if ($APP_TYPE == 'Ride' || $APP_TYPE == 'Delivery' || $APP_TYPE == 'UberX') {
                                        
                                    } else if (ONLYDELIVERALL == "Yes") {
                                        
                                    } else if ($APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') {
                                        ?>
                                        <td width="12%" class="eType_options" id="eType_options" >
                                            <select name="eType" id="eType_value" class="form-control">
                                                <option value="" >Select Service Type</option>
                                                <?php if (ONLYDELIVERALL == "No" && $rideEnable == "Yes") { ?>
                                                    <option value='Ride' <?php
                                                    if ($eType == 'Ride') {
                                                        echo "selected";
                                                    }
                                                    ?> >Ride</option>
                                                        <?php } if (ONLYDELIVERALL == "No" && $deliveryEnable == "Yes") { ?>
                                                    <option value="Deliver" <?php
                                                    if ($eType == 'Deliver') {
                                                        echo "selected";
                                                    }
                                                    ?> >Deliver</option>
                                                        <?php } if (ONLYDELIVERALL == "No" && $APP_TYPE == 'Ride-Delivery-UberX' && strtolower($ufxEnable) == "yes") { ?>
                                                    <option value="UberX" <?php
                                                    if ($eType == 'UberX') {
                                                        echo "selected";
                                                    }
                                                    ?> >Service</option>
                                                        <?php } ?>
                                                        <? if (DELIVERALL == 'Yes' && $deliverallEnable == "Yes") { ?>
                                                    <option value="DeliverAll" <?php
                                                    if ($eType == 'DeliverAll') {
                                                        echo "selected";
                                                    }
                                                    ?> ><?= $optionName; ?></option>
                                                        <? } ?>
                                                    <?php if(checkFlyStationsModule()) {  //added by SP for fly on 27-9-2019 ?>
                                                    <option value="Fly" <?php if ($eType == "Ride" && $eFly==1) echo 'selected="selected"'; ?>>Fly</option>
                                                    <?php } ?>
                                            </select>
                                        </td>
                                    <?php } ?>
                                    <td>
                                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'cancellation_reason.php'"/>
                                    </td>
                                    <?php if ($userObj->hasPermission('create-cancel-reasons')) { ?>
                                        <td width="30%"><a class="add-btn" href="cancellation_reason_action.php" style="text-align: center;">Add <?= $langage_lbl_admin["LBL_CANCEL_REASON_TXT_ADMIN"]; ?></a></td>
                                    <?php } ?>
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
                                            <?php if ($userObj->hasPermission(['update-status-cancel-reasons', 'delete-cancel-reasons'])) { ?>
                                                <select name="changeStatus" id="changeStatus" class="form-control" onchange="ChangeStatusAll(this.value);">
                                                    <option value="" >Select Action</option>
                                                    <?php if ($userObj->hasPermission('update-status-cancel-reasons')) { ?>
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
                                                            <?php } ?>
                                                            <?php if ($userObj->hasPermission('delete-cancel-reasons')) { ?>
                                                        <option value="Deleted" <?php
                                                        if ($option == 'Delete') {
                                                            echo "selected";
                                                        }
                                                        ?> >Delete</option>
                                                            <?php } ?>
                                                </select>
                                            <?php } ?>
                                        </span>
                                    </div>
                                    <!--                                    <div class="panel-heading">
                                                                            <form name="_export_form" id="_export_form" method="post" >
                                                                                <button type="button" onclick="showExportTypes('cancel_reason')" >Export</button>
                                                                            </form>
                                                                        </div>-->
                                </div>
                                <div style="clear:both;"></div>
                                <div class="table-responsive">
                                    <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th align="center" width="3%" style="text-align:center;"><input type="checkbox" id="setAllCheck" ></th>
                                                    <th width="20%"><a href="javascript:void(0);" onClick="Redirect(1,<?php
                                                        if ($sortby == '1') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Reason <?php
                                                                           if ($sortby == 1) {
                                                                               if ($order == 0) {
                                                                                   ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                            <?php
                                                            //if (DELIVERALL == 'Yes' && ($APP_TYPE != 'Ride-Delivery' || $APP_TYPE != 'Ride-Delivery-UberX')) {
                                                            if ($APP_TYPE == 'Ride' || $APP_TYPE == 'Delivery' || $APP_TYPE == 'UberX') {
                                                                
                                                            } else if (ONLYDELIVERALL == "Yes") {
                                                                
                                                            } else if ($APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') {
                                                                ?>
                                                        <th width="12%">Service Type</th>      
                                                    <?php } ?>
                                                    <th style="text-align:center;" width="15%">Order</th>
                                                    <th width="8%" align="center" style="text-align:center;"><a href="javascript:void(0);" onClick="Redirect(3,<?php
                                                        if ($sortby == '3') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Status <?php
                                                                                                                    if ($sortby == 3) {
                                                                                                                        if ($order == 0) {
                                                                                                                            ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <th width="8%" align="center" style="text-align:center;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if (!empty($data_drv)) {
                                                    for ($i = 0; $i < count($data_drv); $i++) {
                                                        ?>
                                                        <tr class="gradeA">
                                                            <td align="center" style="text-align:center;"><input type="checkbox" id="checkbox" name="checkbox[]" <?php echo $default; ?> value="<?php echo $data_drv[$i]['iCancelReasonId']; ?>" />&nbsp;</td>
                                                            <td><?= $data_drv[$i]['vTitle_' . $default_lang]; ?></td>
                                                            <?php
                                                            if ($APP_TYPE == 'Ride' || $APP_TYPE == 'Delivery' || $APP_TYPE == 'UberX') {
                                                                
                                                            } else if (ONLYDELIVERALL == "Yes") {
                                                                
                                                            } else if ($APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') {
                                                                ?>
                                                                <td><?php
                                                                    if ($data_drv[$i]['eType'] == 'UberX') {
                                                                        $eTypeNew = 'Service';
                                                                    } else {
                                                                        $eTypeNew = $data_drv[$i]['eType'];
                                                                    }
                                                                    //added by SP for fly on 27-09-2019
                                                                     if ($data_drv[$i]['eType'] == 'Ride' && $data_drv[$i]['eFly']==1 ) {
                                                                        $eTypeNew = 'Fly';
                                                                     }
                                                                    echo $eTypeNew;
                                                                    ?></td>
                                                            <?php } ?>
                                                            <td width="10%" align="center"><?php echo $data_drv[$i]['iDisplayOrder']; ?></td>
                                                            <td align="center" style="text-align:center;">
                                                                <?php if ($data_drv[$i]['eStatus'] != 'Yes') { ?>
                                                                    <?php
                                                                    if ($data_drv[$i]['eStatus'] == 'Active') {
                                                                        $dis_img = "img/active-icon.png";
                                                                    } else if ($data_drv[$i]['eStatus'] == 'Inactive') {
                                                                        $dis_img = "img/inactive-icon.png";
                                                                    } else if ($data_drv[$i]['eStatus'] == 'Deleted') {
                                                                        $dis_img = "img/delete-icon.png";
                                                                    }
                                                                    ?>
                                                                    <img src="<?= $dis_img; ?>" alt="image" data-toggle="tooltip" title="<?= $data_drv[$i]['eStatus']; ?>">
                                                                    <?php
                                                                } else {
                                                                    ?><img src="img/active-icon.png" alt="image" data-toggle="tooltip" title="<?= $data_drv[$i]['eStatus']; ?>"><?php
                                                                }
                                                                ?>
                                                            </td>
                                                            <td align="center" style="text-align:center;" class="action-btn001">
                                                                <div class="share-button openHoverAction-class" style="display: block;">
                                                                    <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>
                                                                    <div class="social show-moreOptions openPops_<?= $data_drv[$i]['iCancelReasonId']; ?>">
                                                                        <ul>
                                                                            <li class="entypo-twitter" data-network="twitter"><a href="cancellation_reason_action.php?id=<?= $data_drv[$i]['iCancelReasonId']; ?>" data-toggle="tooltip" title="Edit">
                                                                                    <img src="img/edit-icon.png" alt="Edit">
                                                                                </a></li>
                                                                            <?php if ($data_drv[$i]['eDefault'] != 'Yes') { ?>
                                                                                <?php if ($userObj->hasPermission('update-status-cancel-reasons')) { ?>
                                                                                    <li class="entypo-facebook" data-network="facebook"><a href="javascript:void(0);" onclick="changeStatus('<?php echo $data_drv[$i]['iCancelReasonId']; ?>', 'Inactive')"  data-toggle="tooltip" title="Activate">
                                                                                            <img src="img/active-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >
                                                                                        </a></li>
                                                                                    <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onclick="changeStatus('<?php echo $data_drv[$i]['iCancelReasonId']; ?>', 'Active')" data-toggle="tooltip" title="Deactivate">
                                                                                            <img src="img/inactive-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >    
                                                                                        </a></li>
                                                                                <?php } ?>
                                                                                <?php if ($userObj->hasPermission('delete-cancel-reasons')) { ?>
                                                                                    <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onclick="changeStatusDelete('<?php echo $data_drv[$i]['iCancelReasonId']; ?>')"  data-toggle="tooltip" title="Delete">
                                                                                            <img src="img/delete-icon.png" alt="Delete" >
                                                                                        </a></li>
                                                                                <?php } ?>
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
                                                        <td colspan="7"> No Records Found.</td>
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
                            <li>
                                Cancel reason module will list all cancel reason on this page.
                            </li>
                            <li>
                                Administrator can Activate / Deactivate / Delete any cancel reason.
                            </li>
                            <!-- <li>
                                Administrator can export data in XLS format.
                            </li> -->
                        </ul>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->

        <form name="pageForm" id="pageForm" action="action/cancellation_reason.php" method="post" >
            <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
            <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
            <input type="hidden" name="iCancelReasonId" id="iMainId01" value="" >
            <input type="hidden" name="status" id="status01" value="" >
            <input type="hidden" name="eType" id="eType" value="<?= $eType; ?>">
            <input type="hidden" name="statusVal" id="statusVal" value="" >
            <input type="hidden" name="option" value="<?php echo $option; ?>" >
            <input type="hidden" name="keyword" value="<?php echo $keyword; ?>" >
            <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
            <input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
            <input type="hidden" name="method" id="method" value="" >
        </form>
        <?php include_once('footer.php'); ?>
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
                // alert(action);
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

            $(document).on("click", function (e) {
                if ($(e.target).is(".openHoverAction-class,.show-moreOptions,.entypo-export") === false) {
                    $(".show-moreOptions").removeClass("active");
                }
            });

        </script>
    </body>
    <!-- END BODY-->
</html>
