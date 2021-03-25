<?php
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
//$generalobjAdmin->check_member_login();
if (!$userObj->hasPermission('view-vehicle-type')) {
    $userObj->redirect();
}
if ($default_lang == "") {
    $default_lang = "EN";
}
$script = 'StoreVehicleType';
$eSystem = " AND eType = 'DeliverAll' ";
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$iVehicleCategoryId = isset($_REQUEST['iVehicleCategoryId']) ? $_REQUEST['iVehicleCategoryId'] : "";
$ord = ' ORDER BY vt.vVehicleType_' . $default_lang . ' ASC';
if ($sortby == 1) {
    if ($order == 0)
        $ord = " ORDER BY vt.vVehicleType_" . $default_lang . " ASC";
    else
        $ord = " ORDER BY vt.vVehicleType_" . $default_lang . " DESC";
}
if ($sortby == 2) {
    if ($order == 0)
        $ord = " ORDER BY vt.fDeliveryCharge ASC";
    else
        $ord = " ORDER BY vt.fDeliveryCharge DESC";
}
if ($sortby == 3) {
    if ($order == 0)
        $ord = " ORDER BY vt.fRadius ASC";
    else
        $ord = " ORDER BY vt.fRadius DESC";
}
if ($sortby == 4) {
    if ($order == 0)
        $ord = " ORDER BY vt.iDisplayOrder ASC";
    else
        $ord = " ORDER BY vt.iDisplayOrder DESC";
}
if ($sortby == 5) {
    if ($order == 0)
        $ord = " ORDER BY vt.eStatus ASC";
    else
        $ord = " ORDER BY vt.eStatus DESC";
}
// Start Search Parameters
$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$eType = isset($_REQUEST['eType']) ? stripslashes($_REQUEST['eType']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? stripslashes($_REQUEST['eStatus']) : "";
$ssql = '';
if ($keyword != '') {
    if ($option != '') {
        if ($eStatus != '') {
            $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND vt.eStatus = '" . $eStatus . "'";
        } else {
            $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
        }
    } else {
        if ($eStatus != '') {
            $ssql .= " AND (vt.vVehicleType_" . $default_lang . " LIKE '%" . $keyword . "%' OR vt.fDeliveryCharge LIKE '%" . $keyword . "%' OR vt.fDeliveryChargeCancelOrder LIKE '%" . $keyword . "%' OR vt.fRadius LIKE '%" . $keyword . "%' OR vt.iPersonSize   LIKE '%" . $keyword . "%') AND vt.eStatus = '" . $eStatus . "'";
        } else {
            $ssql .= " AND (vt.vVehicleType_" . $default_lang . " LIKE '%" . $keyword . "%' OR vt.fDeliveryCharge LIKE '%" . $keyword . "%' OR vt.fDeliveryChargeCancelOrder LIKE '%" . $keyword . "%' OR vt.fRadius LIKE '%" . $keyword . "%' OR vt.iPersonSize   LIKE '%" . $keyword . "%')";
        }
    }
} else if ($eStatus != '' && $keyword == '') {
    $ssql .= " AND vt.eStatus = '" . $eStatus . "'";
}
// End Search Parameters
if ($eStatus != '') {
    $eStatussql = "";
} else {
    $eStatussql = " AND vt.eStatus != 'Deleted'";
}
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT count(vt.iVehicleTypeId) AS Total FROM  vehicle_type AS vt WHERE 1 = 1 $eSystem $eStatussql $ssql";

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

$sql = "SELECT vt.*,c.vCountry,ct.vCity,st.vState,lm.vLocationName from vehicle_type as vt left join country as c ON c.iCountryId = vt.iCountryId	left join state as st ON st.iStateId = vt.iStateId 	left join city as ct ON ct.iCityId = vt.iCityId left join location_master as lm ON lm.iLocationId = vt.iLocationid 	where 1 = 1 $eSystem  $ssql $eStatussql $adm_ssql $ord LIMIT $start, $per_page";
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
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME; ?> | <?php echo $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?> </title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />

        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <? include_once('global_files.php'); ?>
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
                                <h2><?php echo $langage_lbl_admin['LBL_Vehicle']; ?> Type</h2>
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
                                    <td width="10%" class=" padding-right10">
                                        <select name="option" id="option" class="form-control">
                                            <option value="">All</option>
                                            <option value="vt.vVehicleType_<?= $default_lang ?>" <?php
                                            if ($option == "vt.vVehicleType_" . $default_lang) {
                                                echo "selected";
                                            }
                                            ?> >Type</option>
                                            <option value="vt.fDeliveryCharge" <?php
                                            if ($option == 'vt.fDeliveryCharge') {
                                                echo "selected";
                                            }
                                            ?> >Delivery Charges Per Order For Completed Orders</option> 
                                            <option value="vt.fDeliveryChargeCancelOrder" <?php
                                            if ($option == 'vt.fDeliveryChargeCancelOrder') {
                                                echo "selected";
                                            }
                                            ?> >Delivery Charges Per Order For Canceled Orders</option>
                                            <option value="vt.fRadius" <?php
                                            if ($option == 'vt.fRadius') {
                                                echo "selected";
                                            }
                                            ?> >Delivery Radius</option>
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
                                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'store_vehicle_type.php'"/>
                                    </td>
                                    <?php if ($userObj->hasPermission('create-vehicle-type')) { ?>
                                        <td width="30%"><a class="add-btn" href="store_vehicle_type_action.php" style="text-align: center;">Add <?php echo $langage_lbl_admin['LBL_Vehicle']; ?> Type</a></td>
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
                                            <?php if ($userObj->hasPermission(['update-status-vehicle-type', 'delete-vehicle-type'])) { ?>
                                                <select name="changeStatus" id="changeStatus" class="form-control" onchange="ChangeStatusAll(this.value);">
                                                    <option value="" >Select Action</option>
                                                    <?php if ($userObj->hasPermission('update-status-vehicle-type')) { ?>
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
                                                            <? } if ($eStatus != 'Deleted' && $userObj->hasPermission('delete-vehicle-type')) { ?>
                                                        <option value="Deleted" <?php
                                                        if ($option == 'Delete') {
                                                            echo "selected";
                                                        }
                                                        ?> >Delete</option>
                                                            <? } ?>
                                                </select>
                                            <?php } ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($data_drv)) { ?>
<!--                                        <div class="panel-heading">
                                            <form name="_export_form" id="_export_form" method="post" >
                                                <button type="button" onclick="showExportTypes('store_vehicle_type')" >Export</button>
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
                                                    <th width="15%"><a href="javascript:void(0);" onClick="Redirect(1,<?php
                                                        if ($sortby == '1') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Type<?php
                                                                           if ($sortby == 1) {
                                                                               if ($order == 0) {
                                                                                   ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?>  <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <th width="10%">Localization</th>
                                                    <th width="20%"><a href="javascript:void(0);" onClick="Redirect(2,<?php
                                                        if ($sortby == '2') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Delivery Charges Per Order For Completed Orders <?php
                                                            if ($sortby == 2) {
                                                                if ($order == 0) {
                                                                    ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                            }
                                                        } else {
                                                            ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <th width="20%"><a href="javascript:void(0);" onClick="Redirect(2,<?php
                                                                           if ($sortby == '2') {
                                                                               echo $order;
                                                                           } else {
                                                                               ?>0<?php } ?>)">Delivery Charges Per Order For Cancelled Orders <?php
                                                            if ($sortby == 2) {
                                                                if ($order == 0) {
                                                                                   ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                            }
                                                        } else {
                                                            ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <th width="20%"><a href="javascript:void(0);" onClick="Redirect(3,<?php
                                                            if ($sortby == '3') {
                                                                echo $order;
                                                            } else {
                                                                ?>0<?php } ?>)">Delivery Radius <?php
                                                        if ($sortby == 3) {
                                                            if ($order == 0) {
                                                                ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                               }
                                                                           } else {
                                                                               ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <th width="20%"><a href="javascript:void(0);" onClick="Redirect(4,<?php
                                                            if ($sortby == '4') {
                                                                echo $order;
                                                            } else {
                                                                               ?>0<?php } ?>)">Order <?php
                                                                       if ($sortby == 4) {
                                                                           if ($order == 0) {
                                                                               ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <th width="20%"><a href="javascript:void(0);" onClick="Redirect(5,<?php
                                                            if ($sortby == '5') {
                                                                echo $order;
                                                            } else {
                                                                ?>0<?php } ?>)">Status <?php
                                                if ($sortby == 5) {
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
                                                            <td align="center" style="text-align:center;"><input type="checkbox" id="checkbox" name="checkbox[]" <?php echo $default; ?> value="<?php echo $data_drv[$i]['iVehicleTypeId']; ?>" />&nbsp;</td>
                                                            <td><?= $data_drv[$i]['vVehicleType_' . $default_lang] ?></td>
                                                                <?php if (($data_drv[$i]['iLocationid'] == "-1")) { ?>
                                                                <td>All Locations</td>
                                                                <?php } else { ?>
                                                                <td style="text-transform: capitalize;"><?= $data_drv[$i]['vLocationName']; ?></td>
                                                                <?php } ?>
                                                            <td><?= $data_drv[$i]['fDeliveryCharge'] ?></td>
                                                            <td><?= $data_drv[$i]['fDeliveryChargeCancelOrder'] ?></td>
                                                            <td><?= $data_drv[$i]['fRadius'] ?></td>
                                                            <td><?= $data_drv[$i]['iDisplayOrder'] ?></td>
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
                                                            <td align="center" style="text-align:center;" class="action-btn001">
                                                                <div class="share-button openHoverAction-class" style="display: block;">
                                                                    <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>
                                                                    <div class="social show-moreOptions for-two openPops_<?= $data_drv[$i]['iVehicleTypeId']; ?>">
                                                                        <ul>
                                                                            <li class="entypo-twitter" data-network="twitter"><a href="store_vehicle_type_action.php?id=<?= $data_drv[$i]['iVehicleTypeId']; ?>" data-toggle="tooltip" title="Edit">
                                                                                    <img src="img/edit-icon.png" alt="Edit">
                                                                                </a></li>
                                                                            <?php if ($userObj->hasPermission('update-status-vehicle-type')) { ?>
                                                                                <li class="entypo-facebook" data-network="facebook"><a href="javascript:void(0);" onClick="changeStatus('<?php echo $data_drv[$i]['iVehicleTypeId']; ?>', 'Inactive')"  data-toggle="tooltip" title="Activate"><img src="img/active-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" ></a></li>

                                                                                <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onClick="changeStatus('<?php echo $data_drv[$i]['iVehicleTypeId']; ?>', 'Active')" data-toggle="tooltip" title="Deactivate">
                                                                                        <img src="img/inactive-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >
                                                                                    </a></li>
                                                        <? } if ($eStatus != 'Deleted' && $userObj->hasPermission('delete-vehicle-type')) { ?>

                                                                                <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onclick="changeStatusDelete('<?php echo $data_drv[$i]['iVehicleTypeId']; ?>')"data-toggle="tooltip" title="Delete">
                                                                                        <img src="img/delete-icon.png" alt="Delete" >
                                                                                    </a></li>
            <?php
        }
        ?>
                                                                        </ul>
                                                                    </div>
                                                                </div>                           
                                                            </td>
                                                        </tr>    
    <?
    }
} else {
    ?>
                                                    <tr class="gradeA">
                                                        <td colspan="12"> No Records Found.</td>
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
<?php echo $langage_lbl_admin['LBL_Vehicle']; ?> Type module will list all <?php echo $langage_lbl_admin['LBL_Vehicle']; ?> types on this page.
                            </li>

                            <li>
                                Administrator can Edit / Delete any <?php echo $langage_lbl_admin['LBL_Vehicle']; ?> type. 
                            </li>

                          <!--   <li>
                                Administrator can export data in XLS or PDF format.
                            </li> -->
                        </ul>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->

        <form name="pageForm" id="pageForm" action="action/store_vehicle_type.php" method="post" >
            <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
            <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
            <input type="hidden" name="iVehicleTypeId" id="iMainId01" value="" >
            <input type="hidden" name="status" id="status01" value="" >
            <input type="hidden" name="eStatus" id="eStatus" value="<?= $eStatus ?>">
            <input type="hidden" name="statusVal" id="statusVal" value="" >
            <input type="hidden" name="option" value="<?php echo $option; ?>" >
            <input type="hidden" name="keyword" value="<?php echo $keyword; ?>" >
            <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
            <input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
            <input type="hidden" name="method" id="method" value="">
        </form>
<?php include_once('footer.php'); ?>
        <script>
            $(document).ready(function () {
                $('#eType_options').hide();
                $('#option').each(function () {
                    if (this.value == 'vt.eType') {
                        $('#eType_options').show();
                        $('.searchform').hide();
                    }
                });
            });

            $(function () {
                $('#option').change(function () {
                    if ($('#option').val() == 'vt.eType') {
                        $('#eType_options').show();
                        $("input[name=keyword]").val("");
                        $('.searchform').hide();
                    } else {
                        $('#eType_options').hide();
                        $("#eType_value").val("");
                        $('.searchform').show();
                    }
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