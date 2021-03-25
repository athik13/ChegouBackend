<?php
include_once('../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();

if(!$userObj->hasPermission('view-delivery-charges')){
  $userObj->redirect();
}

$script = 'Delivery Charges';

$id     = isset($_REQUEST['id'])?$_REQUEST['id']:'';
$action = isset($_REQUEST['action'])?$_REQUEST['action']:'view';
$iDeliveyChargeId = isset($_REQUEST['iDeliveyChargeId'])?$_REQUEST['iDeliveyChargeId']:'';    
$status = isset($_GET['status'])?$_GET['status']:'';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$tbl_name     = 'delivery_charges';


//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY dc.iDeliveyChargeId DESC';
if($sortby == 1){
  if($order == 0)
  $ord = " ORDER BY lm1.vLocationName ASC";
  else
  $ord = " ORDER BY lm1.vLocationName DESC";
}

 if($sortby == 2){
  if($order == 0)
  $ord = " ORDER BY dc.fOrderPriceValue ASC";
  else
  $ord = " ORDER BY dc.fOrderPriceValue DESC";
}

if($sortby == 3){
  if($order == 0)
  $ord = " ORDER BY dc.fDeliveryChargeAbove ASC";
  else
  $ord = " ORDER BY dc.fDeliveryChargeAbove DESC";
} 


if($sortby == 4){
  if($order == 0)
  $ord = " ORDER BY dc.fDeliveryChargeBelow ASC";
  else
  $ord = " ORDER BY dc.fDeliveryChargeBelow DESC";
}

if($sortby == 5){
  if($order == 0)
  $ord = " ORDER BY dc.fFreeOrderPriceSubtotal ASC";
  else
  $ord = " ORDER BY dc.fFreeOrderPriceSubtotal DESC";
}

if($sortby == 6){
  if($order == 0)
  $ord = " ORDER BY dc.iFreeDeliveryRadius ASC";
  else
  $ord = " ORDER BY dc.iFreeDeliveryRadius DESC";
}
if($sortby == 7){
  if($order == 0)
  $ord = " ORDER BY dc.eStatus ASC";
  else
  $ord = " ORDER BY dc.eStatus DESC";
}
//End Sorting


// Start Search Parameters
$option = isset($_REQUEST['option'])?stripslashes($_REQUEST['option']):"";
$keyword = isset($_REQUEST['keyword'])?stripslashes($_REQUEST['keyword']):"";
$searchDate = isset($_REQUEST['searchDate'])?$_REQUEST['searchDate']:"";
$eStatus = isset($_REQUEST['eStatus'])?$_REQUEST['eStatus']:"";
$ssql =  $eStatussql ='';
if($keyword != ''){
    if($option != '') {		
		if($eStatus != ''){
            $ssql.= " AND ".stripslashes($option)." LIKE '%".$generalobjAdmin->clean($keyword)."%' AND dc.eStatus = '".$generalobjAdmin->clean($eStatus)."'";
        }else {
            $ssql.= " AND ".stripslashes($option)." LIKE '%".$generalobjAdmin->clean($keyword)."%' AND dc.eStatus != 'Deleted'";
        } 
    }else {
		if($eStatus != ''){
            $ssql.= " AND (lm1.vLocationName LIKE '%".$keyword."%' OR dc.fOrderPriceValue LIKE '%".$keyword."%' OR dc.fDeliveryChargeAbove LIKE '%".$keyword."%' OR dc.fDeliveryChargeBelow LIKE '%".$keyword."%' OR dc.fFreeOrderPriceSubtotal LIKE '%".$keyword."%' OR dc.iFreeDeliveryRadius LIKE '%".$keyword."%') AND dc.eStatus = '".$generalobjAdmin->clean($eStatus)."'";
        }else {
            $ssql.= " AND (lm1.vLocationName LIKE '%".$keyword."%' OR dc.fOrderPriceValue LIKE '%".$keyword."%' OR dc.fDeliveryChargeAbove LIKE '%".$keyword."%' OR dc.fDeliveryChargeBelow LIKE '%".$keyword."%' OR dc.fFreeOrderPriceSubtotal LIKE '%".$keyword."%' OR dc.iFreeDeliveryRadius LIKE '%".$keyword."%') AND dc.eStatus != 'Deleted'";
        }
    }
}else{
	if($eStatus != ''){
            $ssql.= " AND dc.eStatus = '".$generalobjAdmin->clean($eStatus)."'";
        }else{
			 $eStatussql = " AND dc.eStatus != 'Deleted'";
		}
}
 
// End Search Parameters

//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT COUNT(dc.iLocationId) as Total FROM `delivery_charges` dc left join location_master lm1 on dc.iLocationId = lm1.iLocationId WHERE 1 = 1 $eStatussql $ssql";
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
//Pagination End
$sql = "SELECT dc.*,lm1.vLocationName FROM `delivery_charges` dc left join location_master lm1 on dc.iLocationId = lm1.iLocationId WHERE 1 = 1 $eStatussql $ssql $ord LIMIT $start, $per_page";
$data_drv = $obj->MySQLSelect($sql);

$endRecord = count($data_drv);

$var_filter = "";
foreach ($_REQUEST as $key=>$val)
{
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
       <title><?=$SITE_NAME?> | Delivery Charges</title>
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
                                <h2>Delivery Charges</h2>
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
                                      <option value="lm1.vLocationName" <?php if ($option == "lm1.vLocationName") { echo "selected"; } ?> >Location</option>
                                      <option value="dc.fOrderPriceValue" <?php if ($option == "dc.fOrderPriceValue") { echo "selected"; } ?> >Order Amount</option>
                                    </select>
                                    </td>
                                    <td width="15%"><input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"  class="form-control" /></td>
									<td width="12%" class="estatus_options" id="eStatus_options" >
                                        <select name="eStatus" id="estatus_value" class="form-control">
                                            <option value="" >Select Status</option>
                                            <option value='Active' <?php if ($eStatus == 'Active') { echo "selected"; } ?> >Active</option>
                                            <option value="Inactive" <?php if ($eStatus == 'Inactive') {echo "selected"; } ?> >Inactive</option>
                                            <option value="Deleted" <?php if ($eStatus == 'Deleted') {echo "selected"; } ?> >Delete</option>
                                        </select>
                                    </td> 
                                    <td width="12%">
                                      <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                      <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href='delivery_charges.php'"/>
                                    </td>
                                    <?php if($userObj->hasPermission('create-delivery-charges')){ ?>
                                      <td width="20%"><a class="add-btn" href="delivery_charges_action.php" style="text-align: center;">Add Delivery Charges</a></td>
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
                                        <?php if($userObj->hasPermission(['update-status-delivery-charges', 'delete-delivery-charges'])){ ?>
                                            <select name="changeStatus" id="changeStatus" class="form-control" onchange="ChangeStatusAll(this.value);">
                                                    <option value="" >Select Action</option>
                                                    <?php if($userObj->hasPermission('update-status-delivery-charges')){ ?>
                                                      <option value='Active' <?php if ($option == 'Active') { echo "selected"; } ?> >Activate</option>
                                                      <option value="Inactive" <?php if ($option == 'Inactive') {echo "selected"; } ?> >Deactivate</option>
                                                    <?php } ?>
                                                    <?php if($userObj->hasPermission('delete-delivery-charges')){ ?>
                                                    <option value="Deleted" <?php if ($option == 'Delete') {echo "selected"; } ?> >Delete</option>
                                                    <?php } ?>
                                            </select>
                                        <?php } ?>
                                    </span>
                                    </div>
                                   <!--  <?php if(!empty($data_drv)) {?>
                                    <div class="panel-heading">
                                        <form name="_export_form" id="_export_form" method="post" >
                                            <button type="button" onclick="showExportTypes('delivery_charges')" >Export</button>
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
                                                        <th width="20%"><a href="javascript:void(0);" onClick="Redirect(1,<?php if($sortby == '1'){ echo $order; }else { ?>0<?php } ?>)">Location Name<?php if ($sortby == 1) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                        <th width="20%"><a href="javascript:void(0);" onClick="Redirect(2,<?php if($sortby == '2'){ echo $order; }else { ?>0<?php } ?>)">Order Price <?php if ($sortby == 2) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>                                                   

                                                        <th width="20%"><a href="javascript:void(0);" onClick="Redirect(3,<?php if($sortby == '3'){ echo $order; }else { ?>0<?php } ?>)">Order Delivery Charges Above Amount<?php if ($sortby == 3) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                                                  
                                                         <th width="20%"><a href="javascript:void(0);" onClick="Redirect(4,<?php if($sortby == '4'){ echo $order; }else { ?>0<?php } ?>)">Order Delivery Charges Below Amount<?php if ($sortby == 4) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                          <th width="20%"><a href="javascript:void(0);" onClick="Redirect(5,<?php if($sortby == '5'){ echo $order; }else { ?>0<?php } ?>)">Free Order Delivery Charges<?php if ($sortby == 5) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>      
                                                        <th width="20%"><a href="javascript:void(0);" onClick="Redirect(6,<?php if($sortby == '6'){ echo $order; }else { ?>0<?php } ?>)"> Free Delivery Radius<?php if ($sortby == 6) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                        <th width="8%" align="center" style="text-align:center;"><a href="javascript:void(0);" onClick="Redirect(7,<?php if($sortby == '7'){ echo $order; }else { ?>0<?php } ?>)">Status <?php if ($sortby == 7) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                        <th width="8%" align="center" style="text-align:center;">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    if(!empty($data_drv)) {
                                                    for ($i = 0; $i < count($data_drv); $i++) { 
                                                        
                                                        $default = '';
                                                        if($data_drv[$i]['eDefault']=='Yes'){
                                                                $default = 'disabled';
                                                        } ?>
                                                    <tr class="gradeA">
                                                        <td align="center" style="text-align:center;"><input type="checkbox" id="checkbox" name="checkbox[]" <?php echo $default; ?> value="<?php echo $data_drv[$i]['iDeliveyChargeId']; ?>" />&nbsp;</td>
                                                        <td>
                                                        <? if($data_drv[$i]['iLocationId'] == '0') { 
                                                          echo 'All Location'; 
                                                        } else { 
                                                          echo $data_drv[$i]['vLocationName']; 
                                                        } ?>
                                                        </td>
                                                        <td><?= $data_drv[$i]['fOrderPriceValue']; ?></td>
                                                        <td><?= $data_drv[$i]['fDeliveryChargeAbove']; ?></td>
                                                        <td><?= $data_drv[$i]['fDeliveryChargeBelow']; ?></td>
                                                        <?php //added by SP 27-06-2019 for remove validation to leave blank ?>
                                                        <td><?php if(!empty($data_drv[$i]['fFreeOrderPriceSubtotal']) && $data_drv[$i]['fFreeOrderPriceSubtotal']!=0) echo $data_drv[$i]['fFreeOrderPriceSubtotal']; else echo ''; ?></td>
                                                        <td><?php if(!empty($data_drv[$i]['iFreeDeliveryRadius']) && $data_drv[$i]['iFreeDeliveryRadius']!=0) echo $data_drv[$i]['iFreeDeliveryRadius']; else echo ''; ?></td>
                                                        <td align="center" style="text-align:center;">
                                                            <?php if($data_drv[$i]['eStatus'] == 'Active') {
                                                            $dis_img = "img/active-icon.png";
                                                            }else if($data_drv[$i]['eStatus'] == 'Inactive'){
                                                            $dis_img = "img/inactive-icon.png";
                                                            }else if($data_drv[$i]['eStatus'] == 'Deleted'){
                                                            $dis_img = "img/delete-icon.png";
                                                            }?>
                                                            <img src="<?= $dis_img; ?>" alt="<?=$data_drv[$i]['eStatus'];?>" data-toggle="tooltip" title="<?=$data_drv[$i]['eStatus'];?>">
                                                        </td>
                                                        <td align="center" style="text-align:center;" class="action-btn001">
                                                            <div class="share-button openHoverAction-class" style="display: block;">
                                                                <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>
                                                                <div class="social show-moreOptions openPops_<?= $data_drv[$i]['iDeliveyChargeId']; ?>">
                                                                    <ul>
                                                                        <li class="entypo-twitter" data-network="twitter"><a href="delivery_charges_action.php?id=<?= $data_drv[$i]['iDeliveyChargeId']; ?>" data-toggle="tooltip" title="Edit">
                                                                            <img src="img/edit-icon.png" alt="Edit">
                                                                        </a></li>
                                                                        <?php if ($data_drv[$i]['eDefault'] != 'Yes') { ?>
                                                                          <?php if($userObj->hasPermission('update-status-delivery-charges')){ ?>
                                                                            <li class="entypo-facebook" data-network="facebook"><a href="javascript:void(0);" onclick="changeStatus('<?php echo $data_drv[$i]['iDeliveyChargeId']; ?>','Inactive')"  data-toggle="tooltip" title="Activate">
                                                                                <img src="img/active-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >
                                                                            </a></li>
                                                                            <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onclick="changeStatus('<?php echo $data_drv[$i]['iDeliveyChargeId']; ?>','Active')" data-toggle="tooltip" title="Deactivate">
                                                                                <img src="img/inactive-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >  
                                                                            </a></li>
                                                                          <?php } ?>
                                                                          <?php if($userObj->hasPermission('delete-delivery-charges')){ ?>
                                                                          <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onclick="changeStatusDelete('<?php echo $data_drv[$i]['iDeliveyChargeId']; ?>')"  data-toggle="tooltip" title="Delete">
                                                                              <img src="img/delete-icon.png" alt="Delete" >
                                                                          </a></li>
                                                                          <?php } ?>
                                                                        <?php } ?>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        </tr>
                                                    <?php } }else { ?>
                                                        <tr class="gradeA">
                                                            <td colspan="9"> No Records Found.</td>
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
                               1. Set delivery charges as per the location. Ex. delivery charges for city California. You can define the location from Manage location -> Geo fence location.
                              </li>
                              <li>
                                2. You can define the order range for delivery charges from this module. Ex. delivery charges $5 will apply on all orders below $20. Or delivery charges $3 will apply on all orders above $20. 
                              </li>
                              <li>
                                3. You can also define free delivery based on order amount. Say, free delivery on all orders above $100.
                              </li>
                              <li>
                                4. You can also define the free delivery radius. Ex. distance from <?php echo strtolower($langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']); ?> to <?php echo strtolower($langage_lbl_admin['LBL_RIDER']); ?>'s location up to 1 KM will be free.
                              </li>
                              <li>
                              5. Make sure you define the delivery charges for all the areas in which you are going to provide the service.
                              </li>
                            </ul>
                      </div>
                    </div>
                </div>
                <!--END PAGE CONTENT -->
            </div>
            <!--END MAIN WRAPPER -->
            
<form name="pageForm" id="pageForm" action="action/delivery_charges.php" method="post" >
<input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
<input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
<input type="hidden" name="iDeliveyChargeId" id="iMainId01" value="" >
<input type="hidden" name="iLocationId" id="iMainId02" value="" >
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
//               alert(action+formValus);
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