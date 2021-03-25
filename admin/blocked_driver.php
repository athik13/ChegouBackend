<?php
include_once('../common.php');
$$msgType = "";
global $userObj;
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
if (!$userObj->hasPermission('view-blocked-driver')) {
    $userObj->redirect();
}
$script = 'blockeddriver';

$activeTab = 'Blocked';

$cancel_for_hours = $CANCEL_DECLINE_TRIPS_IN_HOURS;

$c_date = date("Y-m-d H:i:s");
$s_date = date("Y-m-d H:i:s", strtotime('-' . $cancel_for_hours . ' hours'));


$iCompanyId = isset($_REQUEST['iCompanyId']) ? $_REQUEST['iCompanyId'] : '';
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';

$ord = ' ORDER BY  Allcountercanceled DESC';
if ($sortby == 1) {
    if ($order == 0)
        $ord = " ORDER BY rd.vName ASC";
    else
        $ord = " ORDER BY rd.vName DESC";
}
if ($sortby == 2) {
    if ($order == 0)
        $ord = " ORDER BY rd.vEmail ASC";
    else
        $ord = " ORDER BY rd.vEmail DESC";
}
if ($sortby == 3) {
    if ($order == 0)
        $ord = " ORDER BY countertrip ASC";
    else
        $ord = " ORDER BY countertrip DESC";
}
if ($sortby == 4) {
    if ($order == 0)
        $ord = " ORDER BY counterdeclined ASC";
    else
        $ord = " ORDER BY counterdeclined DESC";
}

if ($sortby == 5) {
    if ($order == 0)
        $ord = " ORDER BY Allcountercanceled ASC";
    else
        $ord = " ORDER BY Allcounterdeclined DESC";
}

if ($sortby == 6) {
    if ($order == 0)
        $ord = " ORDER BY `Allcounterdeclined` ASC";
    else
        $ord = " ORDER BY `Allcounterdeclined` DESC";
}

if ($sortby == 7) {
    if ($order == 0)
        $ord = " ORDER BY `eIsBlocked` ASC";
    else
        $ord = " ORDER BY `eIsBlocked` DESC";
}
if ($sortby == 8) {
    if ($order == 0)
        $ord = " ORDER BY tBlockeddate ASC";
    else
        $ord = " ORDER BY tBlockeddate DESC";
}
//End Sorting
$dri_ssql = "";
if (SITE_TYPE == 'Demo') {
    $dri_ssql = " And rd.tRegistrationDate > '" . WEEK_DATE . "'";
}

// Start Search Parameters
$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eIsBlocked = isset($_REQUEST['eIsBlocked']) ? $_REQUEST['eIsBlocked'] : '';
$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');

if ($_POST['action'] == "Blocked") {
    $iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : "";
    $eIsBlocked1 = isset($_REQUEST['eIsBlocked1']) ? $_REQUEST['eIsBlocked1'] : "No";
    $sql = "SELECT vEmail,vLastName,vName FROM register_driver  where  iDriverId =  '" . $iDriverId . "'";
    $data_drv = $obj->MySQLSelect($sql);
    $vEmail = $data_drv[0]['vEmail'];
    $vName = ucfirst($data_drv[0]['vName']);
    $vLastName = $data_drv[0]['vLastName'];
    if ($eIsBlocked1 == "Yes") {
        $mailTemplate = "MEMBER_BLOCKED_INACTIVE_DRIVER";
    } else if ($eIsBlocked1 == "No") {
        $mailTemplate = "MEMBER_BLOCKED_ACTIVE_DRIVER";
    }
    $tBlockeddate = date('Y-m-d H:i:s');
    $maildata['EMAIL'] = $vEmail;
    $maildata['NAME'] = ucfirst($vName) . ' ' . $vLastName;
    $generalobj->send_email_user($mailTemplate, $maildata);
    $Fsql = "UPDATE `register_driver` SET `eIsBlocked`='" . $eIsBlocked1 . "',`tBlockeddate`='" . $tBlockeddate . "' WHERE iDriverId ='" . $iDriverId . "'";
    $obj->sql_query($Fsql);
    $_SESSION['success'] = '1';
    $_SESSION['var_msg'] = $langage_lbl_admin["LBL_Record_Updated_successfully"];
    //header("Location:" . $tconfig["tsite_url_main_admin"] . "blocked_driver.php");
    //exit;
}

$ssql = '';

if ($keyword != '') {
    $keyword_new = $keyword;
    $chracters = array("(", "+", ")");
    $removespacekeyword = preg_replace('/\s+/', '', $keyword);
    $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));
    if (is_numeric($keyword_new)) {
        $keyword_new = $keyword_new;
    } else {
        $keyword_new = $keyword;
    }
    if ($option != '') {
        $option_new = $option;
        if ($option == 'DriverName') {
            $option_new = "CONCAT(rd.vName,' ',rd.vLastName)";
        }
        if ($eIsBlocked != '') {
            $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' AND rd.eIsBlocked = '" . $generalobjAdmin->clean($eIsBlocked) . "'";
        } else {
            $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%'";
        }
    } else {
        if (ONLYDELIVERALL == 'Yes') {
            if ($eIsBlocked != '') {
                $ssql .= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR rd.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%') AND rd.eIsBlocked = '" . $generalobjAdmin->clean($eIsBlocked) . "'";
            } else {
                $ssql .= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR rd.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%')";
            }
        } else {
            if ($eIsBlocked != '') {
                $ssql .= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR rd.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%') AND rd.eIsBlocked = '" . $generalobjAdmin->clean($eIsBlocked) . "'";
            } else {
                $ssql .= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR rd.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%')";
            }
        }
    }
} else if ($eIsBlocked != '' && $keyword == '') {

    $ssql .= " AND rd.eIsBlocked = '" . $generalobjAdmin->clean($eIsBlocked) . "'";
}

// End Search Parameters

$ssql1 = "AND (rd.vEmail != '' OR rd.vPhone != '')";

//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page

$sql = "SELECT  COUNT(rd.iDriverId) AS Total
				FROM  register_driver rd 
				LEFT JOIN 
				  ( SELECT  iDriverId,COUNT( tr.iTripId ) AS cnt,iActive,tEndDate   FROM
					trips tr where tEndDate BETWEEN  '" . $s_date . "' AND  '" . $c_date . "'  AND  iActive =  'Canceled' AND eCancelledBy	='Driver'
					GROUP BY tr.iDriverId ) m
				ON rd.iDriverId = m.iDriverId	
				LEFT JOIN 
				  ( SELECT  iDriverId,COUNT( trAll.iTripId ) AS cntAll,iActive   FROM
					trips trAll where  iActive =  'Canceled' AND eCancelledBy	='Driver'
					GROUP BY trAll.iDriverId ) mAll
				ON rd.iDriverId = mAll.iDriverId					
				LEFT JOIN
				  ( SELECT  iDriverId,COUNT( dr.iDriverRequestId ) AS cnt,dAddedDate,eStatus  FROM
					 driver_request dr where  dr.dAddedDate BETWEEN  '" . $s_date . "'  AND  '" . $c_date . "'	AND dr.eStatus =  'Decline' 
					GROUP BY  dr.iDriverId ) d
				ON rd.iDriverId = d.iDriverId					
				LEFT JOIN
				  ( SELECT  iDriverId,COUNT( drAll.iDriverRequestId ) AS cntAll,dAddedDate,eStatus  FROM
					 driver_request drAll where  drAll.eStatus =  'Decline' 
					GROUP BY  drAll.iDriverId ) dAll
				ON rd.iDriverId = dAll.iDriverId  where (mAll.cntAll >'0'  $ssql $ssql1 $dri_ssql) OR  (dAll.cntAll >'0' $ssql $ssql1 $dri_ssql) ";

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

$sql = "SELECT  rd.iDriverId , 
				   COALESCE( m.cnt , 0 ) AS countertrip,
				   COALESCE( d.cnt, 0 ) AS counterdeclined,
				   COALESCE( mAll.cntAll, 0 ) AS Allcountercanceled,
				   COALESCE( dAll.cntAll, 0 ) AS Allcounterdeclined,rd.vEmail,rd.tRegistrationDate,rd.eIsBlocked,rd.tBlockeddate,rd.vCode,rd.eStatus,CONCAT(rd.vName,' ',rd.vLastName) AS driverName
				FROM  register_driver rd 
				LEFT JOIN 
				  ( SELECT  iDriverId,COUNT( tr.iTripId ) AS cnt,iActive,tEndDate   FROM
					trips tr where tEndDate BETWEEN  '" . $s_date . "' AND  '" . $c_date . "'  AND  iActive =  'Canceled' AND eCancelledBy	='Driver'
					GROUP BY tr.iDriverId ) m
				ON rd.iDriverId = m.iDriverId	
				LEFT JOIN 
				  ( SELECT  iDriverId,COUNT( trAll.iTripId ) AS cntAll,iActive   FROM
					trips trAll where  iActive =  'Canceled' AND eCancelledBy	='Driver'
					GROUP BY trAll.iDriverId ) mAll
				ON rd.iDriverId = mAll.iDriverId					
				LEFT JOIN
				  ( SELECT  iDriverId,COUNT( dr.iDriverRequestId ) AS cnt,dAddedDate,eStatus  FROM
					 driver_request dr where  dr.dAddedDate BETWEEN  '" . $s_date . "'  AND  '" . $c_date . "'	AND dr.eStatus =  'Decline' 
					GROUP BY  dr.iDriverId ) d
				ON rd.iDriverId = d.iDriverId					
				LEFT JOIN
				  ( SELECT  iDriverId,COUNT( drAll.iDriverRequestId ) AS cntAll,dAddedDate,eStatus  FROM
					 driver_request drAll where  drAll.eStatus =  'Decline' 
					GROUP BY  drAll.iDriverId ) dAll
				ON rd.iDriverId = dAll.iDriverId  where (mAll.cntAll >'0' $ssql $ssql1 $dri_ssql) OR  (dAll.cntAll >'0' $ssql $ssql1 $dri_ssql)  $ord LIMIT $start, $per_page";



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
        <title><?= $SITE_NAME ?> |  Decline/Cancelled <?= $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'] ?> Alert For <?= $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN']; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php include_once('global_files.php'); ?>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53">
        <!-- Main Loading -->
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
                                <h2><?php echo "Decline/Cancelled " . $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'] . " Alert For " . $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN'];
            ; ?></h2>
                                <!--<input type="button" id="" value="ADD A DRIVER" class="add-btn">-->
                            </div>
                        </div>
                        <hr />
                    </div>
<?php include('valid_msg.php'); ?>
                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                        <input type="hidden" name="iDriverId" value="<?php echo $iDriverId; ?>" >
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                            <tbody>
                                <tr>
                                    <td width="5%"><label for="textfield"><strong>Search:</strong></label></td>
                                    <td width="10%" class="padding-right10"><select name="option" id="option" class="form-control">
                                            <option value="">All</option>
                                            <option  value="DriverName" <?php
                                            if ($option == "DriverName") {
                                                echo "selected";
                                            }
                                            ?> ><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Name</option>                                      

                                            <option value="rd.vEmail" <?php
                                            if ($option == 'rd.vEmail') {
                                                echo "selected";
                                            }
                                            ?> >E-mail</option> 
                                            <!-- <option value="rd.eStatus" <?php
                                            if ($option == 'rd.eStatus') {
                                                echo "selected";
                                            }
                                            ?> >Status</option> -->

                                        </select>
                                    </td>
                                    <td width="15%" class="searchform"><input type="Text" id="keyword" name="keyword" value="<?
                                        if (!empty($keyword)) {
                                            echo $generalobjAdmin->clearName($keyword);
                                        }
                                        ?>"  class="form-control" /></td>
                                    <td width="12%" class="estatus_options" id="eStatus_options" >
                                        <select name="eIsBlocked" id="eIsBlocked" class="form-control">
                                            <option value="" >Select Status</option> 
                                            <option value="Yes" <?php
                                            if ($eIsBlocked == 'Yes') {
                                                echo "selected";
                                            }
                                            ?> >Blocked</option> 
                                            <option value="No" <?php
                                            if ($eIsBlocked == 'No') {
                                                echo "selected";
                                            }
                                            ?> >Unblocked</option> 
                                        </select>
                                    </td>
                                    <td>
                                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'blocked_driver.php'"/>
                                    </td> 
                                </tr>
                            </tbody>
                        </table>
                    </form>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="admin-nir-export">
<?php if (!empty($data_drv)) { ?>
                                        <div class="panel-heading">
                                            <form name="_export_form" id="_export_form" method="post" >
                                                <button type="button" onClick="showExportTypes('blocked_driver')" >Export</button>
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
                                                    <th width="13%"><a href="javascript:void(0);" onClick="Redirect(1,<?php
                                                        if ($sortby == '1') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Name <?php
                                                                           if ($sortby == 1) {
                                                                               if ($order == 0) {
                                                                                   ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                    <th width="18%"><a href="javascript:void(0);" onClick="Redirect(2,<?php
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


                                                    <th width="12%" class="align-center"><a href="javascript:void(0);" onClick="Redirect(3,<?php
                                                        if ($sortby == '3') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Total Cancelled Trips (In <?= $cancel_for_hours ?> hours) <?php
                                                                                                if ($sortby == 3) {
                                                                                                    if ($order == 0) {
                                                                                                        ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>


                                                    <th width="12%" class="align-center"><a href="javascript:void(0);" onClick="Redirect(4,<?php
                                                        if ($sortby == '4') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Total Declined Trips (In <?= $cancel_for_hours ?> hours)  <?php
                                                                                                if ($sortby == 4) {
                                                                                                    if ($order == 0) {
                                                                                                        ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                    <th width="12%" class="align-center"><a href="javascript:void(0);" onClick="Redirect(5,<?php
                                                        if ($sortby == '5') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Total Cancelled Trips (Till now) <?php
                                                                                                if ($sortby == 5) {
                                                                                                    if ($order == 0) {
                                                                                                        ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                    <th width="12%" class="align-center"><a href="javascript:void(0);" onClick="Redirect(6,<?php
                                                        if ($sortby == '6') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Total Declined Trips (Till now) <?php
                                                                                                if ($sortby == 6) {
                                                                                                    if ($order == 0) {
                                                                                                        ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                        <?php if ($userObj->hasPermission('update-status-blocked-driver')) { ?>
                                                        <th width="12%" class="align-center"><a href="javascript:void(0);" onClick="Redirect(7,<?php
                                                            if ($sortby == '7') {
                                                                echo $order;
                                                            } else {
                                                                ?>0<?php } ?>)">Block <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?><?php
                                                                                                    if ($sortby == 7) {
                                                                                                        if ($order == 0) {
                                                                                                            ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                    }
                                                                } else {
                                                                    ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                        <?php } ?>    
                                                    <th width="12%" class="align-center"><a href="javascript:void(0);" onClick="Redirect(7,<?php
                                                        if ($sortby == '8') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Block Date <?php
                                                                                                if ($sortby == 8) {
                                                                                                    if ($order == 0) {
                                                                                                        ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                </tr>

                                            </thead>
                                            <tbody>
                                                <?
                                                if (!empty($data_drv)) {
                                                    for ($i = 0; $i < count($data_drv); $i++) {
                                                        $status_cmp = ($data_drv[$i]['cmp_status'] == "Inactive") ? " (Inactive)" : "";
                                                        ?>
                                                        <tr class="gradeA" >
                                                            <td>
                                                                <a href="javascript:void(0);" onClick="show_driver_details('<?= $data_drv[$i]['iDriverId']; ?>')" style="text-decoration: underline;"><?= $generalobjAdmin->clearName($data_drv[$i]['driverName']); ?></a>
                                                            </td> 
                                                            <td style="word-break: break-all;"><?= $generalobjAdmin->clearEmail($data_drv[$i]['vEmail']); ?></td> 
                                                            <td align="center" ><?= $data_drv[$i]['countertrip']; ?></td>
                                                            <td align="center" ><?= $data_drv[$i]['counterdeclined']; ?></td>
                                                            <td align="center" ><?= $data_drv[$i]['Allcountercanceled']; ?></td>
                                                            <td align="center" ><?= $data_drv[$i]['Allcounterdeclined']; ?></td>
                                                            <?php
                                                            if ($userObj->hasPermission('update-status-blocked-driver')) {
                                                                if ($data_drv[$i]['eIsBlocked'] == "No") {
                                                                    ?>
                                                                    <td align="center"><a style="background-color:#DDDDDD;color:#000;
                                                                                          " class="btn" href="javascript:void(0);" onClick="Block_driver_details('<?= $data_drv[$i]['iDriverId']; ?>')" ><i class="fa fa-check-circle"style="color:#000;" ></i> <?= ucfirst($data_drv[$i]['eIsBlocked']); ?> 
                                                                        </a></td> 
            <?php } else { ?>
                                                                    <td align="center">
                                                                        <!--  <form name="frmfeatured" id="frmfeatured" action="" method="post">
                                                                             <input type="hidden" name="iDriverId" value="<?php echo $data_drv[$i]['iDriverId']; ?>" >
                                                                             <input type="hidden" name="eIsBlocked" value="<?= ($data_drv[$i]['eIsBlocked'] == "Yes") ? 'No' : 'Yes' ?>" >
                                                                             <input type="hidden" name="action" value="Blocked" >
                                                                             <button class="btn btn-danger">
                                                                                 <i class="<?= ($data_drv[$i]['eIsBlocked'] == "Yes") ? 'fa fa-check-circle' : 'fa fa-check-circle-o' ?>"></i> <?= ucfirst($data_drv[$i]['eIsBlocked']); ?>
                                                                             </button>
                                                                         </form> -->
                                                                        <a class="btn btn-danger" href="javascript:void(0);" onClick="Block_driver_details1('<?= $data_drv[$i]['iDriverId']; ?>')" ><i class="fa fa-check-circle" ></i> <?= ucfirst($data_drv[$i]['eIsBlocked']); ?> 
                                                                        </a>
                                                                    </td>
            <?php } ?>
        <?php } ?>
                                                            <td align="center" ><?= $generalobjAdmin->DateTime($data_drv[$i]['tBlockeddate'], 'No') ?></td>



                                                        </tr>
                                                        <?php
                                                    }
                                                } else {
                                                    ?>
                                                    <tr class="gradeA">
                                                        <td colspan="14"> No Records Found.</td>
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

        <form name="pageForm" id="pageForm" action="action/blocked_driver.php" method="post" >
            <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
            <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
            <input type="hidden" name="iDriverId" id="iMainId01" value="" >
            <input type="hidden" name="iCompanyId" id="iCompanyId" value="<?php echo $iCompanyId; ?>" >
            <input type="hidden" name="eStatus" id="eStatus" value="<?php echo $eStatus; ?>" >
            <input type="hidden" name="status" id="status01" value="" >
            <input type="hidden" name="statusVal" id="statusVal" value="" >
            <input type="hidden" name="option" value="<?php echo $option; ?>" >
            <input type="hidden" name="keyword" value="<?php echo $keyword; ?>" >
            <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
            <input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
            <input type="hidden" name="method" id="method" value="" >
        </form>

        <div  class="modal fade" id="detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
            <div class="modal-dialog" >
                <div class="modal-content">
                    <div class="modal-header">
                        <h4>
                            <i style="margin:2px 5px 0 2px;"><img src="images/icon/driver-icon.png" alt=""></i>
<?= $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN']; ?> Details
                            <button type="button" class="close" data-dismiss="modal">x</button>
                        </h4>
                    </div>
                    <div class="modal-body" style="max-height: 450px;overflow: auto;">
                        <div id="imageIcons" style="display:none">
                            <div align="center">                                                                       
                                <img src="default.gif"><br/>                                                            
                                <span>Retrieving details,please Wait...</span>                       
                            </div>    
                        </div>
                        <div id="driver_detail"></div>
                    </div>
                </div>
            </div>
        </div> 
        <div  class="modal fade " id="blocked_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
            <div class="modal-dialog" >
                <div class="modal-content">
                    <div class="modal-header"><h4>Block <?php echo $langage_lbl_admin['LBL_RIDER']; ?>?</h4></div>  
                    <div class="modal-body" style="max-height: 450px;overflow: auto;">
                        <p id="new-msg-activeid">If you block the <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> he will unable to take the job. Are you sure you want to block?</p>
                        <div id="imageIconss">
                            <div align="center">                                                                       
                                <img src="default.gif"><br/>                                                            
                                <span>Retrieving details,please Wait...</span>                       
                            </div>    
                        </div>
                        <div id="blockeddriver" ></div>
                    </div>
                </div>
            </div>
        </div>


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
            function show_driver_details(driverid) {
                $("#driver_detail").html('');
                $("#imageIcons").show();
                $("#detail_modal").modal('show');

                if (driverid != "") {
                    var request = $.ajax({
                        type: "POST",
                        url: "ajax_driver_details.php",
                        data: "iDriverId=" + driverid,
                        datatype: "html",
                        success: function (data) {
                            $("#driver_detail").html(data);
                            $("#imageIcons").hide();
                        }
                    });
                }
            }


            function Block_driver_details(driverId) {
                $("#blockeddriver").html('');
                $("#imageIconss").show();
                $("#blocked_modal").modal('show');

                if (driverId != "") {
                    var request = $.ajax({
                        type: "POST",
                        url: "ajax_blockdriver_details.php",
                        data: "iDriverId=" + driverId,
                        datatype: "html",
                        success: function (data) {

                            $("#blockeddriver").html(data);
                            $("#imageIconss").hide();
                        }
                    });
                }
            }
            function Block_driver_details1(driverId) {
                $("#blockeddriver").html('');

                if (driverId != "") {
                    var request = $.ajax({
                        type: "POST",
                        url: "ajax_blockdriver_details.php",
                        data: "iDriverId=" + driverId,
                        datatype: "html",
                        success: function (data) {

                            $("#blockeddriver").html(data);
                            $("#imageIconss").hide();
                            $('#frmfeatured').submit();
                        }
                    });
                }
            }
            function changeOrder(iAdminId) {
                $('#is_dltSngl_modal').modal('show');
                $(".action_modal_submit").unbind().click(function () {
                    var action = $("#pageForm").attr('action');
                    var page = $("#pageId").val();
                    $("#pageId01").val(page);
                    $("#iMainId01").val(iAdminId);
                    $("#method").val('delete');
                    var formValus = $("#pageForm").serialize();
                    window.location.href = action + "?" + formValus;
                });
            }

            function checkzero(userlimit)
            {
                if (userlimit != "") {
                    if (userlimit == 0)
                    {
                        $('#iLimitmsg').html('<span class="red">You Can Not Enter Zero Number</span>');
                    } else if (userlimit <= 0) {
                        $('#iLimitmsg').html('<span class="red">You Can Not Enter Negative Number</span>');
                    } else {
                        $('#iLimitmsg').html('');
                    }
                } else {
                    $('#iLimitmsg').html('');
                }
            }
        </script>
    </body>
    <!-- END BODY-->
</html>