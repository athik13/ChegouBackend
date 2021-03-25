<?php
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
// echo '<pre>';print_r($tconfig);die;

$sql = "SELECT rd.vName,rd.vLastName,rd.vCode,rd.vEmail,rd.vPhone, dsr.iDriverId FROM register_driver AS rd JOIN driver_service_request AS dsr ON dsr.iDriverId = rd.iDriverId GROUP BY dsr.iDriverId";
$script = 'DriverRequest';
$Requests = $obj->MySQLSelect($sql);
?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | Driver Service Requests</title>
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
                                <h2><?= $langage_lbl_admin['LBL_DRIVER_SERVICE_MODIFICATION_REQUESTS_TXT'] ?></h2>
                                <!--<input type="button" id="" value="ADD A DRIVER" class="add-btn">-->
                            </div>
                        </div>
                        <hr />
                    </div>

                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="admin-nir-export">

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
                                                    <th>#</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Phone</th>
                                                    <th class="text-center">View Requests</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                    if(count($Requests) == 0){
                                                        echo '<tr>';
                                                            echo '<td colspan="5" class="text-center">'.$langage_lbl['LBL_SERVICE_REQ_NOT_FOUND'].'</td>';
                                                        echo '<tr>';
                                                    }else{
                                                        foreach ($Requests as $key => $Request) {
                                                            echo '<tr>';
                                                                echo '<td>'.($key + 1).'</td>';
                                                                echo '<td>'.$generalobjAdmin->clearName($Request['vName'].' '.$Request['vLastName']).'</td>';
                                                                echo '<td>'.$generalobjAdmin->clearEmail(" " . $Request['vEmail']).'</td>';
                                                                echo '<td>+'.$Request['vCode'].' '.$generalobjAdmin->clearPhone(" " . $Request['vPhone']).'</td>';
                                                                echo '<td class="text-center"><a href="action_driver_service_request.php?did='.base64_encode(base64_encode($Request['iDriverId'])).'" class="btn btn-primary">View Requested Services </a></td>';
                                                            echo '<tr>';
                                                        }
                                                    }
                                                ?>
                                            </tbody>
                                        </table>
                                    </form>
                                </div>
                            </div> <!--TABLE-END-->
                        </div>
                    </div>
                    <div class="admin-notes">
                        <h4>Notes:</h4>
                        <ul>
                            <li> This module will list all the providers who has requested the admin to approve new services.</li>
                            <li> Admin can click on "View Requested Services" button to see the details of requested services by the providers.</li>
                            <li> Admin will see the list of all the providers whose request is in pending state.</li>
                            
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