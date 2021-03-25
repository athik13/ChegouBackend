<?
include_once('../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();
include_once ('../app_common_functions.php'); //added by SP on 27-9-2019 for fly changes

require_once(TPATH_CLASS . "Imagecrop.class.php");
$thumb = new thumbnail();

//$default_lang = $generalobj->get_default_lang();
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : ''; // iCancelReasonId
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$action = ($id != '') ? 'Edit' : 'Add';

//$temp_gallery = $tconfig["tpanel_path"];
$tbl_name = 'cancel_reason';
$script = 'cancel_reason';

//$ufxService = $generalobj->CheckUfxServiceAvailable();
$ufxEnable = isUberXModuleAvailable() ? "Yes" : "No"; //add function to modules availibility
$rideEnable = isRideModuleAvailable() ? "Yes" : "No";
$deliveryEnable = isDeliveryModuleAvailable() ? "Yes" : "No";
$deliverallEnable = isDeliverAllModuleAvailable() ? "Yes" : "No";
$cubeDeliverallOnly = isDeliverAllOnlySystem();
$onlyDeliverallModule = strtoupper(ONLYDELIVERALL);
if($cubeDeliverallOnly > 0){
    $onlyDeliverallModule = "YES";
}
// fetch all lang from language_master table 
$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = count($db_master);

// set all variables with either post (when submit) either blank (when insert)
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'on';
//echo "<pre>";print_r($_POST);die;
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';
$thumb = new thumbnail();
/* to fetch max iDisplayOrder from table for insert */
$select_order = $obj->MySQLSelect("SELECT MAX(iDisplayOrder) AS iDisplayOrder FROM " . $tbl_name);
$iDisplayOrder = isset($select_order[0]['iDisplayOrder']) ? $select_order[0]['iDisplayOrder'] : 0;
$iDisplayOrder = $iDisplayOrder + 1; // Maximum order number

$iCancelReasonId = isset($_POST['iCancelReasonId']) ? $_POST['iCancelReasonId'] : $iCancelReasonId;
$iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : $iDisplayOrder;
$temp_order = isset($_POST['temp_order']) ? $_POST['temp_order'] : "";
$eType = isset($_POST['eType']) ? $_POST['eType'] : '';

//added by SP for fly on 27-09-2019 start
$eFly = 0;
if($eType=='Fly') {
    $eType = 'Ride';
    $eFly = 1;
}
//added by SP for fly on 27-09-2019 end

$eFor = isset($_POST['eFor']) ? $_POST['eFor'] : '';
if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {

        $vTitle = 'vTitle_' . $db_master[$i]['vCode'];
        $$vTitle = isset($_POST[$vTitle]) ? $_POST[$vTitle] : '';
    }
}
if (isset($_POST['submit'])) { //form submit
    if ($action == "Add" && !$userObj->hasPermission('create-cancel-reasons')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create Cancel Reason.';
        header("Location:company_action.php");
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission('edit-cancel-reasons')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update Cancel Reason.';
        header("Location:company_action.php");
        exit;
    }
    if (!empty($iCancelReasonId)) {
        if (SITE_TYPE == 'Demo') {
            header("Location:cancellation_reason_action.php?id=" . $id . "&success=2");
            exit;
        }
    }

    if ($temp_order > $iDisplayOrder) {
        for ($i = $temp_order; $i >= $iDisplayOrder; $i--) {
            $sql = "UPDATE " . $tbl_name . " SET iDisplayOrder = " . ($i + 1) . " WHERE iDisplayOrder = " . $i;
            $obj->sql_query($sql);
        }
    } else if ($temp_order < $iDisplayOrder) {
        for ($i = $temp_order; $i <= $iDisplayOrder; $i++) {
            $sql = "UPDATE " . $tbl_name . " SET iDisplayOrder = " . ($i - 1) . " WHERE iDisplayOrder = " . $i;
            $obj->sql_query($sql);
        }
    }
    //Commented By HJ On 16-09-2019 As Per Discuss With PM Ref. Project - ineedapp Sheet Bug = 314 Start
    /*if ($onlyDeliverallModule == "YES" || DELIVERALL == "Yes" || $eType=='DeliverAll') {
        $eFor = "Company";
    }*/
    //Commented By HJ On 16-09-2019 As Per Discuss With PM Ref. Project - ineedapp Sheet Bug = 314 End
    $q = "INSERT INTO ";
    $where = '';

    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `iCancelReasonId` = '" . $id . "'";
    }

    $sql_str = '';
    if ($count_all > 0) {
        for ($i = 0; $i < $count_all; $i++) {
            $vTitle = 'vTitle_' . $db_master[$i]['vCode'];
            $sql_str .= $vTitle . " = '" . $$vTitle . "',";
        }
    }
//added by SP for fly on 27-09-2019 start
    $query = $q . " `" . $tbl_name . "` SET 	
				" . $sql_str . "
				`eStatus` = '" . $eStatus . "',
                `eType` = '" . $eType . "',
                `eFor`= '" . $eFor . "',
                `eFly` = '".$eFly."',
				`iDisplayOrder` = '" . $iDisplayOrder . "'"
            . $where;
    $obj->sql_query($query);

    $id = ($id != '') ? $id : $obj->GetInsertId();

    //header("Location:cancel_reason_action.php?id=".$id."&success=1");
    if ($action == "Add") {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    } else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    }
    header("location:" . $backlink);
}


// for Edit
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iCancelReasonId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);

    if ($count_all > 0) {
        for ($i = 0; $i < $count_all; $i++) {
            $vTitle = 'vTitle_' . $db_master[$i]['vCode'];
            $$vTitle = isset($db_data[0][$vTitle]) ? $db_data[0][$vTitle] : $$vTitle;
            $eStatus = $db_data[0]['eStatus'];
            $eType = $db_data[0]['eType'];
            $eFor = $db_data[0]['eFor'];
            $eFly = $db_data[0]['eFly']; //added by SP for fly on 27-09-2019 
            $iDisplayOrder = $db_data[0]['iDisplayOrder'];
        }
    }
}
//echo $eStatus;die;
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title>Admin | Home Page <?= $langage_lbl_admin["LBL_CANCEL_REASON_TXT_ADMIN"]; ?>  <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <script type="text/javascript" language="javascript">

            function getAllLanguageCode() {
                var def_lang = '<?= $default_lang ?>';
                var getEnglishText = $('#vTitle_' + def_lang).val();
                var error = false;
                var msg = '';

                if (getEnglishText == '') {
                    msg += '<div class="alert alert-danger fade in"><a href="#" class="close" data-dismiss="alert"><icon class="fa fa-close"></icon></a><strong>Please Enter English Value</strong></div> <br>';
                    error = true;
                }

                if (error == true) {
                    $('#errorMessage').html(msg);
                    return false;
                } else {
                    $('#imageIcon').show();
                    $.ajax({
                        url: "ajax_get_all_reason_translate.php",
                        type: "post",
                        data: {'englishText': getEnglishText},
                        dataType: 'json',
                        success: function (response) {
                            $.each(response, function (name, Value) {
                                $('#' + name).val(Value);
                            });
                            $('#imageIcon').hide();
                        }
                    });
                }


            }
        </script>
        <? include_once('global_files.php'); ?>
        <!-- On OFF switch -->
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />

        <!-- PAGE LEVEL STYLES -->
        <link rel="stylesheet" href="../assets/plugins/Font-Awesome/css/font-awesome.css" />
        <link rel="stylesheet" href="../assets/plugins/wysihtml5/dist/bootstrap-wysihtml5-0.0.2.css" />
        <link rel="stylesheet" href="../assets/css/Markdown.Editor.hack.css" />
        <link rel="stylesheet" href="../assets/plugins/CLEditor1_4_3/jquery.cleditor.css" />
        <link rel="stylesheet" href="../assets/css/jquery.cleditor-hack.css" />
        <link rel="stylesheet" href="../assets/css/bootstrap-wysihtml5-hack.css" />
        <style>
            ul.wysihtml5-toolbar > li {
                position: relative;
            }
        </style>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >

        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <? include_once('header.php'); ?>
            <? include_once('left_menu.php'); ?>       
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2><?= $action; ?> Cancel Reason </h2>
                            <a href="cancellation_reason.php">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />	
                    <div class="body-div">
                        <div class="form-group">
                            <? if ($success == 0 && $_REQUEST['var_msg'] != "") { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <? echo $_REQUEST['var_msg']; ?>
                                </div><br/>
                            <? } ?>

                            <? if ($success == 1) { ?>
                                <div class="alert alert-success alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                                </div><br/>
                            <? } ?>

                            <? if ($success == 2) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                </div><br/>
                            <? } ?>

                            <form method="post" action="" enctype="multipart/form-data" id="cancel_reason_action" name="cancel_reason_action">
                                <input type="hidden" name="id" value="<?= $id; ?>"/>
                                <input type="hidden" name="temp_order" id="temp_order" value="1	">
                                <input type="hidden" name="vImage_old" value="<?= $vImage ?>">
                                <input type="hidden" name="backlink" id="backlink" value="cancellation_reason.php"/>

                                <?php
                                if($onlyDeliverallModule == 'YES') { ?> <input type="hidden" name="eType" id="eType" value="DeliverAll">
                                <?php } else if ($APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') { ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Cancel Reason Service Type</label>
                                        </div>
                                        <div class="col-lg-6">
                                            <select name="eType" id="eType" class="form-control" onchange="changeeForCompany(this.value);">
                                                <? if ($rideEnable == "Yes") { ?>
                                                    <option value="Ride" <?php if ($eType == "Ride") echo 'selected="selected"'; ?> >Ride</option>
                                                <? } if ($deliveryEnable == "Yes") { ?>
                                                    <option value="Deliver" <?php if ($eType == "Deliver") echo 'selected="selected"'; ?> >Delivery</option>
                                                <? } if ($APP_TYPE == 'Ride-Delivery-UberX' && strtoupper($ufxEnable) == "YES") { ?>
                                                    <option value="UberX" <?php if ($eType == "UberX") echo 'selected="selected"'; ?> >Service</option>
                                                <? } if (DELIVERALL == 'Yes' && $deliverallEnable == "Yes") { ?>
                                                    <option value="DeliverAll" <?php if ($eType == "DeliverAll") echo 'selected="selected"'; ?> >DeliverAll</option>
                                                <? } ?>
                                               <?php if(checkFlyStationsModule()) {  //added by SP for fly on 27-9-2019 ?>
                                                        <option value="Fly" <?php if ($eType == "Ride" && $eFly==1) echo 'selected="selected"'; ?> >Fly</option>
                                                   <?php  } ?>
                                            </select>
                                        </div>
                                    </div>
                                <?php } else {
                                    if ($APP_TYPE == 'Ride') {
                                        $apptype = 'Ride';
                                    } else if ($APP_TYPE == 'Delivery') {
                                        $apptype = 'Deliver';
                                    } else if ($APP_TYPE == 'UberX') {
                                        $apptype = 'UberX';
                                    }
                                    ?>
                                    <input type="hidden" name="eType" id="eType" value="<?= $apptype; ?>">
                                <?php } if ($onlyDeliverallModule == "NO") { ?>
                                <div class="row" id="eFor">
                                    <div class="col-lg-12">
                                        <label>Cancel Reason For</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select name="eFor" class="form-control">
                                            <option value="General" <?php if ($eFor == "Ride") echo 'selected="selected"'; ?> >General</option>
                                            <option value="Passenger" <?php if ($eFor == "Passenger") echo 'selected="selected"'; ?> ><?= $langage_lbl_admin['LBL_RIDER'] ?></option>
                                            <option value="Driver" <?php if ($eFor == "Driver") echo 'selected="selected"'; ?> ><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?></option>
                                            <?php //if ((DELIVERALL == "Yes" && $eFor!='') || $onlyDeliverallModule == "YES") { ?>
                                                <option id="eForCompany" value="Company" <?php if ($eFor == "Company") echo 'selected="selected"'; ?> ><?= $langage_lbl_admin['LBL_COMPANY'] ?></option>
                                            <? //} ?>
                                        </select>
                                    </div>
                                </div>
                                <?php } ?>
                                <? /*if (($APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') && DELIVERALL != 'Yes') { ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Cancel Reason Service Type</label>
                                        </div>
                                        <div class="col-lg-6">
                                            <select name="eType" class="form-control">
                                                <? if ($APP_TYPE == 'Ride' || $APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') { ?>
                                                    <option value="Ride" <?php if ($eType == "Ride") echo 'selected="selected"'; ?> >Ride</option>
                                                <? } if ($APP_TYPE == 'Delivery' || $APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') { ?>
                                                    <option value="Deliver" <?php if ($eType == "Deliver") echo 'selected="selected"'; ?> >Delivery</option>
                                                <? } if ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') { ?>
                                                    <option value="UberX" <?php if ($eType == "UberX") echo 'selected="selected"'; ?> >Service</option>
                                                <? } if (DELIVERALL == 'Yes') { ?>
                                                    <option value="DeliverAll" <?php if ($eType == "DeliverAll") echo 'selected="selected"'; ?> >DeliverAll</option>
                                                <? } ?>
                                            </select>
                                        </div>
                                    </div>
                                <? } else if (DELIVERALL == 'Yes' && ($APP_TYPE != 'Ride-Delivery' || $APP_TYPE != 'Ride-Delivery-UberX')) {  ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Cancel Reason Service Type</label>
                                        </div>
                                        <div class="col-lg-6">
                                            <select name="eType" class="form-control">
                                                <? if (($APP_TYPE == 'Ride' || $APP_TYPE == 'Ride-Delivery-UberX')) { ?>
                                                    <option value="Ride" <?php if ($eType == "Ride") echo 'selected="selected"'; ?> >Ride</option>
                                                <? } if (($APP_TYPE == 'Delivery' || $APP_TYPE == 'Ride-Delivery-UberX')) { ?>
                                                    <option value="Deliver" <?php if ($eType == "Deliver") echo 'selected="selected"'; ?> >Delivery</option>
                                                <? } if (($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX')) {
                                                    ?>
                                                    <option value="UberX" <?php if ($eType == "UberX") echo 'selected="selected"'; ?> >Service</option>
                                                    <?
                                                }
                                                if (DELIVERALL == 'Yes') {
                                                    ?>
                                                    <option value="DeliverAll" <?php if ($eType == "DeliverAll") echo 'selected="selected"'; ?> >DeliverAll</option>
                                                <? } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <?
                                } else {
                                    if ($APP_TYPE == 'Ride') {
                                        $apptype = 'Ride';
                                    } else if ($APP_TYPE == 'Delivery') {
                                        $apptype = 'Deliver';
                                    } else if ($APP_TYPE == 'UberX') {
                                        $apptype = 'UberX';
                                    } else if($onlyDeliverallModule == 'YES') {
                                        $apptype = 'DeliverAll';
                                    }
                                    ?>
                                    <input type="hidden" name="eType" value="<?= $apptype; ?>">
                                <? } ?>
                                <?php if ($onlyDeliverallModule == "NO") { ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Cancel Reason For</label>
                                        </div>
                                        <div class="col-lg-6">
                                            <select name="eFor" class="form-control">
                                                <option value="General" <?php if ($eFor == "Ride") echo 'selected="selected"'; ?> >General</option>
                                                <option value="Passenger" <?php if ($eFor == "Passenger") echo 'selected="selected"'; ?> ><?= $langage_lbl_admin['LBL_RIDER'] ?></option>
                                                <option value="Driver" <?php if ($eFor == "Driver") echo 'selected="selected"'; ?> ><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?></option>
                                                <?php if (DELIVERALL == "Yes" || $onlyDeliverallModule == "YES") { ?>
                                                    <option value="Company" <?php if ($eFor == "Company") echo 'selected="selected"'; ?> ><?= $langage_lbl_admin['LBL_COMPANY'] ?></option>
                                                <? } ?>
                                            </select>
                                        </div>
                                    </div>
                                <?php 
                                } */ 
                                if ($count_all > 0) {
                                    for ($i = 0; $i < $count_all; $i++) {
                                        $vCode = $db_master[$i]['vCode'];
                                        $vTitle = $db_master[$i]['vTitle'];

                                        $vTitle_val = "vTitle_" . $vCode;
                                        $eDefault = $db_master[$i]['eDefault'];

                                        $required = ($eDefault == 'Yes') ? 'required' : '';
                                        $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                        ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?= $vTitle; ?> Reason <?= $required_msg; ?></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="<?= $vTitle_val; ?>"  id="<?= $vTitle_val; ?>" value="<?= $$vTitle_val; ?>" placeholder="Reason" <?= $required; ?>>
                                            </div>
                                            <?php
                                            if ($vCode == $default_lang) {
                                                ?>
                                                <div class="col-lg-6">
                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode();">Convert To All Language</button>
                                                </div>

                                                <?php
                                            }
                                            ?>
                                        </div>
                                        <?
                                    }
                                }
                                ?>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Order</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <?
                                        $temp = 1;
                                        $query1 = $obj->MySQLSelect("SELECT max(iDisplayOrder) as maxnumber FROM " . $tbl_name . " ORDER BY iDisplayOrder");
                                        $maxnum = isset($query1[0]['maxnumber']) ? $query1[0]['maxnumber'] : 0;
                                        $dataArray = array();
                                        for ($i = 1; $i <= $maxnum; $i++) {
                                            $dataArray[] = $i;
                                            $temp = $iDisplayOrder;
                                        }
                                        /* while($res = mysqli_fetch_array($query1)) 
                                          {
                                          $dataArray[] = $res['iDisplayOrder'];
                                          $temp = $iDisplayOrder;
                                          } */
                                        ?>
                                        <input type="hidden" name="temp_order" id="temp_order" value="<?= $temp ?>">
                                        <select name="iDisplayOrder" class="form-control">
<? foreach ($dataArray as $arr): ?>
                                                <option <?= $arr == $temp ? ' selected="selected"' : '' ?> value="<?= $arr; ?>" >
                                                    -- <?= $arr ?> --
                                                </option>
                                            <? endforeach; ?>
<? if ($action == "Add") { ?>
                                                <option value="<?= $temp; ?>" >
                                                    -- <?= $temp ?> --
                                                </option>
<? } ?>
                                        </select>

                                    </div>
                                </div>
                                <input type="hidden" value="<?php if ($eStatus == "Active") { ?>on<?php } else { ?>off<?php } ?>" name="eStatus" <?= ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?>/>
                                <!-- <div class="row">
                                    <div class="col-lg-12">
                                        <label>Status</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="make-switch" data-on="success" data-off="warning">
                                            <input type="checkbox" name="eStatus" <?= ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?>/>
                                        </div>
                                    </div>
                                </div> -->
                                <div class="row">
                                    <div class="col-lg-12">
  <!--   <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?= $action; ?> Home Page <?= $langage_lbl_admin["LBL_CANCEL_REASON_TXT_ADMIN"]; ?>" > -->
<?php if (($action == 'Edit' && $userObj->hasPermission('edit-cancel-reasons')) || ($action == 'Add' && $userObj->hasPermission('create-cancel-reasons'))) { ?>
                                            <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?= $action; ?> <?= $langage_lbl_admin["LBL_CANCEL_REASON_TXT_ADMIN"]; ?>" > 
                                            <input type="reset" value="Reset" class="btn btn-default">
<?php } ?>
                                        <!-- <a href="javascript:void(0);" onclick="reset_form('cancel_reason_action');" class="btn btn-default">Reset</a> -->
                                        <a href="cancellation_reason.php" class="btn btn-default back_link">Cancel</a>
                                    </div>


                                </div>

                            </form>
                        </div>
                    </div>
                    <div class="clear"></div>
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

<? include_once('footer.php'); ?>
        <script>
            $(function () {
                /* added by SP on 16-7-2019 start */
                var DELIVERALL = "<?php echo DELIVERALL; ?>";
                var ONLYDELIVERALL = '<?php echo $onlyDeliverallModule; ?>';
                var eFor = '<?php echo $eFor; ?>';
                if ((DELIVERALL == "Yes" && eFor!='') || ONLYDELIVERALL == "YES") {
                    $("#eForCompany").show();
                } else {
                    $("#eForCompany").hide();
                }
            });
            function changeeForCompany(val) {
                if(val!='DeliverAll') {
                    $("#eForCompany").hide();
                    $("#eFor").show();
                } else {
                    $("#eFor").hide();
                }
            }
            /* added by SP on 16-7-2019 end */
        </script>
        <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>

        <!-- PAGE LEVEL SCRIPTS -->
        <script src="../assets/plugins/wysihtml5/lib/js/wysihtml5-0.3.0.js"></script>
        <script src="../assets/plugins/bootstrap-wysihtml5-hack.js"></script>
        <script src="../assets/plugins/CLEditor1_4_3/jquery.cleditor.min.js"></script>
        <script src="../assets/plugins/pagedown/Markdown.Converter.js"></script>
        <script src="../assets/plugins/pagedown/Markdown.Sanitizer.js"></script>
        <script src="../assets/plugins/Markdown.Editor-hack.js"></script>
        <script src="../assets/js/editorInit.js"></script>
        <script>
                                            $(function () {
                                                formWysiwyg();
                                            });

        </script>
    </body>
    <!-- END BODY-->    
</html>
