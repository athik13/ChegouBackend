<?
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$vPurpose = isset($_REQUEST['vPurpose']) ? $_REQUEST['vPurpose'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$tbl_name = $script = 'email_templates';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
// fetch all lang from language_master table
$sql = "SELECT * FROM `language_master` ORDER BY `eDefault`";
$db_master = $obj->MySQLSelect($sql);
$count_all = count($db_master);
// set all variables with either post (when submit) either blank (when insert)
$iEmailId = isset($_POST['iEmailId']) ? $_POST['iEmailId'] : $id;
if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
        $vSubject = 'vSubject_' . $db_master[$i]['vCode'];
        $$vSubject = isset($_POST[$vSubject]) ? $_POST[$vSubject] : '';
        $vBody = 'vBody_' . $db_master[$i]['vCode'];
        $$vBody = isset($_POST[$vBody]) ? $_POST[$vBody] : '';
    }
}
if (isset($_POST['submit'])) {
    if ($action == "Add" && !$userObj->hasPermission('create-email-templates')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create Email Templates.';
        header("Location:email_template.php");
        exit;
    }

    if ($action == "Edit" && !$userObj->hasPermission('edit-email-templates')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update Email Templates.';
        header("Location:email_template.php");
        exit;
    }
    if (SITE_TYPE == 'Demo') {
        header("Location:email_template_action.php?id=" . $iEmailId . '&success=2');
        exit;
    }
    if (count($db_master) > 0) {
        $str = '';
        for ($i = 0; $i < count($db_master); $i++) {
            $vSubject = 'vSubject_' . $db_master[$i]['vCode'];
            $$vSubject = $_REQUEST[$vSubject];
            $vBody = 'vBody_' . $db_master[$i]['vCode'];
            $$vBody = $_REQUEST[$vBody];

            $str .= " " . $vSubject . " = '" . ($$vSubject) . "', " . $vBody . " = '" . ($$vBody) . "', ";
        }
    }
    $q = "INSERT INTO ";
    $where = '';

    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `iEmailId` = '" . $iEmailId . "'";
    }
    $query = $q . " `" . $tbl_name . "` SET " . $str . "
		`vPurpose` = '" . $vPurpose . "'"
            . $where;
    $Id = $obj->sql_query($query);
    if ($action == 'Add') {
        $iEmailId = $obj->GetInsertId();
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
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iEmailId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    $vLabel = $id;
    if (count($db_data) > 0) {
        for ($i = 0; $i < count($db_master); $i++) {
            foreach ($db_data as $key => $value) {
                $vSubject = 'vSubject_' . $db_master[$i]['vCode'];
                $$vSubject = $value[$vSubject];
                $vBody = 'vBody_' . $db_master[$i]['vCode'];
                $$vBody = $value[$vBody];
                //$vEmail_Code = $value['vEmail_Code'];
                $vPurpose = $value['vPurpose'];
                $vSection = $value['vSection'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<head>
    <meta charset="UTF-8" />
    <title>Admin | Email Template <?= $action; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
    <? include_once('global_files.php'); ?>
    <!-- PAGE LEVEL STYLES -->
    <link rel="stylesheet" href="../assets/plugins/Font-Awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="../assets/plugins/wysihtml5/dist/bootstrap-wysihtml5-0.0.2.css" />
    <link rel="stylesheet" href="../assets/css/Markdown.Editor.hack.css" />
    <link rel="stylesheet" href="../assets/plugins/CLEditor1_4_3/jquery.cleditor.css" />
    <link rel="stylesheet" href="../assets/css/jquery.cleditor-hack.css" />
    <link rel="stylesheet" href="../assets/css/bootstrap-wysihtml5-hack.css" />
    <script type="text/javascript">
        (function () {
            var converter1 = Markdown.getSanitizingConverter();
            var editor1 = new Markdown.Editor(converter1);
            editor1.run();
        });
    </script>
    <style>
        ul.wysihtml5-toolbar > li {
            position: relative;
        }
    </style>
</head>
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
                        <h2><?= $action; ?> Email Template</h2>
                        <a href="email_template.php" class="back_link">
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
                        <form method="post" name="_email_template_form" id="_email_template_form" action=""  enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?= $id; ?>"/>
                            <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                            <input type="hidden" name="backlink" id="backlink" value="email_template.php"/>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Purpose</label>
                                </div>
                                <div class="col-lg-6">
                                    <input type="text" class="form-control" name="vPurpose"  id="vPurpose" value="<?= $vPurpose; ?>" placeholder="Purpose">
                                </div>
                            </div>
                            <?
                            if ($count_all > 0) {
                                for ($i = 0; $i < $count_all; $i++) {
                                    $vCode = $db_master[$i]['vCode'];
                                    $vLTitle = $db_master[$i]['vTitle'];
                                    $eDefault = $db_master[$i]['eDefault'];
                                    $vSubject = 'vSubject_' . $vCode;
                                    $vBody = 'vBody_' . $vCode;
                                    $required = ($eDefault == 'Yes') ? 'required' : '';
                                    $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                    ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label><?= $vLTitle; ?> Subject <?= $required_msg; ?></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="text" class="form-control " name="<?= $vSubject; ?>"  id="<?= $vSubject; ?>" value="<?= $$vSubject; ?>" placeholder="<?= $vLTitle; ?> Subject" <?= $required; ?>>
                                        </div>
                                    </div>
                                    <!--- Editor -->
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label><?= $vLTitle; ?> Body <?= $required_msg; ?></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <textarea class="form-control ckeditor" rows="10" name="<?= $vBody; ?>"  id="<?= $vBody; ?>"  placeholder="<?= $vLTitle; ?> Body" <?= $required; ?>> <?= $$vBody; ?></textarea>
                                        </div>
                                    </div>
                                    <!--- Editor -->
                                <?
                                }
                            }
                            ?>
                            <div class="row">
                                <div class="col-lg-12">
<?php if (($action == 'Edit' && $userObj->hasPermission('edit-email-templates')) || ($action == 'Add' && $userObj->hasPermission('create-email-templates'))) { ?>
                                        <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?= $action; ?> Email Template">
                                        <input type="reset" value="Reset" class="btn btn-default">
<?php } ?>
                                    <a href="email_template.php" class="btn btn-default back_link">Cancel</a>
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
<? include_once('footer.php'); ?>
    <!-- PAGE LEVEL SCRIPTS -->
<!--<script src="../assets/plugins/CLEditor1_4_3/jquery.cleditor.min.js"></script>
    <script src="../assets/plugins/wysihtml5/lib/js/wysihtml5-0.3.0.js"></script>
    <script src="../assets/plugins/bootstrap-wysihtml5-hack.js"></script>Remove it becoz image icon is not working-->
    <script src="../assets/plugins/ckeditor/ckeditor.js"></script>
    <script src="../assets/plugins/ckeditor/config.js"></script>
</body>
</html>
<script>
//        $(function () {
//            $('.wysihtml5').wysihtml5({
//                "html": true,
//            });
//        });
</script>
<script>
    $(document).ready(function () {
        var referrer;
        if ($("#previousLink").val() == "") {
            referrer = document.referrer;
        } else {
            referrer = $("#previousLink").val();
        }
        if (referrer == "") {
            referrer = "email_template.php";
        } else { //alert('hi');
            $("#backlink").val(referrer);
        }
        $(".back_link").attr('href', referrer);
    });
</script>