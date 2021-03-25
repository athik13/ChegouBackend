<?php
include_once('../common.php');
require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
//error_reporting(E_ALL);
define("DONATION", "donation");
$script = 'donation';

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : '';
$tLink = isset($_POST['tLink']) ? $_POST['tLink'] : '';

$temp_order     = isset($_POST['temp_order'])? $_POST['temp_order'] : "";

//$eUserType = isset($_POST['eUserType']) ? $_POST['eUserType'] : '';

/* to fetch max iDisplayOrder from table for insert */
$select_order   = $obj->MySQLSelect("SELECT count(iDisplayOrder) AS iDisplayOrder FROM " . DONATION . "");
$iDisplayOrder  = isset($select_order[0]['iDisplayOrder'])?$select_order[0]['iDisplayOrder']:0;
$iDisplayOrder_max  = $iDisplayOrder + 1; // Maximum order number


$sql = "SELECT * FROM `language_master` ORDER BY `eDefault`";
$db_master = $obj->MySQLSelect($sql);
$txtBoxNameArr = array("tTitle");
$lableArr = array("Title");

$vImage = $welComeImg = "";
$img_data = array();
if (isset($_POST['btnsubmit'])) {
    if ($action == "Add" && !$userObj->hasPermission('create-donation')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create ' . $langage_lbl_admin['LBL_DONATION_SMALL_TXT'];
        header("Location:donation.php");
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission('edit-donation')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update ' . $langage_lbl_admin['LBL_DONATION_SMALL_TXT'];
        header("Location:donation.php");
        exit;
    }
    if (SITE_TYPE == 'Demo') {
        //header("Location:Donation_feed_action_new.php?id=" . $id . "&success=2");
        $_SESSION['success'] = '2';
        header("location:" . $backlink);
        exit;
    }
//  for ordering
$iDisplayOrder  = isset($_POST['iDisplayOrder'])?$_POST['iDisplayOrder']:$iDisplayOrder;
$temp_order     = isset($_POST['temp_order'])? $_POST['temp_order'] : "";
     if($temp_order == "1" && $action == "Add"){
        $temp_order = $iDisplayOrder_max;
    }
    if($temp_order > $iDisplayOrder) { 
        for($i = $temp_order-1; $i >= $iDisplayOrder; $i--) { 
           $sql="UPDATE " . DONATION . " SET iDisplayOrder = '".($i+1)."' WHERE iDisplayOrder = '".$i."'";
            $obj->sql_query($sql);
        }
    } else if($temp_order < $iDisplayOrder) {
        for($i = $temp_order+1; $i <= $iDisplayOrder; $i++) {
            $sql="UPDATE " . DONATION . " SET iDisplayOrder = '".($i-1)."' WHERE iDisplayOrder = '".$i."'";
            $obj->sql_query($sql);
        }
    }

    require_once("library/validation.class.php");
        $tCreatedDate = date("Y-m-d H:i:s");
        for ($i = 0; $i < count($db_master); $i++) {
            $tTitle = $tDescription = "";
            if (isset($_POST['tTitle_' . $db_master[$i]['vCode']])) {
                $tTitle = $_POST['tTitle_' . $db_master[$i]['vCode']];
            }
            if (isset($_POST['tDescription_' . $db_master[$i]['vCode']])) {
                $tDescription = $_POST['tDescription_' . $db_master[$i]['vCode']];
            }
            $q = "INSERT INTO ";
            $where = '';

            if ($id != '') {
                $q = "UPDATE ";
                $where = " WHERE `iDonationId` = '" . $id . "'";
            }
            $tTitleArr["tTitle_" . $db_master[$i]['vCode']] = $tTitle;
            $descArr["tDescription_" . $db_master[$i]['vCode']] = $tDescription;
        }
        if ($eStatus == '') {
            $str = ", eStatus = 'Inactive' ";
        } else {
            $str = ", eStatus = 'Active'";
        }
        $time = time();
        if (count($tTitleArr) > 0) {
            $updateProfileImg = "";
            $jsonTitle =  $generalobj->getJsonFromAnArr($tTitleArr);
            $jsonDesc = $generalobj->getJsonFromAnArr($descArr);

            $query = $q . " `" . DONATION . "` SET `iDisplayOrder` = '" . $iDisplayOrder . "', `tTitle` = '" . $jsonTitle . "',`tLink` = '" . $tLink . "',`tCreatedDate` = '" . $tCreatedDate . "',`tDescription` = '" . $jsonDesc . "' $str $updateProfileImg" . $where;
            $obj->sql_query($query);
            $id = ($id != '') ? $id : $obj->GetInsertId();
        }
        // for image upload
        if ($_FILES['vDonationImage']['name'] != '') {
            $img_path = $tconfig["tsite_upload_images_donation_path"];
            $temp_gallery = $img_path . '/';
            $image_object = $_FILES['vDonationImage']['tmp_name'];
            $image_name = $_FILES['vDonationImage']['name'];

            $filecheck = basename($_FILES['vDonationImage']['name']);
            $fileextarr = explode(".", $filecheck);
            $ext = strtolower($fileextarr[count($fileextarr) - 1]);
            $flag_error = 0;
            if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
                $flag_error = 1;
                $var_msg = "You have selected wrong file format for Image. Valid formats are jpg,jpeg,gif,png";
            }

            $dataimg = getimagesize($_FILES['vDonationImage']['tmp_name']);
            $imgwidth = $dataimg[0];
            $imgheight = $dataimg[1];

            if ($imgwidth < 2880) {

                echo"<script>alert('Your Image upload size is less than recommended. Image will look stretched.');</script>";
            }

            $check_file_query = "select vDonationImage from donation where iDonationId=" . $id;
            $check_file = $obj->sql_query($check_file_query);
            $oldImage = $check_file[0]['vDonationImage'];
            $check_file = $img_path . '/' . $oldImage;
            if ($oldImage != '' && file_exists($check_file)) {
                @unlink($img_path . '/' . $oldImage);
            }



            if ($flag_error == 1) {

                if ($action == "Add") {
                    header("Location:donation_action.php?&var_msg=" . $var_msg);
                    exit;
                }else{
                    header("Location:donation_action.php?id=" . $id . "&var_msg=" . $var_msg . "");
                    exit;
                }
            } else {

                $Photo_Gallery_folder = $img_path . '/' . $iDonationId . '/';
                if (!is_dir($Photo_Gallery_folder)) {
                    mkdir($Photo_Gallery_folder, 0777);
                }
                $img1 = $generalobj->fileupload($Photo_Gallery_folder, $image_object, $image_name, '', 'jpg,png,gif,jpeg');
                $vDonationImage = $img1[0];

                $sql1 = "UPDATE " . DONATION . " SET `vDonationImage` = '" . $vDonationImage . "' WHERE `iDonationId` = '" . $id . "'";
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
// for Edit
$userEditDataArr = array();
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . DONATION . " WHERE iDonationId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);

    if (count($db_data) > 0) {
        $tTitle = (array) json_decode($db_data[0]['tTitle']);
        foreach ($tTitle as $key => $value) {
            $userEditDataArr[$key] = $value;
        }
        $tDescription = (array) json_decode($db_data[0]['tDescription']);
        foreach ($tDescription as $key4 => $value4) {
            $userEditDataArr[$key4] = $value4;
        }
        if (count($db_data) > 0) {
            foreach ($db_data as $key => $value) {
                $vDonationImage = $value['vDonationImage'];
                $eStatus = $value['eStatus'];
                $tLink = $value['tLink'];
                $iDisplayOrder_db = $value['iDisplayOrder'];
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
        <title><?= $SITE_NAME ?> | Donation <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />

<? include_once('global_files.php'); ?>
        <!-- On OFF switch -->
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
        <!-- PAGE LEVEL STYLES -->
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
                            <h2> <?= $action; ?> Donation </h2>
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
                            <?  if (isset($_REQUEST['var_msg']) && $_REQUEST['var_msg'] != "") {
                                ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $_REQUEST['var_msg']; ?>
                                </div><br/>  
                            <?php }
                            ?>
                            <form id="_vehicleType_form" name="_vehicleType_form" method="post" action="" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?= $id; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="donation.php"/>
                                <div class="row"> 
                                    <div class="col-lg-12" id="errorMessage"></div>
                                </div>

<?
if (count($db_master) > 0) {
    ?>

                              <?
                                    for ($i = 0; $i < count($db_master); $i++) {
                                        $vCode = $db_master[$i]['vCode'];
                                        $tTitle = $db_master[$i]['vTitle'];
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
                                                    <label><?= $lableText; ?> (<?= $tTitle; ?>) <?php echo $required_msg; ?></label>
                                                </div>
                                                <div class="col-lg-6"><!-- <?= $lableName; ?> -->
                                                    <input type="text" class="form-control" name="<?= $lableName; ?>" id="<?= $lableName; ?>" value="<?= $userEditDataArr[$lableName]; ?>" placeholder="<?= $tTitle; ?> Value" <?= $required; ?>>
                                                </div>
                                                <? if ($vCode == $default_lang && count($db_master) > 1) { ?>
                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('<? echo $txtBoxNameArr[$l]; ?>');">Convert To All Language</button>
                                                <?php } ?>
                                            </div>
                                        <? } ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Description (<?= $tTitle; ?>) <?php echo $required_msg; ?></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <textarea class="form-control" name="<?= $descVal; ?>" id="<?= $descVal; ?>" placeholder="<?= $tTitle; ?> Value" <?= $required; ?>><?= $userEditDataArr[$descVal]; ?></textarea> <!-- ckeditor -->
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

                                     <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Link <span class="red"> *</span></label>
                                                </div>
                                                <div class="col-lg-6"><!-- <?= $lableName; ?> -->
                                                    <input type="url" class="form-control" name="tLink" id="tLink" value="<?= $tLink; ?>"  required>
                                                    <br/>
                                                    Note: Add link with http:// or https://.
                                                </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Image</label>
                                        </div>
                                        <div class="col-lg-6">
    <? if ($vDonationImage != '') { ?>                                               
                                               <!--  <img src="<?= $tconfig['tsite_upload_images_donation'] . "/" . $vDonationImage; ?>" style="width:100px;height:100px;"> -->

                                               <img src="<?= $tconfig["tsite_url"].'resizeImg.php?w=200&h=200&src='.$tconfig['tsite_upload_images_donation'] . '/' . $vDonationImage; ?>" style="width:100px;height:100px;">

    <? } ?>
                                            <input type="file" accept="image/jpg, image/jpeg, image/png image/gif" class="form-control" name="vDonationImage" id="vDonationImage" value="<?= $vDonationImage; ?>">
                                            <br/>
                                            Note: Recommended dimension for banner image is 2880 * 1620.
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
                                    <div class="row">
                                    <div class="col-lg-12">
                                        <label>Display Order</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <?
                                            $display_numbers = ($action=="Add") ? $iDisplayOrder_max : $iDisplayOrder;
                                        ?>
                                        <input type="hidden" name="temp_order" id="temp_order" value="<?=($action == 'Edit') ? $iDisplayOrder_db : $display_numbers;?>">
                                        
                                        <select name="iDisplayOrder" class="form-control">
                                            <? 
                                            for($i=1; $i <= $display_numbers; $i++){ 
                                                if($action=="Add"){
                                                 $iDisplayOrder_db = $display_numbers;
                                                }
                                                ?>
                                                <option value="<?=$i?>" <?if($i == $iDisplayOrder_db){echo "selected";}?>> -- <?=$i?> --</option>
                                            <? } ?>
                                        </select>
                                        
                                    </div>
                                </div>
          
                                <div class="col-lg-12">
<?php if ($userObj->hasRole(1) || ($action == "Edit" && $userObj->hasPermission('edit-donation')) || ($action == "Add" && $userObj->hasPermission('create-donation'))) { ?>
                                        <input type="submit" class="btn btn-default" name="btnsubmit" id="btnsubmit" value="<?= $action; ?> Donation" >
                                        <input type="reset" value="Reset" class="btn btn-default">
<?php } ?>
                                    <a href="donation.php" class="btn btn-default back_link">Cancel</a>
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
        <script type="text/javascript" src="js/validation/jquery.validate.min.js" ></script>
        <script type="text/javascript" src="js/validation/additional-methods.min.js" ></script>
        <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
        <link rel="stylesheet" type="text/css" media="screen" href="css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
        <script type="text/javascript" src="js/moment.min.js"></script>
        <script type="text/javascript" src="js/bootstrap-datetimepicker.min.js"></script>
        <!--Added By Hasmukh On 11-10-2018 For Clock Time Picker Start Js-->
        <script type="text/javascript" src="js/bootstrap-clockpicker.min.js"></script>
        <!--Added By Hasmukh On 11-10-2018 For Clock Time Picker End Js -->
        <!--For Faretype-->
        <script src="../assets/plugins/ckeditor/ckeditor.js"></script>
        <script src="../assets/plugins/ckeditor/config.js"></script>
        
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
                    referrer = "donation.php";
                } else {
                    $("#backlink").val(referrer);
                }
                $(".back_link").attr('href','donation.php');
            });
        </script>
        <!--END MAIN WRAPPER -->

        <!-- GLOBAL SCRIPTS -->
        <!--<script src="../assets/plugins/jquery-2.0.3.min.js"></script>-->
        <script src="../assets/plugins/bootstrap/js/bootstrap.min.js"></script>
        <script src="../assets/plugins/modernizr-2.6.2-respond-1.1.0.min.js"></script>
        <!-- END GLOBAL SCRIPTS -->
    </body>
    <!-- END BODY-->
</html>
