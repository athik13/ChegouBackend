<?
include_once('../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();
$script = 'language_label_other';

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : '';
$action = ($id != '') ? 'Edit' : 'Add';

$tbl_name = 'language_label_other';

$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

//echo '<prE>'; print_R($_REQUEST); echo '</pre>';
// fetch all lang from language_master table
$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = count($db_master);
//echo '<pre>'; print_R($db_master); echo '</pre>';
// set all variables with either post (when submit) either blank (when insert)
$vLabel = isset($_POST['vLabel']) ? $_POST['vLabel'] : $id;
if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
        $vValue = 'vValue_' . $db_master[$i]['vCode'];
        $$vValue = isset($_POST[$vValue]) ? $_POST[$vValue] : '';
    }
}

if (isset($_POST['submit'])) {

    if ($id == '') {
        $sql = "SELECT * FROM `language_label` WHERE vLabel = '" . $vLabel . "'";
        $db_label_check = $obj->MySQLSelect($sql);
        if (count($db_label_check) > 0) {
            $var_msg = "Language label already exists in general label";
            header("Location:languages_action_admin.php?var_msg=" . $var_msg . '&success=0');
            exit;
        }

        $sql = "SELECT * FROM `language_label_other` WHERE vLabel = '" . $vLabel . "'";
        $db_label_check_ride = $obj->MySQLSelect($sql);
        if (count($db_label_check_ride) > 0) {
            $var_msg = "Language label already exists in ride label";
            header("Location:languages_action_admin.php?var_msg=" . $var_msg . '&success=0');
            exit;
        }
    }

    if (SITE_TYPE == 'Demo') {
        header("Location:languages_action_admin.php?id=" . $vLabel . '&success=2');
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
				`vCode` = '" . $db_master[$i]['vCode'] . "',
				`vValue` = '" . $$vValue . "'"
                    . $where;

            $obj->sql_query($query);
        }
    }

    //header("Location:languages_admin.php?id=".$vLabel.'&success=1');
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

    header("location:" . $backlink);
}

// for Edit
if ($action == 'Edit') {


    /* $sql = "SELECT * FROM ".$tbl_name." WHERE vLabel = '".$id."'";
      $db_data = $obj->MySQLSelect($sql);
      echo '<pre>'; print_R($db_data); echo '</pre>'; exit;
      $vLabel = $id; */

    $sql = "SELECT vLabel FROM " . $tbl_name . " WHERE LanguageLabelId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    //print_R($db_data[0]['vLabel']);die;
    $sql = "SELECT * FROM " . $tbl_name . " WHERE vLabel = '" . $db_data[0]['vLabel'] . "'";
    $db_data = $obj->MySQLSelect($sql);
    //echo '<pre>'; print_R($db_data); echo '</pre>'; exit;
    //$vLabel = $id;
    $vLabel = $db_data[0]['vLabel'];
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
                // alert(def_lang);
                var getEnglishText = $('#vValue_' + def_lang).val();
                var error = false;
                var msg = '';

                if (getEnglishText == '') {
                    msg += '<div class="alert alert-danger fade in"><a href="#" class="close" data-dismiss="alert"><icon class="fa fa-close"></icon></a><strong>Please enter ' + def_lang_name + ' value</strong></div> <br>';
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
                            <a href="languages_admin.php" class="back_link">
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
                            <form method="post" name="_language_label_other_form" id="_language_label_other_form" action="">
                                <input type="hidden" name="id" value="<?= $id; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="languages_admin.php"/>
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
                                                <input type="text" class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" value="<?= $$vValue; ?>" placeholder="<?= $vTitle; ?> Value" <? //=$required; ?>>
                                            </div>
        <?php if ($vCode == $default_lang && count($db_master) > 1) { ?>
                                                <div class="col-lg-2">
                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onclick="getAllLanguageCode();">Convert To All Language</button>
                                                </div>
                                                <div class="col-lg-2">
                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="CopyAllLanguageCode();">Copy <?= $def_lang_name ?> To All</button>
                                                </div>
        <?php } ?>
                                        </div>
                                        <? }
                                    }
                                    ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?= $action; ?> Label">
                                        <input type="reset" value="Reset" class="btn btn-default">
                                        <!-- <a href="javascript:void(0);" onclick="reset_form('_language_label_other_form');" class="btn btn-default">Reset</a> -->
                                        <a href="languages_admin.php" class="btn btn-default back_link">Cancel</a>
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
        <div class="row loding-action" id="imageIcon">
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
