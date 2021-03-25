<?php
include_once('common.php');
$generalobj->check_member_login();

require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();

$script = "Vehicle";
$abc = 'driver,company';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$generalobj->setRole($abc, $url);
$tbl_name = 'driver_vehicle';

$success = isset($_GET['success']) ? $_GET['success'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';
$driverid = isset($_GET['driverid']) ? $_GET['driverid'] : '';
$error = isset($_GET['success']) && $_GET['success'] == 0 ? 1 : '';
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : '';

/*$pageDesc = $obj->MySQLSelect("select * from pages where iPageId='46'");
print_R($pageDesc);
  //$pageDesc = get_value('pages', 'tPageDesc_EN', 'iPageId', '46', '', 'true');
    $returnArr['page_desc'] = $pageDesc;
    echo "aaa";exit;*/

if (isset($_POST['Submit'])) {
    $var_msg = $langage_lbl['LBL_EDIT_VEHICLE_DISABLED'];    
    if(strtoupper($ENABLE_EDIT_DRIVER_VEHICLE) == "NO" && $action == 'Edit'){
        header("location:vehicle?success=0&var_msg=" . $var_msg);
        exit;
    }

    $iVehicleId = isset($_REQUEST['iVehicleId1']) ? $_REQUEST['iVehicleId1'] : '';
    $doc_name = $_POST['doc_name'];
    $doc_path = $_POST['doc_path2'];
    $expDate = $_POST['dLicenceExp'];

    $image = $_FILES['file']['name'];
    $image_object = $_FILES['file']['tmp_name'];
    $masterid = $_POST['doc_id'];
    if ($expDate != "") {
        $sql = "select ex_date from document_list where doc_userid='" . $iVehicleId . "' and doc_masterid='" . $masterid . "'";
        $query = $obj->MySQLSelect($sql);
        $fetch = $query[0];

        if ($fetch['ex_date'] != $expDate || $image_name == "") {
            $sql = "UPDATE `document_list` SET  ex_date='" . $expDate . "' WHERE doc_userid='" . $iVehicleId . "' and doc_masterid='" . $masterid . "'";
            $query = $obj->sql_query($sql);
        } else {
            if ($image_name == "") {
                $var_msg = $langage_lbl['LBL_DOC_UPLOAD_ERROR_'];
                header("location:vehicle?success=0&id=" . $iVehicleId . "&var_msg=" . $var_msg);
                exit();
            }
        }
    }

    if ($image != '') {
        $Photo_Gallery_folder = $doc_path . '/' . $iVehicleId . '/';
        if (!is_dir($Photo_Gallery_folder)) {
            mkdir($Photo_Gallery_folder, 0777);
        }
        //$img = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, $tconfig["tsite_upload_documnet_size1"], $tconfig["tsite_upload_documnet_size2"], '', '', '', '', 'Y', '', $Photo_Gallery_folder);
        $vFile = $generalobj->fileupload($Photo_Gallery_folder, $image_object, $image, $prefix = '', $vaildExt = "pdf,doc,docx,jpg,jpeg,gif,png");
        $vImage = $vFile[0];
        if ($vFile[2] == "1") {
            $var_msg = $vFile[1];
            header("location:vehicle?success=0&var_msg=" . $var_msg);
        } else {
            $var_msg = $langage_lbl['LBL_UPLOAD_MSG'];
        }
        $tbl = 'document_list';
        $sql = "select doc_id from  " . $tbl . "  where doc_userid='" . $iVehicleId . "' and doc_usertype='car'  and doc_masterid='" . $masterid . "'";
        $db_data = $obj->MySQLSelect($sql);

        if (count($db_data) > 0) {
            $query = "UPDATE `" . $tbl . "` SET `doc_file`='" . $vImage . "' WHERE doc_userid='" . $iVehicleId . "' and doc_usertype='car'  and doc_masterid='" . $masterid . "'";
            $obj->sql_query($query);
        } else {
            $query = " INSERT INTO `" . $tbl . "` ( `doc_masterid`, `doc_usertype`, `doc_userid`,`ex_date`,`doc_file`, `status`, `edate`) " . "VALUES " . "( '" . $masterid . "', 'car', '" . $iVehicleId . "','" . $expDate . "','" . $vImage . "', 'Inactive', CURRENT_TIMESTAMP)";
            $obj->sql_query($query);
        }

        ###### Email #######

        if ($_SESSION['sess_user'] == 'company') {
            $maildata['NAME'] = $_SESSION['sess_vCompany'] . " (" . $langage_lbl['LBL_DOCUMNET_UPLOAD_BY_COMPANY'] . ")";
        } else {
            $maildata['NAME'] = $_SESSION['sess_vName'] . " " . $_SESSION['sess_vLastName'] . " (" . $langage_lbl['LBL_DOCUMNET_UPLOAD_BY_DRIVER'] . ")";
        }

        $maildata['EMAIL'] = $_SESSION['sess_vEmail'];
        $docname_SQL = "SELECT doc_name_" . $default_lang . " as docname FROM document_master WHERE doc_masterid = '" . $masterid . "'";
        $docname_data = $obj->MySQLSelect($docname_SQL);
        $maildata['DOCUMENTTYPE'] = $docname_data[0]['docname'];

        if ($_SESSION['sess_user'] == 'company') {
            $maildata['DOCUMENTFOR'] = $langage_lbl['LBL_DOCUMNET_UPLOAD_BY_COMPANY'];
        } else {
            $maildata['DOCUMENTFOR'] = $langage_lbl['LBL_DOCUMNET_UPLOAD_BY_DRIVER'];
        }
        $generalobj->send_email_user("DOCUMENT_UPLOAD_WEB", $maildata);
        if ($_SESSION['sess_user'] == 'driver' || $storeDriver > 0) {
            $sqlquery = "SELECT vEmail,vCompany FROM company WHERE iCompanyId = '" . $_SESSION['sess_iCompanyId'] . "'";
            $Companydata = $obj->MySQLSelect($sqlquery);
            $maildata['COMPANYEMAIL'] = $Companydata[0]['vEmail'];
            $maildata['COMPANYNAME'] = $Companydata[0]['vCompany'];
            $generalobj->send_email_user("DOCUMENT_UPLOAD_WEB_COMPANY", $maildata);
        }
        #######Email ##########
    }
    
    header("location:vehicle?success=1&var_msg=" . $var_msg);
    exit;
}

$dri_ssql = "";
if (SITE_TYPE == 'Demo') {
    $dri_ssql = " And rd.tRegistrationDate > '" . WEEK_DATE . "'";
}

$RideDeliveryIconArrStatus = $generalobj->CheckRideDeliveryFeatureDisableWeb();
$eShowRideVehicles = $RideDeliveryIconArrStatus['eShowRideVehicles'];
$eShowDeliveryVehicles = $RideDeliveryIconArrStatus['eShowDeliveryVehicles'];

/* $esql = '';
  if($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery' ){
  if($eShowRideVehicles == 'Yes' && $eShowDeliveryVehicles == 'Yes'){
  $esql .= " and dv.eType != 'UberX'";
  }
  if($eShowRideVehicles == 'Yes' && $eShowDeliveryVehicles == 'No'){
  $esql .= " and dv.eType = 'Ride'";
  }
  if($eShowDeliveryVehicles == 'Yes' && $eShowRideVehicles == 'No'){
  $esql .= " and dv.eType = 'Delivery'";
  }
  if($eShowRideVehicles == 'No' && $eShowDeliveryVehicles == 'No'){
  $esql .= " and dv.eType != 'UberX'";
  }
  } else {
  if($APP_TYPE == 'Delivery'){
  $appType = 'Delivery';
  } else {
  $appType = $APP_TYPE;
  }
  $esql .= " and dv.eType = '".$appType."'";
  } */
$storeDriver = 0;
$vehicleDriverId = $_SESSION['sess_iUserId'];
if(isset($_REQUEST['driverid']) && $_REQUEST['driverid'] > 0){
    $storeDriver = 1;
    $vehicleDriverId =$_REQUEST['driverid']; 
}
$driverFname = "";
if ($_SESSION['sess_user'] == 'driver' || $storeDriver > 0) {
    $sql = "select iCompanyId,vName from `register_driver` where iDriverId = '" . $vehicleDriverId . "'";
    $db_usr = $obj->MySQLSelect($sql);
    if(count($db_usr) > 0){
      $driverFname =   $db_usr[0]['vName'];
    }
    $iCompanyId = $db_usr[0]['iCompanyId'];

    // $sql = "SELECT * FROM " . $tbl_name . " where iCompanyId = '" . $iCompanyId . "' and iDriverId = '" . $vehicleDriverId . "' and eStatus != 'Deleted'";
    // $db_driver_vehicle = $obj->MySQLSelect($sql);

    if ($APP_TYPE == 'UberX') {
        $sql = "SELECT * FROM " . $tbl_name . " dv  where iCompanyId = '" . $iCompanyId . "'  and dv.eType == 'UberX' and dv.iDriverId = '" . $vehicleDriverId . "' and dv.eStatus != 'Deleted' ORDER BY dv.iDriverVehicleId DESC";
        $db_driver_vehicle = $obj->MySQLSelect($sql);
    } else {
        $sql = "SELECT dv.*,rd.vCountry,rd.vName,CONCAT(rd.vName,' ',rd.vLastName) AS driverName,m.vTitle, mk.vMake,dv.vLicencePlate,dv.eStatus,dv.eType  FROM " . $tbl_name . " dv  JOIN model m ON dv.iModelId=m.iModelId JOIN make mk ON  dv.iMakeId=mk.iMakeId JOIN register_driver as rd ON rd.iDriverId = dv.iDriverId where dv.iCompanyId = '" . $iCompanyId . "' and dv.iDriverId = '" . $vehicleDriverId . "' AND dv.eType != 'UberX' and dv.eStatus != 'Deleted' $esql  $dri_ssql ORDER BY dv.iDriverVehicleId DESC";
        $db_driver_vehicle = $obj->MySQLSelect($sql);
    }
    //echo '<pre>';print_r($db_driver_vehicle);die;
    //echo $sql;die;
}

if ($_SESSION['sess_user'] == 'company' && $storeDriver == 0) {
    $iCompanyId =$vehicleDriverId= $_SESSION['sess_iUserId'];
    // $sql = "SELECT * FROM " . $tbl_name . " where iCompanyId = '" . $iCompanyId . "' and eStatus != 'Deleted'";
    // $db_driver_vehicle = $obj->MySQLSelect($sql);
    if ($APP_TYPE == 'UberX') {
        $sql = "SELECT * FROM " . $tbl_name . " dv  where iCompanyId = '" . $iCompanyId . "' and dv.eType == 'UberX' and dv.eStatus != 'Deleted' ORDER BY dv.iDriverVehicleId DESC";
        $db_driver_vehicle = $obj->MySQLSelect($sql);
    } else {
        $sql = "SELECT dv.*,rd.vCountry,rd.vName,CONCAT(rd.vName,' ',rd.vLastName) AS driverName,m.vTitle, mk.vMake,dv.vLicencePlate,dv.eStatus,dv.eType  FROM " . $tbl_name . " dv  JOIN model m ON dv.iModelId=m.iModelId JOIN make mk ON  dv.iMakeId=mk.iMakeId JOIN register_driver as rd ON rd.iDriverId = dv.iDriverId where dv.iCompanyId = '" . $iCompanyId . "' AND dv.eType != 'UberX' and dv.eStatus != 'Deleted' $esql $dri_ssql ORDER BY dv.iDriverVehicleId DESC";
        $db_driver_vehicle = $obj->MySQLSelect($sql);
    }
}

// $sql = "select iDriverVehicleId from driver_vehicle where iDriverId = '".$vehicleDriverId."'";
// $iDriverVehicleId = $obj->MySQLSelect($sql);
// $iDriverVehicleId=$iDriverVehicleId[0]['iDriverVehicleId'];

if (isset($iDriverVehicleId)) {
    $sql = "select * from register_driver where iDriverVehicleId = '" . $iDriverVehicleId . "'";
    $db_data = $obj->MySQLSelect($sql);
}

if ($action == 'delete') {
    // to check user is valid or not to delete vehicle
    // if(SITE_TYPE == 'Demo')
    // {
    // header("Location:vehicle?success=2");
    // exit;
    // }
    $valid_user = false;
    foreach ($db_driver_vehicle as $val) {
        if ($val['iDriverVehicleId'] == $id)
            $valid_user = true;
    }
    if (!$valid_user) {
        $var_msg = $langage_lbl['LBL_VEHICLE_DELETE_ERROR_MSG'];
        header("Location:vehicle?success=0&var_msg=" . $var_msg);
    } else {

        $sql = "select count(*) as trip_cnt from trips where iDriverVehicleId = '" . $id . "' AND  iActive IN ('Active', 'On Going Trip')";
        $db_usr = $obj->MySQLSelect($sql);

        //$sql1 = "SELECT count(iDriverId) as drivers FROM register_driver WHERE iDriverId = '" . $driverid . "' AND iDriverVehicleId = '" . $id . "'";
        //$db_driver_data = $obj->MySQLSelect($sql1);
        //$db_driver_data = $obj->MySQLSelect("SELECT eStatus FROM driver_vehicle WHERE iDriverVehicleId='" . $id . "'"); //added by SP when vehicle is active then it can not be deleted on 28-09-2019
        $getDriverData = $obj->MySQLSelect("SELECT iDriverVehicleId FROM register_driver WHERE iDriverId='" . $driverid . "'");
        $iDriverVehicleId = $getDriverData[0]['iDriverVehicleId'];
        if (count($db_usr) > 0 && $db_usr[0]['trip_cnt'] > 0) {
            $varmsg = $langage_lbl['LBL_TRIP_VEHICLE_DELETE_ERROR_MSG'];
            header("Location:vehicle?success=0&var_msg=" . $varmsg);
            exit;
        //} elseif (count($db_driver_data) > 0 && $db_driver_data[0]['drivers'] > 0) {
        } else if($iDriverVehicleId==$id) { //added by SP when vehicle is active then it can not be deleted on 28-09-2019
            $varmsg = $langage_lbl['LBL_ACTIVE_VEHICLE_NOT_DELETE'];
            header("Location:vehicle?success=0&var_msg=" . $varmsg);
            exit;
        } else {
            /* $sql= "SELECT * FROM register_driver WHERE iDriverId = '".$driverid."' AND iDriverVehicleId = '".$id."'";
              $avail_driver = $obj->MySQLSelect($sql);

              if(!empty($avail_driver)) {
              $query = "UPDATE register_driver SET vAvailability = 'Not Avilable', `iDriverVehicleId`= '0' WHERE iDriverId = '".$driverid."' AND iDriverVehicleId = '" . $id . "'";
              $obj->sql_query($query);
              } */

            $query = "UPDATE `driver_vehicle` SET eStatus = 'Deleted' WHERE iDriverVehicleId = '" . $id . "'";
            $obj->sql_query($query);

            $var_msg = $langage_lbl['LBL_DELETE_VEHICLE'];
            header("Location:vehicle?success=1&var_msg=" . $var_msg);
            exit;
        }
    }
}

for ($i = 0; $i < count($db_driver_vehicle); $i++) {
    $sql = "select vMake from make where iMakeId = '" . $db_driver_vehicle[$i]['iMakeId'] . "' where vMake !=''";
    $name1 = $obj->MySQLSelect($sql);
    $sql = "select vTitle from model where iModelId = '" . $db_driver_vehicle[$i]['iModelId'] . "' WHERE vTitle !=''";
    $name2 = $obj->MySQLSelect($sql);
    $db_msk[$i] = $name1[0]['vMake'] . ' ' . $name2[0]['vTitle'];
}
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= $SITE_NAME ?> | <?php if($storeDriver > 0){ echo $langage_lbl['LBL_VEHICLE_TITLE']; }else{ echo $langage_lbl['LBL_VEHICLES']; }?></title>
        <!-- Default Top Script and css -->
        <link rel="stylesheet" href="assets/css/bootstrap-fileupload.min.css" />
        <?php include_once("top/top_script.php"); ?>
        <? //if ($APP_TYPE == 'Ride-Delivery-UberX') { ?>
            <!-- <link rel="stylesheet" type="text/css" href="assets/css/vehicles_cubejek.css"> -->
        <? //} else { ?>
            <!--<link rel="stylesheet" type="text/css" href="assets/css/vehicles.css">-->
        <? //} ?>
        <style>
            .fileupload-preview  { line-height:150px;}
            .accordian-data .bootstrap-datetimepicker-widget table td,
            .accordian-data .bootstrap-datetimepicker-widget table th {
                padding:6px !important;
                background-color:#dfdfdf;
            }
        </style>
        <!-- End: Default Top Script and css-->
    </head>
    <body>
        <!-- home page -->
        <div id="main-uber-page">
            <!-- Top Menu -->
            <!-- Left Menu -->
            <?php include_once("top/left_menu.php"); ?>
            <!-- End: Left Menu-->
            <?php include_once("top/header_topbar.php"); ?>
            <!-- End: Top Menu-->
            <!-- contact page-->

<section class="profile-section my-trips">
    <div class="profile-section-inner">
        <div class="profile-caption">
            <div class="page-heading">
                <h1><?php 
                if($storeDriver > 0){
                    $headName = $langage_lbl['LBL_LEFT_MENU_VEHICLES'];
                    if($driverFname != ""){
                        $headName = ucwords($driverFname)."'s ".$headName;
                    }
                    echo $headName;
                }else{
                    echo $langage_lbl['LBL_HEADER_TOPBAR_VEHICLES'];
                }
             ?></h1>
            </div>
            <div class="button-block end">
                    <?php if ($APP_TYPE != 'UberX') { ?>
                        <?php if($storeDriver > 0){ ?>
                            <a class="gen-btn" href="vehicle-add?driverid=<?= $vehicleDriverId; ?>&vehicle=store"><?= $langage_lbl['LBL_ADD_VEHICLE']; ?></a>
                       <?php }else { ?>
                            <a class="gen-btn" href="vehicle-add"><?= $langage_lbl['LBL_ADD_VEHICLE']; ?></a>
                    <?php } } ?>
            </div>
        </div>
    </div>
</section>
<section class="profile-earning">
    <div class="profile-earning-inner">
    <div class="table-holder">
                <div class="page-contant-inner">

                    <?php
                    if (SITE_TYPE == 'Demo') {
                        ?>
                        <div class="demo-warning">
                            <p><?= $langage_lbl['LBL_SINCE_THIS']; ?></p>
                        </div>
                        <?php
                    }
                    ?>

                    <!-- driver vehicles page -->
                    <div class="VehicleList">
                        <?php
                        if ($error) {
                            ?>
        
                                <div class="alert alert-danger">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><?= $var_msg ?>
                                </div>
                            <?php
                        }
                        if ($success == 1) {
                            ?>
                            <div class="row">
                                <div class="alert alert-success paddiing-10">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?= $var_msg ?>
                                </div>
                            </div>
                            <?php
                        } else if ($success == 2) {
                            ?>
                            <div class="row">
                                <div class="alert alert-danger paddiing-10">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?= $langage_lbl['LBL_VEHICLE_EDIT_DELETE_RECORD']; ?>
                                </div>
                            </div>
                            <?
                        }
                        ?>
                        <div class="vehicles-page">
                            <div class="accordion">
                            <table id="my-trips-data" class="ui celled table custom-table dataTable no-footer" style="width:100%">
                            <thead>
                                <tr>
                                    <th data-text="init"><?= $langage_lbl['LBL_Vehicle'] ?></th>
                                    <th><?= $langage_lbl['LBL_Status'] ?></th>
                                    <th><?= $langage_lbl['LBL_EDIT'] ?></th>
                                    <th><?= $langage_lbl['LBL_DELETE'] ?></th>
                                    <th><?= $langage_lbl['LBL_OPEN_CLOSE'] ?></th>
                                </tr>
                            </thead>
                            <tbody>
                               
                                        <?php
                                        if (count($db_driver_vehicle) > 0) {
                                            for ($i = 0; $i < count($db_driver_vehicle); $i++) {
                                                ?>
                                                 <tr>
                                                <input type="hidden" name="iVehicleId" value = "<?php echo $db_driver_vehicle[$i]['iDriverVehicleId']; ?>"/>
                                                <div class="accordion-section">
                                                    <div class="accordionheading">
                                                        <?php
                                                        if ($APP_TYPE == 'UberX') {
                                                            $displayname = $db_driver_vehicle[$i]['vLicencePlate'];
                                                        } else {
                                                            $displayname = $db_driver_vehicle[$i]['vMake'] . "   " . $db_driver_vehicle[$i]['vTitle'] . "  " . $db_driver_vehicle[$i]['vLicencePlate'] . "  ";
                                                        }
                                                        ?> 
                                                        <td data-text="init"><span class="vehicle_name"><?php echo $displayname; ?></span></td>
                                                        <td>
                                                                <!-- <?php
                                                                $class_name = ($db_driver_vehicle[$i]['eStatus'] == "Active") ? 'badge success-vehicle-active' : 'badge success-vehicle-inactive';
                                                                ?>

                                                                

                                                                <span class="<?php echo $class_name; ?>">
                                                                    <i class="<?= ($db_driver_vehicle[$i]['eStatus'] == "Active") ? 'icon-eye-open' : 'icon-eye-close' ?>"></i> <?= ucfirst($db_driver_vehicle[$i]['eStatus']); ?>
                                                                </span>   -->
                                                                <?php
                                                                    if($db_driver_vehicle[$i]['eStatus'] == "Active"){
                                                                        echo '<i class="icon-verified"></i>';
                                                                    }else{
                                                                        echo '<i class="icon-cancel"></i>';
                                                                    }
                                                                ?>
                                                                                                                          
                                                        </td>
                                                        <td>
                                                            <?php if($storeDriver > 0){ ?>
                                                            <a href ="cx-vehicle_add_form.php?id=<?= base64_encode(base64_encode($db_driver_vehicle[$i]['iDriverVehicleId'])) ?>&driverid=<?= $vehicleDriverId; ?>&vehicle=store" class="action-click icon-edit"></a>
                                                            
                       <?php }else { ?>
                            <a href ="cx-vehicle_add_form.php?id=<?= base64_encode(base64_encode($db_driver_vehicle[$i]['iDriverVehicleId'])) ?>" class="action-click icon-edit"></a>
                       <?php } ?>
                                                            <!-- <a href ="vehicle_add_form.php?id=<?= base64_encode(base64_encode($db_driver_vehicle[$i]['iDriverVehicleId'])) ?>" class="action-click icon-edit"><?= $langage_lbl['LBL_VEHICLE_EDIT']; ?></a> -->
                                                            

                                                        </td>
                                                        <td>

                                                            <?php if ($APP_TYPE != 'UberX') { ?> 
                                                                    
                                                                    <!-- <a class="active active2" onClick="confirm_delete('<?= $db_driver_vehicle[$i]['iDriverVehicleId'] ?>', '<?= $db_driver_vehicle[$i]['iDriverId'] ?>');" href="javascript:void(0);"><?= $langage_lbl['LBL_DELETE']; ?></a> -->
                                                                    
                                                                    <a class="action-click icon-delete" onClick="confirm_delete('<?= $db_driver_vehicle[$i]['iDriverVehicleId'] ?>', '<?= $db_driver_vehicle[$i]['iDriverId'] ?>');" href="javascript:void(0);"></a>                                                                    

                                                                    
                                                            <?php } ?>                                                            
                                                        </td>

                                                        <td class="details-control">
                                                     

                                                            <?php
                                                            /* if($APP_TYPE == 'Ride-Delivery') {

                                                              $eTypeQuery = " AND (eType='".$db_driver_vehicle[$i]['eType']."')";
                                                              } else if($APP_TYPE == 'Ride-Delivery-UberX'){
                                                              $eTypeQuery = " AND (eType='".$db_driver_vehicle[$i]['eType']."')";
                                                              } else {
                                                              $eTypeQuery = " AND eType='".$APP_TYPE."'";
                                                              } */

                                                            /*  $sql1= "SELECT dm.doc_masterid masterid, dm.doc_usertype , dm.doc_name ,dm.ex_status,dm.status, dl.doc_masterid masterid_list ,dl.ex_date,dl.doc_file , dl.status, dm.eType FROM document_master dm left join (SELECT * FROM `document_list` where doc_userid='" .$db_driver_vehicle[$i]['iDriverVehicleId']."') dl on dl.doc_masterid=dm.doc_masterid where dm.doc_usertype='car' and dm.status='Active' and (dm.country ='".$db_driver_vehicle[$i]['vCountry']."' OR dm.country ='All') $eTypeQuery"; */


                                                            $sql1 = "SELECT dm.doc_masterid masterid, dm.doc_usertype , dm.doc_name ,dm.ex_status,dm.status, dl.doc_masterid masterid_list ,dl.ex_date,dl.doc_file , dl.status, dm.eType FROM document_master dm left join (SELECT * FROM `document_list` where doc_userid='" . $db_driver_vehicle[$i]['iDriverVehicleId'] . "') dl on dl.doc_masterid=dm.doc_masterid where dm.doc_usertype='car' and dm.status='Active' and (dm.country ='" . $db_driver_vehicle[$i]['vCountry'] . "' OR dm.country ='All')";

                                                            $db_userdoc = $obj->MySQLSelect($sql1);

                                                            if ($APP_TYPE != 'UberX' && $db_userdoc > 0) {
                                                                ?> 
                                                                <i class="icon-arrow-down-avigate expanding"></i>
                                                                <!-- <a class="accordion-section-title" href="#accordion-<?php echo $i; ?>">&nbsp;</a> -->

                                                            <?php } ?>

                                              
                                                        

                                                        <?php if ($_SESSION['sess_user'] == 'company' && $storeDriver == 0) { ?>

                                                            <div style=" clear: both;margin: 8px 0 0 10px;font-size: 10px;"><?php echo $langage_lbl['LBL_DRIVER_NAME_ADMIN']; ?> :<?php
                                                                echo $generalobj->clearName($db_driver_vehicle[$i]['driverName']);

                                                                /*  if($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery'){
                                                                  ?>
                                                                  <strong style="font-size: 15px;">(<?= $db_driver_vehicle[$i]['eType']?>)</strong>

                                                                  <?php } */
                                                                ?>

                                                            </div>
                                                            <?php
                                                        }

                                                        /* else { */
                                                        ?>
                                                        <!--  <div style=" clear: both;margin: 8px 0 0 10px;font-size: 10px;">
                                                        <? if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery') { ?>
                                                                           <strong style="font-size: 15px;"><?= $db_driver_vehicle[$i]['eType'] ?></strong>
                                                        <? }
                                                        ?>
                                                         </div> -->
                                                        <? //}  ?>

                                                    </div>
 
                                                </div>
                                                 </td>
                                                 </tr>


                                                 <tr class="accordian-data">
                                                    <td colspan="5">
                                                   <div id="accordion-<?php echo $i; ?>" class="">
                                                        <div class="driver-vehicles-page-new">
                                                            <!-- <h2><?= $langage_lbl['LBL_DOCUMENTS']; ?></h2>      -->                                      

                                                            <ul>
                                                                <?php
                                                                for ($s = 0; $s < count($db_userdoc); $s++) {
                                                                    if ($db_userdoc[$s]['eType'] == 'UberX') {
                                                                        $etypeName = 'Service';
                                                                    } else {
                                                                        $etypeName = $db_userdoc[$s]['eType'];
                                                                    }
                                                                    ?>

                                                                    <li>
                                                 
                                                                        <form id="<?=  $db_driver_vehicle[$i]['iDriverVehicleId'].$s ?>" class="upload_docform upload-block" data-provides="fileupload" method="post" action="" enctype="multipart/form-data">
                                                                            <input type="hidden" name="iVehicleId1" value = "<?php echo $db_driver_vehicle[$i]['iDriverVehicleId']; ?>"/>
                                                                            <input type="hidden" name="doc_name" value="<?php echo $db_userdoc[$s]['doc_name']; ?>">
                                                                            <input type="hidden" name="doc_id" value="<?php echo $db_userdoc[$s]['masterid']; ?>">
                                                                            <input type="hidden" name="doc_path2" value="<?php echo $tconfig["tsite_upload_vehicle_doc"]; ?>">  
                                                                            <strong> 
                                                                                <?php echo $db_userdoc[$s]['doc_name']; ?>
                                                                                <?php if ($APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') { ?>
                                                                                               <!--  <div style="font-size: 10px;font-weight: normal;color:#362f2d">(For <?= $etypeName; ?>)</div> -->

                                                                                    <div style="font-size: 10px;font-weight: normal;color:#362f2d"></div>

                                                                                <?php } ?>   
                                                                            </strong>

                                                                            <div class="fileupload fileupload-new upload-block-inner" >
                                                                                <div class="doc-image-block <?php if($file_ext == 'is_image'){ echo 'has-data';}?>">
                                                                                <i href="#" class="fileupload-exists icon-close" data-dismiss="fileupload"></i>
                                                                                <div class="fileupload-preview thumbnail">

                                                                                    <?php if ($db_userdoc[$s]['doc_file'] != '') { ?>
                                                                                        <?php
                                                                                        $file_ext = $generalobj->file_ext($db_userdoc[$s]['doc_file']);
                                                                                        if ($file_ext == 'is_image') {
                                                                                            ?>
                                                                                            <a href="<?= $tconfig["tsite_upload_vehicle_doc_panel"] . '/' . $db_driver_vehicle[$i]['iDriverVehicleId'] . '/' . $db_userdoc[$s]['doc_file']; ?>" target="_blank"><img src = "<?= $tconfig["tsite_upload_vehicle_doc_panel"] . '/' . $db_driver_vehicle[$i]['iDriverVehicleId'] . '/' . $db_userdoc[$s]['doc_file']; ?>" alt ="<?php echo $db_userdoc[$s]['doc_name']; ?>" style="cursor:pointer;"></a>
                                                                                        <?php } else { ?>
                                                                                            <a href="<?= $tconfig["tsite_upload_vehicle_doc_panel"] . '/' . $db_driver_vehicle[$i]['iDriverVehicleId'] . '/' . $db_userdoc[$s]['doc_file']; ?>" target="_blank"><?php echo $db_userdoc[$s]['doc_name']; ?></a>
                                                                                        <?php } ?>
                                                                                        <?php
                                                                                    } else {
                                                                                        echo $db_userdoc[$s]['doc_name'] . ' not found';
                                                                                    }
                                                                                    ?>
                                                                                    </b> 
                                                                                </div>
                                                                                </div>


                                                                                <?php if ($db_userdoc[$s]['ex_status'] == 'yes') { ?>
                                                                                    <div class="col-lg-13 exp-date">
                                                                                        <div class="input-group input-append date filter-form">
                                                                                            <div class="filters-column">
                                                                                                <label><?= $langage_lbl['LBL_MYTRIP_FROM_DATE']; ?></label>
                                                                                                <input class="form-control readonlyNew dp123" type="text" name="dLicenceExp"  value="<?php echo ($db_userdoc[$s]['ex_date'] != "") ? $db_userdoc[$s]['ex_date'] : ''; ?>"  required/>
                                                                                                <i class="icon-cal" id="from-date"></i>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                <?php } ?>
                                                                                <div class="fileerror"></div>
                                                                               
                                                                            </div>
                                                                            <div class="button-block">
                                                                                <div class="upload-doc-button">
                                                                                <input type="file"  name="file" <? if ($db_userdoc[$s]['doc_file'] == "") { ?>required<? } ?> class="ins" accept="image/*,.doc,.docx,.pdf" onChange="validate_fileextension(<?php echo $s; ?>, this.value)"/>
                                                                                    <button class="gen-btn"><?= $langage_lbl['LBL_UPLOAD']; ?> </button>
                                                                                </div>
                                                                                <input type="submit" name="Submit" class="save-document gen-btn" value="<?= $langage_lbl['LBL_Save_Documents']; ?>">
                                                                            </div>
                                                                        </form>
                                                                        
                                                                    </li>
                                                                <?php } ?>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <!--end .accordion-section-content-->                                                        
                                                    </td>
                                                    <td style="display: none;"></td>
                                                    <td style="display: none;"></td>
                                                    <td style="display: none;"></td>
                                                    <td style="display: none;"></td>
                                                 </tr>  
                                                <!--end .accordion-section-->
                                                <?php
                                            }
                                        }
                                        ?>

                                
                                </tbody>    
                             </table>   
                            </div>
                        </div>
                    </div>
                    <div style="clear:both;"></div>
                </div>

    </div>
    </div>
</section>

            <!-- footer part -->
            <?php include_once('footer/footer_home.php'); ?>
            <!-- footer part end -->
            <!-- End:contact page-->
            <div style="clear:both;"></div>
        </div>
        <!-- home page end-->
        <!-- Footer Script -->
        <?php include_once('top/footer_script.php'); ?>
        <script type="text/javascript" src="assets/js/accordion.js"></script>
        <script src="assets/plugins/jasny/js/bootstrap-fileupload.js"></script>

        <script src="assets/js/jquery-ui.min.js"></script>
        <script src="assets/plugins/dataTables/jquery.dataTables.js"></script>

        <!-- <link rel="stylesheet" type="text/css" media="screen" href="admin/css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css"> -->
        <script type="text/javascript" src="admin/js/moment.min.js"></script>
        <!-- <script type="text/javascript" src="admin/js/bootstrap-datetimepicker.min.js"></script> -->
        <script type="text/javascript">


            function confirm_delete(id, driverid)
            {
                //alert('sdf');
                var tsite_url = '<?php echo $tconfig["tsite_url"]; ?>';
                if (id != '') {
                    var confirm_ans = confirm('<?= addslashes($langage_lbl['LBL_DELETE_VEHICLE_CONFIRM_MSG']); ?>');
                    if (confirm_ans == true) {
                        window.location.href = "vehicle?action=delete&id=" + id + "&driverid=" + driverid;
                    }
                }
                //document.getElementById(id).submit();
            }

            function del_veh_doc(id, type, img) {
                ans = confirm('<?= addslashes($langage_lbl['LBL_CONFIRM_DELETE_DOC']); ?>');
                if (ans == true)
                {
                    var request = $.ajax({
                        type: "POST",
                        url: "ajax_delete_docimage.php",
                        data: "veh_id=" + id + "&type=" + type + "&img=" + img + "&doc_type=veh_doc",
                        success: function (data) {
                            var url = window.location.href;
                            $("#" + type + "_" + id).load(url + " #" + type + "_" + id);
                        }
                    });
                } else {
                    return false;
                }
            }

            $(function () {
                newDate = new Date('Y-M-D');
                $('.dp123').datepicker({
                    dateFormat: 'yy-mm-dd',
                    minDate: moment().add(1, 'd').toDate(),
                    // minDate: moment(),
                    ignoreReadonly: true,
                    // keepInvalid: true
                });
            });

            function validate_fileextension(formid, filename) {
                var fileExtension = ['jpeg', 'jpg', 'png', 'gif', 'bmp', 'doc', 'docx', 'pdf'];
                if ($.inArray(filename.split('.').pop().toLowerCase(), fileExtension) == -1) {
                    $("#" + formid + " .fileerror").html("Only formats are allowed : " + fileExtension.join(', '));
                    $('.save-document').prop("disabled", true);
                    return false;
                } else {
                    $('.save-document').prop("disabled", false);
                    $("#" + formid + " .fileerror").html("");
                }
            }
    
            $(".readonlyNew").on('keydown paste', function (e) {
                e.preventDefault();
            });

            if($('#my-trips-data').length > 0) {
                 $('#my-trips-data').DataTable({
                    "oLanguage": langData,
                    "bSort" : false,
                    "bFilter": false,
                    "bLengthChange" : false, 
                    "bPaginate": false,
                    "bInfo": false, // hide showing entries

                });
            }

            $('.expanding').on('click', function(e) {
                $(this).toggleClass('active-menu');
                $(this).closest('tr').next('.accordian-data').slideToggle(0);
            })

        </script>
    </body>
</html>

