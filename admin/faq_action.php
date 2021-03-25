<?php
include_once('../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();

if ($action == "Add" && !$userObj->hasPermission('create-faq')) {
    $_SESSION['success'] = 3;
    $_SESSION['var_msg'] = 'You do not have permission to create FAQ.';
    header("Location:faq.php");
    exit;
}

if ($action == "Edit" && !$userObj->hasPermission('edit-faq')) {
    $_SESSION['success'] = 3;
    $_SESSION['var_msg'] = 'You do not have permission to update FAQ.';
    header("Location:faq.php");
    exit;
}

require_once(TPATH_CLASS . "Imagecrop.class.php");

$default_lang = $generalobj->get_default_lang();
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : ''; // iFaqcategoryId
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$faq_cat_id = isset($_REQUEST['faq_cat_id']) ? $_REQUEST['faq_cat_id'] : '';
$action = ($id != '') ? 'Edit' : 'Add';

//$temp_gallery = $tconfig["tpanel_path"];
$tbl_name = 'faqs';
$script = 'Faq';
// fetch all lang from language_master table 
$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = count($db_master);
// set all variables with either post (when submit) either blank (when insert)
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';

$thumb = new thumbnail();
/* to fetch max iDisplayOrder from table for insert */
$select_order = $obj->MySQLSelect("SELECT count(iDisplayOrder) AS iDisplayOrder FROM " . $tbl_name);
$iDisplayOrder = isset($select_order[0]['iDisplayOrder']) ? $select_order[0]['iDisplayOrder'] : 0;
$iDisplayOrder_max = $iDisplayOrder + 1; // Maximum order number

$iFaqcategoryId = isset($_POST['iFaqcategoryId']) ? $_POST['iFaqcategoryId'] : $faq_cat_id;
$iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : $iDisplayOrder;
$temp_order = isset($_POST['temp_order']) ? $_POST['temp_order'] : "";

if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
        $vTitle = 'vTitle_' . $db_master[$i]['vCode'];
        $$vTitle = isset($_POST[$vTitle]) ? $_POST[$vTitle] : '';
        $tAnswer = 'tAnswer_' . $db_master[$i]['vCode'];
        $$tAnswer = isset($_POST[$tAnswer]) ? $_POST[$tAnswer] : '';
    }
}


if (isset($_POST['submit'])) { //form submit
    if (!empty($faq_cat_id)) {
        if (SITE_TYPE == 'Demo') {
            header("Location:faq_action.php?id=" . $id . "&faq_cat_id=" . $faq_cat_id . "&success=2");
            exit;
        }
    }

    if ($temp_order == "1" && $action == "Add") {
        $temp_order = $iDisplayOrder_max;
    }
    if ($temp_order > $iDisplayOrder) {
        for ($i = $temp_order - 1; $i >= $iDisplayOrder; $i--) {
            $sql = "UPDATE " . $tbl_name . " SET iDisplayOrder = '" . ($i + 1) . "' WHERE iDisplayOrder = '" . $i . "'";
            $obj->sql_query($sql);
        }
    } else if ($temp_order < $iDisplayOrder) {
        for ($i = $temp_order + 1; $i <= $iDisplayOrder; $i++) {
            $sql = "UPDATE " . $tbl_name . " SET iDisplayOrder = '" . ($i - 1) . "' WHERE iDisplayOrder = '" . $i . "'";
            $obj->sql_query($sql);
        }
    }

    /* $select_order		= $obj->MySQLSelect("SELECT MAX(iFaqcategoryId) AS iFaqcategoryId FROM ".$tbl_name." WHERE vCode = '".$default_lang."'");
      $iFaqcategoryId			= isset($select_order[0]['iFaqcategoryId'])?$select_order[0]['iFaqcategoryId']:0;
      $iFaqcategoryId			= $iFaqcategoryId + 1; // Maximum order number */

    /* if($count_all > 0) {
      for($i=0;$i<$count_all;$i++) {
     */
    $q = "INSERT INTO ";
    $where = '';

    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `iFaqId` = '" . $id . "'";
    }
    $sql_str = '';
    if ($count_all > 0) {
        for ($i = 0; $i < $count_all; $i++) {
            $vTitle = 'vTitle_' . $db_master[$i]['vCode'];
            $sql_str .= $vTitle . " = '" . $$vTitle . "',";
            $tAnswer = 'tAnswer_' . $db_master[$i]['vCode'];
            $sql_str .= $tAnswer . " = '" . $$tAnswer . "',";
        }
    }

    $query = $q . " `" . $tbl_name . "` SET 	
				" . $sql_str . "
				`eStatus` = '" . $eStatus . "',
				`iFaqcategoryId` = '" . $iFaqcategoryId . "',
				`iDisplayOrder` = '" . $iDisplayOrder . "'"
            . $where;
    $obj->sql_query($query);

    $id = ($id != '') ? $id : $obj->GetInsertId();

    //header("Location:faq_action.php?id=".$id."&faq_cat_id=".$iFaqcategoryId."&success=1");
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
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iFaqId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);


    if ($count_all > 0) {
        for ($i = 0; $i < $count_all; $i++) {
            $vTitle = 'vTitle_' . $db_master[$i]['vCode'];
            $$vTitle = isset($db_data[0][$vTitle]) ? $db_data[0][$vTitle] : $$vTitle;
            $tAnswer = 'tAnswer_' . $db_master[$i]['vCode'];
            $$tAnswer = isset($db_data[0][$tAnswer]) ? $db_data[0][$tAnswer] : $$tAnswer;

            $eStatus = $db_data[0]['eStatus'];
            $iDisplayOrder_db = $db_data[0]['iDisplayOrder'];
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
        <title>Admin | FAQ  <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />

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
                            <h2><?= $action; ?> FAQ </h2>
                            <a href="faq.php" class="back_link">
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
                            <? } ?>

                            <? if ($success == 2) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                </div><br/>
                            <? } ?>

                            <form method="post" name="_faq_form" id="_faq_form" action="" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?= $id; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="faq.php"/>
                                <?php
                                $sql = "SELECT * FROM faq_categories WHERE vCode = '" . $default_lang . "' AND eStatus = 'Active' ORDER BY  vTitle ASC ";
                                $db_cat = $obj->MySQLSelect($sql);

                                if (count($db_cat) > 0) {
                                    ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Category</label>
                                        </div>
                                        <div class="col-lg-6">
                                            <select name="iFaqcategoryId" id="iFaqcategoryId" class="form-control">
                                                <?php for ($i = 0; $i < count($db_cat); $i++) { ?>
                                                    <option value="<?= $db_cat[$i]['iUniqueId']; ?>" <?= ($db_cat[$i]['iUniqueId'] == $faq_cat_id) ? 'selected' : ''; ?>>
                                                        -- <?= $db_cat[$i]['vTitle'] ?> --
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>

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
                                    <? /* if($action == 'Edit') { */ ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Order</label>
                                        </div>
                                        <div class="col-lg-6">

                                            <input type="hidden" name="temp_order" id="temp_order" value="<?= ($action == 'Edit') ? $iDisplayOrder_db : '1'; ?>">
                                            <?
                                            $display_numbers = ($action == "Add") ? $iDisplayOrder_max : $iDisplayOrder;
                                            ?>
                                            <select name="iDisplayOrder" class="form-control">
                                                <? for ($i = 1; $i <= $display_numbers; $i++) { ?>
                                                    <option value="<?= $i ?>" <?
                                                    if ($i == $iDisplayOrder_db) {
                                                        echo "selected";
                                                    }
                                                    ?>> -- <?= $i ?> --</option>
                                                <? } ?>
                                            </select>

                                        </div>
                                    </div>

                                    <?php
                                    if ($count_all > 0) {
                                        for ($i = 0; $i < $count_all; $i++) {
                                            $vCode = $db_master[$i]['vCode'];
                                            $vTitle = $db_master[$i]['vTitle'];

                                            $vTitle_val = "vTitle_" . $vCode;
                                            $tAnswer_val = "tAnswer_" . $vCode;

                                            $eDefault = $db_master[$i]['eDefault'];

                                            $required = ($eDefault == 'Yes') ? 'required' : '';
                                            $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                            ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label><?= $vTitle; ?> Question <?= $required_msg; ?></label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <input type="text" class="form-control" name="<?= $vTitle_val; ?>"  id="<?= $vTitle_val; ?>" value="<?= $$vTitle_val; ?>" placeholder="FAQ" <?= $required; ?>>
                                                </div>
                                                <? if($vCode == $default_lang  && count($db_master) > 1){ ?>
                                                    <div class="col-lg-6">
                                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vTitle_');"> Convert To All Language</button>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label><?= $vTitle; ?> Answer <?= $required_msg; ?></label>
                                                </div>
                                                <div class="col-lg-12">
                                                    <textarea class="form-control wysihtml5" name="<?= $tAnswer_val; ?>"  id="<?= $tAnswer_val; ?>" placeholder="Answer" <?= $required; ?>><?= $$tAnswer_val; ?></textarea>
                                                </div>
                                            </div>
                                        <?
                                        }
                                    }
                                    ?>
                                    <div class="row">
                                        <div class="col-lg-12">		
                                            <?php if (($action == 'Edit' && $userObj->hasPermission('edit-faq')) || ($action == 'Add' && $userObj->hasPermission('create-faq'))) { ?>					
                                                <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?= $action; ?> FAQ">
                                                <input type="reset" value="Reset" class="btn btn-default">
                                            <?php } ?>
                                            <!-- <a href="javascript:void(0);" onclick="reset_form('_faq_form');" class="btn btn-default">Reset</a> -->
                                            <a href="faq.php" class="btn btn-default back_link">Cancel</a>
                                        </div>
                                    </div>
                                    <? } else { ?>
                                    Please enter FAQ Catgory
                                    <? } ?>
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

<!-- GLOBAL SCRIPTS -->
<!--<script src="../assets/plugins/jquery-2.0.3.min.js"></script>-->
<script src="../assets/plugins/bootstrap/js/bootstrap.min.js"></script>
<script src="../assets/plugins/modernizr-2.6.2-respond-1.1.0.min.js"></script>
<!-- END GLOBAL SCRIPTS -->


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
    <script type="text/javascript" language="javascript">
        function getAllLanguageCode(preferID){
            var preferID = preferID;

              var def_lang = '<?=$default_lang?>';
              var def_lang_name = '<?=$def_lang_name?>';
              var getEnglishText = $('#'+preferID+def_lang).val();
              var error = false;
              var msg = '';
              
              if(getEnglishText==''){
                  msg += '<div class="alert alert-danger fade in"><a href="#" class="close" data-dismiss="alert"><icon class="fa fa-close"></icon></a><strong>Please Enter '+def_lang_name+' Value</strong></div> <br>';
                  error = true;
              }
              
              if(error==true){
                      $('#errorMessage').html(msg);
                      return false;
              }else{
                $('#imageIcon').show();
                $.ajax({
                        url: "ajax_get_all_language_translate.php",
                        type: "post",
                        data: {'englishText':getEnglishText},
                        dataType:'json',
                        success:function(response){
                            // $("#doc_name_EN").val(getEnglishText);
                             $.each(response,function(name, Value){
                                var key = name.split('_');
                                $('#'+preferID+key[1]).val(Value);
                             });
                             $('#imageIcon').hide();
                        }
                });
              }
        }
    </script>
    <script>
        $(document).ready(function () {
            var referrer;
            if ($("#previousLink").val() == "") {
                alert(referrer);
                referrer = document.referrer;

            } else {
                referrer = $("#previousLink").val();
            }
            if (referrer == "") {
                referrer = "faq.php";
            } else {
                $("#backlink").val(referrer);
            }
            $(".back_link").attr('href', referrer);
        });
        /**
         * This will reset the CKEDITOR using the input[type=reset] clicks.
         */
        $(function () {
            if (typeof CKEDITOR != 'undefined') {
                $('form').on('reset', function (e) {
                    if ($(CKEDITOR.instances).length) {
                        for (var key in CKEDITOR.instances) {
                            var instance = CKEDITOR.instances[key];
                            if ($(instance.element.$).closest('form').attr('name') == $(e.target).attr('name')) {
                                instance.setData(instance.element.$.defaultValue);
                            }
                        }
                    }
                });
            }
        });
    </script>	
</html>