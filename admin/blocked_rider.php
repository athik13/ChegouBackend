<?php
include_once('../common.php');
$$msgType = "";
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
//$generalobjAdmin->check_member_login(); 
define("CONFIGURATIONS", "configurations");
if (!$userObj->hasPermission('view-blocked-rider')) {
    $userObj->redirect();
}
$script = 'blockedrider';
$activeTab = 'Blocked';
$cancel_for_hours = $CANCEL_DECLINE_TRIPS_IN_HOURS;
$c_date = date("Y-m-d H:i:s");
$s_date = date("Y-m-d H:i:s", strtotime('-' . $cancel_for_hours . ' hours'));
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');
$ord = ' ORDER BY Allcountertrip DESC';
if ($sortby == 1) {
    if ($order == 0)
        $ord = " ORDER BY vName ASC";
    else
        $ord = " ORDER BY vName DESC";
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
        $ord = " ORDER BY Allcountertrip ASC";
    else
        $ord = " ORDER BY Allcountertrip DESC";
}if ($sortby == 7) {
    if ($order == 0)
        $ord = " ORDER BY eIsBlocked ASC";
    else
        $ord = " ORDER BY eIsBlocked DESC";
}if ($sortby == 8) {
    if ($order == 0)
        $ord = " ORDER BY tBlockeddate ASC";
    else
        $ord = " ORDER BY tBlockeddate DESC";
}
//End Sorting
$rdr_ssql = "";
if (SITE_TYPE == 'Demo') {
    $rdr_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
}
// Start Search Parameters

$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
$eIsBlocked = isset($_REQUEST['eIsBlocked']) ? $_REQUEST['eIsBlocked'] : "";
$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');

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
        if ($option == 'RiderName') {
            $option_new = "CONCAT(vName,' ',vLastName)";
        }
        if ($eIsBlocked != '') {
            $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' AND eIsBlocked = '" . $generalobjAdmin->clean($eIsBlocked) . "'";
        } else {
            $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%'";
        }
    } else {
        if ($eIsBlocked != '') {
            $ssql .= " AND (concat(vName,' ',vLastName) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%') AND eIsBlocked = '" . $generalobjAdmin->clean($eIsBlocked) . "'";
        } else {
            $ssql .= " AND (concat(vName,' ',vLastName) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%')";
        }
    }
} else if ($eIsBlocked != '' && $keyword == '') {

    $ssql .= " AND rd.eIsBlocked = '" . $generalobjAdmin->clean($eIsBlocked) . "'";
}
$ssql1 = "AND (vEmail != '' OR vPhone != '')";
if ($_POST['action'] == "Blocked") {
    $iUserId = isset($_REQUEST['iUserId']) ? $_REQUEST['iUserId'] : "";
    $eIsBlocked1 = isset($_REQUEST['eIsBlocked1']) ? $_REQUEST['eIsBlocked1'] : "No";
    $sql = "SELECT vEmail,vLastName,vName FROM register_user  where  iUserId =  '" . $iUserId . "'";
    $data_drv = $obj->MySQLSelect($sql);
    $vEmail = $data_drv[0]['vEmail'];
    $vName = ucfirst($data_drv[0]['vName']);
    $vLastName = $data_drv[0]['vLastName'];
    if ($eIsBlocked1 == "Yes") {
        $mailTemplate = "MEMBER_BLOCKED_INACTIVE_USER";
    } elseif ($eIsBlocked1 == "No") {
        $mailTemplate = "MEMBER_BLOCKED_ACTIVE_USER";
    }
    $tBlockeddate = date('Y-m-d H:i:s');
    $maildata['EMAIL'] = $vEmail;
    $maildata['NAME'] = ucfirst($vName) . ' ' . $vLastName;
    $generalobj->send_email_user($mailTemplate, $maildata);
    $Fsql = "UPDATE `register_user` SET `eIsBlocked`='" . $eIsBlocked1 . "',`tBlockeddate`='" . $tBlockeddate . "' WHERE iUserId ='" . $iUserId . "'";
    $obj->sql_query($Fsql);
    $_SESSION['success'] = '1';
    $_SESSION['var_msg'] = $langage_lbl_admin["LBL_Record_Updated_successfully"];
    // header("Location:" . $tconfig["tsite_url_main_admin"] . "blocked_rider.php");
    // exit;
}


// End Search Parameters
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
if ($eStatus != '') {
    $estatusquery = "";
} else {
    $estatusquery = " AND eStatus != 'Deleted'";
}

$sql = "SELECT 	 COUNT(rd.iUserId) AS Total
				FROM  register_user rd 
				LEFT JOIN 
				  ( SELECT  iUserId,COUNT( tr.iTripId ) AS cnt,iActive,tEndDate   FROM
					trips tr where tEndDate BETWEEN  '" . $s_date . "' AND  '" . $c_date . "'  AND   tr.iActive =  'Canceled' AND tr.eCancelledBy ='Passenger' GROUP BY tr.iUserId ) m
				ON rd.iUserId = m.iUserId	
				LEFT JOIN 
				  ( SELECT  iUserId,COUNT( trAll.iTripId ) AS cnt,iActive FROM
					trips trAll where trAll.iActive =  'Canceled' AND trAll.eCancelledBy ='Passenger'
					GROUP BY trAll.iUserId ) mAll
				ON rd.iUserId = mAll.iUserId
				where   (mAll.cnt >'0') AND  1=1  $esql $ssql $ssql1 $rdr_ssql";

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

$sql = "SELECT 	 rd.iUserId,rd.vEmail,rd.tRegistrationDate,rd.eIsBlocked,rd.tBlockeddate,rd.vPhone,rd.eStatus,CONCAT(rd.vName,' ',rd.vLastName) AS riderName ,  COALESCE( m.cnt , 0 ) AS countertrip ,  COALESCE( mAll.cnt , 0 ) AS Allcountertrip
				FROM  register_user rd 
				LEFT JOIN 
				  ( SELECT  iUserId,COUNT( tr.iTripId ) AS cnt,iActive,tEndDate   FROM
					trips tr where tEndDate BETWEEN  '" . $s_date . "' AND  '" . $c_date . "'  AND   tr.iActive =  'Canceled' AND tr.eCancelledBy ='Passenger' GROUP BY tr.iUserId ) m
				ON rd.iUserId = m.iUserId	
				LEFT JOIN 
				  ( SELECT  iUserId,COUNT( trAll.iTripId ) AS cnt,iActive FROM
					trips trAll where trAll.iActive =  'Canceled' AND trAll.eCancelledBy ='Passenger'
					GROUP BY trAll.iUserId ) mAll
				ON rd.iUserId = mAll.iUserId
				where   (mAll.cnt >'0')  $ssql $ssql1 $dri_ssql $ord LIMIT $start, $per_page";

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
        <title><?= $SITE_NAME ?> | Cancelled <?= $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'] ?> Alert For <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?></title>
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
                                <h2> Cancelled <?= $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'] ?> Alert For <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?></h2>
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
                                            <option  value="RiderName" <?php
                                            if ($option == "RiderName") {
                                                echo "selected";
                                            }
                                            ?> >Name</option>
                                            <option value="vEmail" <?php
                                            if ($option == 'vEmail') {
                                                echo "selected";
                                            }
                                            ?> >E-mail</option>
                                            <!-- <option value="eStatus" <?php
                                            if ($option == 'eStatus') {
                                                echo "selected";
                                            }
                                            ?> >Status</option> -->
                                        </select>
                                    </td>
                                    <td width="15%" class="searchform"><input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"  class="form-control" /></td>
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
                                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'blocked_rider.php'"/>
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
                                                <button type="button" onClick="showExportTypes('blocked_rider')" >Export</button>
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
                                                    <th width="22%"><a href="javascript:void(0);" onClick="Redirect(1,<?php
                                                        if ($sortby == '1') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?> Name <?php
                                                                           if ($sortby == 1) {
                                                                               if ($order == 0) {
                                                                                   ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <th width="22%"><a href="javascript:void(0);" onClick="Redirect(2,<?php
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
                                                            ?>0<?php } ?>)">Total Cancelled Trips (In <?= $cancel_for_hours ?> hours)<?php
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
                                                            ?>0<?php } ?>)">Total Cancelled Trips (Till now) <?php
                                                                                                if ($sortby == 4) {
                                                                                                    if ($order == 0) {
                                                                                                        ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <th width="12%" class="align-center"><a href="javascript:void(0);" onClick="Redirect(7,<?php
                                                        if ($sortby == '7') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Block <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?><?php
                                                                                                if ($sortby == 7) {
                                                                                                    if ($order == 0) {
                                                                                                        ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

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
                                                <?php
                                                if (!empty($data_drv)) {
                                                    for ($i = 0; $i < count($data_drv); $i++) {
                                                        ?>
                                                        <tr class="gradeA">
                                                            <td>
                                                                <? //if($APP_TYPE == "Ride"){  ?>
                                                                <a href="javascript:void(0);" onClick="show_rider_details('<?= $data_drv[$i]['iUserId']; ?>')" style="text-decoration: underline;"><?= $generalobjAdmin->clearName($data_drv[$i]['riderName']); ?></a>
                                                                <? // }else{  ?>
                                                                <!-- <?= $generalobjAdmin->clearName($data_drv[$i]['riderName']); ?> -->
                                                                <? // }   ?>
                                                            </td>
                                                            <td><? echo $generalobjAdmin->clearEmail($data_drv[$i]['vEmail']); ?></td>

                                                            <td align="center"><? echo ($data_drv[$i]['countertrip']); ?></td> 
                                                            <td align="center"><? echo ($data_drv[$i]['Allcountertrip']); ?></td> 
                                                            <?php
                                                            if ($userObj->hasPermission('update-status-blocked-rider')) {
                                                                if ($data_drv[$i]['eIsBlocked'] == "No") {
                                                                    ?>
                                                                    <td align="center"><a style="background-color:#DDDDDD;color:#000;
                                                                                          " class="btn" href="javascript:void(0);" onClick="Block_rider_details('<?= $data_drv[$i]['iUserId']; ?>')" ><i class="fa fa-check-circle"style="color:#000;" ></i> <?= ucfirst($data_drv[$i]['eIsBlocked']); ?> 
                                                                        </a></td> 
                                                                <?php } else { ?>
                                                                    <td align="center">
                                                                    <!--     <form name="frmfeatured" id="frmfeatured_<?php echo $data_drv[$i]['iUserId']; ?>" action="" method="post">
                                                                            <input type="hidden" name="iUserId" value="<?php echo $data_drv[$i]['iUserId']; ?>">
                                                                            <input type="hidden" name="eIsBlocked1" value="<?= ($data_drv[$i]['eIsBlocked'] == "Yes") ? 'No' : 'Yes' ?>" form="frmfeatured"  >
                                                                            <input type="hidden" name="action" value="Blocked">
                                                                            <button class="btn btn-danger">
                                                                                <i class="<?= ($data_drv[$i]['eIsBlocked'] == "Yes") ? 'fa fa-check-circle' : 'fa fa-check-circle-o' ?>"></i> <?= ucfirst($data_drv[$i]['eIsBlocked']); ?>
                                                                            </button>
                                                                        </form> -->
                                                                        <a class="btn btn-danger" href="javascript:void(0);" onClick="Block_rider_details1('<?= $data_drv[$i]['iUserId']; ?>')" ><i class="<?= ($data_drv[$i]['eIsBlocked'] == "Yes") ? 'fa fa-check-circle' : 'fa fa-check-circle-o' ?>"></i> <?= ucfirst($data_drv[$i]['eIsBlocked']); ?> 
                                                                        </a>
                                                                    </td>
                                                                    <?php
                                                                }
                                                            }
                                                            ?>
                                                            <td align="center" ><?= $generalobjAdmin->DateTime($data_drv[$i]['tBlockeddate'], 'No') ?></td>

                                                        </tr>
                                                        <?
                                                    }
                                                } else {
                                                    ?>
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

        <form name="pageForm" id="pageForm" action="action/blocked_rider.php" method="post" >
            <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
            <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
            <input type="hidden" name="iUserId" id="iMainId01" value="" >
            <input type="hidden" name="eStatus" id="eStatus" value="<?php echo $eStatus; ?>" >
            <input type="hidden" name="status" id="status01" value="" >
            <input type="hidden" name="statusVal" id="statusVal" value="" >
            <input type="hidden" name="option" value="<?php echo $option; ?>" >
            <input type="hidden" name="keyword" value="<?php echo $keyword; ?>" >
            <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
            <input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
            <input type="hidden" name="method" id="method" value="" >
        </form>

        <div  class="modal fade " id="detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
            <div class="modal-dialog" >
                <div class="modal-content">
                    <div class="modal-header">
                        <h4>
                        <!--<i aria-hidden="true" class="fa fa-building-o" style="margin:2px 5px 0 2px;"></i>-->
                            <i style="margin:2px 5px 0 2px;"><img src="images/rider-icon.png" alt=""></i>
                            <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?> Details
                            <button type="button" class="close" data-dismiss="modal">x</button>
                        </h4>
                    </div>
                    <div class="modal-body" style="max-height: 450px;overflow: auto;">
                        <div id="imageIcons">
                            <div align="center">                                                                       
                                <img src="default.gif"><br/>                                                            
                                <span>Retrieving details,please Wait...</span>                       
                            </div>    
                        </div>
                        <div id="rider_detail" ></div>
                    </div>
                </div>
            </div>
        </div>


        <div  class="modal fade " id="blocked_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
            <div class="modal-dialog" >
                <div class="modal-content">
                    <div class="modal-header"><h4>Block <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?>?</h4></div>  
                    <div class="modal-body" style="max-height: 450px;overflow: auto;">
                        <p id="new-msg-activeid">If you block the <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?> he will unable to take the ride. Are you sure you want to block?</p>
                        <div id="imageIconss">
                            <div align="center">                                                                       
                                <img src="default.gif"><br/>                                                            
                                <span>Retrieving details,please Wait...</span>                       
                            </div>    
                        </div>
                        <div id="blockedrider" ></div>
                    </div>
                </div>
            </div>
        </div>


        <div  class="modal fade" id="rider_add_wallet_money" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
            <div class="modal-dialog" >
                <div class="modal-content nimot-class">
                    <div class="modal-header">
                        <h4><i style="margin:2px 5px 0 2px;" class= "fa fa-google-wallet"></i>Add Balance
                            <button type="button" class="close" data-dismiss="modal">x</button>
                        </h4>
                    </div>
                    <form class="form-horizontal" id="add_money_frm" method="POST" enctype="multipart/form-data" 	action="" name="add_money_frm">
                        <input type="hidden" id="action" name="action" value="addmoney">
                        <input type="hidden"  name="eTransRequest" id="eTransRequest" value="">
                        <input type="hidden"  name="eType" id="eType" value="Credit">
                        <input type="hidden"  name="eFor" id="eFor" value="Deposit">
                        <input type="hidden"  name="iUserId-id" id="iRider-Id" value="">							
                        <input type="hidden"  name="eUserType" id="eUserType" value="Rider">			
                        <div class="col-lg-12">
                            <div class="input-group input-append" >
                                <h5><?= $langage_lbl['LBL_ADD_WALLET_DESC1_TXT']; ?></h5>
                                <div class="ddtt">
                                    <h4><?= $langage_lbl['LBL_ENTER_AMOUNT']; ?></h4>
                                    <input type="text" name="iBalance" id="iBalance" class="form-control iBalance add-ibalance" onKeyup="checkzero(this.value);">
                                </div>
                                <div id="iLimitmsg"></div>										
                            </div>
                        </div>
                        <div class="nimot-class-but">
                            <input type="button" onClick="check_add_money();" class="save"  id="add_money" name="<?= $langage_lbl['LBL_save']; ?>" value="<?= $langage_lbl['LBL_Save']; ?>">
                            <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">Close</button>
                        </div>
                    </form>
                    <div style="clear:both;"></div>
                </div>
            </div>

        </div>

        <?php include_once('footer.php'); ?>
        <script>
            /*$(document).ready(function() {
             $('#eStatus_options').hide(); 
             $('#option').each(function(){
             if (this.value == 'eStatus') {
             $('#eStatus_options').show(); 
             $('.searchform').hide(); 
             }
             });
             });
             $(function() {
             $('#option').change(function(){
             if($('#option').val() == 'eStatus') {
             $('#eStatus_options').show();
             $("input[name=keyword]").val("");
             $('.searchform').hide(); 
             } else {
             $('#eStatus_options').hide();
             $("#estatus_value").val("");
             $('.searchform').show();
             } 
             });
             });*/

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
                //alert(action);
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

            function show_rider_details(userid) {
                $("#rider_detail").html('');
                $("#imageIcons").show();
                $("#detail_modal").modal('show');

                if (userid != "") {
                    var request = $.ajax({
                        type: "POST",
                        url: "ajax_rider_details.php",
                        data: "iUserId=" + userid,
                        datatype: "html",
                        success: function (data) {
                            $("#rider_detail").html(data);
                            $("#imageIcons").hide();
                        }
                    });
                }
            }

            function Block_rider_details(userid) {
                $("#blockedrider").html('');
                $("#imageIconss").show();
                $("#blocked_modal").modal('show');

                if (userid != "") {
                    var request = $.ajax({
                        type: "POST",
                        url: "ajax_blockrider_details.php",
                        data: "iUserId=" + userid,
                        datatype: "html",
                        success: function (data) {

                            $("#blockedrider").html(data);
                            $("#imageIconss").hide();
                        }
                    });
                }
            }


            function Block_rider_details1(userid) {
                $("#blockedrider").html('');
                //$("#imageIconss").show();
                //$("#blocked_modal").modal('show');

                if (userid != "") {
                    var request = $.ajax({
                        type: "POST",
                        url: "ajax_blockrider_details.php",
                        data: "iUserId=" + userid,
                        datatype: "html",
                        success: function (data) {

                            $("#blockedrider").html(data);
                            $("#imageIconss").hide();
                            $('#frmfeatured').submit();
                        }
                    });
                }
            }



            function Add_money_driver(riderid) {
                //alert(riderid);
                $("#rider_add_wallet_money").modal('show');
                $(".add-ibalance").val("");
                if (riderid != "") {
                    var riderid = $('#iRider-Id').val(riderid);

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

            function check_add_money() {

                var iBalance = $(".add-ibalance").val();
                if (iBalance == '') {
                    alert("Please Enter Amount");
                    return false;
                } else if (iBalance == 0) {
                    alert("You Can Not Enter Zero Number");
                    return false;
                } else {
                    $("#add_money").val('Please wait ...').attr('disabled', 'disabled');
                    $('#add_money_frm').submit();
                }
            }

            $(".iBalance").keydown(function (e) {
                if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                        (e.keyCode == 65 && e.ctrlKey === true) ||
                        (e.keyCode == 67 && e.ctrlKey === true) ||
                        (e.keyCode == 88 && e.ctrlKey === true) ||
                        (e.keyCode >= 35 && e.keyCode <= 39)) {
                    return;
                }
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }
            });

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
            function nospaces(t) {
                if (t.value.match(/\s/g)) {
                    alert('Sorry, you are not allowed to enter any spaces');
                    t.value = t.value.replace(/\s/g, '');
                }
            }
        </script>
    </body>
    <!-- END BODY-->
</html>