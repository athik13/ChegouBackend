<?php
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}

////$generalobjAdmin->check_member_login();
if (!$userObj->hasPermission('view-service-category')) {
    $userObj->redirect();
}

$deleteAction = 0; //0-InActive,1-Active
if(isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] == "192.168.1.131" || $_SERVER["HTTP_HOST"] == "192.168.1.141")){
    $deleteAction = 1;
}
$script = 'service_categories';
/* get cusine */
$hdn_del_id = isset($_POST['hdn_del_id']) ? $_POST['hdn_del_id'] : '';
$iServiceId = isset($_GET['iServiceId']) ? $_GET['iServiceId'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$tbl_name = 'service_categories';
if ($hdn_del_id != '') {
    if (SITE_TYPE != 'Demo') {
        $query = "DELETE FROM `" . $tbl_name . "` WHERE iServiceId = '" . $hdn_del_id . "'"; //die;
        $obj->sql_query($query);
    } else {
        header("Location:service_category.php?success=2");
        exit;
    }
}
if ($iServiceId != '' && $status != '') {
    if (SITE_TYPE != 'Demo') {
        $query = "UPDATE `" . $tbl_name . "` SET eStatus = '" . $status . "' WHERE iServiceId = '" . $iServiceId . "'";
        $obj->sql_query($query);
    } else {
        header("Location:cuisine.php?success=2");
        exit;
    }
}
/* get cusine */
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY iDisplayOrder ASC';
if ($sortby == 1) {
    if ($order == 0)
        $ord = " ORDER BY vServiceName_" . $default_lang . " ASC";
    else
        $ord = " ORDER BY vServiceName_" . $default_lang . " DESC";
}
if ($sortby == 4) {
    if ($order == 0)
        $ord = " ORDER BY eStatus ASC";
    else
        $ord = " ORDER BY eStatus DESC";
}
//End Sorting
// Start Search Parameters
$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
$ssql = '';
if ($keyword != '') {
    if ($option != '') {
        if ($eStatus != '') {
            $ssql .= " AND " . stripslashes($option) . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%' AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";
        } else {
            $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
        }
    } else {
        if ($eStatus != '') {
            $ssql .= " AND vServiceName_" . $default_lang . " LIKE '%" . $keyword . "%' AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";
        } else {
            $ssql .= " AND vServiceName_" . $default_lang . " LIKE '%" . $keyword . "%'";
        }
    }
} else if ($eStatus != '' && $keyword == '') {
    $ssql .= " AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";
}
if ($eStatus != '') {
    $eStatussql = "";
} else {
    $eStatussql = " AND eStatus != 'Deleted'";
}
// End Search Parameters
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT COUNT(iServiceId) AS Total FROM service_categories WHERE 1=1 $eStatussql $ssql";
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
$sql = "SELECT iServiceId,vServiceName_" . $default_lang . ",iDisplayOrder,vImage,eStatus FROM " . $tbl_name . " WHERE 1=1 $eStatussql  $ssql $ord LIMIT $start, $per_page ";
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
        <title><?= $SITE_NAME ?> | DeliveryAll Service Category</title>
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
                                <h2>DeliveryAll Service Category</h2>
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
                                            <option value="vServiceName_<?= $default_lang ?>" <?php if ($option == "vServiceName_" . $default_lang) {
    echo "selected";
} ?> >DeliverAll Service Category</option>
                                            <!-- <option value="eStatus" <?php if ($option == 'eStatus') {
    echo "selected";
} ?> >Status</option> -->
                                        </select>
                                    </td>
                                    <td width="15%"><input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"  class="form-control" /></td>
                                    <td width="12%" class="estatus_options" id="eStatus_options" >
                                        <select name="eStatus" id="estatus_value" class="form-control">
                                            <option value="" >Select Status</option>
                                            <option value='Active' <?php if ($eStatus == 'Active') {
    echo "selected";
} ?> >Active</option>
                                            <option value="Inactive" <?php if ($eStatus == 'Inactive') {
    echo "selected";
} ?> >Inactive</option>
                                            <!--<option value="Deleted" <?php if ($eStatus == 'Deleted') {
    echo "selected";
} ?> >Delete</option>-->
                                        </select>
                                    </td>
                                    <td>
                                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'service_category.php'"/>
                                    </td>
                                    <!--<td width="30%"><a class="add-btn" href="service_category.php" style="text-align: center;">Add Service Category</a></td>-->
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
                                            <?php if ($userObj->hasPermission('update-status-service-category')) { ?>
                                                <select name="changeStatus" id="changeStatus" class="form-control" onchange="ChangeStatusAll(this.value);">
                                                    <option value="" >Select Action</option>
                                                    <option value='Active' <?php if ($option == 'Active') {
                                                    echo "selected";
                                                } ?> >Make Active</option>
                                                    <option value="Inactive" <?php if ($option == 'Inactive') {
                                                    echo "selected";
                                                } ?> >Make Inactive</option>
                                        <?php  if($eStatus != 'Deleted' && $deleteAction == 1) {  ?>
                                                    <option value="Deleted" <?php if ($option == 'Delete') {echo "selected"; } ?> >Make Delete</option>
    <?php  }  ?>
                                                </select>
<?php } ?>
                                        </span>
                                    </div>
<?php if (!empty($data_drv)) { ?>
                                        <div class="panel-heading">
                                            <!--<form name="_export_form" id="_export_form" method="post" >
                                                <button type="button" onclick="showExportTypes('service_categories')" >Export</button>
                                            </form>-->
                                        </div>
<?php } ?>
                                </div>
                                <div style="clear:both;"></div>
                                <div class="table-responsive">
                                    <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th align="center" width="3%" style="text-align:center;"><input type="checkbox" id="setAllCheck" ></th>
                                                    <th width="20%">Service Category</th>
                                                    <th>Image</th>
                                                    <th>Display Order</th>
                                                    <th width="8%" align="center" style="text-align:center;">Status</th>

                                                    <th width="8%" align="center" style="text-align:center;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                        <?php
                                                        if (!empty($data_drv)) {
                                                            for ($i = 0; $i < count($data_drv); $i++) {

                                                                $default = '';
                                                                if ($data_drv[$i]['eDefault'] == 'Yes') {
                                                                    $default = 'disabled';
                                                                }
                                                                ?>
                                                        <tr class="gradeA">
                                                            <td align="center" style="text-align:center;"><input type="checkbox" id="checkbox" name="checkbox[]" <?php echo $default; ?> value="<?php echo $data_drv[$i]['iServiceId']; ?>" />&nbsp;</td>
                                                            <td><?= $data_drv[$i]['vServiceName_' . $default_lang]; ?></td>
                                                            <td align="center">
        <?php if (!empty($data_drv[$i]['vImage'])) { ?>
                                                                    <img src="<?= $tconfig["tsite_url"].'resizeImg.php?h=200&src='.$tconfig["tsite_upload_service_categories_images"] . '' . $data_drv[$i]['vImage']; ?>" class="thumbnail" style="max-width: 100px; max-height: 100px;margin:0;"/>
        <?php } ?>
                                                            </td>
                                                            <td><?php echo $data_drv[$i]['iDisplayOrder']; ?></td>

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
                                                                <img src="<?= $dis_img; ?>" alt="<?= $data_drv[$i]['eStatus']; ?>" data-toggle="tooltip" title="<?= $data_drv[$i]['eStatus']; ?>">
                                                            </td>
                                                            <td align="center" style="text-align:center;" class="action-btn001">
                                                                <div class="share-button openHoverAction-class" style="display: block;">
                                                                    <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>
                                                                    <div class="social show-moreOptions leastcol openPops_<?= $data_drv[$i]['iServiceId']; ?>">
                                                                        <ul>
                                                                            <li class="entypo-twitter" data-network="twitter"><a href="service_category_action.php?id=<?= $data_drv[$i]['iServiceId']; ?>" data-toggle="tooltip" title="Edit">
                                                                                    <img src="img/edit-icon.png" alt="Edit">
                                                                                </a></li>
        <?php if ($data_drv[$i]['eDefault'] != 'Yes') { ?>
                                                            <?php if ($userObj->hasPermission('update-status-service-category')) { ?>
                                                                                    <li class="entypo-facebook" data-network="facebook"><a href="javascript:void(0);" onclick="changeStatus('<?php echo $data_drv[$i]['iServiceId']; ?>', 'Inactive')"  data-toggle="tooltip" title="Make Active">
                                                                                            <img src="img/active-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >
                                                                                        </a></li>
                                                                                    <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onclick="changeStatus('<?php echo $data_drv[$i]['iServiceId']; ?>', 'Active')" data-toggle="tooltip" title="Make Inactive">
                                                                                            <img src="img/inactive-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >	
                                                                                        </a></li>
            <?php } ?>
            <?php if($eStatus != 'Deleted' && $deleteAction == 1) {  ?>
                                                                            <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onclick="changeStatusDelete('<?php echo $data_drv[$i]['iServiceId']; ?>')"  data-toggle="tooltip" title="Delete">
                                                                                <img src="img/delete-icon.png" alt="Delete" >
                                                                            </a></li>
            <?php }  ?>
        <?php } ?>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
    <?php }
} else { ?>
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
                                DeliveryAll Service Category module will list all DeliveryAll Service Category on this page.
                            </li>
                            <li>
                                Administrator can Activate / Deactivate / Delete any Service Category. 
                            </li>
                            <li>
                                Administrator can export data in XLS or PDF format.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
        <form name="pageForm" id="pageForm" action="action/service_cat_action.php" method="post" >
            <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
            <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
            <input type="hidden" name="iServiceId" id="iMainId01" value="" >
            <input type="hidden" name="eStatus" id="eStatus" value="<?php echo $eStatus; ?>" >
            <input type="hidden" name="status" id="status01" value="" >
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
        </script>
    </body>
    <!-- END BODY-->
</html>