<?php
include_once('../common.php');

require_once(TPATH_CLASS . "/Imagecrop.class.php");

$thumb = new thumbnail();

if (!isset($generalobjAdmin)) {

    require_once(TPATH_CLASS . "class.general_admin.php");

    $generalobjAdmin = new General_admin();
}


$required_rule = "accept='image/png,image/jpeg,image/tif,image/jpg,image/gif'";

$vBannerImage = "";

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';

$usedCount = isset($_REQUEST['usedcount']) ? $_REQUEST['usedcount'] : 0;

$goback = 0;

$message_print_id = $id;

$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;

$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : "";

$action = ($id != '') ? 'Edit' : 'Add';

$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

$tbl_name = 'advertise_banners';

$script = 'Advertisement Banners';

$vBannerTitle = isset($_POST['vBannerTitle']) ? $_POST['vBannerTitle'] : '';

$tRedirectUrl = isset($_POST['tRedirectUrl']) ? $_POST['tRedirectUrl'] : '';

$dStartDate = isset($_POST['dStartDate']) ? $_POST['dStartDate'] : '';

$dExpiryDate = isset($_POST['dExpiryDate']) ? $_POST['dExpiryDate'] : '';

$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : '';

$ePosition = isset($_POST['ePosition']) ? $_POST['ePosition'] : 'MainScreen';

$iImpression = isset($_POST['iImpression']) ? $_POST['iImpression'] : 1;

$eValidityType = isset($_POST['eValidityType']) ? $_POST['eValidityType'] : 'Permanent';

$eImpression = isset($_POST['eImpression']) ? $_POST['eImpression'] : 'Unlimited';

if ($eValidityType == "Permanent") {

    $dStartDate = date("Y-m-d");

    $dExpiryDate = "9999-12-31";
}

/* to fetch max iDisplayOrder from table for insert */

$select_order = $obj->MySQLSelect("SELECT count(iAdvertBannerId) AS iDispOrder FROM " . $tbl_name);

$iDispOrder = isset($select_order[0]['iDisplayOrder']) ? $select_order[0]['iDisplayOrder'] : 0;

$iDispOrder = $iDispOrder + 1; // Maximum order number

$iDispOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : $iDispOrder;

if (isset($_REQUEST['goback'])) {

    $goback = $_REQUEST['goback'];
}

$redirectUrl = $tconfig["tsite_url_main_admin"] . "advertise_banners.php";

$todayDate = date("Y-m-d");

if (isset($_POST['btnsubmit'])) {

    if ($action == "Add" && !$userObj->hasPermission('create-advertise-banner')) {

        $_SESSION['success'] = 3;

        $_SESSION['var_msg'] = 'You do not have permission to create Advertise Banner.';

        header("Location:" . $redirectUrl);

        exit;
    }

    if ($action == "Edit" && !$userObj->hasPermission('edit-advertise-banner')) {

        $_SESSION['success'] = 3;

        $_SESSION['var_msg'] = 'You do not have permission to update Advertise Banner.';

        header("Location:" . $redirectUrl);

        exit;
    }

    if (isset($_FILES['vBannerImage']) && $_FILES['vBannerImage']['name'] != "") {

        $filecheck = basename($_FILES['vBannerImage']['name']);

        $fileextarr = explode(".", $filecheck);

        $ext = strtolower($fileextarr[count($fileextarr) - 1]);

        $flag_error = 0;
        if ($ext != "png" && $ext != "jpg" && $ext != "jpeg" && $ext != "tif" && $ext != "gif") {

            $flag_error = 1;

            $var_msg = "You have selected wrong file format for Image. Valid formats are jpg,jpeg,png,tif,gif.";
        }

        $data = getimagesize($_FILES['vBannerImage']['tmp_name']);

        $width = $data[0];

        $height = $data[1];

        if ($flag_error == 1) {

            header("Location:advertise_banner_action.php?id=" . $id . "&usedcount=" . $usedCount . "&var_msg=" . $var_msg);

            exit;
        }
    }

    if (SITE_TYPE == 'Demo') {

        header("Location:advertise_banner_action.php?id=" . $id . "&usedcount=" . $usedCount . "&usedcount=" . $usedCount . "&success=2");

        exit;
    }

    $q = "INSERT INTO ";

    $where = $whereDispOrder = '';

    if ($id != '') {

        $q = "UPDATE ";

        $where = " WHERE `iAdvertBannerId` = '" . $id . "'";

        $whereDispOrder = " AND `iAdvertBannerId` != '" . $id . "'";
    }
    //Removed By HJ On 26-02-2019 As Per Discuss With CD Sir Start
    /* $checkDispOrder = $obj->MySQLSelect("SELECT iAdvertBannerId,vBannerTitle FROM  advertise_banners WHERE iDispOrder='" . $iDispOrder . "' AND eStatus='Active' $whereDispOrder");
      if (count($checkDispOrder) > 0) {
      $bannerName = $checkDispOrder[0]['vBannerTitle'];
      $dis_msg = "Display order already assign to banner - " . $bannerName;
      header("Location:advertise_banner_action.php?id=" . $id . "&usedcount=".$usedCount."&var_msg=" . $dis_msg);
      exit;
      } */
    //Removed By HJ On 26-02-2019 As Per Discuss With CD Sir End
    if ($eImpression != "Unlimited") {
        if ($usedCount > $iImpression) {

            $dis_msg = "Total impression count must be greater than the used count.";

            header("Location:advertise_banner_action.php?id=" . $id . "&usedcount=" . $usedCount . "&var_msg=" . $dis_msg);

            exit;
        }
    }

    $query = $q . " `" . $tbl_name . "` SET `vBannerTitle` = '" . $vBannerTitle . "',`tRedirectUrl` = '" . $tRedirectUrl . "',`eStatus` = '" . $eStatus . "',`ePosition` = '" . $ePosition . "',`dStartDate`= '" . $dStartDate . "',`dExpiryDate`= '" . $dExpiryDate . "',`dAddedDate`= '" . $todayDate . "',`iDispOrder` = '" . $iDispOrder . "',`iImpression` = '" . $iImpression . "',`eValidityType` = '" . $eValidityType . "',`eImpression`='" . $eImpression . "'" . $where;

    $obj->sql_query($query);

    $id = ($id != '') ? $id : $obj->GetInsertId();

    if (isset($_FILES['vBannerImage']) && $_FILES['vBannerImage']['name'] != "") {

        $currrent_upload_time = time();

        $img_path = $tconfig["tsite_upload_advertise_banner_path"];

        $temp_gallery = $img_path . '/';

        $image_object = $_FILES['vBannerImage']['tmp_name'];

        $image_name = $_FILES['vBannerImage']['name'];

        /* Calculate aspect ratio by dividing height by width */

        $check_file_query = "select iAdvertBannerId,vBannerImage from advertise_banners where iAdvertBannerId=" . $id;

        $check_file = $obj->sql_query($check_file_query);

        if ($image_name != "") {

            if ($message_print_id != "") {

                $check_file['vBannerImage'] = $img_path . '/' . $id . '/' . $check_file[0]['vBannerImage'];

                $android_path = $img_path . '/' . $id;

                if ($check_file['vBannerImage'] != '' && file_exists($check_file['vBannerImage'])) {

                    @unlink($check_file['vBannerImage']);
                }
            }

            //$Photo_Gallery_folder = $img_path . '/' . $id . '/';

            $Photo_Gallery_folder = $img_path . '/';

            $Photo_Gallery_folder_android = $Photo_Gallery_folder . 'android/';

            $Photo_Gallery_folder_ios = $Photo_Gallery_folder . 'ios/';

            if (!is_dir($Photo_Gallery_folder)) {

                mkdir($Photo_Gallery_folder, 0777);

                //mkdir($Photo_Gallery_folder_android, 0777);
                //mkdir($Photo_Gallery_folder_ios, 0777);
            }
            $img = $generalobj->imageupload($Photo_Gallery_folder, $image_object, $image_name, '', 'png,jpg,jpeg,tif,gif');
            if ($img[2] == "1") {
                $_SESSION['success'] = '0';
                $_SESSION['var_msg'] = $img[1];
                header("location:" . $backlink);
            }
            //$imgName = $generalobj->general_upload_image_vehicle_category_android($image_object, $image_name, $Photo_Gallery_folder, '', '', '', '', '', '', 'Y', '', $Photo_Gallery_folder, $vBannerTitle, NULL);

            $imgName = $generalobj->uploadImagesOrFiles($image_object, $image_name, $Photo_Gallery_folder, '', '', '', '', '', '', 'Y', '', $Photo_Gallery_folder, $vBannerTitle, NULL);
            $sql = "UPDATE " . $tbl_name . " SET `vBannerImage` = '" . $imgName . "' WHERE `iAdvertBannerId` = '" . $id . "'";
            $obj->sql_query($sql);
        }
    }



    //$obj->sql_query($query);

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

    $sql = "SELECT * FROM " . $tbl_name . " WHERE iAdvertBannerId = '" . $id . "'";

    $db_data = $obj->MySQLSelect($sql);

    $vLabel = $id;

    if (count($db_data) > 0) {

        foreach ($db_data as $key => $value) {

            $eStatus = $value['eStatus'];

            $vBannerTitle = $value['vBannerTitle'];

            $tRedirectUrl = $value['tRedirectUrl'];

            $ePosition = $value['ePosition'];

            $iImpression = $value['iImpression'];

            $dExpiryDate = $value['dExpiryDate'];

            $dAddedDate = $value['dAddedDate'];

            $dStartDate = $value['dStartDate'];

            $iDispOrder = $value['iDispOrder'];

            $vBannerImage = $value['vBannerImage'];

            $eValidityType = $value['eValidityType'];

            $eImpression = $value['eImpression'];
        }
    }
}

if ($eValidityType == "Permanent") {

    $dStartDate = $dExpiryDate = "";
}

$required_msg = '<span class="red"> *</span>';
?>

<!DOCTYPE html>

<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->

<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->

<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

    <!-- BEGIN HEAD-->

    <head>

        <meta charset="UTF-8" />

        <title>Admin | <?= $langage_lbl_admin['LBL_ADVERTISEMENT_BANNERS_TXT_ADMIN']; ?> <?= $action; ?></title>

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

                            <h2><?= $langage_lbl_admin['LBL_ADVERTISEMENT_BANNERS_TXT_ADMIN']; ?></h2>

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

                                <input type="hidden" name="backlink" id="backlink" value="advertise_banners.php"/>

                                <div class="row">

                                    <div class="col-lg-12">

                                        <label>Advertisement Name <?php echo $required_msg; ?></label>

                                    </div>

                                    <div class="col-lg-6">

                                        <input type="text" class="form-control" name="vBannerTitle" id="vBannerTitle" value="<?= $vBannerTitle; ?>" placeholder="Banner Title" required="">

                                    </div>

                                </div>

                                <div class="row Banner imagebox">

                                    <div class="col-lg-12">

                                        <label>Banner Image <?php echo $required_msg; ?></label>

                                    </div>

                                    <div class="col-lg-6">

                                        <?
                                        $requireImg = 'required="required"';

                                        if (isset($vBannerImage) && $vBannerImage != '') {

                                            $requireImg = '';
                                            ?>                                     

                                           <!--  <img src="<?= $tconfig['tsite_upload_advertise_banner'] . "/" . $vBannerImage; ?>" style="width:200px;"> -->

                                           <img src="<?= $tconfig["tsite_url"].'resizeImg.php?w=400&src='.$tconfig['tsite_upload_advertise_banner'] . "/" . $vBannerImage; ?>" style="width:200px;">

                                        <? } ?>

                                        <input value="<?= $vBannerImage; ?>" type="file" class="form-control" name="vBannerImage" <?php echo $required_rule; ?> id="vBannerImage" placeholder="" style="padding-bottom: 39px;" <?= $requireImg; ?>>

                                        [Note: Recommended dimension for banner image is 1024 * 1024.]

                                    </div>

                                </div>

                                <div class="row">

                                    <div class="col-lg-12">

                                        <label>Display Order</label>

                                    </div>

                                    <div class="col-lg-6">

                                        <input value="<?= $iDispOrder; ?>" type="text" class="form-control" name="iDisplayOrder" id="iDisplayOrder" placeholder="Display Order">

                                    </div>

                                </div>

                                <div class="row">

                                    <div class="col-lg-12">

                                        <label>Redirect Url</label>

                                    </div>

                                    <div class="col-lg-6">

                                        <input type="text" class="form-control" name="tRedirectUrl" id="tRedirectUrl" value="<?= $tRedirectUrl; ?>" placeholder="Redirect Url">

                                        [Note: Please enter the complete link with the 'http:// or https://]

                                    </div>

                                </div>

                                <div class="row">

                                    <div class="col-lg-12">

                                        <label>Validity :<span class="red"> *</span></label>

                                    </div>

                                    <div class="col-lg-6">

                                        <input type="radio" name="eValidityType" onClick="showhidedate(this.value)" value="Permanent"

                                               <?php if (isset($eValidityType) && $eValidityType == "Permanent") { ?> checked <?php } ?> >

                                        Permanent

                                        <input class="coup-act1" type="radio" name="eValidityType" onClick="showhidedate(this.value)" value="Custom" <?php if (isset($eValidityType) && $eValidityType == "Custom") { ?> checked <?php } ?> >

                                        Custom</div>

                                </div>

                                <div class="row" id="date1" style="display:none;">

                                    <div class="col-lg-12" >

                                        <label>Activation Date :<span class="red"> *</span></label>

                                    </div>

                                    <div class="col-lg-6">

                                        <input type="text" class="form-control" name="dStartDate"  id="dStartDate" value="<?= $dStartDate; ?>" placeholder="Activation Date" required="required">

                                    </div>

                                </div>

                                <div class="row" id="date2" style="display:none;">	

                                    <div class="col-lg-12">

                                        <label>Expiry Date:<span class="red"> *</span></label>

                                    </div>

                                    <div class="col-lg-6">

                                        <input type="text" class="form-control" name="dExpiryDate" value="<?= $dExpiryDate ?>"  id="dExpiryDate" placeholder="Expiry Date" required="required">

                                    </div>

                                </div>

                                <div class="row">

                                    <div class="col-lg-12">

                                        <label>Impression/ Click Count :<span class="red"> *</span>[Note: Add count only if the advertisement banner Redirect URL is added]</label>

                                    </div>

                                    <div class="col-lg-6">

                                        <input type="radio" name="eImpression" onClick="showhideimpression(this.value)" value="Unlimited"

                                               <?php if (isset($eImpression) && $eImpression == "Unlimited") { ?> checked <?php } ?> >

                                        Unlimited

                                        <input class="coup-act1" type="radio" name="eImpression" onClick="showhideimpression(this.value)" value="Limited" <?php if (isset($eImpression) && $eImpression == "Limited") { ?> checked <?php } ?> >

                                        Limited</div>

                                </div>

                                <div class="row" id="impression" style="display:none;">

                                    <div class="col-lg-12">

                                        <label>Impression Count <?php echo $required_msg; ?></label>

                                    </div>

                                    <div class="col-lg-6">

                                        <input type="number" min="1" class="form-control" name="iImpression" id="iImpression" value="<?= $iImpression; ?>" placeholder="Impression Count" required="">
                                        <!-- Note added after discussion with KS -->
                                        <span style="font-size:13px; font-weight: 400">[Note: Impression Count must be grater than used count, current used count is <?php echo ($usedCount > 0) ? $usedCount : 0; ?>]</span>
                                    </div>

                                </div>

                                <?php if ($userObj->hasPermission('update-status-advertise-banner')) { ?>

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

                                        <?php if (($action == 'Edit' && $userObj->hasPermission('edit-advertise-banner')) || ($action == 'Add' && $userObj->hasPermission('create-user-profile'))) { ?>

                                            <input type="submit" class="save btn-info" name="btnsubmit" id="btnsubmit" value="<?= $action; ?> Advertisement">

                                            <input type="reset" value="Reset" class="btn btn-default">

                                        <?php } ?>

                                        <a href="advertise_banners.php" class="btn btn-default back_link">Cancel</a>

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