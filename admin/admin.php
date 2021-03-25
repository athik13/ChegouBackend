<?php
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
if (!$userObj->hasRole(1) && !$userObj->hasPermission('view-admin')) {
    $userObj->redirect();
}
$script = 'Admin';
$query = Models\Administrator::with(['roles', 'locations']);
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
switch ($sortby) {
    case 1:
    $query->orderBy('vFirstName', $order);
    break;
    case 2:
    $query->orderBy('vEmail', $order);
    break;
    case 3:
        //   $query->orderBy('iGroupId', $order);
    break;
    case 4:
    $query->orderBy('eStatus', $order);
    break;

    default:
    break;
}
$ssql = '';
if(ONLYDELIVERALL=='Yes') {
    $ssql .= " AND iGroupId != 4";
}
$role_sql = "select * from admin_groups where eStatus = 'Active'".$ssql;
$role_sql_data = $obj->MySQLSelect($role_sql);
$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
$eRole = isset($_REQUEST['eRole']) ? stripslashes($_REQUEST['eRole']) : "";
if (!empty($keyword)) {
    if (!empty($option)) {
        if ($option == 'eStatus') {
            //$query->where('eStatus', $StatusValue);
        } else if ($option == "concat(vFirstName,' ',vLastName)") {
            $query->where(DB::raw('concat(`vFirstName`," ",`vLastName`)'), "LIKE", "%{$keyword}%");
        } else if ($option == "vContactNo") {
            $query->where('vContactNo', "LIKE", "%{$keyword}%");
        } else if ($option == "vEmail") {
            $query->where('vEmail', "LIKE", "%{$keyword}%");
        } else if ($option == "vGroup") {
            $sql = "SELECT iGroupId FROM admin_groups WHERE vGroup LIKE '%" . $keyword . "%' AND eStatus != 'Deleted'";
            $totalData = $obj->MySQLSelect($sql);
            $iGroupIdArr = array();
            if (count($totalData) > 0) {
                for ($t = 0; $t < count($totalData); $t++) {
                    $iGroupIdArr[$t] = $totalData[$t]['iGroupId'];
                    $query->orWhere('iGroupId', $totalData[$t]['iGroupId']);
                }
            } elseif (count($totalData) == 0) {  //changed by me
                $query->orWhere('iGroupId', '');
            }
        } else {
            $query->where(function($q) use ($keyword) {
                $q->where(DB::raw('concat(`vFirstName`," ",`vLastName`)'), "LIKE", "%{$keyword}%");
                $q->orWhere('vEmail', "LIKE", "%{$keyword}%");
                $q->orwhere('vContactNo', "LIKE", "%{$keyword}%");
            });
            $sql = "SELECT iGroupId FROM admin_groups WHERE vGroup LIKE '%" . $keyword . "%' AND eStatus != 'Deleted'";
            $totalData = $obj->MySQLSelect($sql);
            $iGroupIdArr = array();
            if (count($totalData) > 0) {
                for ($t = 0; $t < count($totalData); $t++) {
                    $iGroupIdArr[$t] = $totalData[$t]['iGroupId'];
                    $query->orWhere('iGroupId', $totalData[$t]['iGroupId']);
                }
            }
        }
    } else {
        $query->where(function($q) use ($keyword) {
            $q->where(DB::raw('concat(`vFirstName`," ",`vLastName`)'), "LIKE", "%{$keyword}%");
            $q->orWhere('vEmail', "LIKE", "%{$keyword}%");
            $q->orwhere('vContactNo', "LIKE", "%{$keyword}%");
            //$q->orwhere('eStatus', "LIKE", "%{$keyword}%");
        });
        $sql = "SELECT iGroupId FROM admin_groups WHERE vGroup LIKE '%" . $keyword . "%' AND eStatus != 'Deleted'";
        $totalData = $obj->MySQLSelect($sql);
        $iGroupIdArr = array();
        if (count($totalData) > 0) {
            for ($t = 0; $t < count($totalData); $t++) {
                $iGroupIdArr[$t] = $totalData[$t]['iGroupId'];
                $query->orWhere('iGroupId', $totalData[$t]['iGroupId']);
            }
        }
    }
} else {
    if ($option == 'eStatus') {
        $query->where('eStatus', $StatusValue);
    }
}
if($eRole != '')
{
    $query->where('iGroupId', $eRole);
}
if ($eStatus != '') {
    $query->where('eStatus', $eStatus);
}
if (!$userObj->hasRole(1)) {
    $query->where('iGroupId', $userObj->role_id);
}
if ($option != 'eStatus' && $eStatus != 'Deleted') {
    $query->where('eStatus', '!=', "Deleted");
}
$hotelPanel = (isHotelPanelEnable()) ? "Yes" : "No"; 
$kioskPanel = (isKioskPanelEnable()) ? "Yes" : "No";

if(ONLYDELIVERALL=='Yes' || $hotelPanel == "No" || $kioskPanel == "No") {
    $query->where('iGroupId', '!=', "4");
}

$per_page = $DISPLAY_RECORD_NUMBER;
//Added By HJ On 18-10-2019 For Get Admin Data By Pagination Start
$start = 0;
if (isset($_REQUEST['page']) && $_REQUEST['page']) {
    $start = ($_REQUEST['page'] - 1) * $per_page;
}
//Added By HJ On 18-10-2019 For Get Admin Data By Pagination End
$query->take($per_page);
//exit;
$total_results = $query->count();
$total_pages = ceil($total_results / $per_page);
$data_drv = $query->offset($start)->get();
$endRecord = $data_drv->count();
$var_filter = "";
$end = $per_page;
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    }
}
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages = $total_pages;
if ($page <= 0)
    $page = 1;
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
                            <h2>Admin</h2>
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
                                <td width="1%"><label for="textfield"><strong>Search:</strong></label></td>
                                <td width="8%" class=" padding-right10"><select name="option" id="option" class="form-control" onChange="return changeMyStatus(this);">
                                    <option value="">All</option>
                                    <option value="concat(vFirstName,' ',vLastName)" <?php
                                    if ($option == "concat(vFirstName,' ',vLastName)") {
                                        echo "selected";
                                    }
                                    ?> >Name</option>
                                    <option value="vEmail" <?php
                                    if ($option == 'vEmail') {
                                        echo "selected";
                                    }
                                    ?> >E-mail</option>
                                    <option value="vGroup" <?php
                                    if ($option == 'vGroup') {
                                        echo "selected";
                                    }
                                    ?> >Role</option>
                                            <!--<option value="vContactNo" <?php
                                            if ($option == 'vContactNo') {
                                                echo "selected";
                                            }
                                            ?> >Mobile</option>
                                            <option value="eStatus" <?php
                                            if ($option == 'eStatus') {
                                                echo "selected";
                                            }
                                            ?> >Status</option>-->
                                        </select>
                                    </td>
                                    <td width="10%">
                                        <input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"  class="form-control" />
                                    </td>
                                    <td width="10%">
                                        <select name="eStatus" id="StatusValue" class="form-control">
                                            <option value="">Select Status</option>
                                            <option value="Active" <?php
                                            if ($eStatus == 'Active') {
                                                echo "selected";
                                            }
                                            ?>>Active</option>
                                            <option value="Inactive" <?php
                                            if ($eStatus == 'Inactive') {
                                                echo "selected";
                                            }
                                            ?>>Inactive</option>
                                            <option value="Deleted" <?php
                                            if ($eStatus == 'Deleted') {
                                                echo "selected";
                                            }
                                            ?>>Deleted</option>
                                        </select>
                                    </td>
                                    <td width="15%">
                                        <select name="eRole" id="RoleValue" class="form-control">
                                            <option value="">Select Role</option>
                                            <?php foreach ($role_sql_data as $role_value) { ?>
                                                <option value="<?php echo $role_value['iGroupId']; ?>" <?php if($eRole == $role_value['iGroupId']) echo "selected"; ?>><?php echo $role_value['vGroup']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td width="15%">
                                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'admin.php'"/>
                                    </td>
                                    <?php if ($userObj->hasPermission('create-admin')) { ?>
                                        <td width="30%"><a class="add-btn" href="admin_action.php" style="text-align: center;">Add</a></td>
                                    <?php } ?>
                                </tr>
                            </tbody>
                        </table>

                    </form>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="admin-nir-export">
                                    <div class="changeStatus col-lg-6 option-box-left">
                                        <span class="col-lg-3 new-select001">
                                            <?php if ($userObj->hasPermission(['update-status-admin', 'delete-admin'])) { ?>
                                                <select name="changeStatus" id="changeStatus" class="form-control" onChange="ChangeStatusAll(this.value);">
                                                    <option value="" >Select Action</option>
                                                    <?php if ($userObj->hasPermission('update-status-admin')) { ?>
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
                                                    <?php if ($userObj->hasPermission('delete-admin')) { ?>
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
                                    <?php if (!empty($data_drv)) { ?>
                                        <!--<div class="panel-heading">
                                            <form name="_export_form" id="_export_form" method="post" >
                                                <button type="button" onClick="showExportTypes('admin')" >Export</button>
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

                                                    <th width="20%"><a href="javascript:void(0);" onClick="Redirect(1,<?php
                                                    if ($sortby == '1') {
                                                        echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Name <?php
                                                            if ($sortby == 1) {
                                                             if ($order == 0) {
                                                                 ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                             }
                                                         } else {
                                                            ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                            <th width="20%"><a href="javascript:void(0);" onClick="Redirect(2,<?php
                                                            if ($sortby == '2') {
                                                                echo $order;
                                                                } else {
                                                                    ?>0<?php } ?>)">Email <?php
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
                                                                            ?>0<?php } ?>)">Roles <?php
                                                                            if ($sortby == 3) {
                                                                             if ($order == 0) {
                                                                                 ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                             }
                                                                         } else {
                                                                            ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                                            <!--  <th width="15%"><a href="javascript:void(0);" >Locations </th> -->
                                                                                
                                                                                <th width="8%" align="center" style="text-align:center;"><a href="javascript:void(0);" onClick="Redirect(4,<?php
                                                                                if ($sortby == '4') {
                                                                                    echo $order;
                                                                                    } else {
                                                                                        ?>0<?php } ?>)">Status <?php
                                                                                        if ($sortby == 4) {
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


                                                                                            foreach ($data_drv as $key => $row) {
                                                                                                $default = '';
                                                                                                if ($_SESSION['sess_iAdminUserId'] == $row['iAdminId']) {
                                                                                                    $default = 'disabled';
                                                                                                }
                                                                                                if ($eStatus == '' && $row['eStatus'] == 'Deleted') {
                                                                                                    continue;
                                                                                                }
                                                                                                if ($eStatus != '' && $eStatus != $row['eStatus']) {
                                                                                                    continue;
                                                                                                }
                                                                                                ?>
                                                                                                <tr class="gradeA">
                                                                                                    <?php if (($_SESSION['sess_iAdminUserId'] == $row['iAdminId']) || $row['eDefault'] == 'Yes') { ?>
                                                                                                        <td align="center" style="text-align:center;"></td>
                                                                                                    <?php } else { ?>
                                                                                                        <td align="center" style="text-align:center;"><input type="checkbox" id="checkbox" name="checkbox[]" <?php echo $default; ?> value="<?php echo $row['iAdminId']; ?>" />&nbsp;</td>
                                                                                                    <?php } ?>


                                                                                                    <td><?= $generalobjAdmin->clearName($row['vFirstName'] . ' ' . $row['vLastName']); ?></td>
                                                                                                    <td><?= $generalobjAdmin->clearEmail($row['vEmail']); ?></td>
                                                                                                    <td><?= $row->roles->vGroup; ?></td>
                                                                                                    <!-- <td><?php echo implode(', ', ($row->locations->pluck('vLocationName')->toArray())) ?></td> -->
                                                                                                    <!--  <td><?= $generalobjAdmin->clearPhone($row['vContactNo']); ?></td> -->
                                                                                                    <td align="center" style="text-align:center;">
                                                                                                        <?php
                                                                                                        if ($row['eStatus'] == 'Active') {
                                                                                                            $dis_img = "img/active-icon.png";
                                                                                                        } else if ($row['eStatus'] == 'Inactive') {
                                                                                                            $dis_img = "img/inactive-icon.png";
                                                                                                        } else if ($row['eStatus'] == 'Deleted') {
                                                                                                            $dis_img = "img/delete-icon.png";
                                                                                                        }
                                                                                                        ?>
                                                                                                        <img src="<?= $dis_img; ?>" alt="image" data-toggle="tooltip" title="<?php echo $row['eStatus']; ?>">
                                                                                                    </td>
                                                                                                    <td align="center" style="text-align:center;" class="action-btn001">
                                                                                                        <?php if (($_SESSION['sess_iAdminUserId'] == $row['iAdminId']) || $row['eDefault'] == 'Yes') { ?>
                                                                                                            <a href="admin_action.php?id=<?= $row['iAdminId']; ?>" data-toggle="tooltip" title="Edit">
                                                                                                                <img src="img/edit-icon.png" alt="Edit">
                                                                                                            </a>
                                                                                                        <?php } else { ?>
                                                                                                            <div class="share-button share-button4 openHoverAction-class" style="display: block;">
                                                                                                                <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>
                                                                                                                <div class="social show-moreOptions openPops_<?= $row['iAdminId']; ?>">
                                                                                                                    <ul>
                                                                                                                        <li class="entypo-twitter" data-network="twitter"><a href="admin_action.php?id=<?= $row['iAdminId']; ?>" data-toggle="tooltip" title="Edit">
                                                                                                                            <img src="img/edit-icon.png" alt="Edit">
                                                                                                                        </a></li>
                                                                                                                        <?php if ($userObj->hasPermission('update-status-admin')) { ?>
                                                                                                                            <li class="entypo-facebook" data-network="facebook"><a href="javascript:void(0);" onClick="changeStatus('<?php echo $row['iAdminId']; ?>', 'Inactive')"  data-toggle="tooltip" title="Activate">
                                                                                                                                <img src="img/active-icon.png" alt="<?php echo $row['eStatus']; ?>" >
                                                                                                                            </a></li>
                                                                                                                            <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onClick="changeStatus('<?php echo $row['iAdminId']; ?>', 'Active')" data-toggle="tooltip" title="Deactivate">
                                                                                                                                <img src="img/inactive-icon.png" alt="<?php echo $row['eStatus']; ?>" >    
                                                                                                                            </a></li>
                                                                                                                        <?php } ?>
                                                                                                                        <?php if ($userObj->hasPermission('delete-admin') && $_SESSION['sess_iAdminUserId'] != $row['iAdminId']) { ?>
                                                                                                                            <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onClick="changeStatusDelete('<?php echo $row['iAdminId']; ?>')"  data-toggle="tooltip" title="Delete">
                                                                                                                                <img src="img/delete-icon.png" alt="Delete" >
                                                                                                                            </a></li>
                                                                                                                        <?php } ?>

                                                                                                                    </ul>
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        <?php } ?>
                                                                                                    </td>
                                                                                                </tr>
                                                                                                <?php
                                                                                            }
                                                                                        } else {
                                                                                            ?>
                                                                                            <tr class="gradeA">
                                                                                                <td colspan="7"><?php echo $langage_lbl_admin['LBL_NO_RECORDS_FOUND1']; ?></td>
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
                                                                        Administrator module will list all hotel users on this page.
                                                                    </li>
                                                                    <li>
                                                                        Administrator can Activate , Deactivate , Delete any other hotel users.
                                                                    </li>
                                                                    <li>
                                                                        Super Admin cannot be Activated , Deactivated or Deleted.
                                                                    </li>
                            <!--li>
                                    "Export by Search Data" will export only search result data in XLS or PDF format.
                                </li-->
                            </ul>
                        </div>
                    </div>
                </div>
                <!--END PAGE CONTENT -->
            </div>
            <!--END MAIN WRAPPER -->

            <form name="pageForm" id="pageForm" action="action/admin.php" method="post" >
                <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
                <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
                <input type="hidden" name="iAdminId" id="iMainId01" value="" >
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


            // function changeMyStatus(data){
            // var option = $(data).val();
            // if(option == 'eStatus'){
            // $('#StatusValue').show();
            // $('#keyword').hide();
            // $('#keyword').val('');

            // }else{
            // $('#StatusValue').hide();
            // $('#StatusValue').val('');
            // $('#keyword').show();
            // }
            // }

            // <?php if ($option == 'eStatus') { ?>
                // $('#changeMyStatusId').show();
                // $('#keyword').hide();
                // <?php } else { ?>
                // $('#changeMyStatusId').hide();
                // $('#keyword').show();
                // <?php } ?>
            </script>
        </body>
        <!-- END BODY-->
        </html>