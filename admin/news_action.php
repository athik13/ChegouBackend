<?php
include_once('../common.php');
require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}

define("NEWSFEED", "newsfeed");
$script = 'news';

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : '';
$eUserType = isset($_POST['eUserType']) ? $_POST['eUserType'] : '';

$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$txtBoxNameArr = array("vTitle");
$lableArr = array("Title"); 

$vImage = $welComeImg = "";
$img_data = array();
if (isset($_POST['btnsubmit'])) {
    if ($action == "Add" && !$userObj->hasPermission('create-news')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create ' . $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'];
        header("Location:news.php");
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission('edit-news')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update ' . $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'];
        header("Location:news.php");
        exit;
    }
    if (SITE_TYPE == 'Demo') {
        //header("Location:news_feed_action_new.php?id=" . $id . "&success=2");
        $_SESSION['success'] = '2';
        header("location:" . $backlink);
        exit;
    }

    require_once("library/validation.class.php");
    $validobj = new validation();
    $validobj->add_fields($_POST['eUserType'], 'req', 'User Type is required');
    $vtitleArr = $descArr = array();
    $error = $validobj->validate();

    if ($error) {
        $success = 3;
        $newError = $error;
        $_SESSION['var_msg'] = $newError;
        $_SESSION['success'] = "3";
        exit;
    } else {
        $tPublishdate = date("Y-m-d H:i:s");
        for ($i = 0; $i < count($db_master); $i++) {
            $vTitle = $tDescription = "";
            if (isset($_POST['vTitle_' . $db_master[$i]['vCode']])) {
                $vTitle = $_POST['vTitle_' . $db_master[$i]['vCode']];
            }
            if (isset($_POST['tDescription_' . $db_master[$i]['vCode']])) {
                $tDescription = $_POST['tDescription_' . $db_master[$i]['vCode']];
            }
            $q = "INSERT INTO ";
            $where = '';

            if ($id != '') {
                $q = "UPDATE ";
                $where = " WHERE `iNewsfeedId` = '" . $id . "'";
            }
            $vtitleArr["vTitle_" . $db_master[$i]['vCode']] = $vTitle;
            $descArr["tDescription_" . $db_master[$i]['vCode']] = $tDescription;
        }
        if ($eStatus == '') {
            $str = ", eStatus = 'Inactive' ";
        } else {
            $str = ", eStatus = 'Active'";
        }
        $time = time();
        if (count($vtitleArr) > 0) {
            $updateProfileImg = "";
            /*$jsonTitle = $obj->cleanQuery(json_encode($vtitleArr));
            $jsonDesc = $obj->cleanQuery(json_encode($descArr));*/
            
            $jsonTitle = $generalobj->getJsonFromAnArr($vtitleArr);
            $jsonDesc = $generalobj->getJsonFromAnArr($descArr);
            
            $query = $q . " `" . NEWSFEED . "` SET `vTitle` = '" . $jsonTitle . "',`eUserType` = '" . $eUserType . "',`tPublishdate` = '" . $tPublishdate . "',`tDescription` = '" . $jsonDesc . "' $str $updateProfileImg" . $where;
            
            $obj->sql_query($query);
            $id = ($id != '') ? $id : $obj->GetInsertId();
        }
        // for image upload
        if ($_FILES['vNewfeedImage']['name'] != '') {
            $img_path = $tconfig["tsite_upload_images_news_feed_path"];
            $temp_gallery = $img_path . '/';
            $image_object = $_FILES['vNewfeedImage']['tmp_name'];
            $image_name = $_FILES['vNewfeedImage']['name'];

            $filecheck = basename($_FILES['vNewfeedImage']['name']);
            $fileextarr = explode(".", $filecheck);
            $ext = strtolower($fileextarr[count($fileextarr) - 1]);
            $flag_error = 0;
            if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
                $flag_error = 1;
                $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png";
            }

            $dataimg = getimagesize($_FILES['vNewfeedImage']['tmp_name']);
            $imgwidth = $dataimg[0];
            $imgheight = $dataimg[1];
            if ($imgwidth < 1024) {
                echo"<script>alert('Your Image upload size is less than recommended. Image will look stretched.');</script>";
            }
            $check_file_query = "select vNewfeedImage from newsfeed where iNewsfeedId=" . $id;
            $check_file = $obj->sql_query($check_file_query);
            $oldImage = $check_file[0]['vNewfeedImage'];
            $check_file = $img_path . '/' . $oldImage;
            if ($oldImage != '' && file_exists($check_file)) {
                @unlink($img_path . '/' . $oldImage);
            }



            if ($flag_error == 1) {
                $_SESSION['success'] = '3';
                $_SESSION['var_msg'] = $var_msg;
                header("Location:news.php");
            } else {

                $Photo_Gallery_folder = $img_path . '/' . $iNewsfeedId . '/';
                if (!is_dir($Photo_Gallery_folder)) {
                    mkdir($Photo_Gallery_folder, 0777);
                }
                $img1 = $generalobj->fileupload($Photo_Gallery_folder, $image_object, $image_name, '', 'jpg,png,gif,jpeg');
                $vNewfeedImage = $img1[0];

                $sql1 = "UPDATE newsfeed SET `vNewfeedImage` = '" . $vNewfeedImage . "' WHERE `iNewsfeedId` = '" . $id . "'";
                $obj->sql_query($sql1);
            }
        }
        if ($action == "Add") {
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
            $_SESSION['success'] = "1";
        } else {
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
            $_SESSION['success'] = "1";
        }
        header("location:" . $backlink);
        exit;
    }
}
// for Edit
$userEditDataArr = array();
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . NEWSFEED . " WHERE iNewsfeedId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);

    if (count($db_data) > 0) {
        $vTitle = (array) json_decode($db_data[0]['vTitle']);
        foreach ($vTitle as $key => $value) {
            $userEditDataArr[$key] = $value;
        }
        $tDescription = (array) json_decode($db_data[0]['tDescription']);
        foreach ($tDescription as $key4 => $value4) {
            $userEditDataArr[$key4] = $value4;
        }
        if (count($db_data) > 0) {
            foreach ($db_data as $key => $value) {
                $vNewfeedImage = $value['vNewfeedImage'];
                $eStatus = $value['eStatus'];
                $eUserType = $value['eUserType'];
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
        <title><?= $SITE_NAME ?> | News <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />

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
<?
include_once('header.php');
include_once('left_menu.php');
?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2> <?= $action; ?> News </h2>
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
                                    <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
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
                            <? if (isset($_REQUEST['var_msg']) && $_REQUEST['var_msg'] != Null) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    Record  Not Updated .
                                </div><br/>
<? } ?>                   
                            <form id="_vehicleType_form" name="_vehicleType_form" method="post" action="" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?= $id; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="news.php"/>
                                <div class="row"> 
                                    <div class="col-lg-12" id="errorMessage"></div>
                                </div>

<?
if (count($db_master) > 0) {
    ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Select User Type<span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <select class="form-control" name = 'eUserType' id="eUserType" required="">
                                                <option value="">Select User Type</option>
                                                <option value="all"  <?php if ($eUserType == 'all') {
                                    echo 'selected';
                                } ?>>All</option>
                                                <option value="driver" <?php if ($eUserType == 'driver') {
                                    echo 'selected';
                                } ?>><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></option>
                                                <option value="rider" <?php if ($eUserType == 'rider') {
                                                echo 'selected';
                                            } ?>><?php echo $langage_lbl_admin['LBL_RIDER']; ?></option>
    <? if (DELIVERALL == "Yes") { ?>
                                                    <option value="company" <?php if ($eUserType == 'company') {
            echo 'selected';
        } ?>>Store/ Restaurant</option>
    <? } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Image</label>
                                        </div>
                                        <div class="col-lg-6">
    <? if ($vNewfeedImage != '') { ?>                                               
                                                <!-- <img src="<?= $tconfig['tsite_upload_images_news_feed'] . "/" . $vNewfeedImage; ?>" style="width:100px;height:100px;"> -->

                                                <img src="<?= $tconfig['tsite_url'].'resizeImg.php?w=200&h=200&src='.$tconfig['tsite_upload_images_news_feed'] . "/" . $vNewfeedImage; ?>" style="width:100px;height:100px;">

    <? } ?>
                                            <input type="file" class="form-control" name="vNewfeedImage" id="vNewfeedImage" value="<?= $vNewfeedImage; ?>">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Status</label>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="make-switch" data-on="success" data-off="warning">
                                                <input type="checkbox" name="eStatus" id="eStatus" <?= ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?> />
                                            </div>
                                        </div>
                                    </div>
                                    <?
                                    for ($i = 0; $i < count($db_master); $i++) {
                                        $vCode = $db_master[$i]['vCode'];
                                        $vTitle = $db_master[$i]['vTitle'];
                                        $eDefault = $db_master[$i]['eDefault'];
                                        $descVal = 'tDescription_' . $vCode;
                                        $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                        for ($l = 0; $l < count($txtBoxNameArr); $l++) {
                                            $lableText = $lableArr[$l];
                                            $lableName = $txtBoxNameArr[$l] . '_' . $vCode;
                                            $required = ($eDefault == 'Yes') ? 'required' : '';
                                            ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label><?= $lableText; ?> (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                                </div>
                                                <div class="col-lg-6"><!-- <?= $lableName; ?> -->
                                                    <input type="text" class="form-control" name="<?= $lableName; ?>" id="<?= $lableName; ?>" value="<?= $userEditDataArr[$lableName]; ?>" placeholder="<?= $vTitle; ?> Value" <?= $required; ?>>
                                                </div>
            <? if ($vCode == $default_lang && count($db_master) > 1) { ?>
                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('<? echo $txtBoxNameArr[$l]; ?>');">Convert To All Language</button>
            <?php } ?>
                                            </div>
        <? } ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Description (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <textarea class="form-control" name="<?= $descVal; ?>" id="<?= $descVal; ?>" placeholder="<?= $vTitle; ?> Value" <?= $required; ?>><?= $userEditDataArr[$descVal]; ?></textarea>
                                            </div>
                                        <? if ($vCode == $default_lang && count($db_master) > 1) { ?>
                                                <div class="col-lg-6">
                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tDescription');">Convert To All Language</button>
                                                </div>
                                        <?php } ?>
                                        </div>

        <?
    }
}
?>
                                <div class="col-lg-12">
<?php if ($userObj->hasRole(1) || ($action == "Edit" && $userObj->hasPermission('edit-news')) || ($action == "Add" && $userObj->hasPermission('create-news'))) { ?>
                                        <input type="submit" class="btn btn-default" name="btnsubmit" id="btnsubmit" value="<?= $action; ?> News" >
                                        <input type="reset" value="Reset" class="btn btn-default">
<?php } ?>
                                    <a href="news.php" class="btn btn-default back_link">Cancel</a>
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
<?
include_once('footer.php');
?>  
        <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>

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
                    referrer = "news.php";
                } else {
                    $("#backlink").val(referrer);
                }
                $(".back_link").attr('href', referrer);
            });
        </script>
        <!--END MAIN WRAPPER -->

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
</html>
