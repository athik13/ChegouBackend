<?
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
//$action 	= ($id != '')?'Edit':'Add';
$action = 'Edit';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$tbl_name = $script = 'service_categories';
$vTitle_store = $tDescriptionArr = array();
$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$iDisplayOrder = 'iDisplayOrder';
$$iDisplayOrder = isset($_POST[$iDisplayOrder]) ? $_POST[$iDisplayOrder] : '';
$count_all = count($db_master);
if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
        $vValue = 'vServiceName_' . $db_master[$i]['vCode'];
        array_push($vTitle_store, $vValue);
        $$vValue = isset($_POST[$vValue]) ? $_POST[$vValue] : '';
    }
}
// set all variables with either post (when submit) either blank (when insert)
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';
if ($id != "") {
    $sql = "SELECT iDisplayOrder FROM `service_categories` where iServiceId = '$id'";
    $displayOld = $obj->MySQLSelect($sql);
    $oldDisplayOrder = $displayOld[0]['iDisplayOrder'];
    if ($oldDisplayOrder > $iDisplayOrder) {
        $sql = "SELECT * FROM `service_categories` where iServiceId = '$id' AND iDisplayOrder >= '$iDisplayOrder' AND iDisplayOrder < '$oldDisplayOrder' ORDER BY iDisplayOrder ASC";
        $db_orders = $obj->MySQLSelect($sql);
        if (!empty($db_orders)) {
            $j = $iDisplayOrder + 1;
            for ($i = 0; $i < count($db_orders); $i++) {
                $query = "UPDATE service_categories SET iDisplayOrder = '$j' WHERE iServiceId = '" . $db_orders[$i]['iServiceId'] . "'";
                $obj->sql_query($query);
                $j++;
            }
        }
    } else if ($oldDisplayOrder < $iDisplayOrder) {
        $sql = "SELECT * FROM `service_categories` where iServiceId = '$iServiceId' AND iDisplayOrder > '$oldDisplayOrder' AND iDisplayOrder <= '$iDisplayOrder' ORDER BY iDisplayOrder ASC";
        $db_orders = $obj->MySQLSelect($sql);
        if (!empty($db_orders)) {
            $j = $oldDisplayOrder;
            for ($i = 0; $i < count($db_orders); $i++) {
                $query = "UPDATE service_categories SET iDisplayOrder = '$j' WHERE iServiceId = '" . $db_orders[$i]['iServiceId'] . "'";
                $obj->sql_query($query);
                $j++;
            }
        }
    }
} else {
    $sql = "SELECT * FROM `service_categories` WHERE iServiceId = '$iServiceId' AND iDisplayOrder >= '$iDisplayOrder' ORDER BY iDisplayOrder ASC";
    $db_orders = $obj->MySQLSelect($sql);

    if (!empty($db_orders)) {
        $j = $iDisplayOrder + 1;
        for ($i = 0; $i < count($db_orders); $i++) {
            $query = "UPDATE service_categories SET iDisplayOrder = '$j' WHERE iServiceId = '" . $db_orders[$i]['iServiceId'] . "'";
            $obj->sql_query($query);
            $j++;
        }
    }
}
if (isset($_POST['submit'])) {
    //echo "<pre>";print_r($_POST);die;
    if ($action == "Add" && !$userObj->hasPermission('create-service-category')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create service category.';
        header("Location:service_category.php");
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission('edit-service-category')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update service category.';
        header("Location:service_category.php");
        exit;
    }
    if (SITE_TYPE == 'Demo') {
        header("Location:service_category_action.php?id=" . $id . '&success=2');
        exit;
    }
    $img_arr = $_FILES;
    for ($d = 0; $d < count($db_master); $d++) {
        $tDescription = "";
        if (isset($_POST['tDescription_' . $db_master[$d]['vCode']])) {
            $tDescription = $_POST['tDescription_' . $db_master[$d]['vCode']];
        }
        $tDescriptionArr["tDescription_" . $db_master[$d]['vCode']] = $tDescription;
    }
    $tDescriptionArr = array();
    if (!empty($img_arr)) {
        foreach ($img_arr as $key => $value) {
            if (!empty($value['name'])) {
                $img_path = $tconfig["tsite_upload_service_categories_images_path"];
                $temp_gallery = $img_path . '/';
                $image_object = $value['tmp_name'];
                $image_name = $value['name'];
                $check_file_query = "SELECT " . $key . " FROM service_categories where 	iServiceId='" . $id . "'";
                $check_file = $obj->MySQLSelect($check_file_query);
                if ($message_print_id != "") {
                    $check_file = $img_path . '/' . $check_file[0][$key];
                    if ($check_file != '' && file_exists($check_file[0][$key])) {
                        @unlink($check_file);
                    }
                }
                $Photo_Gallery_folder = $img_path . '/';
                if (!is_dir($Photo_Gallery_folder)) {
                    mkdir($Photo_Gallery_folder, 0777);
                }
                $img = $generalobj->imageupload($Photo_Gallery_folder, $image_object, $image_name, '', 'png,jpg,jpeg,gif');
                //$img = $generalobj->fileupload_home($Photo_Gallery_folder,$image_object,$image_name,'','png,jpg,jpeg,gif','');
                if ($img[2] == "1") {
                    $_SESSION['success'] = '0';
                    $_SESSION['var_msg'] = $img[1];
                    header("location:" . $backlink);
                }
                if (!empty($img[0])) {
                    $sql = "UPDATE service_categories SET " . $key . " = '" . $img[0] . "' WHERE iServiceId = '" . $id . "'";
                    $obj->sql_query($sql);
                }
            }
        }
    }
    $q = "INSERT INTO ";
    $where = '';
    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `iServiceId` = '" . $id . "'";
    }
    $serviceName = "";
    for ($i = 0; $i < count($vTitle_store); $i++) {
        $vValue = 'vServiceName_' . $db_master[$i]['vCode'];
        $serviceName .= ",`" . $vValue . "`='" . $_POST[$vTitle_store[$i]] . "'";
    }
    $jsonServiceDesc = $obj->cleanQuery(json_encode($tDescriptionArr));
    $query = $q . " `" . $tbl_name . "` SET " . trim($serviceName, ",") . ",`tDescription`='" . $jsonServiceDesc . "',`iDisplayOrder` = '" . $iDisplayOrder . "'" . $where;
    $obj->sql_query($query);
    $id = ($id != '') ? $id : $obj->GetInsertId();
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
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iServiceId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    //print_r($db_data);die;
    $vLabel = $id;
    if (count($db_data) > 0) {
        $tDescription = (array) json_decode($db_data[0]['tDescription']);
        foreach ($tDescription as $key => $value) {
            $userEditDataArr[$key] = $value;
        }
        for ($i = 0; $i < count($db_master); $i++) {
            foreach ($db_data as $key => $value) {
                $iServiceId = $value['iServiceId'];
                $vValue = 'vServiceName_' . $db_master[$i]['vCode'];
                $$vValue = $value[$vValue];
                $eStatus = $value['eStatus'];
                $iDisplayOrder = $value['iDisplayOrder'];
                $Image = $value['vImage'];
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
        <title>Admin | DeliveryAll Service Category <?= $action; ?></title>
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
                            <h2><?= $action; ?> DeliveryAll Service Category</h2>
                            <a href="service_category.php" class="back_link">
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
                            <form method="post" name="_service_category_form" id="_service_category_form" action="" enctype='multipart/form-data'>
                                <input type="hidden" name="id" value="<?= $id; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="service_category.php"/>
                                <?
                                if ($count_all > 0) {
                                    for ($i = 0; $i < $count_all; $i++) {
                                        $vCode = $db_master[$i]['vCode'];
                                        $vTitle = $db_master[$i]['vTitle'];
                                        $eDefault = $db_master[$i]['eDefault'];
                                        $vValue = 'vServiceName_' . $vCode;
                                        $required = ($eDefault == 'Yes') ? 'required' : '';
                                        $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                        $tDescription = 'tDescription_' . $vCode;
                                        $serviceDescValue = "";
                                        if (isset($userEditDataArr[$tDescription])) {
                                            $serviceDescValue = $userEditDataArr[$tDescription];
                                        }
                                        ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>DeliveryAll Service Category Name  (<?= $vTitle; ?>)<?php echo $required_msg; ?></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" value="<?= $$vValue; ?>" placeholder="<?= $vTitle; ?>Value" <?= $required; ?>>
                                            </div>
                                            <? if ($vCode == $default_lang && count($db_master) > 1) { ?>
                                                <div class="col-lg-6">
                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vServiceName');">Convert To All Language</button>
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <!--<div class="row">
                                            <div class="col-lg-12">
                                                <label>DeliveryAll  Service Category Description (<?= $vTitle; ?>) </label>
                                            </div>
                                            <div class="col-lg-6">
                                                <textarea <?= $required; ?> class="form-control" name="<?= $tDescription; ?>" id="<?= $tDescription; ?>" placeholder="<?= $vTitle; ?> Value"><?= $serviceDescValue; ?></textarea>                                              
                                            </div>
                                            <? if ($vCode == $default_lang && count($db_master) > 1) { ?>
                                                <div class="col-lg-6">
                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tDescription');">Convert To All Language</button>
                                                </div>
                                            <?php } ?>
                                        </div>-->
                                        <?
                                    }
                                }
                                ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Image</label>
                                    </div>
                                    <div class="col-lg-12">
                                        <?php
                                        if (!empty($Image)) {
                                            ?>
                                            <img src="<?= $tconfig["tsite_url"].'resizeImg.php?h=500&src='.$tconfig["tsite_upload_service_categories_images"] . '' . $Image; ?>" class="thumbnail" style="max-width: 250px; max-height: 250px"/>
                                        <?php } ?>
                                    </div>
                                    <div class="classfixed">&nbsp;</div>
                                    <div class="col-lg-6">
                                        <input type="file" class="form-control" name="vImage"  id="vImage" accept=".png,.jpg,.jpeg,.gif">
                                    </div>
                                </div>
                                <?php
                                $count1 = "select iServiceId from service_categories";
                                $cnt = $obj->MySQLSelect($count1);
                                $count = count($cnt);
                                ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label><?php echo $langage_lbl['LBL_DISPLAY_ORDER_FRONT'] ?><span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-2" id="showDisplayOrder001">
                                        <?php if ($action == 'EDIT') { ?>

                                            <input type="hidden" name="total" value="<?php echo $count; ?>" >
                                            <select name="iDisplayOrder" id="iDisplayOrder" class="form-control" required>
                                                <?php for ($i = 1; $i <= $count; $i++) { ?>
                                                    <option value="<?php echo $i ?>" 
                                                    <?php
                                                    if ($i == $count)
                                                        echo 'selected';
                                                    ?>> <?php echo $i ?> </option>
                                                        <?php } ?>
                                            </select>
                                        <?php }else { ?>
                                            <input type="hidden" name="total" value="<?php echo $iDisplayOrder; ?>">
                                            <select name="iDisplayOrder" id="iDisplayOrder" class="form-control" required>
                                                <?php for ($i = 1; $i <= $count; $i++) { ?>
                                                    <option value="<?php echo $i ?>"
                                                    <?php if ($i == $iDisplayOrder) echo 'selected'; ?>
                                                            > <?php echo $i ?> </option>
                                                        <?php } ?>
                                            </select>
                                        <?php } ?>
                                    </div>
                                </div>


                        </div>

                        <!-- 								<div class="row">
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
                                <?php if (($action == 'Edit' && $userObj->hasPermission('edit-service-category')) || ($action == 'Add' && $userObj->hasPermission('create-service-category'))) { ?>
                                    <input type="submit" class=" btn btn-default" name="submit" id="submit" value="<?= $action; ?> DeliveryAll Service Category">
                                    <input type="reset" value="Reset" class="btn btn-default">
                                <?php } ?>
                                <a href="service_category.php" class="btn btn-default back_link">Cancel</a>
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
<script type='text/javascript' src='../assets/js/jquery-ui.min.js'></script>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script>
                                            $(document).ready(function () {
                                                var referrer;
                                                if ($("#previousLink").val() == "") {
                                                    referrer = document.referrer;
                                                } else {
                                                    referrer = $("#previousLink").val();
                                                }

                                                if (referrer == "") {
                                                    referrer = "cuisine.php";
                                                } else {
                                                    $("#backlink").val(referrer);
                                                }
                                                $(".back_link").attr('href', referrer);
                                            });

                                            function getAllLanguageCode(textBoxId) {
                                                var def_lang = '<?= $default_lang ?>';
                                                var def_lang_name = '<?= $def_lang_name ?>';
                                                var getEnglishText = $('#' + textBoxId + '_' + def_lang).val();
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
                                                                $('#' + textBoxId + '_' + key[1]).val(Value);
                                                            });
                                                            $('#imageIcon').hide();
                                                        }
                                                    });
                                                }
                                            }
</script>