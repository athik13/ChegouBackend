<?
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();
require_once(TPATH_CLASS . "Imagecrop.class.php");
$thumb = new thumbnail();

//$default_lang = $generalobj->get_default_lang();
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$action = ($id != '') ? 'Edit' : 'Add';

//$temp_gallery = $tconfig["tpanel_path"];
$tbl_name = 'order_status';
$script = 'order_status';


// fetch all lang from language_master table 
$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = count($db_master);

// set all variables with either post (when submit) either blank (when insert)
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';

$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';
$thumb = new thumbnail();
/* to fetch max iDisplayOrder from table for insert */
$select_order = $obj->MySQLSelect("SELECT MAX(iDisplayOrder) AS iDisplayOrder FROM " . $tbl_name);
$iDisplayOrder = isset($select_order[0]['iDisplayOrder']) ? $select_order[0]['iDisplayOrder'] : 0;
$iDisplayOrder = $iDisplayOrder + 1; // Maximum order number

$iOrderStatusId = isset($_POST['iOrderStatusId']) ? $_POST['iOrderStatusId'] : 0;
$iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : $iDisplayOrder;
$temp_order = isset($_POST['temp_order']) ? $_POST['temp_order'] : "";
//echo '<pre>';print_r($db_master);exit;
if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
        $vTitle = 'vStatus_' . $db_master[$i]['vCode'];
        $$vTitle = isset($_POST[$vTitle]) ? $_POST[$vTitle] : '';
        $vDesc = 'vStatus_Track_' . $db_master[$i]['vCode'];
        $$vDesc = isset($_POST[$vDesc]) ? $_POST[$vDesc] : '';
    }
}


if (isset($_POST['submit'])) { //form submit
    if ($action == "Add" && !$userObj->hasPermission('create-order-status')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create order status.';
        header("Location:order_status.php");
        exit;
    }

    if ($action == "Edit" && !$userObj->hasPermission('edit-order-status')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update order status.';
        header("Location:order_status.php");
        exit;
    }

    if (!empty($iOrderStatusId)) {
        if (SITE_TYPE == 'Demo') {
            header("Location:order_status_action.php?id=" . $id . "&success=2");
            exit;
        }
    }

    //echo "<pre>";print_r($_REQUEST);echo '</pre>'; echo $temp_order.'=='.$iDisplayOrder;
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


    $q = "INSERT INTO ";
    $where = '';

    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `iOrderStatusId` = '" . $id . "'";
    }
    $sql_str = '';
    if ($count_all > 0) {
        for ($i = 0; $i < $count_all; $i++) {
            $vTitle = 'vStatus_' . $db_master[$i]['vCode'];
            $vDesc = 'vStatus_Track_' . $db_master[$i]['vCode'];
            $sql_str .= $vTitle . " = '" . $$vTitle . "',";
            $sql_str .= $vDesc . " = '" . $$vDesc . "',";
        }
    }

    $query = $q . " `" . $tbl_name . "` SET  " . $sql_str . "
				`iDisplayOrder` = '" . $iDisplayOrder . "'"
            . $where;
    $obj->sql_query($query);
    // print_r($query);
    // exit;		

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
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iOrderStatusId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    //echo '<pre>'; print_R($db_data); echo '</pre>'; exit;

    if ($count_all > 0) {
        for ($i = 0; $i < $count_all; $i++) {
            $vTitle = 'vStatus_' . $db_master[$i]['vCode'];
            $$vTitle = isset($db_data[0][$vTitle]) ? $db_data[0][$vTitle] : $$vTitle;
            $vDesc = 'vStatus_Track_' . $db_master[$i]['vCode'];
            $$vDesc = isset($db_data[0][$vDesc]) ? $db_data[0][$vDesc] : $$vDesc;
            $iDisplayOrder = $db_data[0]['iDisplayOrder'];
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
        <title>Admin | Home Page <?= $langage_lbl_admin["LBL_CANCEL_REASON_TXT_ADMIN"]; ?>  <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <script type="text/javascript" language="javascript">

            function getAllLanguageCode() {
                var def_lang = '<?= $default_lang ?>';
                var getEnglishText = $('#vStatus_' + def_lang).val();
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
                                var key = name.split('_');
                                $('#vStatus_' + key[1]).val(Value);
                            });
                            $('#imageIcon').hide();
                        }
                    });
                }


            }

            function getAllLanguageCode1() {
                var def_lang = '<?= $default_lang ?>';
                var getEnglishText = $('#vStatus_Track_' + def_lang).val();
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
                                var key = name.split('_');
                                $('#vStatus_Track_' + key[1]).val(Value);
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
                            <h2><?= $action; ?> Order Status</h2>
                            <a href="order_status.php">
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

                            <form method="post" action="" enctype="multipart/form-data" id="order_status_action" name="order_status_action">
                                <input type="hidden" name="id" value="<?= $id; ?>"/>
                                <input type="hidden" name="temp_order" id="temp_order" value="1	">
                                <input type="hidden" name="vImage_old" value="<?= $vImage ?>">
                                <input type="hidden" name="backlink" id="backlink" value="order_status.php"/>


<?php
//echo '<pre>';print_r($db_data);exit;
//echo '<pre>';print_r($db_master);exit;
if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
        $vCode = $db_master[$i]['vCode'];
        $vTitle = $db_master[$i]['vTitle'];

        $vTitle_val = "vStatus_" . $vCode;
        $vDesc_val = "vStatus_Track_" . $vCode;
        $eDefault = $db_master[$i]['eDefault'];
        $label_title = "Status Title(" . $vTitle . ")";
        $label_desc = "Status Description(" . $vTitle . ")";
        $required = ($eDefault == 'Yes') ? 'required' : '';

        $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
        ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?= $label_title; ?> <?= $required_msg; ?></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="<?= $vTitle_val; ?>"  id="<?= $vTitle_val; ?>" value="<?= $$vTitle_val; ?>" placeholder="<?= $label_title; ?>" <?= $required; ?>>
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

                                    if ($count_all > 0) {
                                        for ($i = 0; $i < $count_all; $i++) {
                                            $vCode = $db_master[$i]['vCode'];
                                            $vTitle = $db_master[$i]['vTitle'];
                                            $vDesc_val = "vStatus_Track_" . $vCode;
                                            $eDefault = $db_master[$i]['eDefault'];
                                            $label_title = "Status Title(" . $vTitle . ")";
                                            $label_desc = "Status Description(" . $vTitle . ")";
                                            $required = ($eDefault == 'Yes') ? 'required' : '';

                                            $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                            ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?= $label_desc; ?> <?= $required_msg; ?></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <textarea name="<?= $vDesc_val; ?>"  id="<?= $vDesc_val; ?>" class="form-control" rows="5" placeholder="<?= $label_desc; ?>"><?= $$vDesc_val; ?></textarea>
                                            </div>
        <?php
        if ($vCode == $default_lang) {
            ?>
                                                <div class="col-lg-6">
                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode1();">Convert To All Language</button>
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
<?php if (($action == 'Edit' && $userObj->hasPermission('edit-order-status')) || ($action == 'Add' && $userObj->hasPermission('create-order-status'))) { ?>
                                            <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?= $action; ?> Order Status" >
                                            <input type="reset" value="Reset" class="btn btn-default">
<?php } ?>
                                        <!-- <a href="javascript:void(0);" onclick="reset_form('cancel_reason_action');" class="btn btn-default">Reset</a> -->
                                        <a href="order_status.php" class="btn btn-default back_link">Cancel</a>
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
                                                                                                                formWysiwyg(); });
        </script>
    </body>
    <!-- END BODY-->    
</html>