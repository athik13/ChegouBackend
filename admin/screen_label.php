<?php
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
if (!$userObj->hasPermission('view-app-screen-label')) {
//    $userObj->redirect();
}

if ($default_lang == "") {
    $default_lang = "EN";
}


$script = 'Screen Label';
$tbl_name = 'app_screen_language_label';
$currnt_file='screen_label.php';
$action_file='screen_label_action.php';

//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$spageid =isset($_REQUEST['spageid']) ? $_REQUEST['spageid'] : '';

$ord = ' ORDER BY `LanguageLabelId` ASC';
if ($sortby == 1) {
    if ($order == 0)
        $ord = " ORDER BY vLabel ASC";
    else
        $ord = " ORDER BY vLabel DESC";
}
if ($sortby == 2) {
    if ($order == 0)
        $ord = " ORDER BY vValue ASC";
    else
        $ord = " ORDER BY vValue DESC";
}

if ($sortby == 4) {
    if ($order == 0)
        $ord = " ORDER BY lPage_id ASC";
    else
        $ord = " ORDER BY lPage_id DESC";
}

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
            $ssql .= " AND " . stripslashes($option) . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%'";
        }
    } else {
        if ($eStatus != '') {
            $ssql .= " AND  vLabel LIKE '%" . $generalobjAdmin->clean($keyword) . "%' AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";
        } else {
            $ssql .= " AND  vLabel LIKE '%" . $generalobjAdmin->clean($keyword) . "%'";
        }
    }
} else if ($eStatus != '' && $keyword == '') {
    $ssql .= " AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";
}

$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
if ($eStatus != '') {
    $estatusquery = "";
} else {
    $estatusquery = " AND eStatus != 'Deleted'";
}
  if(empty($spageid)){
    $sqlp = "SELECT COUNT(LanguageLabelId) AS Total FROM $tbl_name WHERE  1 = 1 AND vCode ='$default_lang' $estatusquery $ssql";
    }else{

   $sqlp = "SELECT COUNT(LanguageLabelId) AS Total FROM $tbl_name WHERE lPage_id = $spageid AND vCode ='$default_lang' $estatusquery $ssql";
    }
//exit;
//die;
$totalData = $obj->MySQLSelect($sqlp);
$total_results = 0;
if (isset($totalData[0]['Total'])) {
    $total_results = $totalData[0]['Total'];
}
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

if(empty($spageid)){
 $sql = "SELECT * from $tbl_name WHERE eStatus != 'Deleted' AND vCode ='$default_lang' $ssql $ord LIMIT $start, $per_page ";
}else{
     $sql = "SELECT * from $tbl_name WHERE eStatus != 'Deleted' AND vCode ='$default_lang' AND 
     lPage_id =$spageid $ssql $ord LIMIT $start, $per_page ";

}
//echo $sql;

$data_drv = $obj->MySQLSelect($sql);
$getUserCount = $obj->MySQLSelect("SELECT * FROM $tbl_name WHERE LanguageLabelId > 0 AND vCode ='$default_lang'");

$usedCountArr = array();
for ($c = 0; $c < count($getUserCount); $c++) {
    $bannerId = $getUserCount[$c]['LanguageLabelId'];
    if (isset($usedCountArr[$bannerId]) && $usedCountArr[$bannerId] > 0) {
        $usedCountArr[$bannerId] += 1;
    } else {
        $usedCountArr[$bannerId] = 1;
    }
}
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
        <title></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php include_once('global_files.php'); ?>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 ">
        <!-- Main LOading -->
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?php include_once('header.php'); ?>
            <?php include_once('left_menu.php'); ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div id="add-hide-show-div" class="vehicleCategorylist">
                        <div class="row">
                            <div class="col-lg-12">
                                <h2>Manage Screen Label</h2>
                            </div>
                        </div>
                        <hr />

                    </div>
                    <?php include('valid_msg.php'); ?>
                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                            <tbody>
                                <tr>
                                    <td width="2%"><label for="textfield"><strong>Search:</strong></label></td>
                                    <td width="8%" class="padding-right10">
                                        <select name="option" id="option" class="form-control">
                                            <option value="">All</option>
                                            <option  value="vLabel" <?php
                                            if ($option == "vLabel") {
                                                echo "selected";
                                            }
                                            ?> >Code</option>

                                            <option  value="vValue" <?php
                                            if ($option == "vValue") {
                                                echo "selected";
                                            }
                                            ?> >Value</option>
                                           
                                        </select>
                                    </td>
                                    <td width="12%" class="searchform"><input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"  class="form-control" /></td>
                                    <td width="10%" class="estatus_options" id="eStatus_options" >
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
                                        </select>
                                    </td>
                                    <td>
                                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = '<?=$currnt_file?>'"/>
                                    </td>

                                    <?php //if ($parent_ufx_catid == "0") { ?>
                                        <?php if ($userObj->hasPermission('create-app-screen-label')) { ?>
                                            <td width="30%"><a class="add-btn" href="<?=$action_file?>" style="text-align: center;">Screen Label Add</a></td>
                                        <?php } ?>
                                    <?php //} ?>

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
                                            <?php if ($userObj->hasPermission(['update-status-app-screen-label', 'delete-view-app-screen-label'])) { ?>
                                                <select name="changeStatus" id="changeStatus" class="form-control" onChange="ChangeStatusAll(this.value);">
                                                    <option value="" >Select Action</option>
                                                    <?php if ($userObj->hasPermission('update-status-app-screen-label')) { ?>
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
                                                            <?php if ($userObj->hasPermission('delete-app-screen-label')) { ?>
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
                                                    <th align="center" width="3%" style="text-align:left;"><input type="checkbox" id="setAllCheck" ></th>
                                                 
                                                    <th width="20%" style="text-align:left;"><a href="javascript:void(0);" onClick="Redirect(1,<?php
                                                        if ($sortby == '1') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Code<?php
                                                          if ($sortby == 1) {
                                                              if ($order == 0) {
                                                                  ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                        <th width="20%" align="center" style="text-align:left;"><a href="javascript:void(0);" onClick="Redirect(2,<?php
                                                        if ($sortby == '2') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Value In English Language <?php
                                                            if ($sortby == 2) {
                                                             if ($order == 0) {
                                                                ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>         

                                                    
                                                    
                                                     <th width="10%" align="left" style="text-align:left;"><a href="javascript:void(0);" onClick="Redirect(4,<?php
                                                        if ($sortby == '4') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">App Screen<?php
                                                     if ($sortby == 4) {
                                                         if ($order == 0) {
                                                             ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                   <th width="8%" align="center" style="text-align:center;">Status</th>
                                                    <th width="8%" align="center" style="text-align:center;">Action</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if (!empty($data_drv)) {
                                           for ($i = 0; $i < count($data_drv); $i++) { ?>
                                                        <tr class="gradeA">
                                                            <td align="center" style="text-align:center;"><input type="checkbox" id="checkbox" name="checkbox[]" value="<?php echo $data_drv[$i]['LanguageLabelId']; ?>" />&nbsp;</td>
                                                          
                                                            <td align="left"><? echo $data_drv[$i]['vLabel']; ?></td>	

                                                            <td align="left"><? echo $data_drv[$i]['vValue']; ?></td> 

                                                           

                                                               <td align="left">

                                                              <?php if($data_drv[$i]['lPage_id']=="0")
                                                              {
                                                                echo '----' ;
                                                              }else{

                                                            $sql3 = "SELECT lPage_id,vScreenName from app_screen_master WHERE lPage_id=".$data_drv[$i]['lPage_id'];

                                                                $name2 = $obj->MySQLSelect($sql3);   ?>
                                                                
                                                                <?php echo $name2[0]['vScreenName'];     } ?>
                                                                        
                                                
                                                              </td>

                                                            
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
                                                                <img src="<?= $dis_img; ?>" alt="<?= $data_drv[$i]['eStatus'] ?>" data-toggle="tooltip" title="<?php echo $data_drv[$i]['eStatus']; ?>" >
                                                            </td>

                                                            <td align="center" style="text-align:center;" class="action-btn001">
                                                                <div class="share-button openHoverAction-class" style="display: block;">
                                                                    <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>
                                                                    <div class="social show-moreOptions for-five openPops_<?= $data_drv[$i]['lPage_id']; ?>">
                                                                        <ul>
                                                                         <?php if ($userObj->hasPermission('edit-app-screen-label')) { ?>   <li class="entypo-twitter" data-network="twitter"><a href="<?=$action_file?>?id=<?= $data_drv[$i]['LanguageLabelId']; ?>&usedcount=<?= $bannerUsedCount; ?>" data-toggle="tooltip" title="Edit">
                                                                          <img src="img/edit-icon.png" alt="Edit">
                                                                                </a></li><?php } ?>
                                        <?php if ($userObj->hasPermission('update-status-app-screen-label')) {
                                                                             ?>
                                                                                <li class="entypo-facebook" data-network="facebook"><a href="javascript:void(0);" onClick="changeStatus('<?php echo $data_drv[$i]['LanguageLabelId']; ?>', 'Inactive')"  data-toggle="tooltip" title="Activate">
                                                                                        <img src="img/active-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >
                                                                                    </a></li>
                                                                                <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onClick="changeStatus('<?php echo $data_drv[$i]['LanguageLabelId']; ?>', 'Active')" data-toggle="tooltip" title="Deactivate">
                                                                                        <img src="img/inactive-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >	
                                                                                    </a></li>
                                                                            <?php } ?>   
                                                                            <?php if ($eStatus != 'Deleted' && $userObj->hasPermission('delete-app-screen-label')) { ?>
                                                                                <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onClick="changeStatusDelete('<?php echo $data_drv[$i]['LanguageLabelId']; ?>')"  data-toggle="tooltip" title="Delete">
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
                                                        <td colspan="10" style="text-align:center;"> No Records Found.</td>
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
                            <li><?= $script  ?> module will list all Screen types on this page.</li>
                            <li>Administrator can Edit / Delete any Screen type.</li>
                           
                        </ul>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
        <form name="pageForm" id="pageForm" action="action/<?=$currnt_file?>" method="post" >
            <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
            <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
            <input type="hidden" name="lPage_id" id="iMainId01" value="" >
            <input type="hidden" name="eStatus" id="eStatus" value="<?php echo $eStatus; ?>" >
            <input type="hidden" name="status" id="status01" value="" >
            <input type="hidden" name="statusVal" id="statusVal" value="" >
            <input type="hidden" name="option" value="<?php echo $option; ?>" >
            <input type="hidden" name="keyword" value="<?php echo $keyword; ?>" >
            <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
            
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