<?php
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
if (!$userObj->hasPermission('view-advertise-banner')) {
    $userObj->redirect();
}
///ini_set("display_errors", 1);
//error_reporting(E_ALL);
$script = 'Advertisement Banners';
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY iBannerImpLog ASC';
if ($sortby == 1) {
    if ($order == 0)
        $ord = " ORDER BY eUserType ASC";
    else
        $ord = " ORDER BY eUserType DESC";
} 
// Start Search Parameters

 $iAdvertBannerId = isset($_REQUEST['id']) ? $_REQUEST['id'] : ""; 

  $backlink = 'advertise_banners.php';

if ($iAdvertBannerId == '') { 
        header("location:".$backlink);exit;
}
$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
$eUserType = isset($_REQUEST['eUserType']) ? $_REQUEST['eUserType'] : "";
$ssql = '';
if ($keyword != '') {
    if ($option != '') {
        if ($eUserType != '') {
            $ssql .= " AND (m.Name LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR n.Name LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR l.Name LIKE '%" . $generalobjAdmin->clean($keyword) . "%') AND bim.eUserType = '" . $generalobjAdmin->clean($eUserType) . "'";
        } else {
            $ssql .= " AND (m.Name LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR n.Name LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR l.Name LIKE '%" . $generalobjAdmin->clean($keyword) . "%')";
        }
    } else {
        if ($eUserType != '') {
            $ssql .= " AND (m.Name LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR n.Name LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR l.Name LIKE '%" . $generalobjAdmin->clean($keyword) . "%') AND bim.eUserType = '" . $generalobjAdmin->clean($eUserType) . "'";
        } else {
            $ssql .= " AND (m.Name LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR n.Name LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR l.Name LIKE '%" . $generalobjAdmin->clean($keyword) . "%')";
        }
    }		 
} else if ($eUserType != '' && $keyword == '') {
    $ssql .= " AND bim.eUserType = '" . $generalobjAdmin->clean($eUserType) . "'";
}	 
//echo $ssql;die;
// End Search Parameters
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page 
//echo $estatusquery;die;
$sql = "SELECT  COUNT(bim.iBannerImpLog) AS Total  FROM  banner_impression bim 
			LEFT JOIN  ( SELECT  distinct(ru.iUserId),(concat(vName,' ',vLastName)) AS Name FROM banner_impression bi inner join   register_user ru on ru.iUserId = bi.iUserId where  bi.eUserType='Passenger' AND bi.iAdvertBannerId='".$iAdvertBannerId."' )
			m ON bim.iUserId = m.iUserId
			LEFT JOIN  ( SELECT  distinct(rd.iDriverId),(concat(vName,' ',vLastName)) AS Name FROM banner_impression bis inner join   register_driver rd on rd.iDriverId = bis.iUserId where  bis.eUserType='Driver' AND bis.iAdvertBannerId='".$iAdvertBannerId."' )
			n ON bim.iUserId = n.iDriverId 
			LEFT JOIN  ( SELECT  distinct(c.iCompanyId),c.vCompany AS Name FROM banner_impression bisn inner join   company c on c.iCompanyId = bisn.iUserId where  bisn.eUserType='Store' AND bisn.iAdvertBannerId='".$iAdvertBannerId."' )
			l ON bim.iUserId = l.iCompanyId 
			WHERE  1 = 1 AND iAdvertBannerId='".$iAdvertBannerId."'  $ssql"; 
$totalData = $obj->MySQLSelect($sql);
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
				
			    $sql="SELECT  bim.iBannerImpLog, bim.iUserId,bim.eUserType,m.Name as mname,n.Name as nname,l.Name as lname,bim.vIP,bim.dDateTime FROM  banner_impression bim 
			LEFT JOIN  ( SELECT  distinct(ru.iUserId),(concat(vName,' ',vLastName)) AS Name FROM banner_impression bi inner join   register_user ru on ru.iUserId = bi.iUserId where  bi.eUserType='Passenger' AND bi.iAdvertBannerId='".$iAdvertBannerId."' )
			m ON bim.iUserId = m.iUserId
			LEFT JOIN  ( SELECT  distinct(rd.iDriverId),(concat(vName,' ',vLastName)) AS Name FROM banner_impression bis inner join   register_driver rd on rd.iDriverId = bis.iUserId where  bis.eUserType='Driver' AND bis.iAdvertBannerId='".$iAdvertBannerId."' )
			n ON bim.iUserId = n.iDriverId 
			LEFT JOIN  ( SELECT  distinct(c.iCompanyId),c.vCompany AS Name FROM banner_impression bisn inner join   company c on c.iCompanyId = bisn.iUserId where  bisn.eUserType='Store' AND bisn.iAdvertBannerId='".$iAdvertBannerId."' )
			l ON bim.iUserId = l.iCompanyId 
			WHERE bim.iAdvertBannerId='".$iAdvertBannerId."' $ssql $ord LIMIT $start, $per_page";
			 
// 	$sql = "SELECT * from banner_impression WHERE iAdvertBannerId='".$iAdvertBannerId."' $ssql $ord LIMIT $start, $per_page";
//echo $sql;die;
$data_drv = $obj->MySQLSelect($sql);
//echo "<pre>";
//print_r($data_drv);
//die;
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
        <title><?= $SITE_NAME ?> | <?php echo $langage_lbl_admin['LBL_ADVERTISEMENT_BANNERS_TXT_ADMIN']; ?> Impression</title>
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
                    <div id="add-hide-show-div" class="vehicleCategorylist">
                        <div class="row">
                            <div class="col-lg-12">
                                <h2><?php echo $langage_lbl_admin['LBL_ADVERTISEMENT_BANNERS_TXT_ADMIN']; ?> Impression</h2>
                            </div>
                        </div>
                        <hr />

                    </div>
<?php include('valid_msg.php'); ?>
                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
									<input type="hidden" name="id" id="id" value="<?php echo $iAdvertBannerId; ?>">
                                <input type="hidden" name="previousLink" id="previousLink" value="advertise_banners.php"/>
                                <input type="hidden" name="backlink" id="backlink" value="advertise_banners.php"/>
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                            <tbody>
                                <tr>
                                    <td width="2%"><label for="textfield"><strong>Search:</strong></label></td>
                                    <td width="8%" class="padding-right10">
                                        <select name="option" id="option" class="form-control">
                                            <option value="">All</option>
                                            <option  value="vName" <?php
                                            if ($option == "vName") {
                                                echo "selected";
                                            }
                                            ?> >Name</option>
                                        </select>
                                    </td>
                                    <td width="12%" class="searchform"><input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"  class="form-control" /></td>
                                    <td width="12%" class="estatus_options" id="eStatus_options" >
                                        <select name="eUserType" id="eUserType" class="form-control">
                                            <option value="" >Select User Type</option>
                                            <option value='Passenger' <?php
                                            if ($eUserType == 'Passenger') {
                                                echo "selected";
                                            }
                                            ?> ><?php echo $langage_lbl_admin['LBL_RIDER']; ?></option>
                                            <option value="Driver" <?php
                                            if ($eUserType == 'Driver') {
                                                echo "selected";
                                            }
                                            ?> ><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></option>
                                            <option value="Store" <?php
                                            if ($eUserType == 'Store') {
                                                echo "selected";
                                            }
                                            ?> ><?php echo $langage_lbl_admin['LBL_STORE']; ?></option>	
                                        </select>
                                    </td>
                                    <td>
                                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'banner_impression.php?id=<?php  echo $iAdvertBannerId; ?>'"/>
                                    </td>  

                                </tr>
                            </tbody>
                        </table>
                    </form>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12"> 
                                <div style="clear:both;"></div>
                                <div class="table-responsive">
                                    <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                       
                                <input type="hidden" name="previousLink" id="previousLink" value="advertise_banners.php"/>
                                <input type="hidden" name="backlink" id="backlink" value="advertise_banners.php"/>
									<input type="hidden" name="id" id="id" value="<?php echo $iAdvertBannerId; ?>">
 <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr>  
                                                    <th width="10%" style="text-align:center;">Name</th>
											<th width="22%" style="text-align:center;"><a href="javascript:void(0);" onClick="Redirect(1,<?php
												if ($sortby == '1') {
													echo $order;
												} else {
												?>0<?php } ?>)">User Type <?php
												  if ($sortby == 1) {
													  if ($order == 0) {
														  ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
																		}
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                    <th width="10%" style="text-align:center;">IP Address</th>
                                                    <th width="10%" style="text-align:center;">Date</th> 
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php	 
                                                if (!empty($data_drv)) {
                                                    for ($i = 0; $i < count($data_drv); $i++) {
														  $UserType=$data_drv[$i]['eUserType'];
														
                                                     if ($UserType == 'Passenger') {
														 $name=$data_drv[$i]['mname'];
													 }
                                                     if ($UserType == 'Driver') {
														 $name=$data_drv[$i]['nname'];
													 }
                                                     if ($UserType == 'Store') {
														 $name=$data_drv[$i]['lname'];
													 }  
                                                        ?>
                                                        <tr class="gradeA"> 
                                                            <td align="center"><? echo $generalobj->clearName(" " .ucfirst($name)); ?></td>
                                                            <td align="center"><? echo $data_drv[$i]['eUserType']; ?></td>	
                                                            <td align="center"><? echo $data_drv[$i]['vIP']; ?></td>
                                                            <td width="20%" align="center"><? echo $generalobjAdmin->DateTime($data_drv[$i]['dDateTime'], 'No')	 ?></td> 
																		</tr>
													<? }
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
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
        <form name="pageForm" id="pageForm" action="action/banner_impression.php" method="post" >
            <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
            <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
            <input type="hidden" name="id" id="id" value="<?php echo $iAdvertBannerId; ?>" >
            <input type="hidden" name="eStatus" id="eStatus" value="<?php echo $eStatus; ?>" >
            <input type="hidden" name="status" id="status01" value="" >
            <input type="hidden" name="statusVal" id="statusVal" value="" >
            <input type="hidden" name="option" value="<?php echo $option; ?>" >
            <input type="hidden" name="keyword" value="<?php echo $keyword; ?>" >
            <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
            <input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
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
            function updateAddType(value) {
                $.ajax({
                    url: "ajax_validate_coupon.php",
                    type: "post",
                    data: {'advertiseType': value},
                    dataType: 'json',
                    success: function (response) {
                        console.log(response);
                    }
                });
            }
        </script>
    </body>
    <!-- END BODY-->
</html>