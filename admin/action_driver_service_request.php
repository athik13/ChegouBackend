<?php 
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobj = new General();

$did = base64_decode(base64_decode($_REQUEST['did']));
$sql = "SELECT vName,vLastName FROM register_driver WHERE iDriverId = ".$did ;
$dDetails = $obj->MySQLSelect($sql);

$name = $dDetails[0]['vName'];
$vLastName = $dDetails[0]['vLastName'];

$sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();


$sql = "SELECT dsr.*  FROM register_driver AS rd 
        JOIN driver_service_request AS dsr ON dsr.iDriverId = rd.iDriverId 
        WHERE dsr.iDriverId = ".$did." AND dsr.cRequestStatus = 'Pending'";
$Requests = $obj->MySQLSelect($sql);

if(isset($_POST['submit'])){
    $status = $_POST['status'];
    $VehicleCatIds = $_POST['VehicleCatIds'];
    $driverId = $_POST['driverId'];
    
    $sql  = 'SELECT dv.vCarType, rd.vEmail, rd.vName ,rd.vLastName FROM driver_vehicle AS dv JOIN register_driver AS rd ON rd.iDriverId = dv.iDriverId WHERE dv.iDriverId = ' .$did.' AND dv.vLicencePlate = "My Services"' ;
    $existingServicesdb = $obj->MySQLSelect($sql);
    // echo '<pre>';print_r($existingServices);echo '</pre>';die;
    
    $existingServices = explode(',',$existingServicesdb[0]['vCarType']);
    
    $rejectedServices = [];
    $newServices = [];
    foreach ($status as $key => $value) {
        if($status[$key] == 'Approve'){
            $newServices[] = $VehicleCatIds[$key];
        }
        if($status[$key] == 'Reject'){
            $rejectedServices[] = $VehicleCatIds[$key];
        }        
    }
    $allServices = implode(',',array_merge($newServices,$existingServices));

    $sqlu  = 'UPDATE driver_vehicle SET vCarType = "'.$allServices.'" WHERE iDriverId = "' .$did.'" AND vLicencePlate = "My Services"' ;
    $existingServices = $obj->sql_query($sqlu);

   
    if ($existingServices) {

        $rejectedNewServies = array_merge($newServices,$rejectedServices);
        if(!empty($rejectedNewServies)){
            /* Delete Request as Its Processed */
            $sqlDel = 'DELETE FROM driver_service_request WHERE iDriverId = "'.$did.'" AND iVehicleCategoryId IN ('.implode(',',$rejectedNewServies).')';
            $obj->sql_query($sqlDel);
        }
 
        if(!empty($newServices) || !empty($rejectedServices)){
            /* Send Email to Driver */        
            $getMaildata['vEmail'] = $existingServicesdb[0]['vEmail'];
            $getMaildata['FromName'] = $existingServicesdb[0]['vName']." ".$existingServicesdb[0]['vLastName'];
            $getMaildata['serviceMsg'] = $langage_lbl_admin['LBL_DRIVER_SERVICE_ACCEPTED_REJECT'];
            $mail = $generalobj->send_email_user('DRIVER_SERVICE_ACCEPTED_REJECT',$getMaildata);
        }

        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
        header("location:driver_service_request.php");
    }
}
$title = $generalobjAdmin->clearName($name." ".$vLastName);
?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | Service Request for  <?=$title ?></title>
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
                                <h2>Service Request for  <?=$title ?></h2>
                                <!--<input type="button" id="" value="ADD A DRIVER" class="add-btn">-->

                                <a class="back_link" href="driver_service_request.php">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                            </div>
                            
                        </div>
                        <hr />
                    </div>

                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="admin-nir-export">
                                    <form method="POST">
                                    <input type='hidden' name="driverId" value="<?php echo $did; ?>">
                                    <?php if (!empty($Requests)) { 
                                        // echo '<pre>' ; print_r($Requests);
                                        ?>
                                            <table class="table table-striped table-bordered table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Category </th>
                                                        <th>Service Name </th>
                                                        <th>Requested Value</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                        <?php
                                                            foreach ($Requests as $key => $Request) {
                 
                                                                $sql  = "SELECT vt.vVehicleType,vt.iVehicleCategoryId,vc.vCategory_".$default_lang." AS catName, vc1.vCategory_".$default_lang." AS pCate FROM vehicle_type AS vt
                                                                LEFT JOIN ".$sql_vehicle_category_table_name." AS vc ON vc.iVehicleCategoryId = vt.iVehicleCategoryId 
                                                                LEFT JOIN ".$sql_vehicle_category_table_name." AS vc1 ON vc1.iVehicleCategoryId = vc.iParentId 
                                                                WHERE iVehicleTypeId = " .$Request['iVehicleCategoryId'] ;
                                                                $existingServices = $obj->MySQLSelect($sql);
                                                                
                                                                // echo '<pre>' ; print_r($existingServices);

                                                                echo '<tr>';
                                                                    echo '<td>'.($key + 1).'</td>';
                                                                    echo '<td>'.$existingServices[0]['pCate'].'</td>';
                                                                    echo '<td><strong>'.$existingServices[0]['catName'].' - '.$existingServices[0]['vVehicleType'].'</strong></td>';
                                                                    echo '<td>Enable </td>';
                                                                    echo '<td >
                                                                        <input type="radio" name="status['.$key.']" value="Pending" checked>Pending <br>
                                                                        <input type="radio" name="status['.$key.']" value="Approve">Approve <br>
                                                                        <input type="radio" name="status['.$key.']" value="Reject">Reject 
                                                                        <input type="hidden" name="VehicleCatIds[]" value="'.$Request['iVehicleCategoryId'].'">
                                                                        </td>';
                                                                echo '</tr>';
                                                            }
                                                        ?>
                                                        
                                                </tbody>
                                            </table>

                                        <input type="submit" name="submit" value="Process Request" class="btn btn-primary">
                                        <?php } ?>
                                    </form>
                                </div>
                                <div style="clear:both;"></div>
                                <div class="table-responsive">

                                </div>
                            </div> <!--TABLE-END-->
                        </div>
                    </div>
                    <div class="admin-notes">
                        <h4>Notes:</h4>
                        <ul>
                            <li> This module will list the details of all the services requested by the providers.</li>
                            <li> Administrator can take appropriate action (Approve , Reject , Pending). </li>
                            <li> Pending request will remain here, which the admin can approve or reject on later stage. </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->


        <?php
        include_once('footer.php');
        ?>
    <!-- END BODY-->
</html>