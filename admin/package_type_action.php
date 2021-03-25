<?
include_once('../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';

$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

$tbl_name = 'package_type';
$script = 'Package';

//echo '<prE>'; print_R($_REQUEST); echo '</pre>';
// set all variables with either post (when submit) either blank (when insert)
$vName = isset($_POST['vName']) ? $_POST['vName'] : '';
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';

$vTitle_store = array();
$sql = "SELECT * FROM `language_master` where eStatus='Active' ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = count($db_master);
if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
        $vValue = 'vName_' . $db_master[$i]['vCode'];
        array_push($vTitle_store, $vValue);
        $$vValue = isset($_POST[$vValue]) ? $_POST[$vValue] : '';
    }
}

if ($APP_TYPE == 'Delivery' || $APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') {
        $sql = "select iDeliveryFieldId,vFieldName from delivery_fields where eStatus = 'Active' AND eInputType='Select'";
        $db_delivery_fields_data = $obj->MySQLSelect($sql);
}        

if (isset($_POST['submit'])) {
    if (($APP_TYPE == 'Delivery' || $APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') && !empty($db_delivery_fields_data)) {
    $iDeliveryFieldId = isset($_POST['iDeliveryFieldId']) ? $_POST['iDeliveryFieldId'] : 0;
    }else{
        $iDeliveryFieldId = 0;
    }

    if ($action == "Add" && !$userObj->hasPermission('create-package-type')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create package type.';
        header("Location:state.php");
        exit;
    }

    if ($action == "Edit" && !$userObj->hasPermission('edit-package-type')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update package type.';
        header("Location:state.php");
        exit;
    }

    if (SITE_TYPE == 'Demo') {
        header("Location:package_type_action.php?id=" . $id . '&success=2');
        exit;
    }
    for ($i = 0; $i < count($vTitle_store); $i++) {
        $vValue = 'vName_' . $db_master[$i]['vCode'];

        $q = "INSERT INTO ";
        $where = '';

        if ($id != '') {
            $q = "UPDATE ";
            $where = " WHERE `iPackageTypeId` = '" . $id . "'";
        }

    
        $query = $q . " `" . $tbl_name . "` SET
			`vName` = '" . $_POST['vName_' . $default_lang] . "',
			`eStatus` = '" . $eStatus . "',
            `iDeliveryFieldId` = '" . $iDeliveryFieldId . "',

			" . $vValue . " = '" . $_POST[$vTitle_store[$i]] . "'"
                . $where;

        $obj->sql_query($query);
        $id = ($id != '') ? $id : $obj->GetInsertId();
    }

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
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iPackageTypeId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);

    $vLabel = $id;
    if (count($db_data) > 0) {
        for ($i = 0; $i < count($db_master); $i++) {
            foreach ($db_data as $key => $value) {
                $vValue = 'vName_' . $db_master[$i]['vCode'];
                $$vValue = $value[$vValue];
                $vName = $value['vName'];
                $eStatus = $value['eStatus'];
                $iDeliveryFieldId = $value['iDeliveryFieldId'];
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
        <title>Admin | Package <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />

        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />

        <? include_once('global_files.php'); ?>
        <!-- On OFF switch -->
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
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
                            <h2><?= $action; ?> Package Type</h2>
                            <a href="package_type.php" class="back_link">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />
                    <div class="body-div">
                        <div class="form-group">
                            <? if ($success == 1) { ?>
                                <div class="alert alert-success alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                                </div><br/>
                            <? } elseif ($success == 2) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                </div><br/>
                            <? } ?>
                            <form method="post" name="_make_form" id="_make_form" action="">
                                <input type="hidden" name="id" value="<?= $id; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="package_type.php"/>
                                <div class="col-lg-12" id="errorMessage"></div>
                                <!-- 								<div class="row">
                                                                                                        <div class="col-lg-12">
                                                                                                                <label>Package Type Label<span class="red"> *</span></label>
                                                                                                        </div>
                                                                                                        <div class="col-lg-6">
                                                                                                                <input type="text" class="form-control" name="vName"  id="vName" value="<?= $vName; ?>" placeholder="Package Label" required>
                                                                                                        </div>
                                                                                                </div> -->
                                <?
                                if ($count_all > 0) {
                                    for ($i = 0; $i < $count_all; $i++) {
                                        $vCode = $db_master[$i]['vCode'];
                                        $vTitle = $db_master[$i]['vTitle'];
                                        $eDefault = $db_master[$i]['eDefault'];

                                        $vValue = 'vName_' . $vCode;

                                        $required = ($eDefault == 'Yes') ? 'required' : '';
                                        $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                        ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Package Type (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>

                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" value="<?= $$vValue; ?>" placeholder="<?= $vTitle; ?>Value" <?= $required; ?>>

                                            </div>
                                            <?
                                            if ($vCode == $default_lang && count($db_master) > 1) {
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
                                  <?php if (($APP_TYPE == 'Delivery' || $APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') && !empty($db_delivery_fields_data)) {?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Delivery Field (Only for Multi Delivery)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                            <select class="form-control" name = 'iDeliveryFieldId' id="iDeliveryFieldId"  required>
                                            <option value="">Select Delivery Field</option>
                                            <? for ($i = 0; $i < count($db_delivery_fields_data); $i++) { ?>
                                                <option <?php if($action == 'Edit' && ($db_delivery_fields_data[$i]['iDeliveryFieldId']==$iDeliveryFieldId) ){ echo "selected";}?> value = "<?= $db_delivery_fields_data[$i]['iDeliveryFieldId'] ?>"><?= $db_delivery_fields_data[$i]['vFieldName'] ?></option>
                                            <? } ?>
                                        </select>
                                    </div>
                                </div>
                            <?php }?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Status</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="make-switch" data-on="success" data-off="warning">
                                            <input type="checkbox" name="eStatus" <?= ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?>/>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <?php if (($action == 'Edit' && $userObj->hasPermission('edit-package-type')) || ($action == 'Add' && $userObj->hasPermission('create-package-type'))) { ?>
                                            <input type="submit" class=" btn btn-default" name="submit" id="submit" value="<?= $action; ?> Package">
                                            <input type="reset" value="Reset" class="btn btn-default">
                                        <?php } ?>
                                        <!-- <a href="javascript:void(0);" onclick="reset_form('_make_form');" class="btn btn-default">Reset</a> -->
                                        <a href="package_type.php" class="btn btn-default back_link">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
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
        <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
    </body>
    <!-- END BODY-->
</html>
<script>
                                            $(document).ready(function () {
                                                var referrer;
                                                if ($("#previousLink").val() == "") { //alert('pre1');
                                                    referrer = document.referrer;
                                                } else {
                                                    referrer = $("#previousLink").val();
                                                }

                                                if (referrer == "") {
                                                    referrer = "package_type.php";
                                                } else {
                                                    $("#backlink").val(referrer);
                                                }
                                                $(".back_link").attr('href', referrer);
                                            });
                                            function getAllLanguageCode() {
                                                var def_lang = '<?= $default_lang ?>';
                                                var def_lang_name = '<?= $def_lang_name ?>';
                                                var getEnglishText = $('#vName_' + def_lang).val();
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
                                                            $.each(response, function (name, Value) {
                                                                var key = name.split('_');
                                                                $('#vName_' + key[1]).val(Value);
                                                            });
                                                            $('#imageIcon').hide();
                                                        }
                                                    });
                                                }


                                            }
 <?php if (($APP_TYPE == 'Delivery' || $APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') && !empty($db_delivery_fields_data)) {?>
    $('#_make_form').validate({
        rules: {
            iDeliveryFieldId: {
                required: true
            },
        }
    });
<?php }?>


</script>