<?php
include_once('../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();
$exDocConfig = isExpiredDocumentEnable();
if (!$userObj->hasPermission('expired-documents') || $exDocConfig != true) {
    $userObj->redirect();
}


$default_lang = $generalobj->get_default_lang();
$script = 'Expired Documents';
$tbl_name = 'pages';

//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY dm.doc_usertype ASC';
if ($sortby == 1) {
    if ($order == 0)
        $ord = " ORDER BY dm.doc_usertype ASC";
    else
        $ord = " ORDER BY dm.doc_usertype DESC";
}
if ($sortby == 2) {
    if ($order == 0)
        $ord = " ORDER BY dm.country ASC";
    else
        $ord = " ORDER BY dm.country DESC";
}

if ($sortby == 3) {
    if ($order == 0)
        $ord = " ORDER BY dm.doc_name_" . $default_lang . " ASC";
    else
        $ord = " ORDER BY dm.doc_name_" . $default_lang . " DESC";
}
if ($sortby == 4) {
    if ($order == 0)
        $ord = " ORDER BY dl.ex_date ASC";
    else
        $ord = " ORDER BY dl.ex_date DESC";
}
if ($sortby == 5) {
    if ($order == 0)
        $ord = " ORDER BY doc_username ASC";
    else
        $ord = " ORDER BY doc_username DESC";
}
if ($sortby == 6) {
    if ($order == 0)
        $ord = " ORDER BY doc_useremail ASC";
    else
        $ord = " ORDER BY doc_useremail DESC";
}
if ($sortby == 7) {
    if ($order == 0)
        $ord = " ORDER BY doc_userphone ASC";
    else
        $ord = " ORDER BY doc_userphone DESC";
}

// Start Search Parameters
$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$ssql = '';
$ssql1 = '';
if ($keyword != '') {
    if ($option != '') {
        if (strpos($option, 'ex_date') !== false) {
            $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
        }else if (strpos($option, 'doc_username') !== false) {
            $ssql1 .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
        }else if (strpos($option, 'doc_useremail') !== false) {
            $ssql1 .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
        }else if (strpos($option, 'doc_userphone') !== false) {
            $ssql1 .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
        } else {
            $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
        }
    } else {
		
        //$ssql .= " AND dl.doc_usertype LIKE '%" . $keyword . "%' OR dm.country LIKE '%" . $keyword . "%' OR dm.doc_name_" . $default_lang . " LIKE '%" . $keyword . "%' OR dl.ex_date LIKE '" . $keyword . "'";
		$ssql1 .= " AND (doc_username LIKE '%" . $keyword . "%' OR doc_useremail LIKE '%" . $keyword . "%' OR doc_userphone LIKE '%" . $keyword . "%' OR doc_usertype LIKE '%" . $keyword . "%' OR country LIKE '%" . $keyword . "%' OR doc_name_" . $default_lang . " LIKE '%" . $keyword . "%' OR ex_date LIKE '" . $keyword . "')";
    }
}



// End Search Parameters
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT * from (SELECT COUNT(dm.doc_masterid) AS Total,dl.doc_masterid,dl.doc_userid,dm.doc_name_". $default_lang . ",dm.country,dm.doc_usertype,dl.ex_date,CASE dm.doc_usertype
    WHEN 'company' THEN (select vCompany from company where iCompanyId = dl.doc_userid)
    WHEN 'driver' THEN (select CONCAT(vName,' ', vLastName) from register_driver where iDriverId = dl.doc_userid)
    WHEN 'car' THEN (select CONCAT(mk.vMake,' ',model.vTitle)  from driver_vehicle dv INNER JOIN make mk ON dv.iMakeId = mk.iMakeId INNER JOIN model model ON dv.iModelId=model.iModelId where dv.iDriverVehicleId = dl.doc_userid)
    WHEN 'store' THEN (select vCompany from company where iCompanyId =dl.doc_userid)
END AS doc_username,
CASE dm.doc_usertype
    WHEN 'company' THEN ''
    WHEN 'driver' THEN ''
    WHEN 'car' THEN (select mk.vMake from driver_vehicle dv INNER JOIN make mk ON dv.iMakeId = mk.iMakeId INNER JOIN model model ON dv.iModelId=model.iModelId where dv.iDriverVehicleId = dl.doc_userid)
    WHEN 'store' THEN ''
END AS vehicle,
CASE dm.doc_usertype
    WHEN 'company' THEN (select vEmail from company where iCompanyId = dl.doc_userid)
    WHEN 'driver' THEN (select vEmail from register_driver where iDriverId = dl.doc_userid)
    WHEN 'car' THEN (select vEmail  from driver_vehicle dv INNER JOIN register_driver rd ON dv.iDriverId = rd.iDriverId where dv.iDriverVehicleId = dl.doc_userid)
    WHEN 'store' THEN (select vEmail from company where iCompanyId =dl.doc_userid)
END AS doc_useremail,
CASE dm.doc_usertype
    WHEN 'company' THEN (select vPhone from company where iCompanyId = dl.doc_userid)
    WHEN 'driver' THEN (select vPhone from register_driver where iDriverId = dl.doc_userid)
    WHEN 'car' THEN (select vPhone  from driver_vehicle dv INNER JOIN register_driver rd ON dv.iDriverId = rd.iDriverId where dv.iDriverVehicleId = dl.doc_userid)
    WHEN 'store' THEN (select vPhone from company where iCompanyId =dl.doc_userid)
END AS doc_userphone
FROM document_list dl INNER JOIN document_master dm ON dl.doc_masterid = dm.doc_masterid  where dl.ex_date!='0000-00-00' AND dl.ex_date < CURDATE() AND dm.status !='Deleted' $ssql $ord) AS documentlist WHERE doc_username IS NOT NULL $ssql1";
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
//$sql = "SELECT * FROM ".$tbl_name."  WHERE eStatus != 'Deleted' $ssql $ord LIMIT $start, $per_page ";
$sql = "SELECT * from (SELECT dl.doc_masterid,dl.doc_userid,dm.doc_name_". $default_lang . ",dm.country,dm.doc_usertype,dl.ex_date,dl.req_date,CASE dm.doc_usertype
    WHEN 'company' THEN (select vCompany from company where iCompanyId = dl.doc_userid)
    WHEN 'driver' THEN (select CONCAT(vName,' ', vLastName) from register_driver where iDriverId = dl.doc_userid)
    WHEN 'car' THEN (select CONCAT(mk.vMake,' ',model.vTitle)  from driver_vehicle dv INNER JOIN make mk ON dv.iMakeId = mk.iMakeId INNER JOIN model model ON dv.iModelId=model.iModelId where dv.iDriverVehicleId = dl.doc_userid)
    WHEN 'store' THEN (select vCompany from company where iCompanyId =dl.doc_userid)
END AS doc_username,
CASE dm.doc_usertype
    WHEN 'company' THEN ''
    WHEN 'driver' THEN ''
    WHEN 'car' THEN (select mk.vMake from driver_vehicle dv INNER JOIN make mk ON dv.iMakeId = mk.iMakeId INNER JOIN model model ON dv.iModelId=model.iModelId where dv.iDriverVehicleId = dl.doc_userid)
    WHEN 'store' THEN ''
END AS vehicle,
CASE dm.doc_usertype
    WHEN 'company' THEN (select vEmail from company where iCompanyId = dl.doc_userid)
    WHEN 'driver' THEN (select vEmail from register_driver where iDriverId = dl.doc_userid)
    WHEN 'car' THEN (select vEmail  from driver_vehicle dv INNER JOIN register_driver rd ON dv.iDriverId = rd.iDriverId where dv.iDriverVehicleId = dl.doc_userid)
    WHEN 'store' THEN (select vEmail from company where iCompanyId =dl.doc_userid)
END AS doc_useremail,
CASE dm.doc_usertype
    WHEN 'company' THEN (select vPhone from company where iCompanyId = dl.doc_userid)
    WHEN 'driver' THEN (select vPhone from register_driver where iDriverId = dl.doc_userid)
    WHEN 'car' THEN (select vPhone  from driver_vehicle dv INNER JOIN register_driver rd ON dv.iDriverId = rd.iDriverId where dv.iDriverVehicleId = dl.doc_userid)
    WHEN 'store' THEN (select vPhone from company where iCompanyId =dl.doc_userid)
END AS doc_userphone
FROM document_list dl INNER JOIN document_master dm ON dl.doc_masterid = dm.doc_masterid  where dl.ex_date!='0000-00-00' AND dl.ex_date < CURDATE() AND dm.status !='Deleted'  $ssql $ord) AS documentlist WHERE doc_username IS NOT NULL $ssql1   LIMIT $start, $per_page";
$data_drv = $obj->MySQLSelect($sql);
//echo '<pre>'; print_r($data_drv); die;
$endRecord = count($data_drv);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page')
        $var_filter .= "&$key=" . stripslashes($val);
}

$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;

$dDocStatus = $generalobj->getConfigurations("configurations", "SET_DRIVER_OFFLINE_AS_DOC_EXPIRED");
?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | Expired Documents</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
<?php include_once('global_files.php'); ?>
<link href="../assets/css/jquery-ui.css" rel="stylesheet" />
<link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />  
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
                                
                                <?php if($_REQUEST['success'] == '1' && $_REQUEST['page'] == ''){ ?>
                                    <div class="alert alert-success">Setting for Restrict Drivers Updted Successfully.</div>
                                    <br>
                                <? } ?>
                                

                                <h2><?php echo $langage_lbl_admin['LBL_EXPIRED_DOCUMETS']; ?></h2>
                                <!--<input type="button" id="" value="ADD A DRIVER" class="add-btn">-->
                                <?php if(ENABLE_EXPIRE_DOCUMENT == "Yes"){ ?>
                                    <div class="panel-heading" style="text-align:right">
                                        <div class="col-lg-12">
                                        <label> Restrict Drivers to be online if one or more document is expired.   </label>
                                            <div class="make-switch" data-on="success" data-off="warning">
                                                <input type="checkbox" name="dDocStatus" id="dDocStatus" <?= ($dDocStatus != '' && $dDocStatus == 'Yes') ? 'checked' : ''; ?>>
                                            </div>
                                            <div class="upResponce"></div>                                                    
                                        </div>
                                    </div>
                                <?php } ?>                               
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
                                            <option  value="dm.doc_usertype" <?php if ($option == "dm.doc_usertype") {
												echo "selected";
											} ?> >Document For</option>
											<option  value="dm.country" <?php if ($option == "dm.country") {
												echo "selected";
											} ?> >Country</option>
					                         <option value="<?= 'dm.doc_name_' . $default_lang ?>" <?php if ($option == "dm.doc_name_".$default_lang) {
												echo "selected";
											} ?> >Document Name</option>
											<option  value="dl.ex_date" <?php if ($option == "dl.ex_date") {
													echo "selected";
												} ?> >Expire Date</option>
											<option  value="doc_username" <?php if ($option == "doc_username") {
													echo "selected";
												} ?> >Document User Name</option>
											<option  value="doc_useremail" <?php if ($option == "doc_useremail") {
													echo "selected";
												} ?> >Email</option>
											<option  value="doc_userphone" <?php if ($option == "doc_userphone") {
													echo "selected";
												} ?> >Phone</option>		



                                        </select>
                                    </td>
                                    <td width="15%"><input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"  class="form-control" /></td>
                                    <td width="12%">
                                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'expired_documents.php'"/>
                                    </td>
                                    <td width="30%"><!--<a class="add-btn" href="page_action.php" style="text-align: center;">Add Pages</a>--></td>
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
                                                <!--<select name="changeStatus" id="changeStatus" class="form-control" onchange="ChangeStatusAll(this.value);">
                                                        <option value="" >Select Action</option>
                                                        <option value='Active' <?php if ($option == 'Active') {
    echo "selected";
} ?> >Make Active</option>
                                                        <option value="Inactive" <?php if ($option == 'Inactive') {
    echo "selected";
} ?> >Make Inactive</option>
                                                        <option value="Deleted" <?php if ($option == 'Delete') {
    echo "selected";
} ?> >Make Delete</option>
                                                </select>-->
                                        </span>
                                    </div>
                                </div>
                                <div style="clear:both;"></div>
                                <div class="table-responsive">
                                    <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr>

                                                    <th width="15%"><a href="javascript:void(0);" onClick="Redirect(1,<?php if ($sortby == '1') {
														echo $order;
													} else { ?>0<?php } ?>)">Document For <?php if ($sortby == 1) {
														if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
													} else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
													
													<th width="10%"><a href="javascript:void(0);" onClick="Redirect(2,<?php if ($sortby == '2') {
														echo $order;
													} else { ?>0<?php } ?>)">Country <?php if ($sortby == 2) {
													if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
															} else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
													
													<th width="20%"><a href="javascript:void(0);" onClick="Redirect(3,<?php if ($sortby == '3') {
														echo $order;
													} else { ?>0<?php } ?>)">Document Name <?php if ($sortby == 3) {
													if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
															} else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
													
													<th width="15%"><a href="javascript:void(0);" onClick="Redirect(4,<?php if ($sortby == '4') {
														echo $order;
													} else { ?>0<?php } ?>)">Expire Date  <?php if ($sortby == 4) {
													if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
													} else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

													<th width="15%"><a href="javascript:void(0);" onClick="Redirect(5,<?php if ($sortby == '5') {
														echo $order;
													} else { ?>0<?php } ?>)">Document User Name <?php if ($sortby == 5) {
													if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
													} else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

													<th width="15%"><a href="javascript:void(0);" onClick="Redirect(6,<?php if ($sortby == '6') {
														echo $order;
													} else { ?>0<?php } ?>)">Email <?php if ($sortby == 6) {
													if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
													} else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>													
															
                                                    <th width="15%"><a href="javascript:void(0);" onClick="Redirect(7,<?php if ($sortby == '7') {
														echo $order;
													} else { ?>0<?php } ?>)">Phone <?php if ($sortby == 7) {
																										if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                        } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                <?php if($SET_DRIVER_OFFLINE_AS_DOC_EXPIRED == 'Yes') { ?>
                                                    <th>Expired Doc Action</th>
                                                <?php } ?>    


                                                <th width="8%" align="center" style="text-align:center;"><!--<a href="javascript:void(0);" onClick="Redirect(4,<?php if ($sortby == '4') {
                                                    echo $order;
                                                } else { ?>0<?php } ?>)"> <?php if ($sortby == 4) {
                                                    if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>-->
                                                        Action</th>

                                                </tr>
                                            </thead>
                                            <tbody>
												<?php
												if (!empty($data_drv)) {
													for ($i = 0; $i < count($data_drv); $i++) {

														$file = '';
														$document_for = '';
														if ($data_drv[$i]['doc_usertype'] == 'company') {
															$file = 'company_document_action.php?id='.$data_drv[$i]['doc_userid'].'&action=edit';
															$document_for ='Company';
														}else if($data_drv[$i]['doc_usertype'] == 'driver'){
															$file = 'driver_document_action.php?id='.$data_drv[$i]['doc_userid'].'&action=edit&user_type=driver';
															$document_for ='Provider';
														}else if($data_drv[$i]['doc_usertype'] == 'car'){
															$file = 'vehicle_document_action.php?id='.$data_drv[$i]['doc_userid'].'&vehicle='.$data_drv[$i]['vehicle'].'';
															$document_for ='Car';
														}else if($data_drv[$i]['doc_usertype'] == 'store'){
															$file = 'store_document_action.php?id='.$data_drv[$i]['doc_userid'].'&action=edit';
															$document_for ='Store';
														}	
                                                            
                                                        ?>
															<tr class="gradeA">
                                                            
                                                            <td><?= $document_for; ?></td>
                                                            <td><?= $data_drv[$i]['country']; ?></td>
															<td><?= $data_drv[$i]['doc_name_' . $default_lang]; ?></td>
															<td><?= $data_drv[$i]['ex_date']; ?></td>
															<td><?= $generalobj->clearName(" " . $data_drv[$i]['doc_username']); ?></td>
															<td><?= $generalobj->clearEmail(" " . $data_drv[$i]['doc_useremail']); ?></td>
															<td><?= $generalobj->clearPhone(" " . $data_drv[$i]['doc_userphone']); ?></td>

                                                            <?php if($SET_DRIVER_OFFLINE_AS_DOC_EXPIRED == 'Yes') { ?>
                                                            <td class="text-center">
                                                                <?php if($data_drv[$i]['req_date'] && $data_drv[$i]['req_date'] != '0000-00-00'){ ?>
                                                                    <a href="<?= $file; ?>" class="btn btn-primary" data-toggle="tooltip" title="Review Expired Documents" target="_blank">Review</a>
                                                                <?php }else{ ?>
                                                                    -
                                                                <?php }?>
                                                            
                                                            </td>
                                                            <?php } ?>


                                                            <td align="center" style="text-align:center;" class="action-btn001">

                                                                <div class="share-button openHoverAction-class" style="display: block;">

                                                                    <label class="entypo-twitter" data-network="twitter" style="margin-top: -10px;"><a href="<?= $file; ?>" data-toggle="tooltip" title="View Expired Documents" target="_blank">
                                                                            <img src="img/edit-doc.png" alt="Edit">
                                                                        </a></label>
                                                                    <div class="social show-moreOptions openPops_<?= $data_drv[$i]['iPageId']; ?>">
                                                                    </div>
                                                                </div>															


                                                            </td>


                                                        </tr>
												<?php }
											} else { ?>
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
                                Expired Documents module will list all Expired Documents on this page.
                            </li>
                            <li>
                                Administrator can view Expired Documents.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->

        <form name="pageForm" id="pageForm" action="action/country.php" method="post" >
            <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
            <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
            <input type="hidden" name="iPageId" id="iMainId01" value="" >
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
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
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

            $(document).on("change", "#dDocStatus",function (e) {
                
                var ckVal = $(this).is(':checked'); 
                var request = $.ajax({
                    type: "POST",
                    url: "ajax_driver_expiry_doc_setting_change.php",
                    data: "ckVal=" + ckVal,
           
                    success: function (data) {
                        
                        $('.upResponce').text(data);
                        window.location = 'expired_documents.php?success=1';
                        
                    }
                });
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