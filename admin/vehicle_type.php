<?php
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
if (!$userObj->hasPermission('view-vehicle-type')) {
    $userObj->redirect();
}
include_once ('../app_common_functions.php'); //added by SP on 6-9-2019 for fly changes

if ($default_lang == "") {
    $default_lang = "EN";
}

$sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();

$script = 'VehicleType';
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$iVehicleCategoryId = isset($_REQUEST['iVehicleCategoryId']) ? $_REQUEST['iVehicleCategoryId'] : "";
$ord = ' ORDER BY vt.vVehicleType_' . $default_lang . ' ASC';
if ($sortby == 1) {
    if ($order == 0)
        $ord = " ORDER BY vt.vVehicleType_" . $default_lang . " ASC";
    else
        $ord = " ORDER BY vt.vVehicleType_" . $default_lang . " DESC";
}
if ($sortby == 2) {
    if ($order == 0)
        $ord = " ORDER BY vt.fPricePerKM ASC";
    else
        $ord = " ORDER BY vt.fPricePerKM DESC";
}
if ($sortby == 3) {
    if ($order == 0)
        $ord = " ORDER BY vt.fPricePerMin ASC";
    else
        $ord = " ORDER BY vt.fPricePerMin DESC";
}
if ($sortby == 4) {
    if ($order == 0)
        $ord = " ORDER BY vt.iPersonSize ASC";
    else
        $ord = " ORDER BY vt.iPersonSize DESC";
}
if ($sortby == 5) {
    if ($order == 0)
        $ord = " ORDER BY vt.eStatus ASC";
    else
        $ord = " ORDER BY vt.eStatus DESC";
}
if ($sortby == 6) {
    if ($order == 0)
        $ord = " ORDER BY vt.iDisplayOrder ASC";
    else
        $ord = " ORDER BY vt.iDisplayOrder DESC";
}
if ($sortby == 7) {
    if ($order == 0)
        $ord = " ORDER BY vt.eType ASC";
    else
        $ord = " ORDER BY vt.eType DESC";
}
if ($sortby == 8) {
    if ($order == 0)
        $ord = " ORDER BY vt.iLocationid ASC";
    else
        $ord = " ORDER BY vt.iLocationid DESC";
}
//End Sorting
// Start Search Parameters
$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$eType = isset($_REQUEST['eType']) ? stripslashes($_REQUEST['eType']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
$iLocationid = isset($_REQUEST['location']) ? stripslashes($_REQUEST['location']) : "";

$ssql = '';
if ($keyword != '') {
    if ($option != '') {
        if ($eStatus != '') {
            if ($iVehicleCategoryId != '') {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "'  AND vt.eStatus = '" . $eStatus . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND vt.eStatus = '" . $eStatus . "'";
            }
        } else {
            if ($iVehicleCategoryId != '') {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        }
    } else {

        if ($eStatus != '') {
            if ($iVehicleCategoryId != '') {
                $ssql .= " AND (vt.vVehicleType_" . $default_lang . " LIKE '%" . $keyword . "%' OR vt.fPricePerKM LIKE '%" . $keyword . "%' OR vt.fPricePerMin LIKE '%" . $keyword . "%' OR vt.iPersonSize	 LIKE '%" . $keyword . "%') AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "' AND vt.eStatus = '" . $eStatus . "'";
            } else {
                $ssql .= " AND (vt.vVehicleType_" . $default_lang . " LIKE '%" . $keyword . "%' OR vt.fPricePerKM LIKE '%" . $keyword . "%' OR vt.fPricePerMin LIKE '%" . $keyword . "%' OR vt.iPersonSize   LIKE '%" . $keyword . "%') AND vt.eStatus = '" . $eStatus . "'";
            }
        } else {
            if ($iVehicleCategoryId != '') {
                $ssql .= " AND (vt.vVehicleType_" . $default_lang . " LIKE '%" . $keyword . "%' OR vt.fPricePerKM LIKE '%" . $keyword . "%' OR vt.fPricePerMin LIKE '%" . $keyword . "%' OR vt.iPersonSize  LIKE '%" . $keyword . "%') AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "'";
            } else {
                $ssql .= " AND (vt.vVehicleType_" . $default_lang . " LIKE '%" . $keyword . "%' OR vt.fPricePerKM LIKE '%" . $keyword . "%' OR vt.fPricePerMin LIKE '%" . $keyword . "%' OR vt.iPersonSize   LIKE '%" . $keyword . "%')";
            }
        }
    }
} else if ($iVehicleCategoryId != '' && $keyword == '' && $eStatus != '') {
    $ssql .= " AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "' AND vt.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";
} else if ($iVehicleCategoryId != '' && $keyword == '' && $eStatus == '') {
    $ssql .= " AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "'";
} else if ($eType != '' && $keyword == '' && $eStatus != '') {
    $ssql .= " AND vt.eType = '" . $eType . "' AND vt.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";
} else if ($eType != '' && $keyword == '' && $eStatus == '') {
    $ssql .= " AND vt.eType = '" . $eType . "'";
} else if ($iLocationid != '' && $keyword == '' && $eStatus != '') {
    $ssql .= " AND vt.iLocationid = '" . $iLocationid . "' AND vt.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";
} else if ($iLocationid != '' && $keyword == '' && $eStatus == '') {
    $ssql .= " AND vt.iLocationid = '" . $iLocationid . "'";
} else if ($eStatus != '' && $keyword == '') {
    $ssql .= " AND vt.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";
}
if ($eStatus != '') {
    $eStatussql = " AND vt.eStatus = '" . $eStatus . "'";
} else {
    $eStatussql = " AND vt.eStatus != 'Deleted'";
}

//Added bu HV on 18-04-2020 as discussed by KS
$isRideModuleAvailable = (isRideModuleAvailable()) ? 'Yes' : 'No';
$isUberXModuleAvailable = (isUberXModuleAvailable()) ? 'Yes' : 'No';
$isDeliveryModuleAvailable = (isDeliveryModuleAvailable()) ? 'Yes' : 'No';
//added by SP for fly stations on 19-08-2019
//if(checkFlyStationsModule(1)) {
$ssql .= " AND vt.eFly = '0' ";
//}
// End Search Parameters
if ($APP_TYPE == 'Delivery') {
    $Vehicle_type_name = 'Deliver';
} else if ($APP_TYPE == 'Ride-Delivery-UberX') {
    $Vehicle_type_name = 'Ride-Delivery';
} else {
    $Vehicle_type_name = $APP_TYPE;
}

$etypeSql = "";
if($isRideModuleAvailable=='No') {
    $etypeSql .= " AND vt.eType != 'Ride'";
}
if($isDeliveryModuleAvailable=='No') {
    $etypeSql .= " AND vt.eType != 'Deliver' AND vt.eType != 'Multi-Delivery'";
}
if($isUberXModuleAvailable=='No') { 
    $etypeSql .= " AND vt.eType != 'UberX'";     
}

//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "";
if ($Vehicle_type_name == "Ride-Delivery") {
    if (empty($eType)) {
        $ssql .= "AND (vt.eType ='Ride' or vt.eType ='Deliver')";
    }
    $sql = "SELECT count(iVehicleTypeId) AS Total from  vehicle_type  as vt where 1 = 1 $ssql $eStatussql $etypeSql";
} else {
    if ($APP_TYPE == 'UberX' && $isUberXModuleAvailable=='Yes') {
        $sql = "SELECT count(vt.iVehicleTypeId) as Total,vc.iVehicleCategoryId,vc.vCategory_" . $default_lang . " from  vehicle_type as vt left join " . $sql_vehicle_category_table_name . " as vc on vt.iVehicleCategoryId = vc.iVehicleCategoryId where vt.eType='" . $Vehicle_type_name . "' $eStatussql $ssql $etypeSql";
    } else {
        $sql = "SELECT count(vt.iVehicleTypeId) as Total  from  vehicle_type as vt where vt.eType='" . $Vehicle_type_name . "' $eStatussql $ssql $etypeSql";
    }
}
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;
$start = 0;
$end = $per_page;
//-------------if page is setcheck------------------//
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
$sql = "";
if ($Vehicle_type_name == "Ride-Delivery") {
    if (empty($eType)) {
        $ssql .= "AND (vt.eType ='Ride' or vt.eType ='Deliver')";
    }
    $sql = "SELECT vt.*,lm.vLocationName from  vehicle_type as vt left join location_master as lm ON lm.iLocationId = vt.iLocationid where 1=1 $etypeSql $eStatussql $ssql $adm_ssql $ord LIMIT $start, $per_page";
} else {
    if ($APP_TYPE == 'UberX' && $isUberXModuleAvailable=='Yes') {
        $sql = "SELECT vt.*,vc.iVehicleCategoryId,vc.vCategory_" . $default_lang . ",lm.vLocationName
		from  vehicle_type as vt  
		left join " . $sql_vehicle_category_table_name . " as vc on vt.iVehicleCategoryId = vc.iVehicleCategoryId 
		left join country as c ON c.iCountryId = vt.iCountryId
		left join state as st ON st.iStateId = vt.iStateId
		left join city as ct ON ct.iCityId = vt.iCityId
    left join location_master as lm ON lm.iLocationId = vt.iLocationid 
		where vt.eType='" . $Vehicle_type_name . "' $eStatussql $ssql $adm_ssql $ord LIMIT $start, $per_page";
    } else if ($APP_TYPE == 'Ride-Delivery-UberX') {
        $sql = "SELECT vt.*,c.vCountry,ct.vCity,st.vState,lm.vLocationName
		from vehicle_type as vt left join country as c ON c.iCountryId = vt.iCountryId 
		left join state as st ON st.iStateId = vt.iStateId 
		left join city as ct ON ct.iCityId = vt.iCityId 
    left join location_master as lm ON lm.iLocationId = vt.iLocationid 
		where 1=1 $etypeSql $eStatussql $ssql $adm_ssql $ord LIMIT $start, $per_page";
    } else {
        $sql = "SELECT vt.*,c.vCountry,ct.vCity,st.vState,lm.vLocationName
		from vehicle_type as vt left join country as c ON c.iCountryId = vt.iCountryId 
		left join state as st ON st.iStateId = vt.iStateId 
		left join city as ct ON ct.iCityId = vt.iCityId 
    left join location_master as lm ON lm.iLocationId = vt.iLocationid 
		where eType='" . $Vehicle_type_name . "' $eStatussql $ssql $adm_ssql $ord LIMIT $start, $per_page";
    }
}

$data_drv = $obj->MySQLSelect($sql);

$endRecord = count($data_drv);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page')
        $var_filter .= "&$key=" . stripslashes($val);
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;

if ($APP_TYPE == 'UberX' && $isUberXModuleAvailable=='Yes') {
    $sql_cat = "select *  from " . $sql_vehicle_category_table_name . " where iParentId='0'";
    $db_data_cat = $obj->MySQLSelect($sql_cat);
}
$Lsql = "SELECT lm.*,c.vCountry FROM location_master as lm LEFT JOIN country as c on c.iCountryId=lm.iCountryId WHERE lm.eStatus != 'Deleted' AND eFor='VehicleType' LIMIT $start, $per_page ";
$LocationData = $obj->MySQLSelect($Lsql);
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME; ?> | <?php echo $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?> </title>
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
                                <h2><?php echo $langage_lbl_admin['LBL_Vehicle']; ?> Type</h2>

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
                                    <?php if ($APP_TYPE != 'UberX') { ?>	
                                        <td width="10%" class=" padding-right10">
                                            <select name="option" id="option" class="form-control">
                                                <option value="">All</option>
                                                <option value="vt.vVehicleType_<?= $default_lang ?>" <?php
                                        if ($option == "vt.vVehicleType_" . $default_lang) {
                                            echo "selected";
                                        }
                                        ?> >Type</option>
                                                <option value="vt.fPricePerKM" <?php
                                            if ($option == 'vt.fPricePerKM') {
                                                echo "selected";
                                            }
                                        ?> >Price Per <em id="change_eUnit" style="font-style: normal"><?= $DEFAULT_DISTANCE_UNIT; ?></em></option>
                                                <option value="vt.fPricePerMin" <?php
                                            if ($option == 'vt.fPricePerMin') {
                                                echo "selected";
                                            }
                                        ?> >Price Per Min</option>
                                                <option value="vt.iPersonSize" <?php
                                            if ($option == 'vt.iPersonSize') {
                                                echo "selected";
                                            }
                                        ?> >Person Capacity</option>
                                                <option value="vt.iLocationid" <?php
                                            if ($option == 'vt.iLocationid') {
                                                echo "selected";
                                            }
                                        ?> >Location</option>
                                                        <?php if ($Vehicle_type_name == 'Ride-Delivery') { ?>
                                                    <option value="vt.eType" <?php
                                                    if ($option == 'vt.eType') {
                                                        echo "selected";
                                                    }
                                                            ?> >Vehicle Type</option>
                                                        <?php } ?>
                                            </select>
                                        </td>
                                    <?php } else { ?>
                                <input type="hidden" name="option" id="option" value="vVehicleType_<?= $default_lang ?>">
                            <?php } ?>
                            <td width="15%" class="searchform"><input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"  class="form-control" /></td>

                            <td width="12%" class="location_options" id="location_options">
                                <select name="location" id="location_value" class="form-control">
                                    <option value="" >Select Location</option>
                                    <option value="-1" <?php
                            if ($iLocationid == '-1') {
                                echo "selected";
                            }
                            ?> >All Location</option>
                                            <?php foreach ($LocationData as $l) { ?>
                                        <option value="<?= $l["iLocationId"] ?>" <?php
                                            if ($iLocationid == $l["iLocationId"]) {
                                                echo "selected";
                                            }
                                                ?> ><?= $l['vLocationName'] ?></option>
                                            <? } ?>
                                </select>
                            </td>
                            <?php if ($Vehicle_type_name == 'Ride-Delivery') { ?>
                                <td width="16%" class="eType_options" id="eType_options" >
                                    <select name="eType" id="eType_value" class="form-control">
                                        <option value=''>Select Vehicle Type</option>
                                        <? if($isRideModuleAvailable=='Yes') { ?><option value='Ride' <?php
                                if ($eType == 'Ride') {
                                    echo "selected";
                                }
                                ?> >Ride</option>
                                        <? } if($isDeliveryModuleAvailable=='Yes') { ?><option value="Deliver" <?php
                                    if ($eType == 'Deliver') {
                                        echo "selected";
                                    }
                                ?> >Deliver</option><? } ?>
                                    </select>
                                </td>
                            <?php } ?>
                            <?php if ($APP_TYPE == 'UberX' && $isUberXModuleAvailable=='Yes') { ?>
                                <td width="15%">
                                    <select  class="form-control" name = 'iVehicleCategoryId'>
                                        <option value="">Select Subcategory</option>
                                        <? for ($i = 0; $i < count($db_data_cat); $i++) { ?>
                                            <optgroup label="<?php echo $db_data_cat[$i]['vCategory_' . $default_lang]; ?>">
                                                <?php
                                                $sql = "SELECT * FROM  `" . $sql_vehicle_category_table_name . "` WHERE  `iParentId` = '" . $db_data_cat[$i]['iVehicleCategoryId'] . "' ";
                                                $db_data2 = $obj->MySQLSelect($sql);
                                                for ($j = 0; $j < count($db_data2); $j++) {
                                                    ?>
                                                    <option value = "<?php echo $db_data2[$j]['iVehicleCategoryId'] ?>"
                                                    <?php
                                                    if ($db_data2[$j]['iVehicleCategoryId'] == $iVehicleCategoryId)
                                                        echo 'selected';
                                                    ?>
                                                            >
                                                        <?php echo "&nbsp;&nbsp;|-- " . $db_data2[$j]['vCategory_' . $default_lang]; ?></option>
                                                    <? } ?>
                                            </optgroup>
                                        <? } ?>
                                    </select>
                                </td>
                            <?php } ?>
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
                            <td>
                                <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'vehicle_type.php'"/>
                            </td>
                            <?php if ($userObj->hasPermission('create-vehicle-type')) { ?>
                                <td width="30%"><a class="add-btn" href="vehicle_type_action.php" style="text-align: center;">Add <?php echo $langage_lbl_admin['LBL_Vehicle']; ?> Type</a></td>
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
                                            <?php if ($userObj->hasPermission(['update-status-vehicle-type', 'delete-vehicle-type'])) { ?>
                                                <select name="changeStatus" id="changeStatus" class="form-control" onchange="ChangeStatusAll(this.value);">
                                                    <option value="" >Select Action</option>
                                                    <?php if ($userObj->hasPermission('update-status-vehicle-type')) { ?>
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
                                                            <? if ($eStatus != 'Deleted' && $userObj->hasPermission('delete-vehicle-type')) { ?>
                                                        <option value="Deleted" <?php
                                                        if ($option == 'Delete') {
                                                            echo "selected";
                                                        }
                                                                ?> >Delete</option>
                                                            <? } ?>
                                                </select>
                                            <?php } ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($data_drv)) { ?>
                                        <!--                                        <div class="panel-heading">
                                                                                    <form name="_export_form" id="_export_form" method="post" >
                                                                                        <button type="button" onclick="showExportTypes('vehicle_type')" >Export</button>
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

                                                    <th width="15%"><a href="javascript:void(0);" onClick="Redirect(1,<?php
                                    if ($sortby == '1') {
                                        echo $order;
                                    } else {
                                        ?>0<?php } ?>)">Type<?php
                                                                       if ($sortby == 1) {
                                                                           if ($order == 0) {
                                                                               ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?>  <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                    <?php if ($APP_TYPE == 'UberX') { ?> 
                                                        <th width="10%">Subcategory</th>
                                                    <?php } ?>

                                                    <th width="10%"><a href="javascript:void(0);" onClick="Redirect(8,<?php
                                                    if ($sortby == '8') {
                                                        echo $order;
                                                    } else {
                                                        ?>0<?php } ?>)">Localization <?php
                                                                       if ($sortby == 8) {
                                                                           if ($order == 0) {
                                                                               ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                    <?php if ($APP_TYPE != 'UberX') { ?> 

                                                        <th width="20%"><a href="javascript:void(0);" onClick="Redirect(2,<?php
                                                        if ($sortby == '2') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Price Per <em id="change_eUnit" style="font-style: normal"><?= $DEFAULT_DISTANCE_UNIT; ?></em> <?php
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
                                                                    ?>0<?php } ?>)">Price Per Min <?php
                                                                           if ($sortby == 3) {
                                                                               if ($order == 0) {
                                                                                   ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                    }
                                                                } else {
                                                                    ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                        <th width="10%">Base Fare</th>
                                                        <th width="12%">Commission (%)</th>
                                                        <?php if ($APP_TYPE != "Delivery") { ?>
                                                            <th width="8%" align="left" style="text-align:left;"><a href="javascript:void(0);" onClick="Redirect(4,<?php
                                                            if ($sortby == '4') {
                                                                echo $order;
                                                            } else {
                                                                ?>0<?php } ?>)">Person Capacity <?php
                                                                                                                    if ($sortby == 4) {
                                                                                                                        if ($order == 0) {
                                                                                                                            ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                        }
                                                                    } else {
                                                                        ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                                    <?php
                                                                }
                                                            }
                                                            ?>
                                                            <?php if ($Vehicle_type_name == "Ride-Delivery") { ?>
                                                        <th width="8%" align="center" style="text-align:center;"><a href="javascript:void(0);" onClick="Redirect(7,<?php
                                                            if ($sortby == '7') {
                                                                echo $order;
                                                            } else {
                                                                    ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?> <?php
                                                                                                                    if ($sortby == 7) {
                                                                                                                        if ($order == 0) {
                                                                                                                            ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                    }
                                                                } else {
                                                                    ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                            <?php } ?>
                                                    <th width="12%" class="align-center"><a href="javascript:void(0);" onClick="Redirect(6,<?php
                                                            if ($sortby == '6') {
                                                                echo $order;
                                                            } else {
                                                                ?>0<?php } ?>)">Order <?php
                                                                                            if ($sortby == 6) {
                                                                                                if ($order == 0) {
                                                                                                    ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <th width="12%" class="align-center"><a href="javascript:void(0);" onClick="Redirect(5,<?php
                                                            if ($sortby == '5') {
                                                                echo $order;
                                                            } else {
                                                                ?>0<?php } ?>)">Status <?php
                                                                                            if ($sortby == 5) {
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
                                                    for ($i = 0; $i < count($data_drv); $i++) {
                                                        $poolTxt = $poolHelpTxt = "";
                                                        if ($data_drv[$i]['ePoolStatus'] == "Yes") {
                                                            $poolTxt = " (Pool)";
                                                            if ($POOL_ENABLE == "No") {
                                                                $poolHelpTxt = ' <i class="fa fa-warning" style="color: red;" data-placement="top" data-toggle="tooltip" data-original-title="Ride Pool Service has been disabled from general configurations."></i>';
                                                            }
                                                        }
                                                        ?>
                                                        <tr class="gradeA">
                                                            <td align="center" style="text-align:center;"><input type="checkbox" id="checkbox" name="checkbox[]" <?php echo $default; ?> value="<?php echo $data_drv[$i]['iVehicleTypeId']; ?>" />&nbsp;</td>
                                                            <td><?= $data_drv[$i]['vVehicleType_' . $default_lang] . $poolHelpTxt; ?></td>
                                                            <?php if ($APP_TYPE == 'UberX') { ?> 
                                                                <td><?= $data_drv[$i]['vCategory_' . $default_lang]; ?></td>
                                                            <?php } ?>
                                                            <?php if (($data_drv[$i]['iLocationid'] == "-1")) { ?>
                                                                <td>All Locations</td>
                                                            <?php } else { ?>
                                                                <td style="text-transform: capitalize;"><?= $data_drv[$i]['vLocationName']; ?></td>
                                                            <?php } ?>
                                                            <?php if ($APP_TYPE != 'UberX') { ?> 
                                                                <td><?= $data_drv[$i]['fPricePerKM'] ?></td>
                                                                <td><?= $data_drv[$i]['fPricePerMin'] ?></td>
                                                                <td><?= $data_drv[$i]['iBaseFare'] ?></td>
                                                                <td><?= $data_drv[$i]['fCommision'] ?></td>
                                                                <?php if ($APP_TYPE != "Delivery") { ?>
                                                                    <td><?= $data_drv[$i]['iPersonSize'] ?></td>
                                                                    <?php
                                                                }
                                                                if ($Vehicle_type_name == "Ride-Delivery") {
                                                                    ?>
                                                                    <td><?= $data_drv[$i]['eType']; ?> <?= $poolTxt; ?></td>
                                                                <?php } ?>
                                                                <td><?= $data_drv[$i]['iDisplayOrder'] ?></td>
                                                            <?php } ?>
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
                                                                <img src="<?= $dis_img; ?>" alt="image" data-toggle="tooltip" title="<?php echo $data_drv[$i]['eStatus']; ?>">
                                                            </td>
                                                            <td align="center" style="text-align:center;" class="action-btn001">
                                                                <div class="share-button openHoverAction-class" style="display: block;">
                                                                    <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>
                                                                    <div class="social show-moreOptions openPops_<?= $data_drv[$i]['iVehicleTypeId']; ?>">
                                                                        <ul>
                                                                            <li class="entypo-twitter" data-network="twitter"><a href="vehicle_type_action.php?id=<?= $data_drv[$i]['iVehicleTypeId']; ?>" data-toggle="tooltip" title="Edit">
                                                                                    <img src="img/edit-icon.png" alt="Edit">
                                                                                </a></li>
                                                                            <?php if ($userObj->hasPermission('update-status-vehicle-type')) { ?>
                                                                                <li class="entypo-facebook" data-network="facebook"><a href="javascript:void(0);" onClick="changeStatus('<?php echo $data_drv[$i]['iVehicleTypeId']; ?>', 'Inactive')"  data-toggle="tooltip" title="Activate"><img src="img/active-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" ></a></li>

                                                                                <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onClick="changeStatus('<?php echo $data_drv[$i]['iVehicleTypeId']; ?>', 'Active')" data-toggle="tooltip" title="Deactivate">
                                                                                        <img src="img/inactive-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >
                                                                                    </a></li>
                                                                            <?php } ?>
                                                                            <?php if ($eStatus != 'Deleted') { ?>
                                                                                <?php if ($userObj->hasPermission('delete-vehicle-type')) { ?>
                                                                                    <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onclick="changeStatusDelete('<?php echo $data_drv[$i]['iVehicleTypeId']; ?>')"data-toggle="tooltip" title="Delete">
                                                                                            <img src="img/delete-icon.png" alt="Delete" >
                                                                                        </a></li>
                                                                                <?php } ?>
                                                                            <? } ?>
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
                            <li>
                                <?php echo $langage_lbl_admin['LBL_Vehicle']; ?> Type module will list all <?php echo $langage_lbl_admin['LBL_Vehicle']; ?> types on this page.
                            </li>

                            <li>
                                Administrator can Edit / Delete any <?php echo $langage_lbl_admin['LBL_Vehicle']; ?> type. 
                            </li>
                            <!--<li>
                                Administrator can export data in XLS or PDF format.
                            </li>-->
                        </ul>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->

        <form name="pageForm" id="pageForm" action="action/vehicle_type.php" method="post" >
            <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
            <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
            <input type="hidden" name="iVehicleTypeId" id="iMainId01" value="" >
            <input type="hidden" name="status" id="status01" value="" >
            <input type="hidden" name="eType" id="eType" value="<?php echo $eType; ?>" >
            <input type="hidden" name="eStatus" id="eStatus" value="<?php echo $eStatus; ?>" >
            <input type="hidden" name="statusVal" id="statusVal" value="" >
            <input type="hidden" name="option" value="<?php echo $option; ?>" >
            <input type="hidden" name="keyword" value="<?php echo $keyword; ?>" >
            <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
            <input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
            <input type="hidden" name="location" id="location" value="<?php echo $iLocationid; ?>" >
            <input type="hidden" name="method" id="method" value="" >
            <?php if ($APP_TYPE == 'UberX') { ?>
                <input type="hidden" name="iVehicleCategoryId" id="iVehicleCategoryId" value="<?php echo $iVehicleCategoryId; ?>" >
            <?php } ?>
        </form>
        <?php include_once('footer.php'); ?>
        <script>
            $(document).ready(function () {
                $('#eType_options').hide();
                $('#option').each(function () {
                    if (this.value == 'vt.eType') {
                        $('#eType_options').show();
                        $('.searchform').hide();
                    }
                });

                $('#location_options').hide();
                $('#option').each(function () {
                    if (this.value == 'vt.iLocationid') {
                        $('#location_options').show();
                        $('.searchform').hide();
                    }
                });
            });


            $(function () {
                $('#option').change(function () {
                    if ($('#option').val() == 'vt.eType') {
                        $('#eType_options').show();
                        $("input[name=keyword]").val("");
                        $('.searchform').hide();
                        $('#location_options').hide();
                    } else if ($('#option').val() == 'vt.iLocationid') {
                        $('#location_options').show();
                        $("input[name=keyword]").val("");
                        $('.searchform').hide();
                        $('#eType_options').hide();
                    } else {
                        $('#eType_options').hide();
                        $("#eType_value").val("");
                        $('#location_options').hide();
                        $("#location_value").val("");
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