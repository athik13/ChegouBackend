<?php
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}

define("USER_PROFILE_MASTER", "user_profile_master");
if (!$userObj->hasPermission('view-user-profile')) {
    $userObj->redirect();
}
if ($default_lang == "") {
    $default_lang = "EN";
}
$script = 'RideProfileType';
//Start Sorting

$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$iUserProfileMasterId = isset($_REQUEST['iUserProfileMasterId']) ? $_REQUEST['iUserProfileMasterId'] : "";
// Start Search Parameters
$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$eType = isset($_REQUEST['eType']) ? stripslashes($_REQUEST['eType']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
// End Search Parameters
//Pagination Start
$ssql = '';
if ($keyword != '') {
    if ($option != '') {
        if ($iUserProfileMasterId != '') {
            $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND iUserProfileMasterId = '" . $iUserProfileMasterId . "'";
        } else {
            $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
        }
    } else {
        if ($iUserProfileMasterId != '') {
            $ssql .= " AND (vShortProfileName LIKE '%" . $keyword . "%' OR (vProfileName LIKE '%" . $keyword . "%' OR vTitle LIKE '%" . $keyword . "%' OR vSubTitle LIKE '%" . $keyword . "%') AND iUserProfileMasterId = '" . $iUserProfileMasterId . "'";
        } else {
            $ssql .= " AND (vShortProfileName LIKE '%" . $keyword . "%' OR (vProfileName LIKE '%" . $keyword . "%' OR vTitle LIKE '%" . $keyword . "%' OR vSubTitle   LIKE '%" . $keyword . "%')";
        }
    }
}
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT count(iUserProfileMasterId) AS Total FROM  " . USER_PROFILE_MASTER . " WHERE eStatus !='Deleted' $ssql";
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
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
$sql = "SELECT * FROM " . USER_PROFILE_MASTER . " WHERE eStatus !='Deleted' $ssql LIMIT $start, $per_page";
$data_drv = $obj->MySQLSelect($sql);
//echo "<pre>";
$userDataArr = array();
for ($u = 0; $u < count($data_drv); $u++) {
    $shortProfileName = (array) json_decode($data_drv[$u]['vShortProfileName']);
    $profileName = (array) json_decode($data_drv[$u]['vProfileName']);
    $title = (array) json_decode($data_drv[$u]['vTitle']);
    $subTitle = (array) json_decode($data_drv[$u]['vSubTitle']);
    $eng_arr = array();
    $eng_arr['iUserProfileMasterId'] = $data_drv[$u]['iUserProfileMasterId'];
    $eng_arr['vShortProfileName'] = $eng_arr['vProfileName'] = $eng_arr['vTitle'] = $eng_arr['vSubTitle'] = "";
    if (isset($shortProfileName['vShortProfileName_' . $default_lang])) {
        $eng_arr['vShortProfileName'] = $shortProfileName['vShortProfileName_' . $default_lang];
    }
    if (isset($profileName['vProfileName_' . $default_lang])) {
        $eng_arr['vProfileName'] = $profileName['vProfileName_' . $default_lang];
    }
    if (isset($title['vTitle_' . $default_lang])) {
        $eng_arr['vTitle'] = $title['vTitle_' . $default_lang];
    }
    if (isset($subTitle['vSubTitle_' . $default_lang])) {
        $eng_arr['vSubTitle'] = $subTitle['vSubTitle_' . $default_lang];
    }
    $eng_arr['eStatus'] = $data_drv[$u]['eStatus'];
    $userDataArr[] = $eng_arr;
}
//print_r($userDataArr);die;
$endRecord = count($userDataArr);
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
        <title><?= $SITE_NAME; ?> | Ride Profile Type </title>
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
                                <h2>Ride Profile Type</h2>
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
                                    <td width="15%" class=" padding-right10">
                                        <select name="option" id="option" class="form-control">
                                            <option value="">All</option>
                                            <option value="vShortProfileName" <?php
                                            if ($option == 'vShortProfileName') {
                                                echo "selected";
                                            }
                                            ?> >Profile Name</option>
                                            <option value="vProfileName" <?php
                                            if ($option == 'vProfileName') {
                                                echo "selected";
                                            }
                                            ?> >Organization Type</option>
                                            <option value="vTitle" <?php
                                            if ($option == 'vTitle') {
                                                echo "selected";
                                            }
                                            ?> >Title</option>
                                            <option value="vSubTitle" <?php
                                            if ($option == 'vSubTitle') {
                                                echo "selected";
                                            }
                                            ?> >Sub Title</option>
                                        </select>
                                    </td>
                                    <td width="15%" class="searchform"><input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"  class="form-control" /></td>
                                    <td>
                                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'user_profile_master.php'"/>
                                    </td>
                                    <?php if ($userObj->hasPermission('create-user-profile')) { ?>
                                        <td width="30%"><a class="add-btn" href="user_profile_master_action.php" style="text-align: center;">Add Profile</a></td>
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
                                            <?php if ($userObj->hasPermission(['update-status-user-profile', 'delete-user-profile'])) { ?>
                                                <select name="changeStatus" id="changeStatus" class="form-control" onchange="ChangeStatusAll(this.value);">
                                                    <option value="" >Select Action</option>
                                                    <?php if ($userObj->hasPermission('update-status-user-profile')) { ?>
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
                                                            <?php if ($userObj->hasPermission('delete-user-profile')) { ?>
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
                                </div>
                                <div style="clear:both;"></div>
                                <div class="table-responsive">
                                    <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th align="center" width="3%" style="text-align:center;"><input type="checkbox" id="setAllCheck" ></th>
                                                    <th width="15%">Profile Short Name</th>
                                                    <th width="15%">Organization type</th>
                                                    <th width="15%">Profile Title</th>
                                                    <th width="15%">Title Description</th>
                                                    <th width="15%">Status</th>
                                                    <th width="8%" align="center" style="text-align:center;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if (!empty($userDataArr)) {
                                                    for ($i = 0; $i < count($userDataArr); $i++) {
                                                        ?>
                                                        <tr class="gradeA">
                                                            <td align="center" style="text-align:center;"><input type="checkbox" id="checkbox" name="checkbox[]" value="<?php echo $userDataArr[$i]['iUserProfileMasterId']; ?>" />&nbsp;</td>
                                                            <td><?= $userDataArr[$i]['vShortProfileName'] ?></td>
                                                            <td><?= $userDataArr[$i]['vProfileName'] ?></td>
                                                            <td><?= $userDataArr[$i]['vTitle'] ?></td>
                                                            <td><?= $userDataArr[$i]['vSubTitle'] ?></td>
                                                            <td align="center" style="text-align:center;">
                                                                <?php
                                                                if ($userDataArr[$i]['eStatus'] == 'Active') {
                                                                    $dis_img = "img/active-icon.png";
                                                                } else if ($userDataArr[$i]['eStatus'] == 'Inactive') {
                                                                    $dis_img = "img/inactive-icon.png";
                                                                } else if ($userDataArr[$i]['eStatus'] == 'Deleted') {
                                                                    $dis_img = "img/delete-icon.png";
                                                                }
                                                                ?>
                                                                <img src="<?= $dis_img; ?>" alt="image" data-toggle="tooltip" title="<?php echo $userDataArr[$i]['eStatus']; ?>">
                                                            </td>
                                                            <td align="center" style="text-align:center;" class="action-btn001">
                                                                <div class="share-button openHoverAction-class" style="display: block;">
                                                                    <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>
                                                                    <div class="social show-moreOptions for-two openPops_<?= $userDataArr[$i]['iUserProfileMasterId']; ?>">
                                                                        <ul>
                                                                            <li class="entypo-twitter" data-network="twitter"><a href="user_profile_master_action.php?id=<?= $userDataArr[$i]['iUserProfileMasterId']; ?>" data-toggle="tooltip" title="Edit">
                                                                                    <img src="img/edit-icon.png" alt="Edit">
                                                                                </a></li>
                                                                            <?php if ($userObj->hasPermission('update-status-user-profile')) { ?>
                                                                                <li class="entypo-facebook" data-network="facebook"><a href="javascript:void(0);" onClick="changeStatus('<?php echo $userDataArr[$i]['iUserProfileMasterId']; ?>', 'Inactive')"  data-toggle="tooltip" title="Activate">
                                                                                        <img src="img/active-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >
                                                                                    </a></li>
                                                                                <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onClick="changeStatus('<?php echo $userDataArr[$i]['iUserProfileMasterId']; ?>', 'Active')" data-toggle="tooltip" title="Deactivate">
                                                                                        <img src="img/inactive-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >	
                                                                                    </a></li>
                                                                            <?php } ?> 
                                                                            <?php if ($userObj->hasPermission('delete-user-profile')) { ?>
                                                                                <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onclick="changeStatusDelete('<?php echo $userDataArr[$i]['iUserProfileMasterId']; ?>')"data-toggle="tooltip" title="Delete">
                                                                                        <img src="img/delete-icon.png" alt="Delete" >
                                                                                    </a></li>
                                                                            <?php } ?>
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
                            <li>Ride Profile Type Module will list all ride profile data on this page.</li>
                            <li>Administrator can Edit / Delete any ride profile data.</li>
                            <!--<li>Administrator can export data in XLS or PDF format.</li>-->
                        </ul>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
        <form name="pageForm" id="pageForm" action="action/user_profile_master.php" method="post" >
            <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
            <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
            <input type="hidden" name="iUserProfileMasterId" id="iMainId01" value="" >
            <input type="hidden" name="status" id="status01" value="" >
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
                    if (this.value == 'eType') {
                        $('#eType_options').show();
                        $('.searchform').hide();
                    }
                });
            });

            $(function () {
                $('#option').change(function () {
                    if ($('#option').val() == 'eType') {
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