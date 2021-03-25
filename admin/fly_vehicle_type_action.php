<?php
include_once('../common.php');
require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$ENABLE_EDIT_DRIVER_VEHICLE = $generalobj->getConfigurations("configurations", "ENABLE_EDIT_DRIVER_VEHICLE");
$POOL_ENABLE = $generalobj->getConfigurations("configurations", "POOL_ENABLE");
$sql = "SELECT iCountryId,vCountry,vCountryCode FROM country WHERE eStatus = 'Active'";
$db_country = $obj->MySQLSelect($sql);

$sql_location = "SELECT * FROM location_master WHERE eStatus = 'Active' AND eFor = 'VehicleType' ORDER BY vLocationName ASC ";
$db_location = $obj->MySQLSelect($sql_location);

$sql = "SELECT vName,vSymbol FROM currency WHERE eDefault = 'Yes'";
$db_currency = $obj->MySQLSelect($sql);

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$message_print_id = $id;
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$tbl_name = 'vehicle_type';
$script = 'FlyVehicleType';
if ($APP_TYPE == 'Ride-Delivery-UberX') {
    $app_type_service = 'Ride-Delivery';
} else if ($APP_TYPE == 'Delivery') {
    $app_type_service = 'Deliver';
} else {
    $app_type_service = $APP_TYPE;
}

$select_order = $obj->MySQLSelect("SELECT count(iDisplayOrder) AS iDisplayOrder FROM vehicle_type where eFly ='1'");
$iDisplayOrder = isset($select_order[0]['iDisplayOrder']) ? $select_order[0]['iDisplayOrder'] : 0;
$iDisplayOrder_max = $iDisplayOrder + 1; // Maximum order number

$vVehicleType = isset($_POST['vVehicleType']) ? $_POST['vVehicleType'] : '';
$iVehicleCategoryId = isset($_POST['iVehicleCategoryId']) ? $_POST['iVehicleCategoryId'] : '';

$fCommision = isset($_POST['fCommision']) ? $_POST['fCommision'] : '';

$tMonPickStartTime = isset($_POST['tMonPickStartTime']) ? $_POST['tMonPickStartTime'] : '';
$tMonPickEndTime = isset($_POST['tMonPickEndTime']) ? $_POST['tMonPickEndTime'] : '';
$fMonPickUpPrice = isset($_POST['fMonPickUpPrice']) ? $_POST['fMonPickUpPrice'] : '';

$tTuePickStartTime = isset($_POST['tTuePickStartTime']) ? $_POST['tTuePickStartTime'] : '';
$tTuePickEndTime = isset($_POST['tTuePickEndTime']) ? $_POST['tTuePickEndTime'] : '';
$fTuePickUpPrice = isset($_POST['fTuePickUpPrice']) ? $_POST['fTuePickUpPrice'] : '';

$tWedPickStartTime = isset($_POST['tWedPickStartTime']) ? $_POST['tWedPickStartTime'] : '';
$tWedPickEndTime = isset($_POST['tWedPickEndTime']) ? $_POST['tWedPickEndTime'] : '';
$fWedPickUpPrice = isset($_POST['fWedPickUpPrice']) ? $_POST['fWedPickUpPrice'] : '';

$tThuPickStartTime = isset($_POST['tThuPickStartTime']) ? $_POST['tThuPickStartTime'] : '';
$tThuPickEndTime = isset($_POST['tThuPickEndTime']) ? $_POST['tThuPickEndTime'] : '';
$fThuPickUpPrice = isset($_POST['fThuPickUpPrice']) ? $_POST['fThuPickUpPrice'] : '';

$tFriPickStartTime = isset($_POST['tFriPickStartTime']) ? $_POST['tFriPickStartTime'] : '';
$tFriPickEndTime = isset($_POST['tFriPickEndTime']) ? $_POST['tFriPickEndTime'] : '';
$fFriPickUpPrice = isset($_POST['fFriPickUpPrice']) ? $_POST['fFriPickUpPrice'] : '';

$tSatPickStartTime = isset($_POST['tSatPickStartTime']) ? $_POST['tSatPickStartTime'] : '';
$tSatPickEndTime = isset($_POST['tSatPickEndTime']) ? $_POST['tSatPickEndTime'] : '';
$fSatPickUpPrice = isset($_POST['fSatPickUpPrice']) ? $_POST['fSatPickUpPrice'] : '';

$tSunPickStartTime = isset($_POST['tSunPickStartTime']) ? $_POST['tSunPickStartTime'] : '';
$tSunPickEndTime = isset($_POST['tSunPickEndTime']) ? $_POST['tSunPickEndTime'] : '';
$fSunPickUpPrice = isset($_POST['fSunPickUpPrice']) ? $_POST['fSunPickUpPrice'] : '';

$tNightStartTime = isset($_POST['tNightStartTime']) ? $_POST['tNightStartTime'] : '';
$tNightEndTime = isset($_POST['tNightEndTime']) ? $_POST['tNightEndTime'] : '';

$eStatus_picktime = isset($_POST['ePickStatus']) ? $_POST['ePickStatus'] : 'off';
$ePickStatus = ($eStatus_picktime == 'on') ? 'Active' : 'Inactive';
$eStatus_nighttime = isset($_POST['eNightStatus']) ? $_POST['eNightStatus'] : 'off';
$eNightStatus = ($eStatus_nighttime == 'on') ? 'Active' : 'Inactive';
$eType = isset($_POST['eType']) ? $_POST['eType'] : '';
$iPersonSize = isset($_POST['iPersonSize']) ? $_POST['iPersonSize'] : '1';
if ($iPersonSize <= 0) {
    $iPersonSize = 1;
}

//$eType = 'Fly';

$eFareType = isset($_POST['eFareType']) ? $_POST['eFareType'] : '';

$iCancellationTimeLimit = isset($_POST['iCancellationTimeLimit']) ? $_POST['iCancellationTimeLimit'] : '';
$fCancellationFare = isset($_POST['fCancellationFare']) ? $_POST['fCancellationFare'] : '';

$iCountryId = isset($_POST['iCountryId']) ? $_POST['iCountryId'] : '';
$iStateId = isset($_POST['iStateId']) ? $_POST['iStateId'] : '';
$iCityId = isset($_POST['iCityId']) ? $_POST['iCityId'] : '';
$iLocationId = isset($_POST['iLocationId']) ? $_POST['iLocationId'] : '';
//$iLocationId = '-1';
$eIconType = 'Fly';

$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

$iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : $iDisplayOrder;
$temp_order = isset($_POST['temp_order']) ? $_POST['temp_order'] : "";

$vTitle_store = array();
$vTitle_rental_store = array();
$sql = "SELECT * FROM `language_master` where eStatus='Active' ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = count($db_master);
if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
        $vValue = 'vVehicleType_' . $db_master[$i]['vCode'];
        array_push($vTitle_store, $vValue);
        $$vValue = isset($_POST[$vValue]) ? $_POST[$vValue] : '';
    }
}
$weekDaysArr = array("Monday" => "Mon", "Tuesday" => "Tue", "Wednesday" => "Wed", "Thursday" => "Thu", "Friday" => "Fri", "Saturday" => "Sat", "Sunday" => "Sun");
$nightTimeArr = $nightSurgeDataArr = array();

if (isset($_POST['btnsubmit'])) {

    if ($action == "Add" && !$userObj->hasPermission('create-fly-vehicle-type')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create fly vehicle type.';
        header("Location:fly_vehicle_type.php");
        exit;
    }

    if ($action == "Edit" && !$userObj->hasPermission('edit-fly-vehicle-type')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update fly vehicle type.';
        header("Location:fly_vehicle_type.php");
        exit;
    }


    foreach ($weekDaysArr as $day => $dval) {
        $dayStartIndex = "t" . $dval . "NightStartTime";
        $dayEndIndex = "t" . $dval . "NightEndTime";
        $priceIndex = "f" . $dval . "NightPrice";
        $nStartTime = isset($_POST[$dayStartIndex]) ? $_POST[$dayStartIndex] : '00:00:00';
        $nEndTime = isset($_POST[$dayEndIndex]) ? $_POST[$dayEndIndex] : '';
        $nPrice = isset($_POST[$priceIndex]) ? $_POST[$priceIndex] : '1.00';
        if ($nStartTime == "") {
            //$nStartTime = '00:00:00';
        }
        if ($nEndTime == "") {
            //$nEndTime = '00:00:00';
        }
        if ($nPrice == "") {
            $nPrice = '1.00';
        }
        $nightTimeArr[$dayStartIndex] = $nStartTime;
        $nightTimeArr[$dayEndIndex] = $nEndTime;
        $nightTimeArr[$priceIndex] = $nPrice;
    }
    $ePickStatus = $ePickStatus;
    $eNightStatus = $eNightStatus;

    $insertUpdateNightJson = "";
    if (count($nightTimeArr) > 0 && $eNightStatus == "Active") {
        $insertUpdateNightJson = ",tNightSurgeData='" . json_encode($nightTimeArr) . "'";
    }
    if (isset($_FILES['vLogo']) && $_FILES['vLogo']['name'] != "") {
        $filecheck = basename($_FILES['vLogo']['name']);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[count($fileextarr) - 1]);
        $flag_error = 0;
        $data = getimagesize($_FILES['vLogo']['tmp_name']);
        $width = $data[0];
        $height = $data[1];

        if ($width != 360 && $height != 360) {
            $flag_error = 1;
            $var_msg = "Please Upload image only 360px * 360px";
        }
        if ($flag_error == 1) {

            if ($action == "Add") {
                header("Location:fly_vehicle_type_action.php?varmsg=" . $var_msg . "&success=3");
                exit;
            } else {
                header("Location:fly_vehicle_type_action.php?id=" . $id . "&varmsg=" . $var_msg . "&success=3");
                exit;
            }

            // $generalobj->getPostForm($_POST, $var_msg, "vehicle_type_action.php?success=0&var_msg=".$var_msg);
            // exit;
        }
    }

    if (isset($_FILES['vLogo1']) && $_FILES['vLogo1']['name'] != "") {
        $filecheck = basename($_FILES['vLogo1']['name']);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[count($fileextarr) - 1]);
        $flag_error = 0;
        $data = getimagesize($_FILES['vLogo1']['tmp_name']);
        $width = $data[0];
        $height = $data[1];

        if ($width != 360 && $height != 360) {

            $flag_error = 1;
            $var_msg = "Please Upload image only 360px * 360px";
        }
        if ($flag_error == 1) {

            if ($action == "Add") {
                header("Location:fly_vehicle_type_action.php?varmsg=" . $var_msg . "&success=3");
                exit;
            } else {
                header("Location:fly_vehicle_type_action.php?id=" . $id . "&varmsg=" . $var_msg . "&success=3");
                exit;
            }
            exit;
        }
    }
    if ($ePickStatus == "Active") {
        
    }
    if (SITE_TYPE == 'Demo') {
        header("Location:fly_vehicle_type_action.php?id=" . $id . "&success=2");
        exit;
    }

    if ($temp_order == "1" && $action == "Add") {
        $temp_order = $iDisplayOrder_max;
    }
    if ($temp_order > $iDisplayOrder) {
        for ($i = $temp_order - 1; $i >= $iDisplayOrder; $i--) {
            $sql1 = "UPDATE " . $tbl_name . " SET iDisplayOrder = '" . ($i + 1) . "' WHERE iDisplayOrder = '" . $i . "' AND eType ='Fly'";
            $obj->sql_query($sql1);
        }
    } else if ($temp_order < $iDisplayOrder) {
        for ($i = $temp_order + 1; $i <= $iDisplayOrder; $i++) {
            $sql1 = "UPDATE " . $tbl_name . " SET iDisplayOrder = '" . ($i - 1) . "' WHERE iDisplayOrder = '" . $i . "' AND eType ='Fly'";
            $obj->sql_query($sql1);
        }
    }

    $q = "INSERT INTO ";
    $where = '';
    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `iVehicleTypeId` = '" . $id . "'";
    }
    $sql_str = '';
    if (count($vTitle_store) > 0) {
        for ($i = 0; $i < count($vTitle_store); $i++) {
            $vValue = 'vVehicleType_' . $db_master[$i]['vCode'];
            $sql_str .= $vValue . " = '" . $_POST[$vTitle_store[$i]] . "',";
        }
    }
    /* for ($i = 0; $i < count($vTitle_store); $i++) {
      $vValue = 'vVehicleType_' . $db_master[$i]['vCode'];
      $vValue_rental = 'vRentalAlias_' . $db_master[$i]['vCode']; */
    $query = $q . " `" . $tbl_name . "` SET
		`vVehicleType` = '" . $vVehicleType . "',
		`iVehicleCategoryId` = '" . $iVehicleCategoryId . "',
		`eIconType` = '" . $eIconType . "',
		`fCommision` = '" . $fCommision . "',
		`fNightPrice` = '" . $fNightPrice . "',				
		`tNightStartTime` = '" . $tNightStartTime . "',
		`tNightEndTime` = '" . $tNightEndTime . "',
		`ePickStatus` = '" . $ePickStatus . "',
		`eType` = '" . $eType . "',
        `eFly` = '1',
		`iCountryId` = '" . $iCountryId . "',
        `iLocationid` = '" . $iLocationId . "',
		`iStateId` = '" . $iStateId . "',
		`iCityId` = '" . $iCityId . "',
		`eNightStatus` = '" . $eNightStatus . "',
		`tMonPickStartTime` = '" . $tMonPickStartTime . "',
		`tMonPickEndTime` = '" . $tMonPickEndTime . "',
		`fMonPickUpPrice` = '" . $fMonPickUpPrice . "',
		`tTuePickStartTime` = '" . $tTuePickStartTime . "',
		`tTuePickEndTime` = '" . $tTuePickEndTime . "',
		`fTuePickUpPrice` = '" . $fTuePickUpPrice . "',
		`tWedPickStartTime` = '" . $tWedPickStartTime . "',
		`tWedPickEndTime` = '" . $tWedPickEndTime . "',
		`fWedPickUpPrice` = '" . $fWedPickUpPrice . "',
		`tThuPickStartTime` = '" . $tThuPickStartTime . "',
		`tThuPickEndTime` = '" . $tThuPickEndTime . "',
		`fThuPickUpPrice` = '" . $fThuPickUpPrice . "',
		`tFriPickStartTime` = '" . $tFriPickStartTime . "',
		`tFriPickEndTime` = '" . $tFriPickEndTime . "',
		`fFriPickUpPrice` = '" . $fFriPickUpPrice . "',
		`tSatPickStartTime` = '" . $tSatPickStartTime . "',
		`tSatPickEndTime` = '" . $tSatPickEndTime . "',
		`fSatPickUpPrice` = '" . $fSatPickUpPrice . "',
		`tSunPickStartTime` = '" . $tSunPickStartTime . "',
		`tSunPickEndTime` = '" . $tSunPickEndTime . "',
		`fSunPickUpPrice` = '" . $fSunPickUpPrice . "',
		`iCancellationTimeLimit` = '" . $iCancellationTimeLimit . "',
		`fCancellationFare` = '" . $fCancellationFare . "',
		`iPersonSize` = '" . $iPersonSize . "',
        " . $sql_str . "
        `iDisplayOrder` = '" . $iDisplayOrder . "'
        $insertUpdateNightJson"
            . $where;
    //}
    $obj->sql_query($query);
    $id = ($id != '') ? $id : $obj->GetInsertId();
    
    if (isset($_FILES['vLogo']) && $_FILES['vLogo']['name'] != "") {
        $currrent_upload_time = time();
        $img_path = $tconfig["tsite_upload_images_vehicle_type_path"];
        $temp_gallery = $img_path . '/';
        $image_object = $_FILES['vLogo']['tmp_name'];
        $image_name = $_FILES['vLogo']['name'];
        $check_file_query = "select iVehicleTypeId,vLogo from vehicle_type where iVehicleTypeId=" . $id;
        $check_file = $obj->sql_query($check_file_query);
        $fileextarr = explode(".", $image_name);
        $ext = strtolower($fileextarr[count($fileextarr) - 1]);
        if ($image_name != "") {
            $img = $generalobj->general_upload_image_vehicle_type($id, $image_name, $image_object, $check_file[0]['vLogo']);
            $img_time = explode("_", $img);
            $time_val = $img_time[0];
            $vImage = $time_val . "." . $ext;
            $sql = "UPDATE " . $tbl_name . " SET `vLogo` = '" . addslashes($vImage) . "' WHERE `iVehicleTypeId` = '" . $id . "'";
            $obj->sql_query($sql);
        }
    }
    if (isset($_FILES['vLogo1']) && $_FILES['vLogo1']['name'] != "") {
        $currrent_upload_time = time();
        $currrent_upload_time += 10;
        $img_path = $tconfig["tsite_upload_images_vehicle_type_path"];
        $temp_gallery = $img_path . '/';
        $image_object = $_FILES['vLogo1']['tmp_name'];
        $image_name = $_FILES['vLogo1']['name'];
        $check_file_query = "select iVehicleTypeId,vLogo1 from vehicle_type where iVehicleTypeId=" . $id;
        $check_file = $obj->sql_query($check_file_query);
        $fileextarr = explode(".", $image_name);
        $ext = strtolower($fileextarr[count($fileextarr) - 1]);
        if ($image_name != "") {
            $img = $generalobj->general_upload_image_vehicle_type($id, $image_name, $image_object, $check_file[0]['vLogo1']);
            $img_time = explode("_", $img);
            $time_val = $img_time[0];
            $vImage1 = $time_val . "." . $ext;

            $sql = "UPDATE " . $tbl_name . " SET `vLogo1` = '" . addslashes($vImage1) . "' WHERE `iVehicleTypeId` = '" . $id . "'";
            $obj->sql_query($sql);
        }
    }

    if ($action == "Add") {
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
        $_SESSION['success'] = "1";
    } else {
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        $_SESSION['success'] = "1";
    }
    header("Location:" . $backlink);
    exit;
}

// for Edit
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iVehicleTypeid = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    $vLabel = $id;
    if (count($db_data) > 0) {
        if (isset($db_data[0]['tNightSurgeData'])) {
            $nightSurgeDataArr = (array) json_decode($db_data[0]['tNightSurgeData']);
        }
        for ($i = 0; $i < count($db_master); $i++) {
            foreach ($db_data as $key => $value) {
                $vValue = 'vVehicleType_' . $db_master[$i]['vCode'];
                $$vValue = $value[$vValue];
                
                $vVehicleType = $value['vVehicleType'];
                $iVehicleCategoryId = $value['iVehicleCategoryId'];
                $fCommision = $value['fCommision'];
	      $iPersonSize = $value['iPersonSize'];
                $fBufferAmount = $value['fBufferAmount'];
                $fNightPrice = ($value['fNightPrice'] == 0) ? '' : $value['fNightPrice'];
                $tNightStartTime = $value['tNightStartTime'];
                $tNightEndTime = $value['tNightEndTime'];
                $ePickStatus = $value['ePickStatus'];
                $eNightStatus = $value['eNightStatus'];
                $tMonPickStartTime = $value['tMonPickStartTime'];
                $tMonPickEndTime = $value['tMonPickEndTime'];
                $fMonPickUpPrice = ($value['fMonPickUpPrice'] == 0) ? '' : $value['fMonPickUpPrice'];
                $tTuePickStartTime = $value['tTuePickStartTime'];
                $tTuePickEndTime = $value['tTuePickEndTime'];
                $fTuePickUpPrice = ($value['fTuePickUpPrice'] == 0) ? '' : $value['fTuePickUpPrice'];
                $tWedPickStartTime = $value['tWedPickStartTime'];
                $tWedPickEndTime = $value['tWedPickEndTime'];
                $fWedPickUpPrice = ($value['fWedPickUpPrice'] == 0) ? '' : $value['fWedPickUpPrice'];
                $tThuPickStartTime = $value['tThuPickStartTime'];
                $tThuPickEndTime = $value['tThuPickEndTime'];
                $fThuPickUpPrice = ($value['fThuPickUpPrice'] == 0) ? '' : $value['fThuPickUpPrice'];
                $tFriPickStartTime = $value['tFriPickStartTime'];
                $tFriPickEndTime = $value['tFriPickEndTime'];
                $fFriPickUpPrice = ($value['fFriPickUpPrice'] == 0) ? '' : $value['fFriPickUpPrice'];
                $tSatPickStartTime = $value['tSatPickStartTime'];
                $tSatPickEndTime = $value['tSatPickEndTime'];
                $fSatPickUpPrice = ($value['fSatPickUpPrice'] == 0) ? '' : $value['fSatPickUpPrice'];
                $tSunPickStartTime = $value['tSunPickStartTime'];
                $tSunPickEndTime = $value['tSunPickEndTime'];
                $fSunPickUpPrice = ($value['fSunPickUpPrice'] == 0) ? '' : $value['fSunPickUpPrice'];
                $vLogo = $value['vLogo'];
                $vLogo1 = $value['vLogo1'];
                $eType = $value['eType'];
                $eFareType = $value['eFareType'];
                $eIconType = $value['eIconType'];
                $iCancellationTimeLimit = ($value['iCancellationTimeLimit'] == 0) ? '' : $value['iCancellationTimeLimit'];
                $fCancellationFare = ($value['fCancellationFare'] == 0) ? '' : $value['fCancellationFare'];

                $iCountryId = $value['iCountryId'];
                $iStateId = $value['iStateId'];
                $iCityId = $value['iCityId'];
                $iLocationId = $value['iLocationid'];
                $iDisplayOrder_db = $value['iDisplayOrder'];
            }
        }
    }
}

?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title>Admin | <?php echo $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?> <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <?
        include_once('global_files.php');
        ?>
        <!-- On OFF switch -->
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
        <!--Added By Hasmukh On 11-10-2018 For Clock Time Picker Start Css-->
        <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="css/bootstrap-clockpicker.min.css">
        <!--Added By Hasmukh On 11-10-2018 For Clock Time Picker Start Css-->
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >

        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?
            include_once('header.php');
            include_once('left_menu.php');
            ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2> <?php echo $langage_lbl_admin['LBL_FLY_VEHICLE_TYPE']; ?> </h2>
                            <!-- <a href="vehicle_type.php">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a> -->
                            <a href="javascript:void(0);" class="back_link">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />
                    <div class="body-div">
                        <div class="form-group">
                            <? if ($success == 1) { ?>
                                <div class="alert alert-success alert-dismissable msgs_hide">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                    <?= $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?> Updated successfully.
                                </div><br/>
                            <? } elseif ($success == 2) { ?>
                                <div class="alert alert-danger alert-dismissable ">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                    "Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.
                                </div><br/>
                            <? } else if ($success == 3) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                    <?php echo $_REQUEST['varmsg']; ?> 
                                </div><br/>	
                            <? } else if ($success == 4) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                    "Please Select Night Start Time less than Night End Time." 
                                </div><br/>	
                            <? } ?>
                            <? if ($_REQUEST['var_msg'] != Null) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    Record  Not Updated .
                                </div><br/>
                            <? } ?>		
                            <div id="price1" ></div>
                            <form id="_vehicleType_form" name="_vehicleType_form" method="post" action="" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?= $id; ?>"/>

                                <?php if ($APP_TYPE == 'Ride-Delivery-UberX') { ?>
                                    <input type="hidden" name="APP_TYPE" value="<?= $app_type_service; ?>"/>
                                <?php } else { ?>
                                    <input type="hidden" name="APP_TYPE" value="<?= $APP_TYPE; ?>"/>
                                <?php } ?>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="eType" value="Ride" id='etypedelivery' />
                                <input type="hidden" name="backlink" id="backlink" value="fly_vehicle_type.php"/>
                                <div class="row"><div class="col-lg-12" id="errorMessage"></div></div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label><?php echo $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?><span class="red"> *</span> 
                                            <? if ($APP_TYPE != "UberX") { ?>
                                                <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Please add if your vehicle type is "Business" , "First Class" , "Light Aircraft" , "Premium Class" etc'></i>
                                            <? } ?>
                                        </label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vVehicleType"  id="vVehicleType"  value="<?= $vVehicleType; ?>"  required placeholder="<?php echo $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?>">
                                    </div>

                                </div>
                                <?
                                if ($count_all > 0) {
                                    for ($i = 0; $i < $count_all; $i++) {
                                        $vCode = $db_master[$i]['vCode'];
                                        $vTitle = $db_master[$i]['vTitle'];
                                        $eDefault = $db_master[$i]['eDefault'];

                                        $vValue = 'vVehicleType_' . $vCode;

                                        $required = ($eDefault == 'Yes') ? 'required' : '';
                                        $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                        ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?php echo $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?> (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" value="<?= $$vValue; ?>" placeholder="<?= $vTitle; ?> Value" <?= $required; ?>>
                                            </div>
                                            <? if ($vCode == $default_lang && count($db_master) > 1) { ?>
                                                <div class="col-lg-6">
                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vVehicleType');">Convert To All Language</button>
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <?
                                    }
                                }
                                ?>
                                <input type="hidden" name="iLocationId" id="iLocationId" value="-1">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Select Location <span class="red"> *</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Define the area where this fly vehicle is available. E.g. This fly vehicle will fly from Source station A to Destination station B, hence define the area where the source to destination station pin point is covered from the Geo fence module.'></i></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select class="form-control" name = 'iLocationId' id="iLocationId" required="" onchange="changeCode_distance(this.value);">
                                            <option value="">Select Location</option>
                                            <option value="-1" <? if ($iLocationId == "-1") { ?>selected<? } ?>>All</option>
                                            <?php
                                            foreach ($db_location as $i => $row) {
                                                if (count($userObj->locations) > 0 && !in_array($row['iLocationId'], $userObj->locations)) {
                                                    continue;
                                                }
                                                ?>
                                                <option value = "<?= $row['iLocationId'] ?>" <? if ($iLocationId == $row['iLocationId']) { ?>selected<? } ?>><?= $row['vLocationName'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <?php if ($userObj->hasPermission('view-geo-fence-locations')) { ?>
                                        <div class="col-lg-6">
                                            <a class="btn btn-primary" href="location.php" target="_blank">Enter New Location</a>
                                        </div>
                                    <?php } ?>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label> Commission (%)<span class="red"> *</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='This would be the amount which you are willing to charge from the <?php echo strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']); ?> in form of commission for each ride.'></i></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="fCommision"  id="fCommision" value="<?= $fCommision; ?>" required >
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label> <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?> Cancellation Time Limit ( in minute )<span class="red"></span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="This is the timelimit based on which the <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?> would be charged if he/she cancel's the ride after the specified period limit."></i></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="iCancellationTimeLimit"  id="iCancellationTimeLimit" value="<?= $iCancellationTimeLimit; ?>" onblur="checkblanktimelimit('iCancellationTimeLimit','fCancellationFare');" >
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label> <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?> Cancellation Charges  (Price In <?= $db_currency[0]['vName'] ?>)<span class="red"></span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Below mentioned charges would be applied to the <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?> when the <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?> cancels the ride after the specific period of time.'></i></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="fCancellationFare"  id="fCancellationFare" value="<?= $fCancellationFare; ?>" onfocus="checkcancellationfare('iCancellationTimeLimit');"><span class="error" id="showerrorlimit"></span>
                                    </div>
                                </div>
                                <?php
                                $enableBufferAmt = 0;
                                if ($SYSTEM_PAYMENT_FLOW == "Method-3" || $SYSTEM_PAYMENT_FLOW == "Method-2") {
                                    $enableBufferAmt = 1;
                                    if ($SYSTEM_PAYMENT_FLOW == "Method-2" && $APP_TYPE == "Delivery") {
                                        $enableBufferAmt = 0;
                                    }
                                }
                                if ($enableBufferAmt == 1) {
                                    $helfTxtBufferAmt = "This will be used when your payment method is based on `Wallet payment`. When partial payment via cash is not available in your payment method then user needs to top up their wallet. User will be charged as `Estimated Fare + Buffer Amount`. When partial payment via cash is available then user will be charged as `Estimated Fare + Buffer Amount` in specific cases (Like - destination later) only.Note: This will be applied when user selects `Pay by wallet` method.";
                                    if ($SYSTEM_PAYMENT_FLOW == "Method-2") {
                                        $helfTxtBufferAmt = "This will be used when your payment method is based on `Wallet payment`. When partial payment via cash is not available in your payment method then user needs to top up their wallet. User will be charged as `Estimated Fare + Buffer Amount`. When partial payment via cash is available then user will be charged as `Estimated Fare + Buffer Amount` in specific cases (Like - destination later) only.Note: This will be applied when user selects `Pay by wallet` method.";
                                    }
                                    ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label> Buffer Amount<span class="red"> *</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='<?= $helfTxtBufferAmt; ?>'></i></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="text" min="0" onkeypress="return isNumberKey(event)" class="form-control" name="fBufferAmount"  id="fBufferAmount" value="<?= $fBufferAmount; ?>" required="">
                                        </div>
                                    </div>
                                <?php } ?>
                                <div id="Regular_div2">
				<div class="row" id="Regular_subdiv">
					<div class="col-lg-12">
					    <label> Available Seats/Person capacity<span class="red"> *</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Mention the total number of seats available in the vehicle for the <?php echo strtolower($langage_lbl_admin['LBL_RIDERS_ADMIN']); ?>.'></i></label>
					</div>
					<div class="col-lg-6">
					    <input type="number" class="form-control" min="2" required="" name="iPersonSize"  id="iPersonSize" value="<?= $iPersonSize; ?>" >
					    Note : Excluding the <?php echo strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']); ?>
					</div>
					<div id="digit"></div>
				</div> 
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Peak Time Surcharge On/Off <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='This is a multiplier X  to the standard fares causing the fare to be higher than the standard fare during certain times the day; i.e. if X is 1.2 during some point of time then the standard fare will be multiplied by 1.2 to get the final fare.'></i></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="make-switch" data-on="success" data-off="warning">
                                                <input type="checkbox" id="ePickStatus" onChange="showhidepickuptime();" name="ePickStatus" <?= ($id != '' && $ePickStatus == 'Active') ? 'checked' : ''; ?>/>
                                            </div>
                                        </div>
                                    </div>                                       
                                    <div id="showpickuptime" style="display:none;">
                                        <div class="row">
                                            <div class="col-lg-12 main-table001">
                                                <div class="main-table001">
                                                    <table class="col-lg-2">	
                                                        <tr>
                                                            <td align="center"><b>Monday</b></td>
                                                        </tr>
                                                        <tr>
                                                            <td> Start Time</td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <div class="input-group clockpicker-with-callbacks">
                                                                    <input type="text" class="form-control" name="tMonPickStartTime"  id="tMonPickStartTime" value="<?php
                                                                    if ($tMonPickStartTime != "00:00:00") {
                                                                        echo $tMonPickStartTime;
                                                                    }
                                                                    ?>" placeholder="Pickup Start Time" readonly>
                                                                </div>
                                                            </td>
                                                        </tr>	
                                                        <tr>
                                                            <td> End Time</td>
                                                        </tr>
                                                        <tr>
                                                            <td> 
                                                                <div class="input-group clockpicker-with-callbacks">
                                                                    <input type="text" class="form-control" name="tMonPickEndTime"  id="tMonPickEndTime" value="<?php
                                                                    if ($tMonPickEndTime != "00:00:00") {
                                                                        echo $tMonPickEndTime;
                                                                    }
                                                                    ?>" placeholder="Pickup End Time" readonly>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td> Price</td>
                                                        </tr>
                                                        <tr>
                                                            <td>  <input type="text" class="form-control" name="fMonPickUpPrice"  id="fMonPickUpPrice" value="<?= $fMonPickUpPrice; ?>" placeholder="Enter Price"></td>
                                                        </tr>
                                                        <tr>
                                                            <td><input type="button" name="RMonday" id="RMonday" value="reset" /></td>
                                                        </tr>
                                                    </table>
                                                    <table class="col-lg-2">	
                                                        <tr>
                                                            <td align="center"><b>Tuesday</b></td>
                                                        </tr>
                                                        <tr>
                                                            <td>  Start Time</td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <div class="input-group clockpicker-with-callbacks">
                                                                    <input type="text" class="form-control" name="tTuePickStartTime"  id="tTuePickStartTime" value="<?php
                                                                    if ($tTuePickStartTime != "00:00:00") {
                                                                        echo $tTuePickStartTime;
                                                                    }
                                                                    ?>" placeholder="Pickup Start Time" readonly>
                                                                </div>
                                                            </td>
                                                        </tr>	
                                                        <tr>
                                                            <td> End Time</td>
                                                        </tr>
                                                        <tr>
                                                            <td> 
                                                                <div class="input-group clockpicker-with-callbacks">
                                                                    <input type="text" class="form-control" name="tTuePickEndTime"  id="tTuePickEndTime" value="<?php
                                                                    if ($tTuePickEndTime != "00:00:00") {
                                                                        echo $tTuePickEndTime;
                                                                    }
                                                                    ?>" placeholder="Pickup End Time" readonly>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td> Price</td>
                                                        </tr>
                                                        <tr>
                                                            <td> <input type="text" class="form-control" name="fTuePickUpPrice"  id="fTuePickUpPrice" value="<?= $fTuePickUpPrice; ?>" placeholder="Enter Price" ></td>
                                                        </tr>
                                                        <tr>
                                                            <td><input type="button" name="RTuesday" id="RTuesday" value="reset" /></td>
                                                        </tr>
                                                    </table>
                                                    <table class="col-lg-2">	
                                                        <tr>
                                                            <td align="center"><b>Wednesday</b></td>
                                                        </tr>
                                                        <tr>
                                                            <td> Start Time</td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <div class="input-group clockpicker-with-callbacks">
                                                                    <input type="text" class="form-control" name="tWedPickStartTime"  id="tWedPickStartTime" value="<?php
                                                                    if ($tWedPickStartTime != "00:00:00") {
                                                                        echo $tWedPickStartTime;
                                                                    }
                                                                    ?>" placeholder="Pickup Start Time" readonly>
                                                                </div>
                                                            </td>
                                                        </tr>	
                                                        <tr>
                                                            <td> End Time</td>
                                                        </tr>
                                                        <tr>
                                                            <td> 
                                                                <div class="input-group clockpicker-with-callbacks">
                                                                    <input type="text" class="form-control" name="tWedPickEndTime"  id="tWedPickEndTime" value="<?php
                                                                    if ($tWedPickEndTime != "00:00:00") {
                                                                        echo $tWedPickEndTime;
                                                                    }
                                                                    ?>" placeholder="Pickup End Time" readonly>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td> Price</td>
                                                        </tr>
                                                        <tr>
                                                            <td><input type="text" class="form-control" name="fWedPickUpPrice"  id="fWedPickUpPrice" value="<?= $fWedPickUpPrice; ?>"  placeholder="Enter Price" ></td>
                                                        </tr>
                                                        <tr>
                                                            <td><input type="button" name="RWednesday" id="RWednesday" value="reset" /></td>
                                                        </tr>
                                                    </table>
                                                    <table class="col-lg-2">	
                                                        <tr>
                                                            <td align="center"><b>Thursday</b></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Start Time</td>
                                                        </tr>
                                                        <tr>
                                                            <td> 
                                                                <div class="input-group clockpicker-with-callbacks">
                                                                    <input type="text" class="form-control" name="tThuPickStartTime"  id="tThuPickStartTime" value="<?php
                                                                    if ($tThuPickStartTime != "00:00:00") {
                                                                        echo $tThuPickStartTime;
                                                                    }
                                                                    ?>" placeholder="Pickup Start Time" readonly>
                                                                </div>
                                                            </td>
                                                        </tr>	
                                                        <tr>
                                                            <td> End Time</td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <div class="input-group clockpicker-with-callbacks">
                                                                    <input type="text" class="form-control" name="tThuPickEndTime"  id="tThuPickEndTime" value="<?php
                                                                    if ($tThuPickEndTime != "00:00:00") {
                                                                        echo $tThuPickEndTime;
                                                                    }
                                                                    ?>" placeholder="Pickup End Time" readonly>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td> Price</td>
                                                        </tr>
                                                        <tr>
                                                            <td><input type="text" class="form-control" name="fThuPickUpPrice"  id="fThuPickUpPrice" value="<?= $fThuPickUpPrice; ?>" placeholder="Enter Price" ></td>
                                                        </tr>
                                                        <tr>
                                                            <td><input type="button" name="RThursday" id="RThursday" value="reset" /></td>
                                                        </tr>
                                                    </table>
                                                    <table class="col-lg-2">	
                                                        <tr>
                                                            <td align="center"><b>Friday</b></td>
                                                        </tr>
                                                        <tr>
                                                            <td> Start Time</td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <div class="input-group clockpicker-with-callbacks">
                                                                    <input type="text" class="form-control" name="tFriPickStartTime"  id="tFriPickStartTime" value="<?php
                                                                    if ($tFriPickStartTime != "00:00:00") {
                                                                        echo $tFriPickStartTime;
                                                                    }
                                                                    ?>" placeholder="Pickup Start Time" readonly>
                                                                </div>
                                                            </td>
                                                        </tr>	
                                                        <tr>
                                                            <td> End Time</td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <div class="input-group clockpicker-with-callbacks">
                                                                    <input type="text" class="form-control" name="tFriPickEndTime"  id="tFriPickEndTime" value="<?php
                                                                    if ($tFriPickEndTime != "00:00:00") {
                                                                        echo $tFriPickEndTime;
                                                                    }
                                                                    ?>" placeholder="Pickup End Time" readonly>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td> Price</td>
                                                        </tr>
                                                        <tr>
                                                            <td> <input type="text" class="form-control" name="fFriPickUpPrice"  id="fFriPickUpPrice" value="<?= $fFriPickUpPrice; ?>" placeholder="Enter Price"  ></td>
                                                        </tr>
                                                        <tr>
                                                            <td><input type="button" name="RFriday" id="RFriday" value="reset" /></td>
                                                        </tr>
                                                    </table>
                                                    <table class="col-lg-2">	
                                                        <tr>
                                                            <td align="center"><b>Saturday</b></td>
                                                        </tr>
                                                        <tr>
                                                            <td> Start Time</td>
                                                        </tr>
                                                        <tr>
                                                            <td> 
                                                                <div class="input-group clockpicker-with-callbacks">
                                                                    <input type="text" class="form-control" name="tSatPickStartTime"  id="tSatPickStartTime" value="<?php
                                                                    if ($tSatPickStartTime != "00:00:00") {
                                                                        echo $tSatPickStartTime;
                                                                    }
                                                                    ?>" placeholder="Pickup Start Time" readonly>
                                                                </div>
                                                            </td>
                                                        </tr>	
                                                        <tr>
                                                            <td> End Time</td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <div class="input-group clockpicker-with-callbacks">
                                                                    <input type="text" class="form-control" name="tSatPickEndTime"  id="tSatPickEndTime" value="<?php
                                                                    if ($tSatPickEndTime != "00:00:00") {
                                                                        echo $tSatPickEndTime;
                                                                    }
                                                                    ?>" placeholder="Pickup End Time" readonly>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td> Price</td>
                                                        </tr>
                                                        <tr>
                                                            <td> <input type="text" class="form-control" name="fSatPickUpPrice"  id="fSatPickUpPrice" value="<?= $fSatPickUpPrice; ?>" placeholder="Enter Price" ></td>
                                                        </tr>
                                                        <tr>
                                                            <td><input type="button" name="RSaturday" id="RSaturday" value="reset" /></td>
                                                        </tr>
                                                    </table>  	  
                                                    <table class="col-lg-2">	
                                                        <tr>
                                                            <td align="center"><b>Sunday</b></td>
                                                        </tr>
                                                        <tr>
                                                            <td> Start Time</td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <div class="input-group clockpicker-with-callbacks">
                                                                    <input type="text" class="form-control" name="tSunPickStartTime"  id="tSunPickStartTime" value="<?php
                                                                    if ($tSunPickStartTime != "00:00:00") {
                                                                        echo $tSunPickStartTime;
                                                                    }
                                                                    ?>" placeholder="Pickup Start Time" readonly>
                                                                </div>
                                                            </td>
                                                        </tr>	
                                                        <tr>
                                                            <td> End Time</td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <div class="input-group clockpicker-with-callbacks">
                                                                    <input type="text" class="form-control" name="tSunPickEndTime"  id="tSunPickEndTime" value="<?= $tSunPickEndTime; ?>" placeholder="Pickup End Time" readonly>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td> Price</td>
                                                        </tr>
                                                        <tr>
                                                            <td><input type="text" class="form-control" name="fSunPickUpPrice"  id="fSunPickUpPrice" value="<?= $fSunPickUpPrice; ?>" placeholder="Enter Price" ></td>
                                                        </tr>
                                                        <tr>
                                                            <td><input type="button" name="RSunday" id="RSunday" value="reset" /></td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div> 
                                    <div class="row">
                                        <div class="col-lg-12">                                                 
                                            <label> Night Charges On/Off <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='This is a multiplier X  to the standard fares causing the fare to be higher than the standard fare during night time; i.e. if X is 1.2 during some point of time then the standard fare will be multiplied by 1.2 to get the final fare.'></i></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="make-switch" data-on="success" data-off="warning">
                                                <input type="checkbox" id="eNightStatus" onChange="showhidenighttime();" name="eNightStatus" <?= ($id != '' && $eNightStatus == 'Active') ? 'checked' : ''; ?>/>
                                            </div>
                                        </div>
                                    </div>                         
                                    <!--Added By Hasmukh On 10-10-2018 For Store Night Charges Details In Json Start !-->
                                    <div id="shownighttime" style="display:none;">
                                        <div class="row">
                                            <div class="col-lg-12 main-table001">
                                                <div class="main-table001">
                                                    <?php
                                                    foreach ($weekDaysArr as $dayKey => $dayVal) {
                                                        $dayStartId = "t" . $dayVal . "NightStartTime";
                                                        $dayEndId = "t" . $dayVal . "NightEndTime";
                                                        $priceId = "f" . $dayVal . "NightPrice";
                                                        ?>
                                                        <table  class="col-lg-2">	
                                                            <tr>
                                                                <td align="center"><b><?= $dayKey; ?></b></td>
                                                            </tr>
                                                            <tr>
                                                                <td> Start Time</td>
                                                            </tr>
                                                            <tr>
                                                                <td> 
                                                                    <div class="input-group clockpicker-with-callbacks">
                                                                        <input type="text" class="form-control" name="<?= $dayStartId; ?>"  id="<?= $dayStartId; ?>" value="<?
                                                                        if (isset($nightSurgeDataArr[$dayStartId])) {
                                                                            echo $nightSurgeDataArr[$dayStartId];
                                                                        }
                                                                        ?>" placeholder="Night Start Time" >

                                                                    </div>
                                                                </td>
                                                            </tr>	
                                                            <tr>
                                                                <td> End Time</td>
                                                            </tr>
                                                            <tr>
                                                                <td> 
                                                                    <div class="input-group clockpicker-with-callbacks">
                                                                        <input type="text" class="form-control" name="<?= $dayEndId; ?>"  id="<?= $dayEndId; ?>" value="<?
                                                                        if (isset($nightSurgeDataArr[$dayEndId])) {
                                                                            echo $nightSurgeDataArr[$dayEndId];
                                                                        }
                                                                        ?>" placeholder="Night End Time" >

                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td> Price</td>
                                                            </tr>
                                                            <tr>
                                                                <td>  <input type="text" class="form-control" name="<?= $priceId; ?>"  id="<?= $priceId; ?>" value="<?
                                                                    if (isset($nightSurgeDataArr[$priceId])) {
                                                                        echo $nightSurgeDataArr[$priceId];
                                                                    }
                                                                    ?>" placeholder="Enter Price"  ></td>
                                                            </tr>
                                                            <tr>
                                                                <td><input type="button" name="RNight<?= $dayKey ?>" id="RNight<?= $dayKey ?>" value="reset" /></td>
                                                            </tr>
                                                        </table>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--Added By Hasmukh On 10-10-2018 For Store Night Charges Details In Json End !-->

                                    <!--<div id="shownighttime" style="display:none;">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label> Night Charges Start Time</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" readonly class=" form-control" name="tNightStartTime"  id="tNightStartTime" value="<?= $tNightStartTime; ?>" placeholder="Select Night Start Time"  >
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label> Night Charges End Time</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" readonly class=" form-control" name="tNightEndTime"  id="tNightEndTime" value="<?= $tNightEndTime; ?>" placeholder="Select Night End Time" >
                                            </div>
                                        </div> 
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label> Night Time Surcharge (X)</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="fNightPrice"  id="fNightPrice" value="<?= $fNightPrice; ?>" placeholder="Enter Price" >

                                            </div>
                                        </div>
                                    </div> -->
                                    <?php //}                ?> 
                                </div>
                                <?php if ($APP_TYPE != 'UberX') { ?> 
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label><?php echo $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?> Picture (Gray image) <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='This is used to represent the vehicle type as a icon in application.'></i></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <?
                                            $rand = rand(1000, 9999);
                                            if ($vLogo != '') {
                                                ?>
                                                <img src="<?= $tconfig['tsite_upload_images_vehicle_type'] . "/" . $id . "/ios/3x_" . $vLogo . "?dm=$rand"; ?>" style="width:100px;height:100px;">
                                            <? } ?>
                                            <input type="file" class="form-control" name="vLogo" id="vLogo" placeholder="" style="padding-bottom: 4%; height:5%;">
                                            <br/>
                                            Note: Upload only png image size of 360px*360px.
                                        </div>
                                    </div>										
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label><?php echo $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?> Picture (Orange image) <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='This is used to represent the vehicle type as a icon in application. Oragen icon is used to represent the vehicle type as a selected.'></i></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <? if ($vLogo1 != '') { ?>
                                                <img src="<?= $tconfig['tsite_upload_images_vehicle_type'] . "/" . $id . "/ios/3x_" . $vLogo1 . "?dm=$rand"; ?>" style="width:100px;height:100px;">
                                            <? } ?>
                                            <input type="file" class="form-control" name="vLogo1" id="vLogo1" placeholder="" style="padding-bottom: 4%; height: 5%;">
                                            <br/>
                                            Note: Upload only png image size of 360px*360px.
                                        </div>
                                    </div>
                                <?php } ?> 
                                <!--<div id="price" style="margin: 10px;"></div><br/>-->
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Order</label>
                                    </div>
                                    <div class="col-lg-6">

                                        <input type="hidden" name="temp_order" id="temp_order" value="<?= ($action == 'Edit') ? $iDisplayOrder_db : '1'; ?>">
                                        <?
                                        $display_numbers = ($action == "Add") ? $iDisplayOrder_max : $iDisplayOrder_db;
                                        ?>
                                        <select name="iDisplayOrder" class="form-control">
                                            <? for ($i = 1; $i <= $display_numbers; $i++) { ?>
                                                <option value="<?= $i ?>" <?
                                                if ($i == $iDisplayOrder_db) {
                                                    echo "selected";
                                                }
                                                ?>> -- <?= $i ?> --</option>
                                                    <? } ?>
                                        </select>

                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <?php if (($action == 'Edit' && $userObj->hasPermission('edit-fly-vehicle-type')) || ($action == 'Add' && $userObj->hasPermission('create-fly-vehicle-type'))) { ?>
                                            <input type="submit" class="btn btn-default" name="btnsubmit" id="btnsubmit" value="<?php if ($action == 'Add') { ?><?= $action; ?> Vehicle Type<?php } else { ?>Update<?php } ?>">
                                            <input type="reset" value="Reset" class="btn btn-default">
                                        <?php } ?>
                                        <!-- <a href="javascript:void(0);" onclick="reset_form('_vehicleType_form');" class="btn btn-default">Reset</a> -->
                                        <a href="vehicle_type.php" class="btn btn-default back_link">Cancel</a>
                                    </div>
                                </div>

                            </form>
                        </div>

                    </div>
                    <div style="clear:both;"></div>
                </div>

            </div>

            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
        <div class="row loding-action" id="imageIcon" style="display:none;">
            <div align="center">                                                                       
                <img src="default.gif">                                                              
                <span>Language Translation is in Process. Please Wait...</span>                       
            </div>                                                                                 
        </div>

        <? include_once('footer_vehicleType.php'); ?>

        <script type="text/javascript" src="js/validation/jquery.validate.min.js" ></script>
        <script type="text/javascript" src="js/validation/additional-methods.min.js" ></script>
        <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
        <link rel="stylesheet" type="text/css" media="screen" href="css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
        <script type="text/javascript" src="js/moment.min.js"></script>
        <script type="text/javascript" src="js/bootstrap-datetimepicker.min.js"></script>
        <!--Added By Hasmukh On 11-10-2018 For Clock Time Picker Start Js-->
        <script type="text/javascript" src="js/bootstrap-clockpicker.min.js"></script>
        <!--Added By Hasmukh On 11-10-2018 For Clock Time Picker End Js -->
        <!--For Faretype-->
        <script>
                                                document.getElementById('RMonday').onclick = function () {
                                                    var tMonPickStartTime = document.getElementById('tMonPickStartTime');
                                                    var tMonPickEndTime = document.getElementById('tMonPickEndTime');
                                                    var fMonPickUpPrice = document.getElementById('fMonPickUpPrice');

                                                    tMonPickStartTime.value = tMonPickEndTime.value = fMonPickUpPrice.value = '';
                                                    //fMonPickUpPrice.value= fMonPickUpPrice.defaultValue;
                                                };

                                                document.getElementById('RTuesday').onclick = function () {
                                                    var tTuePickStartTime = document.getElementById('tTuePickStartTime');
                                                    var tTuePickEndTime = document.getElementById('tTuePickEndTime');
                                                    var fTuePickUpPrice = document.getElementById('fTuePickUpPrice');

                                                    tTuePickStartTime.value = tTuePickEndTime.value = fTuePickUpPrice.value = '';
                                                    //fTuePickUpPrice.value= fTuePickUpPrice.defaultValue;
                                                };

                                                document.getElementById('RWednesday').onclick = function () {
                                                    var tWedPickStartTime = document.getElementById('tWedPickStartTime');
                                                    var tWedPickEndTime = document.getElementById('tWedPickEndTime');
                                                    var fWedPickUpPrice = document.getElementById('fWedPickUpPrice');

                                                    tWedPickStartTime.value = tWedPickEndTime.value = fWedPickUpPrice.value = '';
                                                    //fWedPickUpPrice.value= fWedPickUpPrice.defaultValue;
                                                };

                                                document.getElementById('RThursday').onclick = function () {
                                                    var tThuPickStartTime = document.getElementById('tThuPickStartTime');
                                                    var tThuPickEndTime = document.getElementById('tThuPickEndTime');
                                                    var fThuPickUpPrice = document.getElementById('fThuPickUpPrice');

                                                    tThuPickStartTime.value = tThuPickEndTime.value = fThuPickUpPrice.value = '';
                                                    //fThuPickUpPrice.value= fThuPickUpPrice.defaultValue;
                                                };


                                                document.getElementById('RFriday').onclick = function () {
                                                    var tFriPickStartTime = document.getElementById('tFriPickStartTime');
                                                    var tFriPickEndTime = document.getElementById('tFriPickEndTime');
                                                    var fFriPickUpPrice = document.getElementById('fFriPickUpPrice');

                                                    tFriPickStartTime.value = tFriPickEndTime.value = fFriPickUpPrice.value = '';
                                                    //fFriPickUpPrice.value= fFriPickUpPrice.defaultValue;
                                                };

                                                document.getElementById('RSaturday').onclick = function () {
                                                    var tSatPickStartTime = document.getElementById('tSatPickStartTime');
                                                    var tSatPickEndTime = document.getElementById('tSatPickEndTime');
                                                    var fSatPickUpPrice = document.getElementById('fSatPickUpPrice');

                                                    tSatPickStartTime.value = tSatPickEndTime.value = fSatPickUpPrice.value = '';
                                                    //fSatPickUpPrice.value= fSatPickUpPrice.defaultValue;
                                                };

                                                document.getElementById('RSunday').onclick = function () {
                                                    var tSunPickStartTime = document.getElementById('tSunPickStartTime');
                                                    var tSunPickEndTime = document.getElementById('tSunPickEndTime');
                                                    var fSunPickUpPrice = document.getElementById('fSunPickUpPrice');

                                                    tSunPickStartTime.value = tSunPickEndTime.value = fSunPickUpPrice.value = '';
                                                    //fSunPickUpPrice.value= fSunPickUpPrice.defaultValue;
                                                };


                                                /*$.validator.addMethod('minStrict', function (value, el, param) {
                                                 return this.optional(el) || value > param;
                                                 }, "please enter more than 1");*/

                                                // just for the demos, avoids form submit
                                                if (_system_script == 'VehicleType') {
                                                    if ($('#_vehicleType_form').length !== 0) {
                                                        var minPersonSize = 1;
                                                        if ($('input[name=ePoolStatus]').is(':checked')) {
                                                            var minPersonSize = 2;
                                                        }
                                                        $("#_vehicleType_form").validate({
                                                            rules: {
                                                                fPricePerKM: {
                                                                    number: true,
                                                                    min: 0
                                                                },
                                                                fPricePerMin: {
                                                                    number: true,
                                                                    min: 0
                                                                },
                                                                fPricePerHour: {
                                                                    number: true,
                                                                    min: 0
                                                                },
                                                                iMinFare: {
                                                                    number: true,
                                                                    min: 0
                                                                },
                                                                iBaseFare: {
                                                                    number: true,
                                                                    min: 0
                                                                },
                                                                fCommision: {
                                                                    number: true,
                                                                    min: 0
                                                                },
                                                                iCancellationTimeLimit: {
                                                                    number: true,
                                                                    min: 1
                                                                },
                                                                fCancellationFare: {
                                                                    number: true,
                                                                    min: 0
                                                                },
                                                                iWaitingFeeTimeLimit: {
                                                                    number: true,
                                                                    min: 1
                                                                },
                                                                fWaitingFees: {
                                                                    number: true,
                                                                    min: 0
                                                                },
                                                                fTripHoldFees: {
                                                                    number: true,
                                                                    min: 0
                                                                },
                                                                fMonPickUpPrice: {
                                                                    required: function () {
                                                                        var tMonPickStartTime = $("#tMonPickStartTime").val();
                                                                        var tMonPickEndTime = $("#tMonPickEndTime").val();
                                                                        if (tMonPickStartTime != '' && tMonPickEndTime != '') {
                                                                            return true;
                                                                        } else {
                                                                            return false;
                                                                        }
                                                                    },
                                                                    number: true,
                                                                    minStrict: 1
                                                                },
                                                                fTuePickUpPrice: {
                                                                    required: function () {
                                                                        var tTuePickStartTime = $("#tTuePickStartTime").val();
                                                                        var tTuePickEndTime = $("#tTuePickEndTime").val();
                                                                        if (tTuePickStartTime != '' && tTuePickEndTime != '') {
                                                                            return true;
                                                                        } else {
                                                                            return false;
                                                                        }
                                                                    },
                                                                    number: true,
                                                                    minStrict: 1
                                                                },
                                                                fWedPickUpPrice: {
                                                                    required: function () {
                                                                        var tWedPickStartTime = $("#tWedPickStartTime").val();
                                                                        var tWedPickEndTime = $("#tWedPickEndTime").val();
                                                                        if (tWedPickStartTime != '' && tWedPickEndTime != '') {
                                                                            return true;
                                                                        } else {
                                                                            return false;
                                                                        }
                                                                    },
                                                                    number: true,
                                                                    minStrict: 1
                                                                },
                                                                fThuPickUpPrice: {
                                                                    required: function () {
                                                                        var tThuPickStartTime = $("#tThuPickStartTime").val();
                                                                        var tThuPickEndTime = $("#tThuPickEndTime").val();
                                                                        if (tThuPickStartTime != '' && tThuPickEndTime != '') {
                                                                            return true;
                                                                        } else {
                                                                            return false;
                                                                        }
                                                                    },
                                                                    number: true,
                                                                    minStrict: 1
                                                                },
                                                                fFriPickUpPrice: {
                                                                    required: function () {
                                                                        var tFriPickStartTime = $("#tFriPickStartTime").val();
                                                                        var tFriPickEndTime = $("#tFriPickEndTime").val();
                                                                        if (tFriPickStartTime != '' && tFriPickEndTime != '') {
                                                                            return true;
                                                                        } else {
                                                                            return false;
                                                                        }
                                                                    },
                                                                    number: true,
                                                                    minStrict: 1
                                                                },
                                                                fSatPickUpPrice: {
                                                                    required: function () {
                                                                        var tSatPickStartTime = $("#tSatPickStartTime").val();
                                                                        var tSatPickEndTime = $("#tSatPickEndTime").val();
                                                                        if (tSatPickStartTime != '' && tSatPickEndTime != '') {
                                                                            return true;
                                                                        } else {
                                                                            return false;
                                                                        }
                                                                    },
                                                                    number: true,
                                                                    minStrict: 1
                                                                },
                                                                fSunPickUpPrice: {
                                                                    required: function () {
                                                                        var tSunPickStartTime = $("#tSunPickStartTime").val();
                                                                        var tSunPickEndTime = $("#tSunPickEndTime").val();
                                                                        if (tSunPickStartTime != '' && tSunPickEndTime != '') {
                                                                            return true;
                                                                        } else {
                                                                            return false;
                                                                        }
                                                                    },
                                                                    number: true,
                                                                    minStrict: 1
                                                                },
                                                                fMonNightPrice: {
                                                                    required: function () {
                                                                        var tMonNightStartTime = $("#tMonNightStartTime").val();
                                                                        var tMonNightEndTime = $("#tMonNightEndTime").val();
                                                                        if (tMonNightStartTime != '' && tMonNightEndTime != '') {
                                                                            return true;
                                                                        } else {
                                                                            return false;
                                                                        }
                                                                    },
                                                                    number: true,
                                                                    minStrict: 1
                                                                },
                                                                fTueNightPrice: {
                                                                    required: function () {
                                                                        var tTueNightStartTime = $("#tTueNightStartTime").val();
                                                                        var tTueNightEndTime = $("#tTueNightEndTime").val();
                                                                        if (tTueNightStartTime != '' && tTueNightEndTime != '') {
                                                                            return true;
                                                                        } else {
                                                                            return false;
                                                                        }
                                                                    },
                                                                    number: true,
                                                                    minStrict: 1
                                                                },
                                                                fWedNightPrice: {
                                                                    required: function () {
                                                                        var tWedNightStartTime = $("#tWedNightStartTime").val();
                                                                        var tWedNightEndTime = $("#tWedNightEndTime").val();
                                                                        if (tWedNightStartTime != '' && tWedNightEndTime != '') {
                                                                            return true;
                                                                        } else {
                                                                            return false;
                                                                        }
                                                                    },
                                                                    number: true,
                                                                    minStrict: 1
                                                                },
                                                                fThuNightPrice: {
                                                                    required: function () {
                                                                        var tThuNightStartTime = $("#tThuNightStartTime").val();
                                                                        var tThuNightEndTime = $("#tThuNightEndTime").val();
                                                                        if (tThuNightStartTime != '' && tThuNightEndTime != '') {
                                                                            return true;
                                                                        } else {
                                                                            return false;
                                                                        }
                                                                    },
                                                                    number: true,
                                                                    minStrict: 1
                                                                },
                                                                fFriNightPrice: {
                                                                    required: function () {
                                                                        var tFriNightStartTime = $("#tFriNightStartTime").val();
                                                                        var tFriNightEndTime = $("#tFriNightEndTime").val();
                                                                        if (tFriNightStartTime != '' && tFriNightEndTime != '') {
                                                                            return true;
                                                                        } else {
                                                                            return false;
                                                                        }
                                                                    },
                                                                    number: true,
                                                                    minStrict: 1
                                                                },
                                                                fSatNightPrice: {
                                                                    required: function () {
                                                                        var tSatNightStartTime = $("#tSatNightStartTime").val();
                                                                        var tSatNightEndTime = $("#tSatNightEndTime").val();
                                                                        if (tSatNightStartTime != '' && tSatNightEndTime != '') {
                                                                            return true;
                                                                        } else {
                                                                            return false;
                                                                        }
                                                                    },
                                                                    number: true,
                                                                    minStrict: 1
                                                                },
                                                                fSunNightPrice: {
                                                                    required: function () {
                                                                        var tSunNightStartTime = $("#tSunNightStartTime").val();
                                                                        var tSunNightEndTime = $("#tSunNightEndTime").val();
                                                                        if (tSunNightStartTime != '' && tSunNightEndTime != '') {
                                                                            return true;
                                                                        } else {
                                                                            return false;
                                                                        }
                                                                    },
                                                                    number: true,
                                                                    minStrict: 1
                                                                },
                                                                /*fNightPrice: {
                                                                 number: true,
                                                                 min: 1
                                                                 },*/
                                                                iPersonSize: {
                                                                    digits: true,
                                                                    min: minPersonSize,
                                                                }
                                                            },
                                                            /*submitHandler: function(form) {  
                                                             if ($(form).valid()){
                                                             var ENABLE_EDIT_DRIVER_VEHICLE = "<?= $ENABLE_EDIT_DRIVER_VEHICLE ?>";
                                                             alert(ENABLE_EDIT_DRIVER_VEHICLE);
                                                             return false;
                                                             if(ENABLE_EDIT_DRIVER_VEHICLE == 'Yes'){
                                                             form.submit(); 
                                                             return false; // prevent normal form posting
                                                             } else {
                                                             alert("<?= $langage_lbl_admin['LBL_EDIT_VEHICLE_DISABLED'] ?>");
                                                             return false; // prevent normal form posting
                                                             }
                                                             } 
                                                             }*/
                                                        });
                                                    }
                                                }
                                                jQuery.extend(jQuery.validator.messages, {
                                                    number: "Please enter a valid number.",
                                                    min: jQuery.validator.format("Please enter a value greater than 0.")
                                                });

        </script>		
        <script>
            $('[data-toggle="tooltip"]').tooltip();
            window.onload = function () {
                var vid = $("#vid").val();
                var eFareType = $("#eFareType").val();
                var AllowQty = $("#AllowQty").val();
                if (vid == '')
                {
                    get_faretype('Regular');
                } else
                {
                    get_faretype(eFareType);
                }

                var AppTypenew = '<?php echo $APP_TYPE; ?>';
                var editTypeId = '<?php echo $id; ?>';
                if (AppTypenew == 'Ride-Delivery-UberX') {
                    var appTYpe = '<?php echo $app_type_service; ?>';
                } else {
                    var appTYpe = '<?php echo $APP_TYPE; ?>';
                }
                /*appTYpe == 'UberX' && eFareType == 'Regular'*/
                if (appTYpe == 'UberX' && eFareType == 'Regular') {
                    $("#Regular_div2").show();
                    //$("#Regular_div1").show();

                } else if (appTYpe == 'Ride' || appTYpe == 'Delivery' || appTYpe == 'Ride-Delivery') {
                    $("#Regular_div2").show();
                    //$("#Regular_div1").show();

                } else {
                    $("#Regular_div2").hide();
                    //$("#Regular_div1").show();
                }
                var aapTypeCheck = '<?php echo $eType; ?>';
                if (editTypeId == "") {
                    aapTypeCheck = "Ride";
                }
                var POOL_ENABLE = '<?php echo $POOL_ENABLE; ?>';
                var poolStatus = '<?php echo $ePoolStatus; ?>';

                if (POOL_ENABLE == "Yes" && (appTYpe == 'Ride' || appTYpe == 'Ride-Delivery') && (aapTypeCheck == 'Ride' || aapTypeCheck == 'Ride-Delivery')) {
                    $("#poolenable,#pool_div").show();
                    if ($('input[name=ePoolStatus]').is(':checked')) {
                        $("#pool_div").show();
                        $("#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv").hide();
                    }
                } else {
                    $("#poolenable,#pool_div").hide();
                    $("#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv").show();
                }
                if ($('input[name=ePoolStatus]').is(':checked') && POOL_ENABLE == "Yes" && (appTYpe == 'Ride' || appTYpe == 'Ride-Delivery') && (aapTypeCheck == 'Ride' || aapTypeCheck == 'Ride-Delivery')) {
                    $("#pool_div").show();
                    $("#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv").hide();
                } else {
                    $("#pool_div").hide();
                    $("#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv").show();
                }
                if (appTYpe == 'Delivery') {
                    $("#Regular_subdiv").hide();
                    //$(".RentalAlias").hide();
                } else if (appTYpe == 'Ride-Delivery') {
                    var eTypedeliverval = $('#etypedelivery').val();
                    if (eTypedeliverval == 'Deliver') {
                        $("#Regular_subdiv,#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv").hide();
                        //$(".RentalAlias").hide();
                    } else {
                        $("#Regular_subdiv,#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv").show();
                        //$(".RentalAlias").show();
                    }
                    $('#etypedelivery').on('change', function () {
                        eTypedeliver = this.value;
                        if (eTypedeliver == 'Deliver') {
                            $("#Regular_subdiv").hide();
                            $("#poolenable,#pool_div").hide();
                            $("#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv").hide();
                            //$(".RentalAlias,#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv").hide();
                            $("#MotoBike,#Cycle,#Truck").show();
                        } else {
                            $("#Regular_subdiv").show();
                            $("#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv").show();
                            //$(".RentalAlias,#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv").show();
                            if (POOL_ENABLE == "Yes") {
                                $("#poolenable").show();
                                if ($('input[name=ePoolStatus]').is(':checked')) {
                                    $("#pool_div").show();
                                    //$(".RentalAlias").hide();
                                    $("#MotoBike,#Cycle,#Truck,#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv").hide();
                                }
                            }
                        }
                    });
                } else {
                    $("#Regular_subdiv,#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv").show();
                    //$(".RentalAlias,#Regular_subdiv,#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv").show();
                }
                if (poolStatus == "Yes") {
                    $("#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv").hide();
                    //$(".RentalAlias,#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv").hide();
                }
            };
            var successMSG1 = '<?php echo $success; ?>';

            if (successMSG1 != '') {
                setTimeout(function () {
                    $(".msgs_hide").hide(1000)
                }, 5000);
            }

            function get_faretype(val) {
                var AppTypenew = '<?php echo $APP_TYPE; ?>';
                if (AppTypenew == 'Ride-Delivery-UberX') {
                    var appTYpe = '<?php echo $app_type_service; ?>';
                } else {
                    var appTYpe = '<?php echo $APP_TYPE; ?>';
                }
                //var appTYpe = '<?php echo $APP_TYPE; ?>';
                if (appTYpe == 'UberX') {
                    if (val == "Fixed") {
                        //$("#fixed_div").show();
                        $("#Regular_div1").hide();
                        $("#Regular_div2").hide();
                        $("#fPickUpPrice").removeAttr('required');
                        $("#tPickStartTime").removeAttr('required');
                        $("#tPickEndTime").removeAttr('required');
                        $("#tNightStartTime").removeAttr('required');
                        $("#tNightEndTime").removeAttr('required');
                        $("#fPricePerHour").removeAttr('required');
                        $("#iMinFare").removeAttr('required');

                    } else if (val == "Regular") {
                        $("#Regular_div2").show();
                        $("#Regular_div1").show();
                        $("#fFixedFare").removeAttr('required');
                        $("#fPricePerKM").attr('required', 'required');
                        $("#iMinFare").attr('required', 'required');
                        $("#fPricePerMin").attr('required', 'required');
                        $("#iBaseFare").attr('required', 'required');
                        $("#iPersonSize").attr('required', 'required');
                        $("#fPickUpPrice").attr('required', 'required');
                        $("#tPickStartTime").attr('required', 'required');
                        $("#tPickEndTime").attr('required', 'required');
                        $("#tNightStartTime").attr('required', 'required');
                        $("#tNightEndTime").attr('required', 'required');
                        
                    } else {
                        $("#Regular_div2").hide();
                        $("#fPricePerHour").attr('required', 'required');
                        $("#iBaseFare").removeAttr('required');
                        $("#fPricePerKM").removeAttr('required');
                        $("#fPricePerMin").removeAttr('required');
                        $("#iPersonSize").removeAttr('required');
                        $("#fPickUpPrice").removeAttr('required');
                        $("#tPickStartTime").removeAttr('required');
                        $("#tPickEndTime").removeAttr('required');
                        $("#tNightStartTime").removeAttr('required');
                        $("#tNightEndTime").removeAttr('required');
                    }
                } else {
                    //$("#Regular_div1").show();
                    $("#Regular_div2").show();
                    //$("#fFixedFare").hide();
                    $("#show-in-fixed").hide();
                    $("#hide-priceHour").hide();
                    $("#fFixedFare").removeAttr('required');
                    $("#iMaxQty").removeAttr('required');
                    $("#fPricePerHour").removeAttr('required');
                    $("#fPricePerKM").attr('required', 'required');
                    $("#iMinFare").attr('required', 'required');
                    $("#fPricePerMin").attr('required', 'required');
                    $("#iBaseFare").attr('required', 'required');
                    $("#iPersonSize").attr('required', 'required');
                    $("#fPickUpPrice").attr('required', 'required');
                    $("#tPickStartTime").attr('required', 'required');
                    $("#tPickEndTime").attr('required', 'required');
                    /*					$("#tNightStartTime").attr('required', 'required');
                     $("#tNightEndTime").attr('required', 'required');*/
                }
            }

        </script>
        <!--For Faretype End--> 
        <script>
            function changeCode(id)
            {
                var request = $.ajax({
                    type: "POST",
                    url: 'change_code.php',
                    data: 'id=' + id,
                    success: function (data)
                    {
                        document.getElementById("code").value = data;
                        //window.location = 'profile.php';
                    }
                });
            }
            function validate_email(id)
            {

                var request = $.ajax({
                    type: "POST",
                    url: 'validate_email.php',
                    data: 'id=' + id,
                    success: function (data)
                    {
                        if (data == 0)
                        {
                            $('#emailCheck').html('<i class="icon icon-remove alert-danger alert">Already Exist,Select Another</i>');
                            $('input[type="submit"]').attr('disabled', 'disabled');
                        } else if (data == 1)
                        {
                            var eml = /^[-.0-9a-zA-Z]+@[a-zA-z]+\.[a-zA-z]{2,3}$/;
                            result = eml.test(id);
                            if (result == true)
                            {
                                $('#emailCheck').html('<i class="icon icon-ok alert-success alert"> Valid</i>');
                                $('input[type="submit"]').removeAttr('disabled');
                            } else
                            {
                                $('#emailCheck').html('<i class="icon icon-remove alert-danger alert"> Enter Proper Email</i>');
                                $('input[type="submit"]').attr('disabled', 'disabled');
                            }
                        }
                    }
                });
            }
            function getpriceCheck(id)
            {
                /*var km_rs=document.getElementById('fPricePerKM').value;
                 var min_rs=document.getElementById('fPricePerMin').value;
                 var base_rs=document.getElementById('iBaseFare').value;
                 var com_rs=document.getElementById('fCommision').value;
                 if(km_rs != 0 && min_rs !=0 && base_rs != 0 && com_rs != 0)
                 {
                 }*/
                if (id > 0)
                {
                    $('#price').html('');
                    $('input[type="submit"]').removeAttr('disabled');
                } else
                {
                    $('#price').html('<i class="alert-danger alert"> You can not enter any price as Zero or Letter.</i>');
                    $('input[type="submit"]').attr('disabled', 'disabled');
                }
            }

            function getpriceCheck_digit(id)
            {
                var check = isNaN(id);
                if (check === false)
                {
                    $('#price').html('');
                    $('input[type="submit"]').removeAttr('disabled');
                } else {
                    $('#price').html('<i class="alert-danger alert"> You can not enter any price as Zero or Letter.</i>');
                    $('input[type="submit"]').attr('disabled', 'disabled');
                }
            }
            function onlydigit(id)
            {
                var digi = /^[1-9]{1}$/;
                result = digi.test(id);
                if (result == true)
                {
                    $('#digit').html('');
                    $('input[type="submit"]').removeAttr('disabled');
                } else
                {
                    $('#digit').html('<i class="alert-danger alert">Only Decimal Number less Than 10</i>');
                    $('input[type="submit"]').attr('disabled', 'disabled');
                }

            }


            $(function () {
                newDate = new Date('Y-M-D');
                $('#tNightStartTime').datetimepicker({
                    format: 'HH:mm:ss',
                    //minDate: moment().format('l'),
                    ignoreReadonly: true,
                    //sideBySide: true,
                });
                $('#tNightEndTime').datetimepicker({
                    format: 'HH:mm:ss',
                    //minDate: moment().format('l'),
                    ignoreReadonly: true,
                    useCurrent: false
                            //sideBySide: true,
                });
            });
            function showhidepickuptime() {
                if ($('input[name=ePickStatus]').is(':checked')) {
                    //alert('Checked');
                    $("#showpickuptime").show();
                } else {
                    //alert('Not checked');
                    $("#showpickuptime").hide();
                }
            }
            function showhidenighttime() {
                if ($('input[name=eNightStatus]').is(':checked')) {
                    $("#shownighttime").show();
                    $("#tNightStartTime").attr('required');
                    $("#tNightEndTime").attr('required');
                } else {
                    //alert('Not checked');
                    $("#shownighttime").hide();
                    $("#tNightStartTime").removeAttr('required');
                    $("#tNightEndTime").removeAttr('required');
                }
            }
            function showpoolpercentage() {
                if ($('input[name=ePoolStatus]').is(':checked')) {
                    $("#pool_div").show();
                    //$(".RentalAlias,#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv,#MotoBike,#Cycle,#Truck").hide();
                    $("#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv,#MotoBike,#Cycle,#Truck").hide();
                    $("#eIconType").val("Car")
                    //$("#fPoolPercentage").attr('required');
                } else {
                    //alert('Not checked');
                    $("#pool_div").hide();
                    //$(".RentalAlias,#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv,#MotoBike,#Cycle,#Truck").show();
                    $("#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv,#MotoBike,#Cycle,#Truck").show();
                    //$("#fPoolPercentage").removeAttr('required');
                }
            }

            function setCity(id, selected)
            {
                var request = $.ajax({
                    type: "POST",
                    url: 'change_stateCity.php',
                    data: {stateId: id, selected: selected},
                    success: function (dataHtml)
                    {
                        $("#iCityId").html(dataHtml);
                    }
                });
            }

            function setState(id, selected)
            {
                var request = $.ajax({
                    type: "POST",
                    url: 'change_stateCity.php',
                    data: {countryId: id, selected: selected},
                    success: function (dataHtml)
                    {
                        $("#iStateId").html(dataHtml);
                        if (selected == '')
                            setCity('', selected);
                    }
                });
                changeCode(id);
            }

            function changeCode(id) {
                $.ajax({
                    type: "POST",
                    url: 'change_code.php',
                    dataType: 'json',
                    data: {id: id, eUnit: 'yes'},
                    success: function (dataHTML2)
                    {
                        if (dataHTML2 != null)
                            $("#change_eUnit").text(dataHTML2.eUnit);
                    }
                });
            }

            function changeCode_distance(id) {
                $.ajax({
                    type: "POST",
                    url: 'ajax_get_unit.php',
                    data: {id: id},
                    success: function (dataHTML2)
                    {
                        if (dataHTML2 != null)
                            $("#change_eUnit").text(dataHTML2);
                    }
                });
            }

            setState('<?php echo $iCountryId; ?>', '<?php echo $iStateId; ?>');
            setCity('<?php echo $iStateId; ?>', '<?php echo $iCityId; ?>');
            showhidepickuptime();
            showhidenighttime();

            changeCode_distance('<?= $iLocationId ?>');
        </script>
        <script type="text/javascript" language="javascript">
            function isNumberKey(evt)
            {
                var charCode = (evt.which) ? evt.which : event.keyCode
                if (charCode > 47 && charCode < 58 || charCode == 46 || charCode == 127 || charCode == 8)
                    return true;
                return false;
            }
            function getAllLanguageCode(textBoxId) {
                var def_lang = '<?= $default_lang ?>';
                var def_lang_name = '<?= $def_lang_name ?>';
                var getEnglishText = $('#' + textBoxId + "_" + def_lang).val();
                // alert(def_lang_name);
                var error = false;
                var msg = '';

                if (getEnglishText == '') {
                    msg += '<div class="alert alert-danger fade in"><a href="#" class="close" data-dismiss="alert"><icon class="fa fa-close"></icon></a><strong>Please Enter ' + def_lang_name + ' Value</strong></div> <br>';
                    error = true;
                }

                if (error == true) {
                    $('#errorMessage').html(msg);
                    return false;
                } else {
                    $('#imageIcon').show();
                    $.ajax({
                        url: "ajax_get_all_language_translate.php",
                        type: "post",
                        data: {'englishText': getEnglishText},
                        dataType: 'json',
                        success: function (response) {
                            // $("#vVehicleType_EN").val(getEnglishText);
                            $.each(response, function (name, Value) {
                                var key = name.split('_');
                                $('#' + textBoxId + "_" + key[1]).val(Value);
                            });
                            $('#imageIcon').hide();
                        }
                    });
                }
            }
            $(document).ready(function () {
                var referrer;
                if ($("#previousLink").val() == "") {
                    referrer = document.referrer;
                    //alert(referrer);
                } else {
                    referrer = $("#previousLink").val();
                }
                if (referrer == "") {
                    referrer = "vehicle_type.php";
                } else {
                    $("#backlink").val(referrer);
                }
                $(".back_link").attr('href', referrer);
            });
        </script>
        <script type="text/javascript">
            var input = $('.clockpicker-with-callbacks').clockpicker({
                donetext: 'Done',
                init: function () {
                    console.log("colorpicker initiated");
                },
                beforeShow: function () {
                    console.log("before show");
                },
                afterShow: function () {
                    console.log("after show");
                },
                beforeHide: function () {
                    console.log("before hide");
                },
                afterHide: function () {
                    console.log("after hide");
                },
                beforeHourSelect: function () {
                    console.log("before hour selected");
                },
                afterHourSelect: function () {
                    console.log("after hour selected");
                },
                beforeDone: function () {
                    console.log("before done");
                },
                afterDone: function () {
                    console.log("after done");
                }
            });

            document.getElementById('RNightMonday').onclick = function () {
                var tMonNightStartTime = document.getElementById('tMonNightStartTime');
                var tMonNightEndTime = document.getElementById('tMonNightEndTime');
                var fMonNightPrice = document.getElementById('fMonNightPrice');

                tMonNightStartTime.value = tMonNightEndTime.value = fMonNightPrice.value = '';
            };

            document.getElementById('RNightTuesday').onclick = function () {
                var tTueNightStartTime = document.getElementById('tTueNightStartTime');
                var tTueNightEndTime = document.getElementById('tTueNightEndTime');
                var fTueNightPrice = document.getElementById('fTueNightPrice');

                tTueNightStartTime.value = tTueNightEndTime.value = fTueNightPrice.value = '';
            };

            document.getElementById('RNightWednesday').onclick = function () {
                var tWedNightStartTime = document.getElementById('tWedNightStartTime');
                var tWedNightEndTime = document.getElementById('tWedNightEndTime');
                var fWedNightPrice = document.getElementById('fWedNightPrice');

                tWedNightStartTime.value = tWedNightEndTime.value = fWedNightPrice.value = '';
            };

            document.getElementById('RNightThursday').onclick = function () {
                var tThuNightStartTime = document.getElementById('tThuNightStartTime');
                var tThuNightEndTime = document.getElementById('tThuNightEndTime');
                var fThuNightPrice = document.getElementById('fThuNightPrice');

                tThuNightStartTime.value = tThuNightEndTime.value = fThuNightPrice.value = '';
            };


            document.getElementById('RNightFriday').onclick = function () {
                var tFriNightStartTime = document.getElementById('tFriNightStartTime');
                var tFriNightEndTime = document.getElementById('tFriNightEndTime');
                var fFriNightPrice = document.getElementById('fFriNightPrice');

                tFriNightStartTime.value = tFriNightEndTime.value = fFriNightPrice.value = '';
            };

            document.getElementById('RNightSaturday').onclick = function () {
                var tSatNightStartTime = document.getElementById('tSatNightStartTime');
                var tSatNightEndTime = document.getElementById('tSatNightEndTime');
                var fSatNightPrice = document.getElementById('fSatNightPrice');

                tSatNightStartTime.value = tSatNightEndTime.value = fSatNightPrice.value = '';
            };

            document.getElementById('RNightSunday').onclick = function () {
                var tSunNightStartTime = document.getElementById('tSunNightStartTime');
                var tSunNightEndTime = document.getElementById('tSunNightEndTime');
                var fSunNightPrice = document.getElementById('fSunNightPrice');

                tSunNightStartTime.value = tSunNightEndTime.value = fSunNightPrice.value = '';
            };
			
			
			//added by SP 27-06-2019 start		
			var timeLimit = $("#iCancellationTimeLimit").val();
			if (timeLimit.trim() == '') {
				$("#fCancellationFare").val('');
			}
			
			var timeLimit = $("#iWaitingFeeTimeLimit").val();
			if (timeLimit.trim() == '') {
				$("#fWaitingFees").val('');
			}
			
            function checkcancellationfare(idval) {
                var timeLimit = $("#"+idval).val();
                if (timeLimit.trim() == '') {
                    document.getElementById(idval).focus();
                    $("#"+idval).val('');
                    return false;
                }
            }
			function checkblanktimelimit(idval,idcanval) {
				var timeLimit = $("#"+idval).val();
				if (timeLimit.trim() == '') {
					$("#"+idcanval).val('');
				}
            }
			//added by SP 27-06-2019 end
        </script>
    </body>
    <!-- END BODY-->
</html>
