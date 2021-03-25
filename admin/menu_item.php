<?php
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
if (!$userObj->hasPermission('view-item')) {
    $userObj->redirect();
}
$script = 'MenuItems';
$menu_itemid = isset($_REQUEST['menu_itemid']) ? $_REQUEST['menu_itemid'] : "";
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY mi.iMenuItemId DESC';
if ($sortby == 1) {
    if ($order == 0)
        $ord = " ORDER BY mi.vItemType_" . $default_lang . " ASC";
    else
        $ord = " ORDER BY mi.vItemType_" . $default_lang . " DESC";
}
if ($sortby == 2) {
    if ($order == 0)
        $ord = " ORDER BY c.vCompany ASC";
    else
        $ord = " ORDER BY c.vCompany DESC";
}
if ($sortby == 3) {
    if ($order == 0)
        $ord = " ORDER BY f.vMenu_" . $default_lang . " ASC";
    else
        $ord = " ORDER BY f.vMenu_" . $default_lang . " DESC";
}

if ($sortby == 4) {
    if ($order == 0)
        $ord = " ORDER BY mi.iDisplayOrder ASC";
    else
        $ord = " ORDER BY mi.iDisplayOrder DESC";
}

if ($sortby == 4) {
    if ($order == 0)
        $ord = " ORDER BY mi.eStatus ASC";
    else
        $ord = " ORDER BY mi.eStatus DESC";
}

//End Sorting
// Start Search Parameters
$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');

$ssql = '';
if ($keyword != '') {
    if ($option != '') {
        if ($eStatus != '') {
            $ssql .= " AND " . stripslashes($option) . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%' AND mi.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";
        } else {
            $ssql .= " AND " . stripslashes($option) . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%'";
        }
    } else {
        if ($eStatus != '') {
            $ssql .= " AND (f.vMenu_" . $default_lang . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR mi.vItemType_" . $default_lang . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%') AND mi.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";
        } else {
            $ssql .= " AND (f.vMenu_" . $default_lang . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR mi.vItemType_" . $default_lang . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%')";
        }
    }
} else if ($eStatus != '' && $keyword == '') {
    $ssql .= " AND mi.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";
}


// End Search Parameters
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
if ($eStatus != '') {
    $eStatussql = " AND c.eStatus != 'Deleted' AND f.eStatus != 'Deleted'";
} else {
    $eStatussql = " AND c.eStatus != 'Deleted' AND f.eStatus != 'Deleted' AND mi.eStatus != 'Deleted'";
}

if (!empty($menu_itemid)) {
    $ssql .= " AND f.iFoodMenuId = '" . $menu_itemid . "'";
}


$cmp_name = "";
if ($menu_itemid != "") {
    $ssql .= " AND f.iFoodMenuId='" . $menu_itemid . "'";

    $sql = "select vMenu_" . $default_lang . " from food_menu where iFoodMenuId = '" . $menu_itemid . "'";
    $data_cmp1 = $obj->MySQLSelect($sql);

    $cmp_name = $data_cmp1[0]['vMenu_' . $default_lang];
    $keyword = $cmp_name;
}
$ssql .= " AND c.iServiceId IN(".$enablesevicescategory.")";
$sql = "SELECT COUNT(mi.iMenuItemId) AS Total FROM menu_items as mi INNER JOIN food_menu f ON f.iFoodMenuId = mi.iFoodMenuId INNER JOIN company c ON f.iCompanyId = c.iCompanyId WHERE 1 = 1  $eStatussql $ssql $dri_ssql";
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;

//-------------if page is setcheck------------------//
$start = 0;
    $end = $per_page;
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];
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

$sql = "SELECT mi.*,f.vMenu_" . $default_lang . ",c.vCompany,c.iCompanyId FROM  `menu_items` as mi INNER JOIN food_menu f ON f.iFoodMenuId = mi.iFoodMenuId INNER JOIN company as c on c.iCompanyId=f.iCompanyId  WHERE 1=1 $eStatussql $ssql $dri_ssql $ord LIMIT $start, $per_page";
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
        <title><?= $SITE_NAME ?> |  Items</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
<?php include_once('global_files.php'); ?>
    </head>
    <body class="padTop53">
        <div id="wrap">
<?php include_once('header.php'); ?>
<?php include_once('left_menu.php'); ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div id="add-hide-show-div">
                        <div class="row">
                            <div class="col-lg-12">
<?
$category_name = ($cmp_name != "") ? " of " . $cmp_name : "";
?>
                                <h2><?php echo 'Items' . $category_name; ?></h2>
                            </div>
                        </div>
                        <hr />
                    </div>
<?php include('valid_msg.php'); ?>
                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                        <input type="hidden" name="iMenuItemId" value="<?php echo $iMenuItemId; ?>" >
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                            <tbody>
                                <tr>
                                    <td width="5%"><label for="textfield"><strong>Search:</strong></label></td>
                                    <td width="10%" class="padding-right10">
                                        <select name="option" id="option" class="form-control"> 
                                            <option value="">All</option>
                                            <option  value="mi.vItemType_<?= $default_lang ?>" <?php if ($option == "mi.vItemType_$default_lang") {
    echo "selected";
} ?> >Item</option>
                                            <option  value="f.vMenu_<?= $default_lang ?>" <?php if ($option == "f.vMenu_$default_lang" || ($menu_itemid != "" && $category_name != "")) {
    echo "selected";
} ?> >Item Category</option>
                                            <option  value="c.vCompany" <?php if ($option == "c.vCompany") {
    echo "selected";
} ?> ><?= $langage_lbl_admin['LBL_STORE_NAME']; ?></option>
                                            <option value="prescription_required">Prescription Needed</option><!--For Prescription required added by Sneha-->
                                        </select>
                                    </td>
                                    <td width="15%" class="searchform"><input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"  class="form-control" /></td>
                                    <td width="12%" class="estatus_options" id="eStatus_options" >
                                        <select name="eStatus" id="estatus_value" class="form-control">
                                            <option value="" >Select Status</option>
                                            <option value="Active" <?php if ($eStatus == 'Active') {
    echo "selected";
} ?> >Active</option>
                                            <option value="Inactive" <?php if ($eStatus == 'Inactive') {
    echo "selected";
} ?> >Inactive</option>
                                            <option value="Deleted" <?php if ($eStatus == 'Deleted') {
    echo "selected";
} ?> >Delete</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'menu_item.php'"/>
                                    </td>
<?php if ($userObj->hasPermission('create-item')) { ?>
                                        <td width="30%"><a class="add-btn" href="menu_item_action.php" style="text-align: center;">Add Item</a></td>
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
                                                <?php if ($userObj->hasPermission(['update-status-item', 'delete-item'])) { ?>
                                                <select name="changeStatus" id="changeStatus" class="form-control" onChange="ChangeStatusAll(this.value);">
                                                    <option value="" >Select Action</option>
    <?php if ($userObj->hasPermission('update-status-item')) { ?>
                                                        <option value="Active" <?php if ($option == 'Active') {
            echo "selected";
        } ?> >Activate</option>
                                                        <option value="Inactive" <?php if ($option == 'Inactive') {
            echo "selected";
        } ?> >Deactivate</option>
                                        <?php } ?>
                                        <?php if ($eStatus != 'Deleted' && $userObj->hasPermission('delete-item')) { ?>
                                                        <option value="Deleted" <?php if ($option == 'Delete') {
                                        echo "selected";
                                    } ?> >Delete</option>
    <?php } ?>
                                                </select>
<?php } ?>
                                        </span>
                                    </div>
<?php if (!empty($data_drv)) { ?>
                                        <div class="panel-heading">
                                            <form name="_export_form" id="_export_form" method="post" >
                                                <button type="button" onClick="showExportTypes('MenuItems')" >Export</button>
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
                                                    <th width="3%" class="align-center"><input type="checkbox" id="setAllCheck" ></th>
                                                    <th width="13%"><a href="javascript:void(0);" onClick="Redirect(1,<?php if ($sortby == '1') {
    echo $order;
} else { ?>0<?php } ?>)">Item <?php if ($sortby == 1) {
                                                    if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                        } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                    <th width="13%"><a href="javascript:void(0);" onClick="Redirect(3,<?php if ($sortby == '3') {
                                            echo $order;
                                        } else { ?>0<?php } ?>)">Item Category <?php if ($sortby == 3) {
                                            if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                        } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                    <th width="18%"><a href="javascript:void(0);" onClick="Redirect(2,<?php if ($sortby == '2') {
                                                        echo $order;
                                                    } else { ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_STORE_NAME']; ?> <?php if ($sortby == 2) {
                                                        if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                            } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                            <!-- <th width="18%" class="align-center">Item Image</th> -->

                                                    <th width="8%"><a href="javascript:void(0);" onClick="Redirect(4,<?php if ($sortby == '4') {
                                                echo $order;
                                            } else { ?>0<?php } ?>)">Display Order <?php if ($sortby == 4) {
                                                            if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                    <th width="12%" class="align-center"><a href="javascript:void(0);" onClick="Redirect(5,<?php if ($sortby == '5') {
                                                            echo $order;
                                                        } else { ?>0<?php } ?>)">Status <?php if ($sortby == 5) {
                                                            if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                        } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <th width="8%" class="align-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?
if (!empty($data_drv)) {
    for ($i = 0; $i < count($data_drv); $i++) {
        ?>
                                                        <tr class="gradeA" >
                                                            <td align="center"><input type="checkbox" id="checkbox" name="checkbox[]" <?php echo $default; ?> value="<?php echo $data_drv[$i]['iMenuItemId']; ?>" />&nbsp;</td>
                                                            <td><?= $data_drv[$i]['vItemType_' . $default_lang]; ?></td>
                                                            <td><?= $generalobjAdmin->clearCmpName($data_drv[$i]['vMenu_' . $default_lang]); ?></td>
                                                            <td><?= $generalobjAdmin->clearCmpName($data_drv[$i]['vCompany']); ?></td>
        <!-- <td align="center">
                                                                            <?
                                                                            $imgpth = $tconfig["tsite_upload_images_menu_item_path"] . '/' . $data_drv[$i]['vImage'];
                                                                            $imgUrl = $tconfig["tsite_upload_images_menu_item"] . '/' . $data_drv[$i]['vImage'];
                                                                            if ($data_drv[$i]['vImage'] != "" && file_exists($imgpth)) {
                                                                                ?>
            <img src="<?php echo $imgUrl; ?>" alt="Image preview" class="thumbnail" style="max-width: 100px; max-height: 100px;margin:0;">
        <?php } ?>
        </td> -->
                                                            <td><?= $data_drv[$i]['iDisplayOrder']; ?></td>
                                                            <td align="center">
                                                                            <?
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

                                                            <td align="center" class="action-btn001">
                                                                <div class="share-button openHoverAction-class" style="display: block;">
                                                                    <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>
                                                                    <div class="social show-moreOptions for-five openPops_<?= $data_drv[$i]['iMenuItemId']; ?>">
                                                                        <ul>
                                                                            <li class="entypo-twitter" data-network="twitter"><a href="menu_item_action.php?id=<?= $data_drv[$i]['iMenuItemId']; ?>" data-toggle="tooltip" title="Edit">
                                                                                    <img src="img/edit-icon.png" alt="Edit">
                                                                                </a></li>
        <?php if ($data_drv[$i]['eDefault'] != 'Yes') { ?>
            <?php if ($userObj->hasPermission('update-status-item')) { ?>
                                                                                    <li class="entypo-facebook" data-network="facebook"><a href="javascript:void(0);" onClick="changeStatus('<?php echo $data_drv[$i]['iMenuItemId']; ?>', 'Inactive')"  data-toggle="tooltip" title="Activate">
                                                                                            <img src="img/active-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >
                                                                                        </a></li>
                                                                                    <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onClick="changeStatus('<?php echo $data_drv[$i]['iMenuItemId']; ?>', 'Active')" data-toggle="tooltip" title="Deactivate">
                                                                                            <img src="img/inactive-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >	
                                                                                        </a></li>
            <?php } ?>
            <?php if ($eStatus != 'Deleted' && $userObj->hasPermission('delete-item')) { ?>

                <?php if (!in_array($data_drv[$i]['iCompanyId'], $DEMO_NOT_DEL_COMPANY_ID)) { ?>   

                                                                                        <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onClick="changeStatusDelete('<?php echo $data_drv[$i]['iMenuItemId']; ?>')"  data-toggle="tooltip" title="Delete"> <img src="img/delete-icon.png" alt="Delete" > </a></li>


                <?php }
            } ?>
            <?php if (SITE_TYPE == 'Demo') { ?>
                                                                                    <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onClick="resetTripStatus('<?php echo $data_drv[$i]['iMenuItemId']; ?>')"  data-toggle="tooltip" title="Reset"> <img src="img/reset-icon.png" alt="Reset"> </a></li>
            <?php } ?>
                <?php } ?>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </td>

                                                        </tr>
    <?php }
} else { ?>
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
        <form name="pageForm" id="pageForm" action="action/menu_item.php" method="post" >
            <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
            <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
            <input type="hidden" name="iMenuItemId" id="iMainId01" value="" >
            <input type="hidden" name="iCompanyId" id="iCompanyId" value="<?php echo $iCompanyId; ?>" >
            <input type="hidden" name="menu_itemid" id="menu_itemid" value="<?php echo $menu_itemid; ?>" >
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

            //For Prescription required start added by Sneha
            $("#option").on('change', function (e) {
                var option_value = $(this).val();
                if (option_value == 'prescription_required') {
                    $("#keyword").val('Yes');
                }
            });
            //For Prescription required end added by Sneha

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
