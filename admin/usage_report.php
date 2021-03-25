<?php
// $checkReferanceBy = (bool) $_SERVER['HTTP_REFERER']; // This will check User came by clicking or Typing URL
// echo "Asd";exit;
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../common.php';
// date_default_timezone_set('America/Los_Angeles'); // DO NOT CHANGE THIS. We need PST time to get records from mongodb using date.
date_default_timezone_set('UTC');
$utc = date("M d Y");
$time = strtotime($utc);
$dateInLocal = date("Y-m-d", $time);
global $userObj;
if (!isset($generalobjAdmin)) {
    require_once TPATH_CLASS . "class.general_admin.php";
    $generalobjAdmin = new General_admin();
}
if (!$userObj->hasPermission('view-providers')) {
    $userObj->redirect();
}
if (empty($oid)) {
    $oid = isset($_REQUEST['oid']) ? trim($_REQUEST['oid']) : "";
}
if (empty($sid)) {
    $sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : "";
}
if (empty($duration)) {
    $duration = isset($_REQUEST['duration']) ? ($_REQUEST['duration']) : "";
}
$authacc_oid = isset($_REQUEST['auth_oid']) ? trim($_REQUEST['auth_oid']) : "";


$startDate = $_REQUEST['sdate'];
$endDate = $_REQUEST['edate'];
if ($oid != '') {
    $oidURL = '?oid=' . $oid . '';
}
if ($sid != '') {
    $sidURL = '&sid=' . $sid . '';
}
if ($duration != '') {
    $durationURL = '&duration=' . $duration . '';
}
if ($authacc_oid != '' && $authacc_oid != 0) {
    $authacc_oid_URL = '&auth_oid=' . $authacc_oid . '';
}
if ($startDate != '') {
    $startDateURL = '&sdate=' . $startDate . '';
}
if ($endDate != '') {
    $endDateURL = '&edate=' . $endDate . '';
}
if (($startDate != '') && ($endDate != '')) {
    $add_date_to_url = $startDateURL . $endDateURL;
}
else {
    $add_date_to_url = $durationURL;
}
$actualURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . $oidURL . $sidURL . $authacc_oid_URL;
$actualURLWithDuration = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . $oidURL . $sidURL . $durationURL;
$actualURLWithRangeDate = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . $oidURL . $sidURL . $add_date_to_url;
if ($duration != '') {
    $fromDate = date('Y-m-d', strtotime("-" . $duration . " days"));
}
else {
    $fromDate = '';
}
// $today = date('Y-m-d');
$today = date("Y-m-d", $time);
if ($startDate != '') {
    $today = $startDate;
    $fromDate = $endDate;
    $duration = 'range';
}
$DbName = TSITE_DB;
$TableName = "auth_report_accounts_places";
$TableName_Auth_account = "auth_accounts_places";
$TableName_Auth_master_account = "auth_master_accounts_places";
if ($oid != '') {
    $activeServiceAry = array();
    $searchQueryServiceName['_id'] = new MongoDB\BSON\ObjectID($oid);
    $getServiceNameAndData = $obj->fetchAllRecordsFromMongoDBWithDBName($DbName, $TableName_Auth_master_account, $searchQueryServiceName);
    $service_name = $getServiceNameAndData[0]['vServiceName'];
    $ActiveServices = $getServiceNameAndData[0]['vActiveServices'];
    $activeServiceAry = explode(",", $ActiveServices);
}
if ($today != '' && $fromDate != '') {
    if ($today != $fromDate) {
        $dates['$lte'] = new \MongoDB\BSON\UTCDateTime(strtotime($today . '00:00:00') * 1000);
        $dates['$gte'] = new \MongoDB\BSON\UTCDateTime(strtotime($fromDate . '00:00:00') * 1000);
        $searchQueryReport['vUsageDate'] = $dates;
    }
    else {
        $searchQueryReport['vUsageDate'] = new \MongoDB\BSON\UTCDateTime(strtotime($today . '00:00:00') * 1000);
    }
}
else {
	$searchQueryReport['vUsageDate'] = new \MongoDB\BSON\UTCDateTime(strtotime($dateInLocal . '00:00:00') * 1000);
}
$allDataDateWise = $obj->fetchAllRecordsFromMongoDBWithDBName($DbName, $TableName, $searchQueryReport);
if ($sid != '') {
    $searchQuery['vServiceId'] = intval($sid);
}
if ($authacc_oid != '') {
    $searchQuery['_id'] = new MongoDB\BSON\ObjectID($authacc_oid);
}
if ($sid != '' || $authacc_oid != '') {
    // $data_from_auth_accounts = $obj->fetchAllCollectionFromMongoDB($DbName, $TableName_Auth_account);
    $data_from_auth_accounts = $obj->fetchAllRecordsFromMongoDBWithDBName($DbName, $TableName_Auth_account, $searchQuery);
}
if ($sid != '') {
    $searchQueryForDropDown['vServiceId'] = intval($sid);
    $data_from_auth_accounts_for_dropdown = $obj->fetchAllRecordsFromMongoDBWithDBName($DbName, $TableName_Auth_account, $searchQueryForDropDown);
}
foreach ($data_from_auth_accounts as $key => $authAccounts) {
    $titles[] = $authAccounts['vTitle'];
    foreach ($allDataDateWise as $key1 => $allDataDateWiseData) {
        if ($key == 0) {
            $authAcc[] = $allDataDateWiseData[$authAccounts['_id']['$oid']];
        }
    }
}
$machedRecords = (array_values(array_filter($authAcc)));
foreach ($machedRecords as $value) {
    $Direction = $Direction + intval($value['Direction']);
    $ReverseGeoCode = $ReverseGeoCode + intval($value['ReverseGeoCode']);
    $AutoComplete = $AutoComplete + intval($value['AutoComplete']);
    $PlaceDetails = $PlaceDetails + intval($value['PlaceDetails']);
}
$PlaceAPI = intval($AutoComplete) + intval($PlaceDetails);
// print_r($activeServiceAry);exit;

?>

<!DOCTYPE html>

<html lang="en">

    <!-- BEGIN HEAD-->

    <head>

        <meta charset="UTF-8" />

        <!-- <META HTTP-EQUIV="refresh" CONTENT="120"> -->

        <title><?=$SITE_NAME
?> |  <?php echo $langage_lbl_admin['LBL_MAP_API_USAGE_REPORT_ADMIN']; ?></title>

        <meta content="width=device-width, initial-scale=1.0" name="viewport" />

        <?php include_once 'global_files.php'; ?>

        <link rel="stylesheet" href="css/new_main.css" />

        <link rel="stylesheet" href="css/adminLTE/AdminLTE.min.css" />

        <script type="text/javascript" src="js/plugins/jquery/jquery.min.js"></script>

        <script type="text/javascript" src="js/plugins/morris/raphael-min.js"></script>

        <script type="text/javascript" src="js/plugins/morris/morris.min.js"></script>

        <script type="text/javascript" src="js/actions.js"></script>

        <script type="text/javascript" src="js/graph.js"></script>

        <link rel="stylesheet" href="css/bar.chart.min.css" />

        <script src='js/d3.v4.min.js'></script>

        <script src="js/jquery.bar.chart.min.js"></script>

        <script src="js/bootstrap-datepicker.min.js"></script>

        <style>

            .usage_report_button{

                border: 0;

                background: #fff;

                margin-left:10px

            }

            .usage_report_button.active{

                border: 0;

                background-color:#e5e5e5;

            }

            .usage_report_button_list{

                float: right;right: 0;

            }

            .info-box-icon-padding{

                /*padding: 20px;*/

            }

            .info-box-content-space{

                padding: 24px 10px;

            }

            .place-api-box{

                width:100% !important;

            }

            .bar_item{

                width:70px !important;

            }

            .datepicker-section{

                /* width:40%; */

            }

        </style>

    </head>

    <script type="text/javascript">

$(document).ready(function(){

        $("#startdate").datepicker({

            format: 'yyyy-mm-dd',

            todayBtn:  true,

            todayHighlight: true,

            autoclose: true,

            endDate: '+0d',

            ignoreReadonly: true,

        }).on('changeDate', function (selected) {

            var minDate = new Date(selected.date.valueOf());

            // $('#enddate').datepicker('setEndDate', minDate);

            $('#enddate').datepicker('setStartDate', minDate);

            $('#enddate').datepicker('setDate', minDate);

        });



        $("#enddate").datepicker({

            autoclose: true,

            ignoreReadonly: true,

            todayHighlight: true,

            endDate: '+0d',

            format: 'yyyy-mm-dd',

        })

        .on('changeDate', function (selected) {

            // var maxDate = new Date(selected.date.valueOf());

            // // $('#startdate').datepicker('setStartDate', maxDate);

            // $('#enddate').datepicker('setStartDate', maxDate);

        });





        $( "#dateRangeForm" ).submit(function( event ) {

            var startdate = $("#startdate").val();

            var enddate = $("#endtdate").val();

            window.location.href = '<?=$actualURLWithDuration . $authacc_oid_URL; ?>'+'&startdate='+startdate+'&enddate='+enddate+'';

        });

});

</script>



    <!-- END  HEAD-->

    <!-- BEGIN BODY-->

    <body class="padTop53">

        <!-- Main Loading -->

        <!-- MAIN WRAPPER -->

        <div id="wrap">

            <?php include_once 'header.php'; ?>

            <?php include_once 'left_menu.php'; ?>

            <!--PAGE CONTENT -->

            <div id="content">

                <div class="inner" style="min-height:10px">

                    <div id="add-hide-show-div">

                        <div class="row">

                            <div class="col-lg-12">

                                <div class="col-lg-6 pull-left">

                                    <?php $Title_Service_name = ($service_name != "") ? " - " . $service_name : ""; ?>

                                    <h2><?php echo $langage_lbl_admin['LBL_MAP_API_USAGE_REPORT_ADMIN'] . $Title_Service_name ?></h2>

                                </div>

                                <div class="col-lg-6 pull-right" style="left:0;">

                                    <?php
$current_filename = basename($_SERVER['REQUEST_URI'], '?' . $_SERVER['QUERY_STRING']);
if ($current_filename == 'usage_report.php') { ?>

                                        <!-- <a class="btn btn-success btn-ok pull-right" type="button" value="Close" href="javascript:window.open('','_self').close();"  value="close"> Back to Map API Setting </a> -->

                                        <a class="btn btn-success btn-ok pull-right" type="button" value="Close" href="map_api_setting.php"  value="close"> Back to Map API Setting </a>

                                    <?php
}
else { ?>

                                        <div style="float: right;    display: flex;">
                                            <a target="_blank" class="btn btn-success btn-ok " type="button" value="View Account" href="map_api_mongo_auth_places.php?id=<?=($sid) ? $sid : $vServiceId; ?>"> <?php echo 'View Account' ?> </a>
                                        <?php if ($less == 1) { ?>
                                        <form name="_export_form" id="_export_form" method="post" style="display: inline-flex;">
                                        <!-- <button class="btn btn-success btn-ok " type="button" style="width: 106px !important; margin-right:10px;margin-left:10px" onClick="showExportTypes('map_api')" >Export All</button>  -->
                                        <button type="button" class="btn btn-success btn-ok " style="width: 106px !important;margin-right:10px;margin-left:10px" id="exportall" >Export All</button>
                                        <button class="btn btn-success btn-ok " type="button" style="width: 100px !important;" onClick="showImportTypes('map_api')" >Import All</button>
                                    </form>
                                    

                                    <?php
    }
} ?>
                                </div>
                                </div>

                            </div>

                        </div>

                        <hr />
                        <?php // if($less == 1) {
 ?>
                       <!--  <div class="col-lg-6 pull-right" style="left:0;">
                            <div style="float: right; margin-bottom: -20px;">
                                    <form name="_export_form" id="_export_form" method="post" style="display: inline-flex;">
                                        <button class="btn btn-success btn-ok pull-right" type="button" style="width: 106px !important; margin-right:10px" onClick="showExportTypes('map_api')" >Export All</button> 
                                        <button class="btn btn-success btn-ok pull-right" type="button" style="width: 100px !important;" onClick="showImportTypes('map_api')" >Import All</button>
                                    </form>
                                </div>
                            </div> -->
                    <?php //}
 ?>
                </div>

                    <?php include 'valid_msg.php'; ?>



                    <div class="table-list">

                        <div class="row">

                            <div class="col-lg-12">

                                <div class="admin-nir-export">

                                    <div class="changeStatus col-lg-6 option-box-left">

                                        <span class="col-lg-4 new-select001">

                                            <?php
if (count($data_from_auth_accounts_for_dropdown) > 1) { ?>

                                            <span for="#ChangeService">Accounts: </span>

                                                <select name="ChangeService" id="ChangeService" class="form-control" onChange="ChangeService(this.value);">

                                                        <?php foreach ($data_from_auth_accounts_for_dropdown as $key => $authAccounts) {
        if (($authacc_oid == '') && ($authacc_oid == 0) && ($key == '0')) {
            $active = ' selected';
        }
        else if ($authacc_oid == $authAccounts['_id']['$oid']) {
            $active = ' selected';
        }
        else {
            $active = ' ';
        }
        echo "<option " . $active . " value='" . $authAccounts['_id']['$oid'] . "'>" . $authAccounts['vTitle'] . "</option>";
    } ?>

                                                </select>

                                            <?php
} ?>

                                        </span>

                                    </div>

                                    <div class="usage_report_button_list" >

                                        <a href="<?=$actualURL; ?>" class="usage_report_button <?=($duration == '') ? ' active' : '' ?>" >Today</a>

                                        <a href="<?=$actualURL . '&duration=2'; ?>" class="usage_report_button <?=($duration == '2') ? ' active' : '' ?>">2 Days</a>

                                        <a href="<?=$actualURL . '&duration=7'; ?>" class="usage_report_button <?=($duration == '7') ? ' active' : '' ?>">7 Days</a>

                                        <a href="<?=$actualURL . '&duration=15'; ?>" class="usage_report_button <?=($duration == '15') ? ' active' : '' ?>">15 Days</a>

                                        <a href="<?=$actualURL . '&duration=30'; ?>" class="usage_report_button <?=($duration == '30') ? ' active' : '' ?>">30 Days</a>

                                        <a href="#" data-toggle="modal" class="usage_report_button <?=($duration == 'range') ? ' active' : '' ?>" data-target="#datepicker-modal ">Select Date Range </a>

                                   </div>

                        </div>

                        <div class="row">

                        <div class="col-lg-12">

                <?php if ($userObj->hasPermission('dashboard-site-statistics')) { ?>

                    <div class="col-lg-6">

                        <div class="panel panel-primary bg-gray-light" >

                            <div class="panel-heading">

                                <div class="panel-title-box">

                                    <i class="fa fa-bar-chart"></i> <?php echo $langage_lbl_admin['LBL_MAP_API_USAGE_REPORT_ADMIN']; ?>

                                </div>

                            </div>

                            <div class="row padding_005">

                                <?php
    // print_r($activeServiceAry);
    if (in_array("Geocoding", $activeServiceAry) || (($ReverseGeoCode != '') && ($ReverseGeoCode != 0))) { ?>

                                <div class="col-lg-6">

                                        <div class="info-box bg-aqua">

                                            <span class="info-box-icon info-box-icon-padding"><i class="fa fa-globe"></i></span>

                                            <div class="info-box-content info-box-content-space">

                                                <span class="info-box-text"><?php echo $langage_lbl_admin['LBL_USAGE_REPORT_GEOCODE_ADMIN']; ?> </span>

                                                <span class="info-box-number"><?=number_format($ReverseGeoCode); ?></span>

                                            </div>

                                        </div>

                                </div>

                                <?php
    } ?>

                                <?php if (in_array("Direction", $activeServiceAry) || ($Direction != '' && $Direction != '0')) { ?>

                                <div class="col-lg-6">

                                        <div class="info-box bg-yellow">

                                            <span class="info-box-icon info-box-icon-padding"><i class="fa fa-location-arrow"></i></span>

                                            <div class="info-box-content info-box-content-space">

                                                <span class="info-box-text"><?php echo $langage_lbl_admin['LBL_USAGE_REPORT_DIRECTION_ADMIN']; ?> </span>

                                                <span class="info-box-number"><?=($Direction != '' || $Direction != '0') ? number_format($Direction) : '0'; ?></span>

                                            </div>

                                        </div>

                                </div>

                                <?php
    } ?>

                                <?php // if ((in_array("AutoComplete", $activeServiceAry)) || (in_array("PlaceDetails", $activeServiceAry))) {
     ?>

                                <?php if (in_array("AutoComplete", $activeServiceAry) || ($PlaceAPI != '' && $PlaceAPI != 0)) { ?>

                                <div class="col-lg-6">

                                        <div class="info-box bg-red">

                                            <span class="info-box-icon info-box-icon-padding"><i class="fa fa-search"></i></span>

                                            <div class="info-box-content info-box-content-space">

                                                <span class="info-box-text">AutoComplete</span>

                                                <span class="info-box-number"><?=number_format($PlaceAPI); ?></span>

                                            </div>

                                        </div>

                                </div>

                                <?php
    } ?>

                            </div>

                        </div>

                    </div>

                <?php
} ?>

                </div>

                        <div class="col-lg-12">

                            <div class="col-lg-6">

                                <div id="chtAnimatedBarChart" class="bcBar"></div>

                            </div>

                        </div>

                        <div class="clearfix"></div>

                        </div>

                            </div>

                                <div style="clear:both;"></div>

                                <div class="table-responsive">



<?php include 'pagination_n.php'; ?>

                                </div>

                            </div> <!--TABLE-END-->

                        </div>

                        <div class="admin-notes">

                        <h4>Notes:</h4>

                        <ul>

                            <li>

                                <strong>Geocoding:</strong> System is using this Service to convert location into address.

                            </li>

                            <li>

                                <strong>Direction:</strong> System is using this Service to draw route b/w two locations on map.

                            </li>

                            <li>

                                <strong>AutoComplete:</strong> System is using this Service to give suggestion of different places.

                            </li>
                             <?php if ($less == 1) { ?>
                            <li>
                                <strong>Import and Export Feature:</strong> Use Import and Export feature when you are going to change the hosting server. This feature will help you to set the existing API configuration into the new hosting server.
                            </li>
                            <?php
} ?>

                        </ul>

                    </div>

                    </div>



                </div>

            </div>

            <!--END PAGE CONTENT -->

        </div>

        <!--END MAIN WRAPPER -->



        <form name="pageForm" id="pageForm" action="action/map_api_setting.php" method="post" >

            <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">

            <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">

            <input type="hidden" name="iMongoName" id="iMainId01" value="" >

            <input type="hidden" name="iCompanyId" id="iCompanyId" value="<?php echo $iCompanyId; ?>" >

            <input type="hidden" name="eStatus" id="eStatus" value="<?php echo $eStatus; ?>" >

            <input type="hidden" name="status" id="status01" value="" >

            <input type="hidden" name="statusVal" id="statusVal" value="" >

            <input type="hidden" name="option" value="<?php echo $option; ?>" >

            <input type="hidden" name="keyword" value="<?php echo $keyword; ?>" >

            <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >

            <input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >

            <!-- <input type="hidden" name="method" id="method" value="" > -->

        </form>

        <div class="modal fade" id="import_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"

        aria-hidden="true">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header">

                    <h4>

                        Import All Map API Settings

                        <button type="button" class="close" data-dismiss="modal">x</button>

                    </h4>

                </div>

                <div class="modal-body" style="max-height: 450px;overflow: auto;">

                    <div id="imageIcons" style="display:none">

                        <div align="center">

                            <img src="default.gif"><br />

                            <span>Retrieving details,please Wait...</span>

                        </div>

                    </div>

                    <div>

                        <form name="_import_map_api_settings" id="_import_map_api_settings" enctype="multipart/form-data" method="POST" onsubmit="return confirm('Are you sure to remove your current map api setting data?');">

                        <div style="color:#1fbad6;"><b>Note:</b> Please note that all map api setting data will be removed.</div><br>

                        <input type="file" name="import_file_map_api" id="import_file_map_api" required ><br>

                        <input type="submit" value="Submit" class="btnalt button11" id="import_submit"  name="Submit" title="Submit" />

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>





        <div  class="modal fade" id="datepicker-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >

            <div class="modal-dialog" >

                <div class="modal-content">

                    <div class="modal-header">

                        <h4>

                            <!-- <i style="margin:2px 5px 0 2px;"><img src="images/icon/driver-icon.png" alt=""></i> -->

                            Select Date Range

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

                        <form id="dateRangeForm" name="dateRangeForm" method="get" autocomplete="off">

                        <div class="form-group">

                            <div class='input-group date' id='startdate' >

                                <input  name="edate" placeholder='Select Start Date' type='text' value="<?=($startDate != '') ? $startDate : '' ?>" title="Select Start Date" autocomplete="off" class="form-control" required  readonly/>

                                <span class="input-group-addon"  >

                                    <span class="glyphicon glyphicon-calendar"></span>

                                </span>

                            </div>

                        </div>

                        <div class="form-group" >

                            <div class='input-group date'  id='enddate' >

                                <input name="sdate" value="<?=($endDate != '') ? $endDate : '' ?>" title="Select End Date" placeholder='Select End Date' type='text' autocomplete="off" class="form-control" required readonly/>

                                <span class="input-group-addon">

                                    <span class="glyphicon glyphicon-calendar" ></span>

                                </span>

                            </div>

                            <?php if ($oid != '') { ?>

                                <input type='hidden' name="oid" value="<?=$oid
?>" />

                            <?php
} ?>

                            <?php if ($sid != '') { ?>

                                <input type='hidden' name="sid" value="<?=$sid
?>" />

                            <?php
} ?>

                            <?php if ($authacc_oid != '') { ?>

                                <input type='hidden' name="auth_oid" value="<?=$authacc_oid
?>" />

                            <?php
} ?>



                        </div>

                        <button  class="btn btn-success btn-ok form-control" type="submit">Submit</button>

                        </div>

                        </form>

                    </div>

                </div>

            </div>



        </div>



        <div  class="modal fade" id="detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >

            <div class="modal-dialog" >

                <div class="modal-content">

                    <div class="modal-header">

                        <h4>

                            <i style="margin:2px 5px 0 2px;"><img src="images/icon/driver-icon.png" alt=""></i>

                                <?=$langage_lbl_admin['LBL_MAP_API_USAGE_REPORT_ADMIN'] ?> Details

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

        <div  class="modal fade" id="services_config_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >

            <div class="modal-dialog" >

                <div class="modal-content">

                    <div class="modal-header">

                        <h4>

                            <i style="margin:2px 5px 0 2px;"><img src="images/icon/driver-icon.png" alt=""></i>

                                <?=$langage_lbl_admin['LBL_MAP_API_SERVICES_CONFIGURATION'] ?> Details

                            <button type="button" class="close" data-dismiss="modal">x</button>

                        </h4>

                    </div>

                    <div class="modal-body" style="max-height: 450px;overflow: auto;">

                        <div id="imageIcons" style="display:none">

                            <div align="center">

                                <img src="default.gif"><br/>



                            </div>

                        </div>

                        <div id="services_config"></div>

                    </div>

                </div>

            </div>

        </div>

<?php include_once 'footer.php'; ?>

<link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css" />

<link rel="stylesheet" href="css/select2/select2.min.css" />

<script src="js/plugins/select2.min.js"></script>

<!-- <script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script> -->



<script>

     function isJson(str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    }

        $(document).ready(function(){

                  $("#exportall").on('click', function () {

                var action = "main_export.php";
                var section = "map_api";

                var formValus = $("#_export_form, #pageForm, #show_export_modal_form_json").serialize();

                

                window.location.href = action + '?section=' + section + '&' + formValus;

                $("#show_export_types_modal_json").modal('hide');
                return false;

            });

      $('[data-toggle="tooltip"]').tooltip();   

    });

    $('INPUT[type="file"]').change(function (e) {
        $('#import_submit').prop('disabled', true);

    var ext = this.value.match(/\.(.+)$/)[1];

      switch (ext) {
        case 'json':

            $('#import_submit').attr('disabled', false);

            break;

        default:

            alert('Please upload json file.');

            this.value = '';

    }

    var fileName = e.target.files[0].name;



    // $.ajax({
    //   url: fileName,
    //   // dataType: 'json',
    //   // contentType: 'application/json',
    //   headers: {  'Access-Control-Allow-Origin': '*' },
    //   success: function(data,textStatus,xhr){
    //     console.log(data);
    //         if(data.servicedata.length > 0){
    //             console.log('more 0');
    //             $('#import_submit').attr('disabled', false);
    //         }else{
    //             console.log('count faile() 0');
    //              fail();
    //          }      
    //     },
    //     error: function(xhr,textStatus,errorThrown){
    //                  fail();
    //              },
    // });
    // $.getJSON(fileName, function( result ){
    //    console.log(result);
    //     // if (result.hasOwnProperty("servicedata")) { 
    //     //     console.log("available");
    //     // }else{
    //     //     console.log("Not available");
    //     //     alert('Please upload valid content file.');
    //     //     $('#import_submit').attr('disabled', true);
    //     //     this.value = '';
    //     // }
    //     // console.log((result.servicedata).length);
    //     // if((result.servicedata).length > 0 && (result.servicedata).length != ''){
    //     //     console.log(result.servicedata);
    //     // }else{
    //     //     alert('Please upload valid content file.');
    //     //     $('#import_submit').attr('disabled', true);
    //     //     this.value = '';
    //     // }
    // });

  

});

    
    function fail(){
        $('#import_submit').attr('disabled', true);
        $('#import_file_map_api').value = '';
         alert('Please upload valid content json file.');
            
    }

   $(function() {



      var chart_data = getData();



      var options = {

         data: chart_data, // data for chart rendering

         params: { // columns from data array for rendering graph

            group_name: 'company', // title for group name to be shown in legend

            value: 'value' // value for yaxis

         },

         horizontal_bars: false, // default chart orientation

         chart_height: 300, // default chart height in px

         colors: null, // colors for chart

         show_legend: true, // show chart legend

         legend: { // default legend settings

            position: LegendPosition.bottom, // legend position (bottom/top/right/left)

            width: 230 // legend width in pixels for left/right

         },

         x_grid_lines: true, // show x grid lines

         y_grid_lines: true, // show y grid lines

         tweenDuration: 2000, // speed for tranistions

         bars: { // default bar settings

            padding: 0.075, // padding between bars

            opacity: 0.7, // default bar opacity

            opacity_hover: 0.45, // default bar opacity on mouse hover

            disable_hover: true, // disable animation and legend on hover



            // hover_name_text: 'name', // text for name column for label displayed on bar hover

            // hover_value_text: 'value', // text for value column for label displayed on bar hover

         },

         number_format: { // default locale for number format

            format: ',.2f', // default number format

            decimal: '.', // decimal symbol

            thousands : ',', // thousand separator symbol

            grouping: [3], // thousand separator grouping

            currency: ['$'] // currency symbol

         },

         margin: { // margins for chart rendering

            top: 0, // top margin

            right: 35, // right margin

            bottom: 20, // bottom margin

            left: 70 // left margin

         },

         rotate_x_axis_labels: { // rotate xaxis label params

            process: true, // process xaxis label rotation

            minimun_resolution: 720, // minimun_resolution for label rotating

            bottom_margin: 15, // bottom margin for label rotation

            rotating_angle: 90, // angle for rotation,

            x_position: 9, // label x position after rotation

            y_position: -5 // label y position after rotation

         }

      };



      $('#chtAnimatedBarChart').animatedBarChart(options);

   });



   getData = function() {

      return [

        <?php if (in_array("Geocoding", $activeServiceAry) || ($ReverseGeoCode != '' && $ReverseGeoCode != '0')) { ?>

            { "company": "Geocoding : <?=intval($ReverseGeoCode); ?>",  "value": <?=($ReverseGeoCode != '' || $ReverseGeoCode != '0') ? (intval($ReverseGeoCode)) : '0'; ?>},

        <?php
}
if (in_array("Direction", $activeServiceAry) || ($Direction != '' && $Direction != '0')) { ?>

            { "company": "Direction : <?=intval($Direction); ?>", "value": <?=($Direction != '' || $Direction != '0') ? intval($Direction) : '0'; ?> },

        <?php
}
if ((in_array("AutoComplete", $activeServiceAry)) || (in_array("PlaceDetails", $activeServiceAry)) || ($PlaceAPI != '' && $PlaceAPI != '0')) { ?>

            { "company": "AutoComplete : <?=intval($PlaceAPI); ?>", "value": <?=($PlaceAPI != '' || $PlaceAPI != '0') ? intval($PlaceAPI) : '0'; ?> }

        <?php
} ?>

      ];

   }

</script>

        <script>

             function ChangeService(oid){

                window.location.href = '<?=$actualURLWithRangeDate
?>'+'&auth_oid='+oid;

            }

        </script>

    </body>

    <!-- END BODY-->

</html>