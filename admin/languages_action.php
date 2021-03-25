<?
include_once('../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();
$script = 'language_label';

$catdata = serviceCategories;
$allservice_cat_data = json_decode($catdata, true);
$selectedlanguage = isset($_REQUEST['selectedlanguage']) ? stripslashes($_REQUEST['selectedlanguage']) : '';

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
// $pageid 		= isset($_REQUEST['lp_id'])?$_REQUEST['lp_id']:0;
$lp_name = isset($_REQUEST['lp_name']) ? $_REQUEST['lp_name'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : '';
$action = ($id != '') ? 'Edit' : 'Add';
if (!empty($selectedlanguage)) {
    $tbl_name = 'language_label_' . $selectedlanguage;
} else {
    $tbl_name = 'language_label';
}
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';


// fetch all lang from language_master table
$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = count($db_master);

// set all variables with either post (when submit) either blank (when insert)
$vLabel = isset($_POST['vLabel']) ? $_POST['vLabel'] : $id;
$lPage_id = isset($_POST['lPage_id']) ? $_POST['lPage_id'] : '';
if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
        $vValue = 'vValue_' . $db_master[$i]['vCode'];
        $$vValue = isset($_POST[$vValue]) ? $_POST[$vValue] : '';
    }
}

if (isset($_POST['submit'])) {

    if ($action == "Add" && !$userObj->hasPermission('create-general-label')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create Language Label.';
        header("Location:company_action.php");
        exit;
    }

    if ($action == "Edit" && !$userObj->hasPermission('edit-general-label')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update Language Label.';
        header("Location:company_action.php");
        exit;
    }

    if ($id == '') {
        $sql = "SELECT * FROM " . $tbl_name . " WHERE vLabel = '" . $vLabel . "'";
        $db_label_check = $obj->MySQLSelect($sql);
        if (count($db_label_check) > 0) {
            $var_msg = "Language label already exists in general label";
            header("Location:languages_action.php?var_msg=" . $var_msg . '&success=0');
            exit;
        }

        /* $sql = "SELECT * FROM `language_label_other` WHERE vLabel = '".$vLabel."'";
          $db_label_check_ride = $obj->MySQLSelect($sql);
          if(count($db_label_check_ride) > 0){
          $var_msg = "Language Label Already Exists In Ride Label";
          header("Location:languages_action.php?var_msg=".$var_msg.'&success=0');
          exit;
          } */
    }

    if (SITE_TYPE == 'Demo') {
        header("Location:languages_action.php?id=" . $vLabel . '&success=2');
        exit;
    }

    if ($count_all > 0) {
        for ($i = 0; $i < $count_all; $i++) {

            $q = "INSERT INTO ";
            $where = '';

            if ($id != '') {
                $q = "UPDATE ";
                $sql = "SELECT vLabel FROM " . $tbl_name . " WHERE LanguageLabelId = '" . $id . "'";
                $db_data = $obj->MySQLSelect($sql);
                $sql = "SELECT * FROM " . $tbl_name . " WHERE vLabel = '" . $db_data[0]['vLabel'] . "'";
                $db_data = $obj->MySQLSelect($sql);
                $vLabel = $db_data[0]['vLabel'];
                $where = " WHERE `vLabel` = '" . $vLabel . "' AND vCode = '" . $db_master[$i]['vCode'] . "'";
            }

            $vValue = 'vValue_' . $db_master[$i]['vCode'];

            $query = $q . " `" . $tbl_name . "` SET
				`vLabel` = '" . $vLabel . "',
				`lPage_id` = '" . $lPage_id . "',
				`vCode` = '" . $db_master[$i]['vCode'] . "',
				`vValue` = '" . $$vValue . "'"
                    . $where;

            $obj->sql_query($query);
        }
    }

    //header("Location:languages.php?id=".$vLabel.'&success=1');
    if ($action == "Add") {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    } else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    }

    $query = "UPDATE register_driver SET eChangeLang = 'Yes' WHERE 1=1";
    $obj->sql_query($query);

    $query1 = "UPDATE register_user SET eChangeLang = 'Yes' WHERE 1=1";
    $obj->sql_query($query1);

    $query1 = "UPDATE company SET eChangeLang = 'Yes' WHERE 1=1";
    $obj->sql_query($query1);

    header("location:" . $backlink);
}

// for Edit
if ($action == 'Edit') {
    $sql = "SELECT vLabel FROM " . $tbl_name . " WHERE LanguageLabelId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);

    $sql = "SELECT * FROM " . $tbl_name . " WHERE vLabel = '" . $db_data[0]['vLabel'] . "'";
    $db_data = $obj->MySQLSelect($sql);

    //$vLabel = $id;
    $vLabel = $db_data[0]['vLabel'];
    $lPage_id = $db_data[0]['lPage_id'];
    if (count($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $vValue = 'vValue_' . $value['vCode'];
            $$vValue = $value['vValue'];
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
        <title>Admin | Language <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <script type="text/javascript" language="javascript">

            function getAllLanguageCode() {
                var def_lang = '<?= $default_lang ?>';
                var def_lang_name = '<?= $def_lang_name ?>';
                var getEnglishText = $('#vValue_' + def_lang).val();
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
                                $('#' + name).val(Value);
                            });
                            $('#imageIcon').hide();
                        }
                    });
                }
            }

        </script>
        <? include_once('global_files.php'); ?>
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
                            <h2><?= $action; ?> Language Label</h2>
                            <a href="languages.php" class="back_link">
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
                            <? } elseif ($success == 0 && $var_msg != '') { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?= $var_msg; ?>
                                </div><br/>
                            <? } ?>
                            <form method="post" name="_languages_form" id="_languages_form" action="">
                                <input type="hidden" name="id" value="<?= $id; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="languages.php"/>
                                <div class="row">
                                    <div class="col-lg-12" id="errorMessage">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Language Label<?= ($id != '') ? '' : '<span class="red"> *</span>'; ?></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vLabel"  id="vLabel" value="<?= $vLabel; ?>" placeholder="Language Label" <?= ($id != '') ? 'disabled' : 'required'; ?>>
                                    </div>
                                </div>
                                <? if ($SITE_VERSION == "v5") { ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Page ID</label>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="text" class="form-control" name="lPage_id"  id="lPage_id" value="<?= $lPage_id; ?>" placeholder="Page id">
                                        </div>
                                    </div>
                                <? } ?>
                                <?
                                if ($count_all > 0) {
                                    for ($i = 0; $i < $count_all; $i++) {
                                        $vCode = $db_master[$i]['vCode'];
                                        $vTitle = $db_master[$i]['vTitle'];
                                        $eDefault = $db_master[$i]['eDefault'];

                                        $vValue = 'vValue_' . $vCode;

                                        if ($vCode != $default_lang) {
                                            $vValue_arr[] = $vValue;
                                        }

                                        $required = ($eDefault == 'Yes') ? 'required' : '';
                                        $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                        ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?= $vTitle; ?> Value <?php echo $required_msg; ?></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" value="<?= htmlentities($$vValue); ?>" placeholder="<?= $vTitle; ?> Value" <?= $required; ?>>
                                            </div>
                                            <?php
                                            if ($vCode == $default_lang && count($db_master) > 1) {
                                                ?>
                                                <div class="col-lg-2">
                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode();">Convert To All Language</button>
                                                </div>
                                                <div class="col-lg-2">
                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="CopyAllLanguageCode();">Copy <?= $def_lang_name ?> To All</button>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    <? }
                                }
                                ?>
                                <div class="row">
                                    <div class="col-lg-12">
<?php if (($action == 'Edit' && $userObj->hasPermission('edit-general-label')) || ($action == 'Add' && $userObj->hasPermission('create-general-label'))) { ?>
                                            <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?= $action; ?> Label">
                                            <input type="reset" value="Reset" class="btn btn-default">
<?php } ?>
                                        <!-- <a href="javascript:void(0);" onclick="reset_form('_languages_form');" class="btn btn-default">Reset</a> -->
                                        <a href="languages.php" class="btn btn-default back_link">Cancel</a>
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
    </body>
    <!-- END BODY-->
</html>
<script type="text/javascript" language="javascript">
    $(document).ready(function () {

        $('#imageIcon').hide();

        $("form[name='_languages_form']").submit(function () {
            var idvalue = $("input[name=id]").val();
            var vLabel = $("input[name=vLabel]").val();
            if (idvalue == '') {
                if (vLabel.match("^LBL_")) {
                    return true;
                } else {
                    alert('Please Add Language Label Start with \"LBL_\".');
                    return false;
                }

            } else {
                return true;
            }
        });

    });

    function CopyAllLanguageCode() {
        var def_lang = '<?= $default_lang ?>';
        var def_lang_name = '<?= $def_lang_name ?>';
        var getEnglishText = $('#vValue_' + def_lang).val();
        var vNameArray = <?php echo json_encode($vValue_arr); ?>;
        //var vName = 'FN';
        if (getEnglishText != '') {
            jQuery.each(vNameArray, function (i, val) {
                document.getElementById(val).value = getEnglishText;
            });

        } else {
            alert("Please Fill " + def_lang_name + " value for copy text in other field.");
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
            referrer = "page.php";
        } else {
            $("#backlink").val(referrer);
        }
        $(".back_link").attr('href', referrer);
    });
</script>



