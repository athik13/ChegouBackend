<?php
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();
if (!$userObj->hasPermission('view-promocode')) {
    $userObj->redirect();
}
$script = 'Coupon';

//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
//$ord = ' ORDER BY vCouponCode ASC';
$ord = ' ORDER BY iCouponId DESC';
if ($sortby == 1) {
    if ($order == 0)
        $ord = " ORDER BY vCouponCode ASC";
    else
        $ord = " ORDER BY vCouponCode DESC";
}

if ($sortby == 2) {
    if ($order == 0)
        $ord = " ORDER BY dActiveDate ASC";
    else
        $ord = " ORDER BY dActiveDate DESC";
}
if ($sortby == 5) {
    if ($order == 0)
        $ord = " ORDER BY dExpiryDate ASC";
    else
        $ord = " ORDER BY dExpiryDate DESC";
}

if ($sortby == 3) {
    if ($order == 0)
        $ord = " ORDER BY eValidityType ASC";
    else
        $ord = " ORDER BY eValidityType DESC";
}

if ($sortby == 4) {
    if ($order == 0)
        $ord = " ORDER BY eStatus ASC";
    else
        $ord = " ORDER BY eStatus DESC";
}

if ($sortby == 6) {
    if ($order == 0)
        $ord = " ORDER BY iUsageLimit ASC";
    else
        $ord = " ORDER BY iUsageLimit DESC";
}

if ($sortby == 7) {
    if ($order == 0)
        $ord = " ORDER BY iUsed ASC";
    else
        $ord = " ORDER BY iUsed DESC";
}
if ($sortby == 8) {
    if ($order == 0)
        $ord = " ORDER BY eSystemType ASC";
    else
        $ord = " ORDER BY eSystemType DESC";
}

if ($sortby == 9) {
    if ($order == 0)
        $ord = " ORDER BY vPromocodeType ASC";
    else
        $ord = " ORDER BY vPromocodeType DESC";
}
//End Sorting
// $adm_ssql = "";
// if (SITE_TYPE == 'Demo') {
// $adm_ssql = " And ad.tRegistrationDate > '" . WEEK_DATE . "'";
// }
//For Currency
$sql = "select vSymbol from  currency where eDefault='Yes'";
$db_currency = $obj->MySQLSelect($sql);
// Start Search Parameters
$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
$ssql = '';
if ($keyword != '') {
    if ($option != '') {
        if (strpos($option, 'eStatus') !== false) {
            $ssql .= " AND " . stripslashes($option) . " LIKE '" . $generalobjAdmin->clean($keyword) . "'";
        } else {
            $ssql .= " AND " . stripslashes($option) . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%'";
        }
    } else {
        $ssql .= " AND (vCouponCode LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR eValidityType LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR eStatus LIKE '%" . $generalobjAdmin->clean($keyword) . "%')";
    }
}

if ($eStatus != '' && $keyword == '') {
    $ssql .= " AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";
} else if($eStatus != '') {
    $ssql .= " AND eStatus = '".$eStatus."'";
} else {
    $ssql .= " AND eStatus != 'Deleted'";
}
//$ufxEnable = $generalobj->CheckUfxServiceAvailable(); 
$ufxEnable = isUberXModuleAvailable() ? "Yes" : "No"; //add function to modules availibility
$rideEnable = isRideModuleAvailable() ? "Yes" : "No";
$deliveryEnable = isDeliveryModuleAvailable() ? "Yes" : "No";
$deliverallEnable = isDeliverAllModuleAvailable() ? "Yes" : "No";

if($ufxEnable != "Yes") {
	$ssql .= " AND eSystemType != 'UberX'";
}
if(!checkFlyStationsModule()) {
    $ssql.= " AND eFly = '0'";
}
if($rideEnable != "Yes") {
    $ssql .= " AND eSystemType != 'Ride'";
}
if($deliveryEnable != "Yes") {
    $ssql .= " AND eSystemType != 'Delivery'";
}
if($deliverallEnable != "Yes") {
    $ssql .= " AND eSystemType != 'DeliverAll'";
}
// End Search Parameters
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT COUNT(iCouponId) AS Total FROM coupon WHERE 1 =1 $ssql ";
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

$sql = "SELECT *,dExpiryDate AS dExpiryDate,dActiveDate AS dActiveDate FROM coupon WHERE 1=1 $ssql $ord LIMIT $start, $per_page";

// $ssql $adm_ssql $ord LIMIT $start, $per_page
$data_drv = $obj->MySQLSelect($sql);

$couponArray = array();

if(count($data_drv)> 0 && !empty($data_drv))
{
    for($i=0;$i<count($data_drv);$i++) {   
            array_push($couponArray,$data_drv[$i]['vCouponCode']);
    }
  
    $couponString = "'".implode("','", $couponArray)."'";
    $couponData = $generalobj->getUnUsedPromocode($couponString);

}









/* echo "<pre>";
print_r($data_drv);
exit; */


//echo "<pre>";
$endRecord = count($data_drv);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page')
        $var_filter .= "&$key=" . stripslashes($val);
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
$onlyDeliverallModule = strtoupper(ONLYDELIVERALL);
if($cubeDeliverallOnly > 0){
    $onlyDeliverallModule = "YES";
}
?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | Promo Code</title>
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
                                <h2>PromoCode</h2>
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
                                            <option value="vCouponCode" <?php
                                            if ($option == "vCouponCode") {
                                                echo "selected";
                                            }
                                            ?> >Gift/Certificate code</option>
                                            <option value="eValidityType" <?php
                                            if ($option == 'eValidityType') {
                                                echo "selected";
                                            }
                                            ?> >Validity</option>
                                            <option value="eSystemType" <?php
                                            if ($option == 'eSystemType') {
                                                echo "selected";
                                            }
                                            ?> >System Type</option>
                                            <option value="eStatus" <?php
                                            if ($option == 'eStatus') {
                                                echo "selected";
                                            }
                                            ?> >Status</option>
                                            
                                            <option value="vPromocodeType" <?php
                                            if ($option == 'vPromocodeType') {
                                                echo "selected";
                                            }
                                            ?> ><?= $langage_lbl_admin['LBL_PROMOCODE_TYPE'] ?></option>
                                        </select>
                                    </td>

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
                                    <td width="15%"><input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"  class="form-control" /></td>
                                    <td width="15%">
                                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'coupon.php'"/>
                                    </td>
                                    <?php if ($userObj->hasPermission('create-promocode')) { ?>
                                        <td width="30%"><a class="add-btn" href="coupon_action.php" style="text-align: center;">Add PROMO CODE</a></td>
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
                                            <?php if ($userObj->hasPermission(['delete-promocode', 'update-status-promocode'])) { ?>
                                                <select name="changeStatus" id="changeStatus" class="form-control" onchange="ChangeStatusAll(this.value);">
                                                    <option value="" >Select Action</option>
                                                    <?php if ($userObj->hasPermission('update-status-promocode')) { ?>
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
                                                            <?php if ($userObj->hasPermission('delete-promocode')) { ?>
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
                                        <div class="panel-heading">
                                            <form name="_export_form" id="_export_form" method="post" >
                                                <button type="button" onclick="showExportTypes('coupon')" >Export</button>
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
                                                    <th align="center" width="" style="text-align:center;"><input type="checkbox" id="setAllCheck" ></th>

                                                    <th width=""><a href="javascript:void(0);" onClick="Redirect(1,<?php
                                                        if ($sortby == '1') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Gift/Certificate Code <?php
                                                                        if ($sortby == 1) {
                                                                            if ($order == 0) {
                                                                                ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                    <th style="text-align:right;" width="">Discount</th>

                                                    <th width=""><a href="javascript:void(0);" onClick="Redirect(3,<?php
                                                        if ($sortby == '3') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Validity <?php
                                                                        if ($sortby == 3) {
                                                                            if ($order == 0) {
                                                                                ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                
                                                <th width=""><a href="javascript:void(0);" onClick="Redirect(9,<?php
                                                        if ($sortby == '9') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Promocode Type <?php
                                                                        if ($sortby == 9) {
                                                                            if ($order == 0) {
                                                                                ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                


                                                    <th width=""><a href="javascript:void(0);" onClick="Redirect(2,<?php
                                                        if ($sortby == '2') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Activation Date <?php
                                                                        if ($sortby == 2) {
                                                                            if ($order == 0) {
                                                                                ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                    <th width=""><a href="javascript:void(0);" onClick="Redirect(5,<?php
                                                        if ($sortby == '5') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Expiry Date <?php
                                                                        if ($sortby == 5) {
                                                                            if ($order == 0) {
                                                                                ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                    <th width=""><a href="javascript:void(0);" onClick="Redirect(6,<?php
                                                        if ($sortby == '6') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Usage Limit <?php
                                                                        if ($sortby == 6) {
                                                                            if ($order == 0) {
                                                                                ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a><i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Promo code can be used one time only for each user. So if you set Usage limit to 100 then 100 unique user can use this promo code."></i></th>

                                                    
                                                    <th width=""><a href="javascript:void(0);" onClick="Redirect(7,<?php
                                                        if ($sortby == '7') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Used <?php
                                                                        if ($sortby == 7) {
                                                                            if ($order == 0) {
                                                                                ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                    <?php if($onlyDeliverallModule == "NO") { ?>
                                                        <th> Used In Schedule Booking</th>
                                                    <?php } ?>

                                                
                                                            <? if (($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery') && $onlyDeliverallModule == "NO") { ?>
                                                        <th width=""><a href="javascript:void(0);" onClick="Redirect(8,<?php
                                                            if ($sortby == '8') {
                                                                echo $order;
                                                            } else {
                                                                ?>0<?php } ?>)">System Type <?php
                                                                            if ($sortby == 8) {
                                                                                if ($order == 0) {
                                                                                    ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                    }
                                                                } else {
                                                                    ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                            <? } ?> 


                                                    <th width="" align="center" style="text-align:center;"><a href="javascript:void(0);" onClick="Redirect(4,<?php
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

                                                    <th width="" align="center" style="text-align:center;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if (!empty($data_drv)) {
                                                    for ($i = 0; $i < count($data_drv); $i++) {

                                                        //echo "<pre>";
                                                        //print_r($data_drv);die;
                                                        /* $default = '';
                                                          if($data_drv[$i]['eDefault']=='Yes'){
                                                          $default = 'disabled';
                                                          } */  

                                                       
                                                        //$isUsedInManualBooking = "No";
                                                        
                                                         
                                                        ?>
                                                        <tr class="gradeA">

                                                            <td align="center" style="text-align:center;"><input type="checkbox" id="checkbox" name="checkbox[]" <?php echo $default; ?> value="<?php echo $data_drv[$i]['iCouponId']; ?>" />&nbsp;</td>
                                                            <td><?= $data_drv[$i]['vCouponCode']; ?></td>
                                                            <?php
                                                            if ($data_drv[$i]['eType'] == "percentage") {
                                                                $e_value = "%";
                                                            } else {
                                                                $e_value = $db_currency[0]['vSymbol'];
                                                            }
                                                            ?>
                                                            <td align="right"><?= $data_drv[$i]['fDiscount'] . " " . $e_value; ?></td>

                                                            <td><?
                                                                if ($data_drv[$i]['eValidityType'] == "Defined") {
                                                                    echo "Custom";
                                                                } else {
                                                                    echo $data_drv[$i]['eValidityType'];
                                                                }
                                                                ?></td>

                                                            <td><?php echo $data_drv[$i]['vPromocodeType']; ?></td>
                                                            <td><?= $generalobjAdmin->DateTime($data_drv[$i]['dActiveDate'], 'no'); ?></td>
                                                            <td><?= $generalobjAdmin->DateTime($data_drv[$i]['dExpiryDate'], 'no'); ?></td>
                                                            <td><?= $data_drv[$i]['iUsageLimit']; ?></td>
                                                            <?php if ($data_drv[$i]['iUsed'] != 0) { ?>
                                                                <td>
                                                                    <?php
                                                                    if ($data_drv[$i]['eSystemType'] == "DeliverAll") {
                                                                        if ($userObj->hasPermission('view-all-orders')) {
                                                                            ?>
                                                                            <a href="allorders.php?type=allorders&promocode=<?php echo $data_drv[$i]['vCouponCode']; ?>">
                                                                                <?php
                                                                            }
                                                                        } else {    if ($userObj->hasPermission('manage-trip-jobs')) { ?>
                                                                                                    <a href="trip.php?promocode=<?php echo $data_drv[$i]['vCouponCode']; ?>">
                                                                        <?php
                                                                                }
                                                                                    
                                                                                
                                                                            }
                                                                            ?>
                                                                            <?= $data_drv[$i]['iUsed']; ?>
                                                                            <?php if ($userObj->hasPermission('manage-trip-jobs') || $userObj->hasPermission('view-all-orders')) { ?>    
                                                                            </a>
                                                                        <?php } ?>
                                                                </td> 
                                                            <?php } else { ?>
                                                                <td><?= $data_drv[$i]['iUsed']; ?></td>
                                                            <?php } ?>
                                                            
                                                                
                                                         <?php if($onlyDeliverallModule == "NO") { ?>
                                                            <td width="5%" align="center" > 
                                                                <?php 
                                                                        if (array_key_exists($data_drv[$i]['vCouponCode'], $couponData)) {
                                                                                     if ($userObj->hasPermission('manage-ride-job-later-bookings')) { ?>    
                                                                                            <a href="cab_booking.php?promocode=<?php echo $data_drv[$i]['vCouponCode']; ?>">
                                                                                            <?php  echo  $couponData[$data_drv[$i]['vCouponCode']];
                                                    
                                                                                }
                                                                        }
                                                                        else {
                                                                                    echo "0";
                                                                        }
                                                                ?>
                                                            </td>
                                                        <?php } ?>

                                                            <? if (($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery') && $onlyDeliverallModule == "NO") { ?>
                                                                <td>
                                                                    <?php
                                                                    if($data_drv[$i]['eFly']==1 && $data_drv[$i]['eSystemType']=='Ride') {
                                                                    echo $langage_lbl_admin['LBL_HEADER_RDU_FLY_RIDE'];
                                                                    } else {
                                                                    echo $data_drv[$i]['eSystemType']; 
                                                                    } ?></td>
                                                            <? } ?>

                                                            <td width="10%" align="center">
                                                                <?
                                                                if ($data_drv[$i]['eStatus'] == 'Active') {
                                                                    $dis_img = "img/active-icon.png";
                                                                } else if ($data_drv[$i]['eStatus'] == 'Inactive') {
                                                                    $dis_img = "img/inactive-icon.png";
                                                                } else if ($data_drv[$i]['eStatus'] == 'Deleted') {
                                                                    $dis_img = "img/delete-icon.png";
                                                                }
                                                                ?>
                                                                <img src="<?= $dis_img; ?>" alt="<?= $data_drv[$i]['eStatus'] ?>" data-toggle="tooltip" title="<?php echo $data_drv[$i]['eStatus']; ?>"> 
                                                            </td>                                                                      
                                                            <td align="center" style="text-align:center;" class="action-btn001">
                                                                <div class="share-button openHoverAction-class" style="display: block;">
                                                                    <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>
                                                                    <div class="social show-moreOptions openPops_<?= $data_drv[$i]['iCouponId']; ?>">
                                                                        <ul>
                                                                            <li class="entypo-twitter" data-network="twitter"><a href="coupon_action.php?iCouponId=<?= $data_drv[$i]['iCouponId']; ?>" data-toggle="tooltip" title="Edit">
                                                                                    <img src="img/edit-icon.png" alt="Edit">
                                                                                </a></li>
                                                                            <?php if ($data_drv[$i]['eDefault'] != 'Yes') { ?>
                                                                                <?php if ($userObj->hasPermission('update-status-promocode')) { ?>
                                                                                    <li class="entypo-facebook" data-network="facebook"><a href="javascript:void(0);" onclick="changeStatus('<?php echo $data_drv[$i]['iCouponId']; ?>', 'Inactive')"  data-toggle="tooltip" title="Activate">
                                                                                            <img src="img/active-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >
                                                                                        </a></li>
                                                                                    <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onclick="changeStatus('<?php echo $data_drv[$i]['iCouponId']; ?>', 'Active')" data-toggle="tooltip" title="Deactivate">
                                                                                            <img src="img/inactive-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >	
                                                                                        </a></li>
                                                                                <?php } ?>
                                                                                <?php if ($userObj->hasPermission('delete-promocode')) { ?>
                                                                                    <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onclick="changeStatusDelete('<?php echo $data_drv[$i]['iCouponId']; ?>')"  data-toggle="tooltip" title="Delete">
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
                                                        <td colspan="11"> No Records Found.</td>
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
                            <li> Coupon module will list all coupons on this page. </li>
                            <li> Administrator can Activate / Deactivate / Delete any coupon.</li>
                            <li> Administrator can export data in XLS format.</li>
                            <li> PromoCode Type : <br>
                                 Public  : If the Admin User selects PromoCode Type as “Public�? then all the User in entire system would be able to see the respective PromoCode in the apps while trying to apply the PromoCode. <br/>
                                 Private : If the Admin User selects PromoCode Type as “Private�? the respective PromoCode would not be visible to the in the apps while trying to apply the PromoCode. However, if the admin shares the private PromoCode with any of the user by any mode that promocode would be applied if it’s a valid.
                             </li>
                        </ul>
                    </div>

                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->

        <form name="pageForm" id="pageForm" action="action/coupon.php" method="post" >
            <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
            <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
            <input type="hidden" name="iCouponId" id="iMainId01" value="" >
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

        </script>
    </body>
    <!-- END BODY-->
</html>