<?php
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$eSystem = "DeliverAll";
define("TRIP_REASON", "trip_reason");
define("USER_PROFILE_MASTER", "user_profile_master");
$script = 'BusinessTripReason';
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$sql = "SELECT vCode,vTitle,eDefault FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$lableNameArr = array("vReasonTitle");
$lableArr = array("Reason");
$sql = "SELECT * FROM " . USER_PROFILE_MASTER . " WHERE eStatus !='Deleted'";
$data_drv = $obj->MySQLSelect($sql);
$userDataArr = array();
$profileMasterId = 0;
for ($u = 0; $u < count($data_drv); $u++) {
    $shortProfileName = (array) json_decode($data_drv[$u]['vShortProfileName']);
    $profileName = (array) json_decode($data_drv[$u]['vProfileName']);
    $title = (array) json_decode($data_drv[$u]['vTitle']);
    $subTitle = (array) json_decode($data_drv[$u]['vSubTitle']);
    $eng_arr = array();
    $eng_arr['iUserProfileMasterId'] = $data_drv[$u]['iUserProfileMasterId'];
    $eng_arr['vShortProfileName'] = $shortProfileName['vShortProfileName_'.$default_lang];
    $eng_arr['vProfileName'] = $profileName['vProfileName_'.$default_lang];
    $eng_arr['vTitle'] = $title['vTitle_'.$default_lang];
    $eng_arr['vSubTitle'] = $subTitle['vSubTitle_'.$default_lang];
    $eng_arr['eStatus'] = $data_drv[$u]['eStatus'];
    $userDataArr[] = $eng_arr;
}
if (isset($_POST['btnsubmit'])) {
    if ($action == "Add" && !$userObj->hasPermission('create-trip-reason')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create ' . strtolower($langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']);
        header("Location:trip_reason.php");
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission('edit-trip-reason')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update ' . strtolower($langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']);
        header("Location:trip_reason.php");
        exit;
    }
    if (SITE_TYPE == 'Demo') {
        header("Location:trip_reason.php?id=" . $id . "&success=2");
        exit;
    }
    $reasonTitleArr = array();
    if (isset($_POST['iUserProfileMasterId']) && $_POST['iUserProfileMasterId'] != "") {
        $profileMasterId = $_POST['iUserProfileMasterId'];
    }
    for ($i = 0; $i < count($db_master); $i++) {
        $vTitle = "";
        if (isset($_POST['vReasonTitle_' . $db_master[$i]['vCode']])) {
            $vTitle = $_POST['vReasonTitle_' . $db_master[$i]['vCode']];
        }
        $q = "INSERT INTO ";
        $where = '';
        if ($id != '') {
            $q = "UPDATE ";
            $where = " WHERE `iTripReasonId` = '" . $id . "'";
        }
        $reasonTitleArr["vReasonTitle_" . $db_master[$i]['vCode']] = $vTitle;
    }
    $time = time();
    if (count($reasonTitleArr) > 0) {
        //$jsonTitle = $obj->cleanQuery(json_encode($reasonTitleArr));
        $jsonTitle = $generalobj->getJsonFromAnArr($reasonTitleArr);
        $query = $q . " `" . TRIP_REASON . "` SET `vReasonTitle` = '" . $jsonTitle . "',`iUserProfileMasterId`='" . $profileMasterId . "'" . $where;
        $obj->sql_query($query);
        $id = ($id != '') ? $id : $obj->GetInsertId();
    }
    if ($action == "Add") {
        $_SESSION['var_msg'] = $langage_lbl['LBL_RECORD_INSERT_MSG'];
        $_SESSION['success'] = "1";
        header("Location:trip_reason.php");
        exit;
    } else {
        $_SESSION['var_msg'] = $langage_lbl['LBL_Record_Updated_successfully'];
        $_SESSION['success'] = "1";
        header("Location:trip_reason.php");
        exit;
    }
}
// for Edit
$userEditDataArr = array();
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . TRIP_REASON . " WHERE iTripReasonId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    if (count($db_data) > 0) {
        $vReasonTitle = (array) json_decode($db_data[0]['vReasonTitle']);
        foreach ($vReasonTitle as $key => $value) {
            $userEditDataArr[$key] = $value;
        }
        $profileMasterId = $db_data[0]['iUserProfileMasterId'];
    }
}
//print_R($userEditDataArr);die;
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title>Admin | Business Trip Reason <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <? include_once('global_files.php'); ?>
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
                            <h2> Business Trip Reason </h2>
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
                                    <?= $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                                </div><br/>
                            <? } elseif ($success == 2) { ?>
                                <div class="alert alert-danger alert-dismissable ">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                    <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                </div><br/>
                            <? } else if ($success == 3) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                    <?php echo $_REQUEST['varmsg']; ?> 
                                </div><br/>	
                            <? } ?>
                            <? if ($_REQUEST['var_msg'] != Null) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    Record  Not Updated .
                                </div><br/>
                            <? } ?>                   
                            <form id="_vehicleType_form" name="_vehicleType_form" method="post" action="" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?= $id; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="trip_reason.php"/>
                                <div class="row"> 
                                    <div class="col-lg-12" id="errorMessage"></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Select Organization type</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <!--<select class="form-control" name = 'iUserProfileMasterId' id="iUserProfileMasterId" required="" onchange="changeCode_distance(this.value);"> Commented on 29-02-2020 Its not defined function-->
										<select class="form-control" name = 'iUserProfileMasterId' id="iUserProfileMasterId" required="">
                                            <option value="">Select Organization type</option>
                                            <?php
                                            for ($p = 0; $p < count($userDataArr); $p++) {
                                                ?>
                                                <option value = "<?= $userDataArr[$p]['iUserProfileMasterId'] ?>" <? if ($profileMasterId == $userDataArr[$p]['iUserProfileMasterId']) { ?>selected<? } ?>><?= $userDataArr[$p]['vProfileName'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <?
                                if (count($db_master) > 0) {
                                    for ($i = 0; $i < count($db_master); $i++) {
                                        $vCode = $db_master[$i]['vCode'];
                                        $vTitle = $db_master[$i]['vTitle'];
                                        $eDefault = $db_master[$i]['eDefault'];
                                        $descVal = 'tDescription_' . $vCode;
                                        for ($l = 0; $l < count($lableNameArr); $l++) {
                                            $lableText = $lableArr[$l];
                                            $lableName = $lableNameArr[$l] . '_' . $vCode;
                                            $required = ($eDefault == 'Yes') ? 'required' : '';
                                            $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                            ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label><?= $lableText; ?> (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <input type="text" class="form-control" name="<?= $lableName; ?>" id="<?= $lableName; ?>" value="<?= $userEditDataArr[$lableName]; ?>" placeholder="<?= $vTitle; ?> Value" <?= $required; ?>>
                                                </div>
                                                <? if ($vCode == $default_lang && count($db_master) > 1) { ?>
                                                    <div class="col-lg-6">
                                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('<? echo $lableNameArr[$l]; ?>');">Convert To All Language</button>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <?
                                        }
                                    }
                                }
                                ?>
                                <div class="col-lg-12">
                                    <?php if (($action == 'Edit' && $userObj->hasPermission('edit-trip-reason')) || ($action == 'Add' && $userObj->hasPermission('create-trip-reason'))) { ?>
                                        <input type="submit" class="btn btn-default" name="btnsubmit" id="btnsubmit" value="<?= $action; ?> Reason" >
                                        <input type="reset" value="Reset" class="btn btn-default">
                                    <?php } ?>
                                    <a href="trip_reason.php" class="btn btn-default back_link">Cancel</a>
                                </div>          
                            </form>
                        </div>      
                    </div>
                </div>
                <div style="clear:both;"></div>
            </div>
        </div>
        <!--END PAGE CONTENT -->
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
        <script>
                                                            $('[data-toggle="tooltip"]').tooltip();
                                                            var successMSG1 = '<?php echo $success; ?>';
                                                            if (successMSG1 != '') {
                                                                setTimeout(function () {
                                                                    $(".msgs_hide").hide(1000)
                                                                }, 5000);
                                                            }
        </script>
        <!--For Faretype End--> 
        <script type="text/javascript" language="javascript">
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

            $(document).ready(function () {
                var referrer;
                if ($("#previousLink").val() == "") {
                    referrer = document.referrer;
                } else {
                    referrer = $("#previousLink").val();
                }
                if (referrer == "") {
                    referrer = "trip_reason.php";
                } else {
                    $("#backlink").val(referrer);
                }
                $(".back_link").attr('href', referrer);
            });
        </script>
    </body>
    <!-- END BODY-->
</html>
