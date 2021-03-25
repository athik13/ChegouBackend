<?php
include_once('../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}

////$generalobjAdmin->check_member_login();
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$ksuccess = isset($_REQUEST['ksuccess']) ? $_REQUEST['ksuccess'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';

if (!$userObj->hasPermission('edit-driver-subscription') && $action=='Edit') {
    $_SESSION['success'] = 3;
    $_SESSION['var_msg'] = 'You do not have permission to update record';
    header("Location:driver_subscription.php");
    exit;
}
if (!$userObj->hasPermission('create-driver-subscription') && $action=='Add') {
    $_SESSION['success'] = 3;
    $_SESSION['var_msg'] = 'You do not have permission to create record';
    header("Location:driver_subscription.php");
    exit;
}

$script = 'DriverSubscription';
$tblname = 'driver_subscription_plan';

$curDetails = $obj->MySQLSelect("SELECT vSymbol FROM `currency` WHERE `eDefault` ='Yes' AND eStatus = 'Active'");
$CurrSymbol = $curDetails[0]['vSymbol'];

$vPlanName_arr = array();
$vPlanDesc_arr = array();

$sql = "SELECT * FROM `language_master` where eStatus='Active' ORDER BY eDefault,iDispOrder";
$db_master = $obj->MySQLSelect($sql);

$count_all = count($db_master);
if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
        $vValue = 'vPlanName_' . $db_master[$i]['vCode'];
        $vValue_desc = 'vPlanDescription_' . $db_master[$i]['vCode'];

        array_push($vPlanName_arr, $vValue);
        $$vValue = isset($_POST[$vValue]) ? $_POST[$vValue] : '';

        array_push($vPlanDesc_arr, $vValue_desc);
        $$vValue_desc = isset($_POST[$vValue_desc]) ? $_POST[$vValue_desc] : '';
    }
}


// set all variables with either post (when submit) either blank (when insert)
//$vPlanName = isset($_POST['vPlanName']) ? $_POST['vPlanName'] : '';
$ePlanValidity = isset($_POST['ePlanValidity']) ? $_POST['ePlanValidity'] : '';
$fPrice = isset($_POST['fPrice']) ? $_POST['fPrice'] : '';
$vPlanPeriod = isset($_POST['vPlanPeriod']) ? $_POST['vPlanPeriod'] : '';
//$vPlanDescription = isset($_POST['vPlanDescription']) ? $_POST['vPlanDescription'] : '';
$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : '';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

if (isset($_POST['submit'])) {

    if (SITE_TYPE == 'Demo') {
        header("Location:driver_subscription_action.php?id=" . $id . '&success=2');
        exit;
    }
    //Add Custom validation
    require_once("library/validation.class.php");
    $validobj = new validation();
    //$validobj->add_fields($_POST['vPlanName'], 'req', 'Plan Name is required');
    //$validobj->add_fields($_POST['ePlanValidity'], 'req', 'Email Address is required.');
    $validobj->add_fields($_POST['fPrice'], 'req', 'Price is required.');
    $error = $validobj->validate();
    if(!is_numeric($_POST['fPrice'])) {
        $error .= "Price must be in Proper Format";
    }
    if($_POST['vPlanPeriod']==0 || empty($_POST['vPlanPeriod'])) {
        $error .= "<br>Plan validity should not be zero";
    }
    if($_POST['ePlanValidity']=='Weekly' && $_POST['vPlanPeriod']>104) {
        $error .= "<br>Plan validity should not be greater than 104 weeks";
    }
    
    if($_POST['ePlanValidity']=='Monthly' && $_POST['vPlanPeriod']>24) {
        $error .= "<br>Plan validity should not be greater than 24 months";
    }
    
    if ($error) {
        $success = 3;
        $newError = $error;
    } else {
        
        for ($i = 0; $i < count($vPlanName_arr); $i++) {

        $vValue = 'vPlanName_' . $db_master[$i]['vCode'];
        $vValue_desc = 'vPlanDescription_' . $db_master[$i]['vCode'];
        
        $q = "INSERT INTO ";
        $where = '';
        if ($action == 'Add') {
            $str = ",`tSubscribeDate` = '" . date("Y-m-d H:i:s") . "'";
        } else {
            $str = '';
        }
        if ($id != '') {
            $q = "UPDATE ";
            $where = " WHERE `iDriverSubscriptionPlanId` = '" . $id . "'";
        }
        
                            
            $query = $q . " `" . $tblname . "` SET
			`vPlanName` = '" . $vPlanName . "',
			`vPlanPeriod` = '" . $vPlanPeriod . "',
			`ePlanValidity` = '" . $ePlanValidity . "',
                        " . $vValue_desc . " = '" .$_POST[$vPlanDesc_arr[$i]] . "',
			" . $vValue . " = '" . $_POST[$vPlanName_arr[$i]] . "',
			`fPrice` = '" . $fPrice . "',
			`vPlanDescription` = '" . $vPlanDescription . "'
                            $str
                        "
                . $where;
//         $query = $q . " `" . $tblname . "` SET
//			`vPlanName` = '" . $vPlanName . "',
//			`vPlanPeriod` = '" . $vPlanPeriod . "',
//			`ePlanValidity` = '" . $ePlanValidity . "',
//                            
//			`fPrice` = '" . $fPrice . "',
//			`vPlanDescription` = '" . $vPlanDescription . "'
//                            $str
//                        "
//                . $where;*/
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
}
// for Edit
$vPlanPeriod = 1;
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tblname . " WHERE iDriverSubscriptionPlanId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    
    if (count($db_data) > 0) {
        for ($i = 0; $i < count($db_master); $i++) {
            foreach ($db_data as $key => $value) {
                $vValue = 'vPlanName_' . $db_master[$i]['vCode'];
                $$vValue = $value[$vValue];
                $vValue_desc = 'vPlanDescription_' . $db_master[$i]['vCode'];
                $$vValue_desc = $value[$vValue_desc];
                //$vPlanName = $value['vPlanName_'.$default_lang];
                $vPlanPeriod = $value['vPlanPeriod'];
                $ePlanValidity = $value['ePlanValidity'];
                $fPrice = $value['fPrice'];
                //$vPlanDescription = $value['vPlanDescription_'.$default_lang];
                $iDriverSubscriptionPlanId = $value['iDriverSubscriptionPlanId'];
            }
        }
    }
    
    
    if (count($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $vPlanName = $value['vPlanName_'.$default_lang];
            $vPlanPeriod = $value['vPlanPeriod'];
            $ePlanValidity = $value['ePlanValidity'];
            $fPrice = $value['fPrice'];
            $vPlanDescription = $value['vPlanDescription_'.$default_lang];
            $iDriverSubscriptionPlanId = $value['iDriverSubscriptionPlanId'];
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
        <title><?= $SITE_NAME ?> | <?= $langage_lbl_admin['LBL_DRIVER_SUBSCRIPTION_PLAN'] ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <?
        include_once('global_files.php');
        ?>
        <!-- On OFF switch -->
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
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
                            <h2><?= $action; ?> Subscription <?= $vPlanName; ?></h2>
                            <a class="back_link" href="driver_subscription.php">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />
                    <div class="body-div">
                        <div class="form-group">
                            <? if ($success == 2) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                    <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                </div><br/>
                            <? } ?>
                            <? if ($success == 3) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                    <?php print_r($error); ?>
                                </div><br/>
                            <? } ?>
                            <form name="_subscription_form" id="_subscription_form" method="post" action="" enctype="multipart/form-data">
                                <input type="hidden" name="actionOf" id="actionOf" value="<?php echo $action; ?>"/>
                                <input type="hidden" name="id" id="id" value="<?php echo $iDriverSubscriptionPlanId; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="driver_subscription.php"/>
                                    
                                <!--<div class="row">-->
                                <?php
                                if ($count_all > 0) {
                                    for ($i = 0; $i < $count_all; $i++) {
                                        $vCode = $db_master[$i]['vCode'];
                                        $vTitle = $db_master[$i]['vTitle'];
                                        $eDefault = $db_master[$i]['eDefault'];

                                        $vValue = 'vPlanName_' . $vCode;

                                        $required = ($eDefault == 'Yes') ? 'required' : '';
                                        $required_msg = ($eDefault == 'Yes') ? '<span class="red"> * </span><i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Set Plan Name"></i>' : '';
                                        
                                        ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?php echo $langage_lbl['LBL_SUBSCRIPTION_PLAN_NAME'] ?>  (<?= $vTitle; ?>) <?= $required_msg; ?> </label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" value="<?= $$vValue; ?>" placeholder="<?= $vTitle; ?>" <?= $required; ?>>

                                            </div>
                                            <? if ($vCode == $default_lang && count($db_master) > 1) { ?>
                                                <div class="col-lg-6">
                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vPlanName');">Convert To All Language</button>
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <?php }
                                } ?>
<!--                                    <div class="col-lg-12">
                                        <label><?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_NAME']; ?><span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vPlanName"  id="vPlanName" value="<?= $vPlanName; ?>" placeholder="<?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_NAME']; ?>" required>
                                    </div>-->
                                <!--</div>-->
                                
                                <?php
                                if ($count_all > 0) {
                                    for ($i = 0; $i < $count_all; $i++) {
                                        $vCode = $db_master[$i]['vCode'];
                                        $vTitle = $db_master[$i]['vTitle'];
                                        $eDefault = $db_master[$i]['eDefault'];

                                        $vValue = 'vPlanDescription_' . $vCode;

                                        $required = ($eDefault == 'Yes') ? 'required' : '';
                                        $required_msg = ($eDefault == 'Yes') ? '<span class="red"> * </span><i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Give brief details about the plan"></i>' : '';
                                        ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?php echo $langage_lbl['LBL_SUBSCRIPTION_PLAN_DESCRIPTION'] ?>  (<?= $vTitle; ?>) <?= $required_msg; ?></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <!--<input type="text" class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" value="<?= $$vValue; ?>" placeholder="<?= $vTitle; ?>" <?= $required; ?>>-->
                                                <textarea placeholder="<?= $vTitle; ?>" class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" <?= $required; ?>><?= $$vValue; ?></textarea>
                                            </div>
                                            <? if ($vCode == $default_lang && count($db_master) > 1) { ?>
                                                <div class="col-lg-6">
                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vPlanDescription');">Convert To All Language</button>
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <?php }
                                } ?>
<!--                                <div class="row">
                                    <div class="col-lg-12">
                                        <label><?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_DESCRIPTION']; ?><span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vPlanDescription"  id="vPlanDescription" value="<?= $vPlanDescription; ?>" placeholder="<?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_DESCRIPTION']; ?>" required>
                                    </div>
                                </div>-->

                                <div class="row">
                                    <div class="col-lg-3">
                                        <label><?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_VALIDITY']; ?><span class="red"> * </span><i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Set the validity e.g. 1 Week, 3 Months.'></i></label>
                                    </div>
                                    <div class="col-lg-7">
                                        <label><?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_TYPE']; ?><span class="red"> * </span><i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Set the Plans Duration'></i></label>
                                    </div>
                                    <div class="col-lg-3">
                                        <input type="number" class="form-control" name="vPlanPeriod"  id="vPlanPeriod" value="<?= $vPlanPeriod; ?>" placeholder="<?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_PERIOD']; ?>" required min="1">
                                    </div>
                                    <div class="col-lg-3">
                                        <select name="ePlanValidity" class="form-control" required>
                                            <option value="">Select Plan</option>
                                            <!--<option value="Daily" <?php if($ePlanValidity == 'Daily') echo "Selected"; ?>>Daily</option>-->
                                            <option value="Weekly" <?php if($ePlanValidity == 'Weekly') echo "selected"; ?>><?php echo $langage_lbl_admin['LBL_SUB_WEEKS']; ?></option>
                                            <option value="Monthly" <?php if($ePlanValidity == 'Monthly') echo "Selected"; ?>><?php echo $langage_lbl_admin['LBL_SUB_MONTH']; ?></option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label><?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_PRICE']; ?> (<?php echo $CurrSymbol; ?>)<span class="red"> *</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Set plan price'></i></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="fPrice"  id="fPrice" value="<?= $fPrice; ?>" placeholder="<?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_PRICE']; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-lg-12">
                                       <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?php if ($action == 'Add') { ?><?= $action; ?> Subscription<?php } else { ?>Update<?php } ?>">
                                        <input type="reset" value="Reset" class="btn btn-default">
                                        <a href="driver_subscription.php" class="btn btn-default back_link">Cancel</a>
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
        
        <?
        include_once('footer.php');
        ?>
    </body>
    <!-- END BODY-->
</html>
<script>
    $(document).ready(function () {
        var referrer;
        if ($("#previousLink").val() == "") {
            referrer = document.referrer;
        } else {
            referrer = $("#previousLink").val();
        }
        if (referrer == "") {
            referrer = "driver_subscription.php";
        } else {
            $("#backlink").val(referrer);
        }
        $(".back_link").attr('href', referrer);
    });
    
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

$('#_subscription_form').validate({
    rules: {
       fPrice:{
                required: true, number: true, min:1
            }
    } 
});
</script>
