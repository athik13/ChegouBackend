<?php

/*
 *
 * Created By : HJ.
 * Date : 31-Jan-2019.
 * File : ajax_dropzon_upload.
 * File Type : .php.
 * Purpose : For Upload and Removed Provider Images By Dropzon
 * */
include_once('common.php');
session_start();
$driverId = $_SESSION['sess_iUserId'];
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$providerImgName = isset($_REQUEST['imgname']) ? $_REQUEST['imgname'] : '';
$img_path = $tconfig["tsite_upload_provider_image_path"];
$dateTime = date("Y-m-d H:is");
if (isset($_FILES['file']) && $_FILES['file'] != "" && $action == "upload" && $driverId > 0) {
    $time_val = time();
    $currrent_upload_time = time();
    $temp_gallery = $img_path . '/';
    $image_object = $_FILES['file']['tmp_name'];
    $image_name = $_FILES['file']['name'];
    if ($image_name != "") {
        $Photo_Gallery_folder = $img_path . '/';
        $Photo_Gallery_folder_android = $Photo_Gallery_folder . 'android/';
        $Photo_Gallery_folder_ios = $Photo_Gallery_folder . 'ios/';
        if (!is_dir($Photo_Gallery_folder)) {
            mkdir($Photo_Gallery_folder, 0777);
        }
        //$image_name = $image_name
        $filecheck = basename($_FILES['file']['name']);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[count($fileextarr) - 1]);
        $image_name = "provider_" . $driverId . "_" . $time_val . "." . $ext;
        $img = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, '', '', '', '', '', '', 'Y', '', $Photo_Gallery_folder, $vBannerTitle, NULL);
        //print_r($img);die;
        //$img_time = explode("_", $img);
        //$time_val = $img_time[0];
        $provider_image = array();
        $provider_image['vImage'] = $img;
        $provider_image['iDriverId'] = $driverId;
        $provider_image['tAddedDate'] = $provider_image['tModifiedDate'] = $dateTime;
        $id = $obj->MySQLQueryPerform("provider_images", $provider_image, 'insert');
    }
} else if ($action == "delete" && $providerImgName != "") {
    $providerImgName = json_decode(stripcslashes($providerImgName), true);
    $deleteImg = $obj->sql_query("DELETE FROM provider_images WHERE `vImage`='" . $providerImgName . "'");
    $unlinkFilePath = $img_path . '/' . $providerImgName;
    if ($unlinkFilePath != '' && file_exists($unlinkFilePath)) {
        @unlink($unlinkFilePath);
    }
    echo "Image Removed Successfully";die;
    /* $whereCondition = "vImage='" . $providerImgName . "'";
      $provider_image = array();
      $provider_image['eStatus'] = $img;
      $provider_image['tModifiedDate'] = $dateTime;
      $id = $obj->MySQLQueryPerform("provider_images", $provider_image, 'update', $whereCondition); */
}else{
    echo "Sorry, Image data not found";die;
}
?>