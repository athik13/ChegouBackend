<?php
include_once('../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();

if(!$userObj->hasRole(1)){
    $userObj->redirect();
}

$script = 'AdminPermissions';

//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY permission_name ASC';

if($sortby == 2){
  if($order == 0)
  $ord = " ORDER BY status ASC";
  else
  $ord = " ORDER BY status DESC";
}
//End Sorting

// Start Search Parameters
echo$option = isset($_REQUEST['option'])?stripslashes($_REQUEST['option']):"";
$keyword = isset($_REQUEST['keyword'])?stripslashes($_REQUEST['keyword']):"";
  $eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
$ssql = '';
if($keyword != ''){
    if($option != '') {
        if (strpos($option, 'status') !== false) {
            $ssql.= " AND ".stripslashes($option)." LIKE '".$generalobjAdmin->clean($keyword)."'";
        }else {
            $ssql.= " AND ".stripslashes($option)." LIKE '%".$generalobjAdmin->clean($keyword)."%'";
        }
    }else {	  
			$permission_name = str_replace(['-', '_', ' ', '/', '\\'], "-", $generalobjAdmin->clean($keyword));
			
		if ($eStatus!='') {
					$ssql.= " AND ap.permission_name LIKE '%".$permission_name."%' AND ap.status = '".$eStatus."'";
			}else{
      
					$ssql.= " AND (ap.permission_name LIKE '%".$permission_name."%' OR ap.status LIKE '%".$generalobjAdmin->clean($keyword)."%')";
			}
    }
}else{ 
		if ($eStatus!='') {
			$ssql.= " AND ap.status = '".$eStatus."'";	
		}
	
}
if($option == "status"){   
    $statussql = " AND ap.status = '".ucfirst($keyword)."'";
}else{
    $statussql = " AND ap.status != 'Deleted'";
}
// End Search Parameters


//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT COUNT(id) AS Total FROM admin_permissions as ap WHERE 1=1 $statussql $ssql";
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
$tpages=$total_pages;
if ($page <= 0)
    $page = 1;

$sql = "SELECT ap.*, REPLACE(GROUP_CONCAT(ag.vGroup ORDER BY ag.iGroupId ASC SEPARATOR ', '), '-', ' ') AS group_name, adg.name AS display_group_name 
    
    FROM admin_permissions as ap
    LEFT JOIN admin_group_permission as agp ON agp.permission_id = ap.id
    LEFT JOIN admin_groups as ag ON agp.group_id = ag.iGroupId
    LEFT JOIN admin_permission_display_groups as adg ON adg.id = ap.display_group_id

    WHERE 1=1 $statussql $ssql GROUP BY ap.id $ord LIMIT $start, $per_page";


$data_drv = $obj->MySQLSelect($sql);
$endRecord = count($data_drv);
$var_filter = "";
foreach ($_REQUEST as $key=>$val) {
    if($key != "tpages" && $key != 'page')
    $var_filter.= "&$key=".stripslashes($val);
}

$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages.$var_filter;

?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?=$SITE_NAME?> | Admin Groups</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php include_once('global_files.php');?>
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
                                <h2>Admin Permissions</h2>
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
                                    <td width="8%" class=" padding-right10">
                                    <select name="option" id="option" class="form-control">
                                          <option value="">All</option>
                                          <option value="" <?php if ($option == "permission_name") { echo "selected"; } ?> >Permission Name</option>
                                    </select>
                                    </td>
                                    <td width="10%"><input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"  class="form-control" /></td>
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
											<!--<option value="Deleted" <?php
                                            if ($eStatus == 'Deleted') {
                                                echo "selected";
                                            }
                                            ?>>Deleted</option>-->
									</select>
									</td>
                                    <td width="12%">
                                      <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                      <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href='admin_permissions.php'"/>
                                    </td>
                                    <td width="30%"><a class="add-btn" href="admin_permission_action.php" style="text-align: center;">Add Admin Permission</a></td>
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
                                            <select name="changeStatus" id="changeStatus" class="form-control" onChange="changeStatusAll(this.value);">
                                                    <option value="" >Select Action</option>
                                                    <option value='Active' <?php if ($option == 'Active') { echo "selected"; } ?> >Activate</option>
                                                    <option value="Inactive" <?php if ($option == 'Inactive') {echo "selected"; } ?> >Deactivate</option>
                                                    <option value="Deleted" <?php if ($option == 'Delete') {echo "selected"; } ?> >Delete</option>
                                            </select>
                                    </span>
                                    </div>
                                    <!-- <?php  if(!empty($data_drv)) { ?>
                                    <div class="panel-heading">
                                        <form name="_export_form" id="_export_form" method="post" >
                                            <button type="button" onClick="showExportTypes('admin')" >Export</button>
                                        </form>
                                   </div>
                                   <?php } ?> -->
                                    </div>
                                    <div style="clear:both;"></div>
                                        <div class="table-responsive">
                                            <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                            <table class="table table-striped table-bordered table-hover">
                                                <thead>
                                                    <tr>
                                                        <th align="center" width="3%" style="text-align:center;"><input type="checkbox" id="setAllCheck" ></th>
                                                        
                                                        <th width="20%"><a href="javascript:void(0);" onClick="Redirect(1,<?php if($sortby == '1'){ echo $order; }else { ?>0<?php } ?>)">Permission Name <?php if ($sortby == 1) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                        <th width="40%">Group Name</th>

                                                        <th width="20%">Display Group Name</th>
                                                        
                                                  
                                                        <th width="10%" align="center" style="text-align:center;"><a href="javascript:void(0);" onClick="Redirect(2,<?php if($sortby == '2'){ echo $order; }else { ?>0<?php } ?>)">Status <?php if ($sortby == 2) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                        <th width="10%" align="center" style="text-align:center;">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    if(!empty($data_drv)) {
                                                    for ($i = 0; $i < count($data_drv); $i++) { 
                                                        
                                                        $default = '';
                                                        if($data_drv[$i]['eDefault']=='Yes' || ($_SESSION['sess_iAdminUserId'] === $data_drv[$i]['id'])){
                                                                $default = 'disabled';
                                                        } ?>
                                                    <tr class="gradeA">
                                                        <td align="center" style="text-align:center;"><input type="checkbox" id="checkbox" name="checkbox[]" <?php echo $default; ?> value="<?php echo $data_drv[$i]['id']; ?>" />&nbsp;</td>
                                                        <td><?= $data_drv[$i]['permission_name'] ?></td>
                                                        <td><?= $data_drv[$i]['group_name'] ?></td>
                                                        <td><?= $data_drv[$i]['display_group_name'] ?></td>
                                                    
                                                        <td align="center" style="text-align:center;">
                                                                <?php if($data_drv[$i]['status'] == 'Active') {
                                                                    $dis_img = "img/active-icon.png";
                                                                }else if($data_drv[$i]['status'] == 'Inactive'){
                                                                    $dis_img = "img/inactive-icon.png";
                                                                }else if($data_drv[$i]['status'] == 'Deleted'){
                                                                    $dis_img = "img/delete-icon.png";
                                                                }?>
                                                                <img src="<?= $dis_img; ?>" alt="image" data-toggle="tooltip" title="<?php echo $data_drv[$i]['status']; ?>">
                                                            </td>
                                                            <td align="center" style="text-align:center;" class="action-btn001">
                                                            <?php if(($_SESSION['sess_iAdminUserId'] === $data_drv[$i]['id']) || $data_drv[$i]['eDefault'] == 'Yes') {?>
                                                                    <a href="admin_permission_action.php?id=<?= $data_drv[$i]['id']; ?>" data-toggle="tooltip" title="Edit">
                                                                    <img src="img/edit-icon.png" alt="Edit">
                                                                </a>
                                                            <?php }else { ?>
                                                            <div class="share-button share-button4 openHoverAction-class" style="display: block;">
                                                                    <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>
                                                                   <div class="social show-moreOptions openPops_<?= $data_drv[$i]['id']; ?>">
                                                                        <ul>
                                                                            <li class="entypo-twitter" data-network="twitter"><a href="admin_permission_action.php?id=<?= $data_drv[$i]['id']; ?>" data-toggle="tooltip" title="Edit">
                                                                                <img src="img/edit-icon.png" alt="Edit">
                                                                            </a></li>
                                                                            
                                                                            <li class="entypo-facebook" data-network="facebook"><a href="javascript:void(0);" onClick="changeStatus('<?php echo $data_drv[$i]['id']; ?>','Inactive')"  data-toggle="tooltip" title="Make Active">
                                                                                <img src="img/active-icon.png" alt="<?php echo $data_drv[$i]['status']; ?>" >
                                                                            </a></li>
                                                                            <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onClick="changeStatus('<?php echo $data_drv[$i]['id']; ?>','Active')" data-toggle="tooltip" title="Make Inactive">
                                                                                <img src="img/inactive-icon.png" alt="<?php echo $data_drv[$i]['status']; ?>" >    
                                                                            </a></li>
                                                                            <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onClick="changeStatusDelete('<?php echo $data_drv[$i]['id']; ?>')"  data-toggle="tooltip" title="Delete">
                                                                                <img src="img/delete-icon.png" alt="Delete" >
                                                                            </a></li>
                                                                            
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            <?php } ?>
                                                            </td>
                                                        </tr>
                                                    <?php } }else { ?>
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
                                            Administrator module will list all administrators on this page.
                                    </li>
                                    <li>
                                            Administrator can Activate / Deactivate / Delete any administrator. Super Admin cannot be Activated / Deactivated / Deleted.
                                    </li>
                                    <li>
                                            Administrator can export data in XLS or PDF format.
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
            
<form name="pageForm" id="pageForm" action="action/admin_permissions.php" method="post" >
<input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
<input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
<input type="hidden" name="id" id="iMainId01" value="" >
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
            
            $("#setAllCheck").on('click',function(){
                if($(this).prop("checked")) {
                    jQuery("#_list_form input[type=checkbox]").each(function() {
                        if($(this).attr('disabled') != 'disabled'){
                            this.checked = 'true';
                        }
                    });
                }else {
                    jQuery("#_list_form input[type=checkbox]").each(function() {
                        this.checked = '';
                    });
                }
            });
            
            $("#Search").on('click', function(){
                //$('html').addClass('loading');
                var action = $("#_list_form").attr('action');
               // alert(action);
                var formValus = $("#frmsearch").serialize();
//                alert(action+formValus);
                window.location.href = action+"?"+formValus;
            });
            
            $('.entypo-export').click(function(e){
                 e.stopPropagation();
                 var $this = $(this).parent().find('div');
                 $(".openHoverAction-class div").not($this).removeClass('active');
                 $this.toggleClass('active');
            });
            
            $(document).on("click", function(e) {
                if ($(e.target).is(".openHoverAction-class,.show-moreOptions,.entypo-export") === false) {
                  $(".show-moreOptions").removeClass("active");
                }
            });
            
        </script>
    </body>
    <!-- END BODY-->
</html>