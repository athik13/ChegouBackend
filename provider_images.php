<?php
include_once('common.php');
$generalobj->check_member_login();

require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();

$script = "Vehicle";
$abc = 'driver';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$generalobj->setRole($abc, $url);
$tbl_name = 'driver_vehicle';

$success = isset($_GET['success']) ? $_GET['success'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';
$driverid = $_SESSION['sess_iUserId'];
$error = isset($_GET['success']) && $_GET['success'] == 0 ? 1 : '';
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : '';
$getProviderImages = $obj->MySQLSelect("SELECT * FROM provider_images WHERE iDriverId='" . $driverid . "' AND eStatus='Active'");
//echo "<pre>";
$providerImgesArr = array();
$providerImgUrl = $tconfig["tsite_upload_provider_image"];
$providerImgPath = $tconfig["tsite_upload_provider_image_path"];
$tSiteUrl = $tconfig["tsite_url"];
$comfirmMessage = $langage_lbl['LBL_DELETE_IMG_CONFIRM_NOTE_WEB'];
echo "<script>localStorage.confirmmessage = '$comfirmMessage';</script>";
?>
<script type="text/javascript">
    var existingFiles = [];
</script>
<?php
for ($r = 0; $r < count($getProviderImages); $r++) {
    $imgTmpArr = array();
    $imgTmpArr['imageUrl'] = $providerImgUrl . "/" . $getProviderImages[$r]['vImage'];
    $imgTmpArr['imagePath'] = $providerImgPath . "/" . $getProviderImages[$r]['vImage'];
    $imgFileSize = filesize($imgTmpArr['imagePath']);
    $imgTmpArr['name'] = $getProviderImages[$r]['vImage'];
    $imgTmpArr['size'] = $imgFileSize;
    //$providerImgesArr[] = $imgTmpArr; 
    ?><script>
            existingFiles.push(<?= json_encode($imgTmpArr); ?>);</script><?php
}
//print_r($providerImgesArr);die;
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_VEHICLES']; ?></title>
        <!-- Default Top Script and css -->
        <?php include_once("top/top_script.php"); ?>
        <link rel="stylesheet" href="assets/css/bootstrap-fileupload.min.css" />
        <? if ($APP_TYPE == 'Ride-Delivery-UberX') { ?>
            <link rel="stylesheet" type="text/css" href="assets/css/vehicles_cubejek.css">
        <? } else { ?>
            <link rel="stylesheet" type="text/css" href="assets/css/vehicles.css">
        <? } ?>
        <link href="assets/plugins/dropzone/css/dropzone.css" rel="stylesheet"/>
        <style>
            .fileupload-preview  { line-height:150px;}
        </style>
        <!-- End: Default Top Script and css-->
    </head>
    <body>
        <!-- home page -->
        <div id="main-uber-page">
            <!-- Top Menu -->
            <!-- Left Menu -->
            <?php include_once("top/left_menu.php"); ?>
            <!-- End: Left Menu-->
            <?php include_once("top/header_topbar.php"); ?>
            <!-- End: Top Menu-->
            <!-- contact page-->
            <div class="page-contant">
                <!-- page start-->  
                <div class="page-contant-inner">
                    <h2 class="header-page"><?= $langage_lbl['LBL_SERVICE_TXT'] . " " . $langage_lbl['LBL_IMAGE'] . "s"; ?></h2>
                    <div class="trips-table">
                        <?= $langage_lbl['LBL_DROPZONE_UPLOAD_IMAGE_TXT']; ?>
                        <form action="<?= $tSiteUrl; ?>ajax_dropzon_upload.php?action=upload" class="dropzone" id="my-awesome-dropzone"></form>
                    </div>
                </div>
                <!-- page end-->
            </div>

            <!-- footer part -->
            <?php include_once('footer/footer_home.php'); ?>
            <!-- footer part end -->
            <!-- End:contact page-->
            <div style="clear:both;"></div>
        </div>
        <!-- home page end-->
        <!-- Footer Script -->
        <?php include_once('top/footer_script.php'); ?>
        <script src="assets/plugins/dropzone/dropzone.js"></script>

    </body>
</html>

