<?php
include_once('../common.php');

require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}

$tbl_name = 'app_screen_master';
$script = 'Manage App Screen';
$required_rule = "accept='image/png,image/jpeg,image/tif,image/jpg,image/gif'";
$vScreenImage = "";

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$usedCount = isset($_REQUEST['usedcount']) ? $_REQUEST['usedcount'] : 0;
$goback = 0;
$message_print_id = $id;
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : "";
$action = ($id != '') ? 'Edit' : 'Add';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$vScreenName = isset($_POST['vScreenName']) ? $_POST['vScreenName'] : '';
$iParentId = isset($_POST['iParentId']) ? $_POST['iParentId'] : '';
$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : '';
$eAppType = isset($_POST['eAppType']) ? $_POST['eAppType'] : '';

$select_order = $obj->MySQLSelect("SELECT count(lPage_id) AS iDispOrder FROM " . $tbl_name);
$iDispOrder = isset($select_order[0]['iDisplayOrder']) ? $select_order[0]['iDisplayOrder'] : 0;
$iDispOrder = $iDispOrder + 1; // Maximum order number
$iDispOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : $iDispOrder;

if (isset($_REQUEST['goback'])) {

    $goback = $_REQUEST['goback'];
}

$redirectUrl = $tconfig["tsite_url_main_admin"] . "app_screen.php";
$backfile='app_screen.php';


$todayDate = date("Y-m-d");

if (isset($_POST['btnsubmit'])) {

    if ($action == "Add" && !$userObj->hasPermission('create-app-screen')) {

        $_SESSION['success'] = 3;

        $_SESSION['var_msg'] = 'You do not have permission to create app screen.';

        header("Location:" . $redirectUrl);

        exit;
    }

    if ($action == "Edit" && !$userObj->hasPermission('edit-app-screen')) {

        $_SESSION['success'] = 3;

        $_SESSION['var_msg'] = 'You do not have permission to update app Screen.';

        header("Location:" . $redirectUrl);

        exit;
    }

    if (isset($_FILES['vScreenImage']) && $_FILES['vScreenImage']['name'] != "") {

        $filecheck = basename($_FILES['vScreenImage']['name']);

        $fileextarr = explode(".", $filecheck);

        $ext = strtolower($fileextarr[count($fileextarr) - 1]);

        $flag_error = 0;
        if ($ext != "png" && $ext != "jpg" && $ext != "jpeg" && $ext != "tif" && $ext != "gif") {

            $flag_error = 1;

            $var_msg = "You have selected wrong file format for Image. Valid formats are jpg,jpeg,png,tif,gif.";
        }

        $data = getimagesize($_FILES['vScreenImage']['tmp_name']);

        $width = $data[0];

        $height = $data[1];

        if ($flag_error == 1) {

            header("Location:app_screen_action.php?id=" . $id . "&usedcount=" . $usedCount . "&var_msg=" . $var_msg);

            exit;
        }
    }

    if (SITE_TYPE == 'Demo') {

        header("Location:app_screen_action.php?id=" . $id . "&usedcount=" . $usedCount . "&usedcount=" . $usedCount . "&success=2");

        exit;
    }

    $q = "INSERT INTO ";

    $where = $whereDispOrder = '';

    if ($id != '') {

        $q = "UPDATE ";

        $where = " WHERE `lPage_id` = '" . $id . "'";

        $whereDispOrder = " AND `lPage_id` != '" . $id . "'";
    }
  
    //$eAppType

    $query = $q . " `" . $tbl_name . "` SET `vScreenName` = '" . $vScreenName . "',`iParentId` = '" . $iParentId . "',`eAppType` = '" . $eAppType . "',`eStatus` = '" . $eStatus . "'" . $where;

    $obj->sql_query($query);

    $id = ($id != '') ? $id : $obj->GetInsertId();

    if (isset($_FILES['vScreenImage']) && $_FILES['vScreenImage']['name'] != "") {

        $currrent_upload_time = time();

        $img_path = $tconfig["tsite_upload_manage_app_screen_path"];

        $temp_gallery = $img_path . '/';

        $image_object = $_FILES['vScreenImage']['tmp_name'];

        $image_name = $_FILES['vScreenImage']['name'];

        /* Calculate aspect ratio by dividing height by width */

        $check_file_query = "select vScreenName,vScreenImage from app_screen_master where lPage_id=" . $id;

        $check_file = $obj->sql_query($check_file_query);

        if ($image_name != "") {

            if ($message_print_id != "") {

                $check_file['vScreenImage'] = $img_path . '/' . $id . '/' . $check_file[0]['vScreenImage'];

                $android_path = $img_path . '/' . $id;

                if ($check_file['vScreenImage'] != '' && file_exists($check_file['vScreenImage'])) {

                    @unlink($check_file['vScreenImage']);
                }
            }

            //$Photo_Gallery_folder = $img_path . '/' . $id . '/';

            $Photo_Gallery_folder = $img_path . '/';
//echo $Photo_Gallery_folder;die;
            $Photo_Gallery_folder_android = $Photo_Gallery_folder . 'android/';

            $Photo_Gallery_folder_ios = $Photo_Gallery_folder . 'ios/';

            if (!is_dir($Photo_Gallery_folder)) {

                mkdir($Photo_Gallery_folder, 0777);

            }
           // echo $image_name;
            //exit;
            $img = $generalobj->imageupload($Photo_Gallery_folder, $image_object, $image_name, '', 'png,jpg,jpeg,tif,gif');

            if ($img[2] == "1") {
                $_SESSION['success'] = '0';
                $_SESSION['var_msg'] = $img[1];
                header("location:" . $backlink);
            }
    

            $imgName = $generalobj->uploadImagesOrFiles($image_object, $image_name, $Photo_Gallery_folder, '', '', '', '', '', '', 'Y', '', $Photo_Gallery_folder, $vBannerTitle, NULL);
            $sql = "UPDATE " . $tbl_name . " SET `vScreenImage` = '" . $imgName . "' WHERE `lPage_id` = '" . $id . "'";
            $obj->sql_query($sql);
        }
    }

    if ($action == "Add") {

        $_SESSION['success'] = '1';

        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG']; 
    } else {

        $_SESSION['success'] = '1';

        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully']; 
    }

    header("Location:" . $backlink);

    exit;
}
// for Edit
$db_data = array();

if ($action == 'Edit') {

    $sql = "SELECT * FROM " . $tbl_name . " WHERE lPage_id = '" . $id . "'";

    $db_data = $obj->MySQLSelect($sql);

    $vLabel = $id;

    if (count($db_data) > 0) {

        foreach ($db_data as $key => $value) {

            $eStatus = $value['eStatus'];

            $vScreenName = $value['vScreenName'];

            $iParentId = $value['iParentId'];

            $vScreenImage =$value['vScreenImage'];

        }
    }

    $showme ="WHERE  lPage_id NOT IN (".$id.")";

}
    $select =  "SELECT * FROM `app_screen_master` ".$showme ;
    $select_boxinfo = $obj->MySQLSelect($select);
    
$required_msg = '<span class="red"> *</span>';
?>

<!DOCTYPE html>

<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->

<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->

<!--[if !IE]><!-->
 <html lang="en"> <!--<![endif]-->

    <!-- BEGIN HEAD-->
<head>

        <meta charset="UTF-8" />

        <title>Admin | app screen <?= $action; ?></title>

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

                            <h2>Manage App Screen</h2>

                            <a href="<?php echo $redirectUrl; ?>">

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

                                    <?= $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>

                                </div><br/>

                            <? } elseif ($success == 2) { ?>

                                <div class="alert alert-danger alert-dismissable">

                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                </div><br/>

                                <?
                            }

                            if (isset($_REQUEST['var_msg']) && $_REQUEST['var_msg'] != "") {
                                ?>

                                <div class="alert alert-danger alert-dismissable">

                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>

                                    <?php echo $_REQUEST['var_msg']; ?>

                                </div><br/>  

                            <?php }
                            ?>

                            <div id="price1" ></div>

                            <div id="price" ></div>

                            <form id="vtype" method="post" action="" enctype="multipart/form-data">

                                <input type="hidden" name="id" value="<?= $id; ?>"/> 

                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>

                                <input type="hidden" name="backlink" id="backlink" value="<?=$backfile?>"/>

                                 <div class="row">

                                    <div class="col-lg-12">

                                        <label>Parent Screen</label>

                                    </div>

                                    <div class="col-lg-6">

                                      

                                           <select  class="form-control" name = 'iParentId'  id= 'iParentId' required>
                                             <option value="0" <?php if($db_data[0]['iParentId']=="0"){ ?>selected <?php } ?>>Parent Screen</option>
                                             <?php foreach ($select_boxinfo as $sinfo) { ?>
                                                <option value="<?= $sinfo['lPage_id'] ?>" 
                                                    <? if (isset($db_data[0]['iParentId']) && $db_data[0]['iParentId'] == $sinfo['lPage_id']) { ?>selected<? } ?>    
                                                    ><?= $sinfo['vScreenName'] ?></option>    
                                             <?php }  ?>
                                            


                                            </select>   


                                    </div>

                                </div>


                                <div class="row">

                                    <div class="col-lg-12">

                                        <label>App Screen Name <?php echo $required_msg; ?></label>

                                    </div>

                                    <div class="col-lg-6">

                                        <input type="text" class="form-control" name="vScreenName" id="vScreenName" value="<?= $vScreenName; ?>" placeholder="App Screen Name" required="">

                                    </div>

                                </div>

                                <div class="row Banner imagebox">

                                    <div class="col-lg-12">

                                        <label>App Screen Image <?php echo $required_msg; ?></label>

                                    </div>

                                    <div class="col-lg-6">

                                        <?
                                        $requireImg = 'required="required"';

                                        if (isset($vScreenImage) && $vScreenImage != '') {

                                            $requireImg = '';
                                            ?>                                     

                                            <img src="<?= $tconfig['tsite_upload_manage_app_screen'] . "/" . $vScreenImage; ?>" style="width:200px;">

                                        <? } ?>

                                        <input value="<?= $vScreenImage; ?>" type="file" class="form-control" name="vScreenImage" <?php echo $required_rule; ?> id="vScreenImage" placeholder="" style="padding-bottom: 39px;" <?= $requireImg; ?>>

                                       

                                    </div>

                                </div>

                                 <!-- Etype -->
                                  <div class="row">

                                        <div class="col-lg-12">

                                            <label>App Type<span class="red"> *</span></label>

                                        </div>

                                        <div class="col-lg-6">

                                           <select  class="form-control" name = 'eAppType'  id= 'eAppType' required>                                   

                                                <option value="General" <? if (isset($db_data[0]['eAppType']) && $db_data[0]['eAppType'] == "General") { ?>selected<? } ?>>General</option>

                                                <option value="Deliverall" <? if (isset($db_data[0]['eAppType']) && $db_data[0]['eAppType'] == "Deliverall") { ?>selected<? } ?>>Deliverall</option>

                                                 <option value="UberX" <? if (isset($db_data[0]['eAppType']) && $db_data[0]['eAppType'] == "UberX") { ?>selected<? } ?>>UberX</option>

                                                 <option value="Delivery" <? if (isset($db_data[0]['eAppType']) && $db_data[0]['eAppType'] == "Delivery") { ?>selected<? } ?>>Delivery</option>

                                                 <option value="Ride" <? if (isset($db_data[0]['eAppType']) && $db_data[0]['eAppType'] == "Ride") { ?>selected<? } ?>>Ride</option>                                                      

                                                </option>                                                    

                                            </select>

                                        </div>

                                    </div>   

   
                         <?php if ($userObj->hasPermission('update-status-app-screen')) { ?>

                                    <div class="row">

                                        <div class="col-lg-12">

                                            <label>Status<span class="red"> *</span></label>

                                        </div>

                                        <div class="col-lg-6">

                                            <select  class="form-control" name = 'eStatus'  id= 'eStatus' required>                                   

                                                <option value="Active" <? if (isset($db_data[0]['eStatus']) && $db_data[0]['eStatus'] == "Active") { ?>selected<? } ?>>Active</option>

                                                <option value="Inactive" <? if (isset($db_data[0]['eStatus']) && $db_data[0]['eStatus'] == "Inactive") { ?>selected<? } ?>>Inactive</option>                                                      

                                                </option>                                                    

                                            </select>

                                        </div>

                                    </div>

                                <?php } ?>

                                <div class="row">

                                    <div class="col-lg-12">

                                        <?php if (($action == 'Edit' && $userObj->hasPermission('edit-app-screen')) || ($action == 'Add' && $userObj->hasPermission('create-app-screen'))) { ?>

                                            <input type="submit" class="save btn-info" name="btnsubmit" id="btnsubmit" value="<?= $action; ?> App Screen">

                                            <input type="reset" value="Reset" class="btn btn-default">

                                        <?php } ?>

                                        <a href="<?=$backfile?>" class="btn btn-default back_link">Cancel</a>

                                    </div>

                                </div>

                            </form>

                        </div>

                    </div>

                    <div style="clear:both;"></div>

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

        <script type='text/javascript' src='../assets/js/jquery-ui.min.js'></script>

        <script>

                                            $(document).ready(function () {

                                                $('#dStartDate').datepicker({

                                                    dateFormat: "yy-mm-dd",

                                                    minDate: 'today'

                                                }).on('dp.change', function (e) {

                                                    $('#dStartDate').data("DateTimePicker").minDate(moment().add(5, 'm'))

                                                });

                                                $('#dExpiryDate').datepicker({

                                                    dateFormat: "yy-mm-dd",

                                                    minDate: $('#dStartDate').val()

                                                }).on('dp.change', function (e) {

                                                    $('#dExpiryDate').data("DateTimePicker").minDate(moment().add(5, 'm'))

                                                });

                                                var referrer;

                                            <?php if ($goback == 1) { ?>

                                                    alert('<?php echo $var_msg; ?>');

                                                    history.go(-1);

                                            <?php } ?>

                                                if ($("#previousLink").val() == "") {

                                                    referrer = document.referrer;

                                                    //alert(referrer);

                                                } else {

                                                    referrer = $("#previousLink").val();

                                                }

                                                if (referrer == "") {

                                                    referrer = "vehicles.php";

                                                } else {

                                                    $("#backlink").val(referrer);

                                                }

                                                $(".back_link").attr('href', referrer);

                                                showhidedate('<?php echo $eValidityType; ?>');

                                                showhideimpression('<?php echo $eImpression; ?>');

                                            });

                                            function showhidedate(val) {

                                                if (val == "Custom") {

                                                    document.getElementById("date1").style.display = '';

                                                    document.getElementById("date2").style.display = '';

                                                    $("#dStartDate").attr("required", "true");

                                                    $("#dExpiryDate").attr("required", "true");

                                                } else {

                                                    document.getElementById("date1").style.display = 'none';

                                                    document.getElementById("date2").style.display = 'none';

                                                    document.getElementById("dStartDate").required = false;

                                                    document.getElementById("dExpiryDate").required = false;

                                                }

                                            }

                                            function showhideimpression(val) {

                                                if (val == "Limited") {

                                                    document.getElementById("impression").style.display = '';

                                                    $("#iImpression").attr("required", "true");

                                                } else {

                                                    document.getElementById("impression").style.display = 'none';

                                                    document.getElementById("iImpression").required = false;

                                                }

                                            }

        </script>

    </body>

    <!-- END BODY-->

</html>