<?php
include_once('../common.php');
include_once(TPATH_CLASS.'/class.general.php');
include_once(TPATH_CLASS.'/configuration.php');
include_once('../generalFunctions.php');

if (!isset($generalobjAdmin)) {
     require_once(TPATH_CLASS . "class.general_admin.php");
     $generalobjAdmin = new General_admin();
}
//$generalobjAdmin->check_member_login();
$script = 'App Main Screen Settings';

$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : '';
$PageType = isset($_REQUEST['PageType']) ? $_REQUEST['PageType'] : '';

$CPageType = strtoupper($PageType);
$configField = $CPageType."_SHOW_SELECTION";
$SHOW_TYPE = $generalobj->getConfigurations("configurations_cubejek",$configField);

// fetch all lang from language_master table
$sql = "SELECT vCode,vTitle,eDefault,eStatus FROM `language_master` WHERE eStatus = 'Active' ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = count($db_master);

$lbl = 'LBL_HEADER_RDU_'.$CPageType;
$lbl1 = 'LBL_HEADER_RDU_ON_DEMAND_'.$CPageType;

if(isset($_POST['frm_type']) && $_POST['frm_type']!="") {

  if($action == "Add" && !$userObj->hasPermission('create-app-home-settings')){
      $_SESSION['success'] = 3;
      $_SESSION['var_msg'] = 'You do not have permission to create Record.';
      header("Location:app_home_settings.php");
      exit;
  }

  if($action == "Edit" && !$userObj->hasPermission('edit-app-home-settings')){
      $_SESSION['success'] = 3;
      $_SESSION['var_msg'] = 'You do not have permission to update Record.';
      header("Location:app_home_settings.php");
      exit;
  }


  if(SITE_TYPE =='Demo'){
    $_SESSION['success'] = '2';
    $_SESSION['var_msg'] = $langage_lbl_admin['LBL_EDIT_DELETE_RECORD'];
     header("Location:app_home_settings.php");exit;
  }

  if($count_all > 0) {
    for($i=0;$i<$count_all;$i++) {
      $vLangCode = $db_master[$i]['vCode'];
      $vValueArr  = isset($_POST['vValue_'.$db_master[$i]['vCode']])? $_POST['vValue_'.$db_master[$i]['vCode']]:'';
      if(!empty($vValueArr)){
        foreach ($vValueArr as $LBL => $uValue) {
          $q = "INSERT INTO ";
          $Conwhere = '';
          $Csql = "SELECT vLabel,vValue FROM `language_label` WHERE vLabel = '".$LBL."' AND vCode = '".$vLangCode."'";
          $db_label_check = $obj->MySQLSelect($Csql);
          
          if(!empty($db_label_check)){
            $q = "UPDATE ";
            $Conwhere = "  WHERE vLabel = '".$LBL."' AND vCode = '".$vLangCode."'";
          }

          $Lquery = $q ." `language_label` SET
            `vLabel` = '".$LBL."',
            `lPage_id` = '0',
            `vCode` = '".$vLangCode."',
            `vValue` = '".addslashes($uValue)."'"
            .$Conwhere;
          $obj->sql_query($Lquery);

        }
      }
    }
  }

  $SHOW_TYPE = $_REQUEST[$configField];
 
  $where = " vName = '".$configField."' AND eType = '".$_REQUEST['frm_type']."'";
  $sql = "UPDATE configurations_cubejek SET `vValue` = '" . $SHOW_TYPE . "' WHERE $where";
  $res = $obj->sql_query($sql);    

  $GridIconType = $CPageType.'_GRID_ICON_NAME';
  $BannerImg = $CPageType."_BANNER_IMG_NAME";

  if($PageType == 'food_app' || $PageType == 'grocery_app' || $PageType == 'deliver_all_app'){
    $CPageTypeNew = $CPageType."_DETAIL";
    $GridIconType_detail = $CPageTypeNew.'_GRID_ICON_NAME';
    $BannerImg_detail = $CPageTypeNew."_BANNER_IMG_NAME";
  }

  if(isset($_FILES[$GridIconType]) && $_FILES[$GridIconType]['name'] != "") {
      $filecheck = basename($_FILES[$GridIconType]['name']);
      $fileextarr = explode(".", $filecheck);
      $ext = strtolower($fileextarr[count($fileextarr) - 1]);
      $flag_error = 0;
      if($ext != "png") {
        $flag_error = 1;
        $var_msg = "Upload only png  ".$PageType." Icon image";
      }
      $data = getimagesize($_FILES[$GridIconType]['tmp_name']);

      $width = $data[0];
      $height = $data[1];
      
      if($width != 360 && $height != 360) {
        $flag_error = 1;
        $var_msg = "Please Upload ".$PageType." Icon image only 360px * 360px";
      }

      if ($flag_error == 1) {
      //  header("Location:app_home_settings_action.php?success=0&var_msg=".$var_msg."&PageType=".$PageType);
       // exit;
      }
  }

    if(isset($_FILES[$GridIconType_detail]) && $_FILES[$GridIconType_detail]['name'] != "") {
      $filecheck = basename($_FILES[$GridIconType_detail]['name']);
      $fileextarr = explode(".", $filecheck);
      $ext = strtolower($fileextarr[count($fileextarr) - 1]);
      $flag_error = 0;
      if($ext != "png") {
        $flag_error = 1;
        $var_msg = "Upload only png  ".$PageType." Icon image";
      }
      $data = getimagesize($_FILES[$GridIconType_detail]['tmp_name']);

      $width = $data[0];
      $height = $data[1];
      
      if($GridIconType_detail != 'FOOD_APP_DETAIL_GRID_ICON_NAME'){
        if($width != 360 && $height != 360) {
          $flag_error = 1;
          $var_msg = "Please Upload ".$PageType." Icon image only 360px * 360px";
        }
      }

      if ($flag_error == 1) {
      //  header("Location:app_home_settings_action.php?success=0&var_msg=".$var_msg."&PageType=".$PageType);
       // exit;
      }
  }

  if(isset($_FILES[$BannerImg]) && $_FILES[$BannerImg]['name'] != "") {
    $filecheck = basename($_FILES[$BannerImg]['name']);
    $fileextarr = explode(".", $filecheck);
    $ext = strtolower($fileextarr[count($fileextarr) - 1]);
    $flag_error = 0;
    if($ext != "png" && $ext != "jpg" && $ext != "jpeg" && $ext != "gif" && $ext != "bmp") {
      $flag_error = 1;
      $var_msg = "You have selected wrong file format for Image. Valid formats are jpg,jpeg,gif,png,bmp.";
    }

    if($flag_error == 1) {
      header("Location:app_home_settings_action.php?success=0&var_msg=".$var_msg."&PageType=".$PageType);
      exit;
    }
  }

  if(isset($_FILES[$BannerImg_detail]) && $_FILES[$BannerImg_detail]['name'] != "") {
    $filecheck = basename($_FILES[$BannerImg_detail]['name']);
    $fileextarr = explode(".", $filecheck);
    $ext = strtolower($fileextarr[count($fileextarr) - 1]);
    $flag_error = 0;
    if($ext != "png" && $ext != "jpg" && $ext != "jpeg" && $ext != "gif" && $ext != "bmp") {
      $flag_error = 1;
      $var_msg = "You have selected wrong file format for Image. Valid formats are jpg,jpeg,gif,png,bmp.";
    }

    if($flag_error == 1) {
      header("Location:app_home_settings_action.php?success=0&var_msg=".$var_msg."&PageType=".$PageType);
      exit;
    }
  }

  if(isset($_FILES[$GridIconType]) && $_FILES[$GridIconType]['name'] != "") {
      $currrent_upload_time = time();   
      $img_path = $tconfig["tsite_upload_images_vehicle_category_path"];      
      $temp_gallery = $img_path . '/';
      $image_object = $_FILES[$GridIconType]['tmp_name'];
      $image_name = $_FILES[$GridIconType]['name'];

      $check_file_query = "select vName,vValue from configurations_cubejek where vName='".$GridIconType."'";
      $check_file = $obj->MySQLSelect($check_file_query);

      
      if($image_name != "") {
        $check_file[$GridIconType] = $img_path . '/' . $check_file[0]['vValue'];

      if ($check_file[$GridIconType] != '' && file_exists($check_file[$GridIconType])) {
        @unlink($check_file[$GridIconType]);
      }
        $Photo_Gallery_folder = $img_path . '/';
         
        $Photo_Gallery_folder_android = $Photo_Gallery_folder;

        if (!is_dir($Photo_Gallery_folder)) {
           mkdir($Photo_Gallery_folder, 0777);
           mkdir($Photo_Gallery_folder_android, 0777);
           mkdir($Photo_Gallery_folder_ios, 0777);
        }   
        
        $vVehicleType1 = $PageType.'_icon';

        $img = $generalobj->general_upload_image_vehicle_category_android($image_object, $image_name, $Photo_Gallery_folder_android,'','','','', '', '', 'Y','', $Photo_Gallery_folder_android,$vVehicleType1,NULL);

        $img_time = explode("_", $img);
        $time_val = $img_time[0];

        $filecheck = basename($_FILES[$GridIconType]['name']);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[count($fileextarr) - 1]);

        $vImage = "ic_car_".$PageType."_icon"."_".$time_val.".".$ext; 
        $where = " vName = '".$GridIconType."' AND eType = '".$_REQUEST['frm_type']."'";
       $sql = "UPDATE configurations_cubejek SET `vValue` = '" . $vImage . "' WHERE $where";
        $obj->sql_query($sql);
      }
    }

  if(isset($_FILES[$GridIconType_detail]) && $_FILES[$GridIconType_detail]['name'] != "") {
      $currrent_upload_time = time();   
      $img_path = $tconfig["tsite_upload_images_vehicle_category_path"];      
      $temp_gallery = $img_path . '/';
      $image_object = $_FILES[$GridIconType_detail]['tmp_name'];
      $image_name = $_FILES[$GridIconType_detail]['name'];

      $check_file_query = "select vName,vValue from configurations_cubejek where vName='".$GridIconType_detail."'";
      $check_file = $obj->MySQLSelect($check_file_query);

      
      if($image_name != "") {
        $check_file[$GridIconType_detail] = $img_path . '/' . $check_file[0]['vValue'];

      if ($check_file[$GridIconType_detail] != '' && file_exists($check_file[$GridIconType_detail])) {
        @unlink($check_file[$GridIconType_detail]);
      }
        $Photo_Gallery_folder = $img_path . '/';
         
        $Photo_Gallery_folder_android = $Photo_Gallery_folder;

        if (!is_dir($Photo_Gallery_folder)) {
           mkdir($Photo_Gallery_folder, 0777);
           mkdir($Photo_Gallery_folder_android, 0777);
           mkdir($Photo_Gallery_folder_ios, 0777);
        }   
        
        $vVehicleType1 = $PageType.'_icon';

        $img = $generalobj->general_upload_image_vehicle_category_android($image_object, $image_name, $Photo_Gallery_folder_android,'','','','', '', '', 'Y','', $Photo_Gallery_folder_android,$vVehicleType1,NULL);

        $img_time = explode("_", $img);
        $time_val = $img_time[0];

        $filecheck = basename($_FILES[$GridIconType_detail]['name']);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[count($fileextarr) - 1]);

        $vImage = "ic_car_".$PageType."_icon"."_".$time_val.".".$ext; 
        $where = " vName = '".$GridIconType_detail."' AND eType = '".$_REQUEST['frm_type']."'";
       $sql = "UPDATE configurations_cubejek SET `vValue` = '" . $vImage . "' WHERE $where";
        $obj->sql_query($sql);
      }
    }

  if(isset($_FILES[$BannerImg]) && $_FILES[$BannerImg]['name'] != "") {
   $currrent_upload_time = time();   
      $img_path = $tconfig["tsite_upload_images_vehicle_category_path"];      
      $temp_gallery = $img_path . '/';
      $image_object = $_FILES[$BannerImg]['tmp_name'];
      $image_name = $_FILES[$BannerImg]['name'];

      $data = getimagesize($_FILES[$BannerImg]['tmp_name']);
      $imgwidth = $data[0];
      $imgheight = $data[1];

      $aspectRatio = $width / $height;
      $aspect = round($aspectRatio, 2);
      if($aspect != "1.78") {
        echo"<script>alert('Please upload image with recommended dimensions and aspect ratio 16:9. Otherwise image will look stretched.');</script>";
      }

      if($width < 2880) {
         echo"<script>alert('Your Image upload size is less than recommended. Image will look stretched.');</script>";
      }

      if($width > 2880) {
        echo"<script>alert('Uploaded image size is larger than recommended size, Image may take much time to load.');</script>";
      }

      $check_file_query = "select vName,vValue from configurations_cubejek where vName='".$BannerImg."'";
      $check_file = $obj->MySQLSelect($check_file_query);

      
      if($image_name != "") {
        $check_file[$BannerImg] = $img_path . '/' . $check_file[0]['vValue'];

        if ($check_file[$BannerImg] != '' && file_exists($check_file[$BannerImg])) {
          @unlink($check_file[$BannerImg]);
        }
        $Photo_Gallery_folder = $img_path . '/';
         
        $Photo_Gallery_folder_android = $Photo_Gallery_folder;

        if (!is_dir($Photo_Gallery_folder)) {
           mkdir($Photo_Gallery_folder, 0777);
           mkdir($Photo_Gallery_folder_android, 0777);
           mkdir($Photo_Gallery_folder_ios, 0777);
        }   
        
        $vVehicleType1 = $PageType.'_banner';

        $img = $generalobj->general_upload_image_vehicle_category_android($image_object, $image_name, $Photo_Gallery_folder_android,'','','','', '', '', 'Y','', $Photo_Gallery_folder_android,$vVehicleType1,NULL);

        $img_time = explode("_", $img);
        $time_val = $img_time[0];

        $filecheck = basename($_FILES[$BannerImg]['name']);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[count($fileextarr) - 1]);

        $vImage = "ic_car_".$PageType."_banner_".$time_val.".".$ext; 
        $where = " vName = '".$BannerImg."' AND eType = '".$_REQUEST['frm_type']."'";
        $sql = "UPDATE configurations_cubejek SET `vValue` = '" . $vImage . "' WHERE $where";
        $obj->sql_query($sql);
      }
    }

    if(isset($_FILES[$BannerImg_detail]) && $_FILES[$BannerImg_detail]['name'] != "") {
      $currrent_upload_time = time();   
      $img_path = $tconfig["tsite_upload_images_vehicle_category_path"];      
      $temp_gallery = $img_path . '/';
      $image_object = $_FILES[$BannerImg_detail]['tmp_name'];
      $image_name = $_FILES[$BannerImg_detail]['name'];

      $data = getimagesize($_FILES[$BannerImg_detail]['tmp_name']);
      $imgwidth = $data[0];
      $imgheight = $data[1];

      $aspectRatio = $width / $height;
      $aspect = round($aspectRatio, 2);
      if($aspect != "1.78") {
        echo"<script>alert('Please upload image with recommended dimensions and aspect ratio 16:9. Otherwise image will look stretched.');</script>";
      }

      if($width < 2880) {
         echo"<script>alert('Your Image upload size is less than recommended. Image will look stretched.');</script>";
      }

      if($width > 2880) {
        echo"<script>alert('Uploaded image size is larger than recommended size, Image may take much time to load.');</script>";
      }

      $check_file_query = "select vName,vValue from configurations_cubejek where vName='".$BannerImg_detail."'";
      $check_file = $obj->MySQLSelect($check_file_query);

      
      if($image_name != "") {
        $check_file[$BannerImg_detail] = $img_path . '/' . $check_file[0]['vValue'];

        if ($check_file[$BannerImg_detail] != '' && file_exists($check_file[$BannerImg_detail])) {
          @unlink($check_file[$BannerImg_detail]);
        }
        $Photo_Gallery_folder = $img_path . '/';
         
        $Photo_Gallery_folder_android = $Photo_Gallery_folder;

        if (!is_dir($Photo_Gallery_folder)) {
           mkdir($Photo_Gallery_folder, 0777);
           mkdir($Photo_Gallery_folder_android, 0777);
           mkdir($Photo_Gallery_folder_ios, 0777);
        }   
        
        $vVehicleType1 = $PageType.'_banner';

        $img = $generalobj->general_upload_image_vehicle_category_android($image_object, $image_name, $Photo_Gallery_folder_android,'','','','', '', '', 'Y','', $Photo_Gallery_folder_android,$vVehicleType1,NULL);

        $img_time = explode("_", $img);
        $time_val = $img_time[0];

        $filecheck = basename($_FILES[$BannerImg_detail]['name']);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[count($fileextarr) - 1]);

        $vImage = "ic_car_".$PageType."_banner_".$time_val.".".$ext; 
        $where = " vName = '".$BannerImg_detail."' AND eType = '".$_REQUEST['frm_type']."'";
        $sql = "UPDATE configurations_cubejek SET `vValue` = '" . $vImage . "' WHERE $where";
        $obj->sql_query($sql);
      }
    }

    if($res) {
      $_SESSION['success'] = '1';
      $_SESSION['var_msg'] = 'Successfully updated main screen settings'; 
      /*    $success = 1;
      $var_msg = "Successfully updated main screen settings";*/
    } else {
      $_SESSION['success'] = '1';
      $_SESSION['var_msg'] = 'Error in update main screen settings'; 
     /* $success = 0;
      $var_msg = "Error in update main screen settings";*/
    }
  header("Location:app_home_settings.php");
  exit;
}

$sql = "SELECT iSettingId,tDescription,vName,vValue,eInputType,tSelectVal,tHelp,eType FROM configurations_cubejek WHERE eType = 'App Settings' AND vName LIKE '".$CPageType."%' ORDER BY eType, vOrder";
$data_gen = $obj->MySQLSelect($sql);

foreach ($data_gen as $key => $value) { 
  $db_gen[$value['eType']][$key]['iSettingId'] = $value['iSettingId'];
  $db_gen[$value['eType']][$key]['tDescription'] = $value['tDescription'];
  $db_gen[$value['eType']][$key]['vValue'] = $value['vValue'];
  $db_gen[$value['eType']][$key]['tHelp'] = $value['tHelp'];
  $db_gen[$value['eType']][$key]['vName'] = $value['vName'];
  $db_gen[$value['eType']][$key]['eInputType'] = $value['eInputType'];
  $db_gen[$value['eType']][$key]['tSelectVal'] = $value['tSelectVal'];
}

$labelsarray = array($lbl,$lbl1);
foreach ($labelsarray as $k => $val) {
  for($i=0;$i<$count_all;$i++) {
      $sql2 = "SELECT l.LanguageLabelId,l.vCode,l.vValue,l.vLabel,lm.vTitle,lm.eDefault FROM language_label as l LEFT JOIN language_master as lm on lm.vCode = l.vCode WHERE l.vLabel = '".$val."' AND lm.eStatus='Active' AND l.vCode='".$db_master[$i]['vCode']."'";
      $db_data_lang = $obj->MySQLSelect($sql2);
      $vCode = $db_master[$i]['vCode'];
      $vTitle = $db_master[$i]['vTitle'];
      $eDefault = $db_master[$i]['eDefault'];
      $vValue1[$val][$i]['LanguageLabelId'] = $db_data_lang[0]['LanguageLabelId'];
      $vValue1[$val][$i]['vLabel'] = $db_data_lang[0]['vLabel'];
      $vValue1[$val][$i]['vCode'] = $vCode;
      $vValue1[$val][$i]['vValue'] = $db_data_lang[0]['vValue'];
      $vValue1[$val][$i]['LBLFor'] = $db_data_lang[0]['vValue'];
      $vValue1[$val][$i]['vTitle'] = $vTitle;
      $vValue1[$val][$i]['eDefault'] = $eDefault;
  }
}
  if($PageType == 'food_app'){
    $array = array('LBL_FOOD_APP_INTRODUCING','LBL_FOOD_APP_DETAIL_NOTE','LBL_FOOD_APP_DOWNLOAD_NOW');
  } else if($PageType == 'grocery_app'){
    $array = array('LBL_GROCERY_APP_INTRODUCING','LBL_GROCERY_APP_DETAIL_NOTE','LBL_GROCERY_APP_DOWNLOAD_NOW');
  }  else if($PageType == 'deliver_all_app'){
    $array = array('LBL_DELIVER_ALL_APP_INTRODUCING','LBL_DELIVER_ALL_APP_DETAIL_NOTE','LBL_DELIVER_ALL_APP_DOWNLOAD_NOW');
  }
  foreach ($array as $key => $value) {
    for($i=0;$i<$count_all;$i++) {
        $sql = "SELECT l.LanguageLabelId,l.vLabel,l.vCode,l.vValue,lm.vTitle,lm.eDefault FROM language_label as l  JOIN language_master as lm on lm.vCode = l.vCode WHERE l.vLabel = '".$value."' AND lm.eStatus='Active' AND l.vCode='".$db_master[$i]['vCode']."'";
        $db_data_label = $obj->MySQLSelect($sql);
        $vCode = $db_master[$i]['vCode'];
        $vTitle = $db_master[$i]['vTitle'];
        $eDefault = $db_master[$i]['eDefault'];
        $landingpagelabels[$value][$i]['LanguageLabelId'] = $db_data_label[0]['LanguageLabelId'];
        $landingpagelabels[$value][$i]['vLabel'] = $db_data_label[0]['vLabel'];
        $landingpagelabels[$value][$i]['vCode'] = $vCode;
        $landingpagelabels[$value][$i]['vValue'] = $db_data_label[0]['vValue'];
        $landingpagelabels[$value][$i]['LBLFor'] = $db_data_label[0]['vValue'];
        $landingpagelabels[$value][$i]['vTitle'] = $vTitle;
        $landingpagelabels[$value][$i]['eDefault'] = $eDefault;
    }
  }


?>
<!DOCTYPE html>
<html lang="en">
 <head>
      <meta charset="UTF-8" />
      <title><?=$SITE_NAME?> | App Main Screen Settings </title>
      <meta content="width=device-width, initial-scale=1.0" name="viewport" />
      <?  include_once('global_files.php'); ?>
 </head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53">
<!-- MAIN WRAPPER -->
<div id="wrap">
  <? include_once('header.php');
  include_once('left_menu.php'); ?>
  <!--PAGE CONTENT -->
  <div id="content">
    <div class="inner">
      <div class="row">
          <div class="col-lg-12">
               <h2>App Main Screen Settings</h2>
               <a href="app_home_settings.php" class="back_link">
                    <input type="button" value="Back to Listing" class="add-btn">
               </a>
          </div>
      </div>
      <hr />
      <div class="body-div">
        <div class="row">
          <div class="col-lg-12">
          <? if ($_REQUEST['success']==1) {?>
            <div class="alert alert-success alert-dismissable">
              <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button> 
              <?= $var_msg ?>
            </div>
            <?}else if($_REQUEST['success']==2){ ?>
            <div class="alert alert-danger alert-dismissable">
              <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
              <?= $langage_lbl['LBL_EDIT_DELETE_RECORD']; ?>
            </div>
            <?php 
            } else if(isset($_REQUEST['success']) && $_REQUEST['success']==0){?>
            <div class="alert alert-danger alert-dismissable">
              <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button> 
              <?= $var_msg ?>
            </div>
            <? }
          ?>
          </div>
        </div>
        <div class="form-group">
          <form id="app_main_screen_form" name="app_main_screen_form" method="post" action="" enctype="multipart/form-data">
          <div class="row">
            <div class="col-lg-6">
              <? foreach ($db_gen as $key => $value) { ?>
                  <input type="hidden" name="frm_type" value="<?=$key?>">
                  <? foreach ($value as $key1 => $value1) { 
                      if($value1['vName'] == 'FOOD_APP_DETAIL_GRID_ICON_NAME' || $value1['vName'] == 'FOOD_APP_DETAIL_BANNER_IMG_NAME' || $value1['vName'] == 'FOOD_APP_PACKAGE_NAME' || $value1['vName'] == 'FOOD_APP_IOS_APP_ID' || $value1['vName'] == 'GROCERY_APP_DETAIL_GRID_ICON_NAME' || $value1['vName'] == 'GROCERY_APP_DETAIL_BANNER_IMG_NAME' || $value1['vName'] == 'GROCERY_APP_PACKAGE_NAME' || $value1['vName'] == 'GROCERY_APP_IOS_APP_ID' || $value1['vName'] == 'FOOD_APP_IOS_PACKAGE_NAME' || $value1['vName'] == 'GROCERY_APP_IOS_PACKAGE_NAME' ||  $value1['vName'] == 'DELIVER_ALL_APP_DETAIL_GRID_ICON_NAME' || $value1['vName'] == 'DELIVER_ALL_APP_DETAIL_BANNER_IMG_NAME' || $value1['vName'] == 'DELIVER_ALL_APP_PACKAGE_NAME' || $value1['vName'] == 'DELIVER_ALL_APP_IOS_APP_ID' || $value1['vName'] == 'DELIVER_ALL_APP_IOS_PACKAGE_NAME'){ 
                              continue;
                      }
                  ?>
                    <div class="form-group">
                      <label id="<?=$value1['vName']?>_LABEL"><?=$value1['tDescription']?><?php if($value1['tHelp']!=""){?> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='<?= htmlspecialchars($value1['tHelp'], ENT_QUOTES, 'UTF-8') ?>'></i><?php }?></label>
                    <?php if ($value1['eInputType'] == 'Select') {
                        $optionArr = explode(',', $value1['tSelectVal']); ?> 
                        <select class="form-control" name="<?=$value1['vName']?>" id="<?=$value1['vName']?>">
                        <?php
                          foreach ($optionArr as $oKey => $oValue) {
                            $selected = $oValue==$value1['vValue']?'selected':'';
                            if($oValue == 'None'){ ?>
                              <option value="<?=$oValue?>" <?=$selected?>> Disable/Hide </option>
                        <?php } else { ?>
                              <option value="<?=$oValue?>" <?=$selected?>><?=$oValue?></option>
                        <?php  } 
                        } ?>
                        </select>
                        [Note : Option Disable/Hide - The service won't available for apps]
                    <? } else if($value1['eInputType'] == 'Textarea') { ?>
                        <textarea class="form-control" rows="5" name="<?=$value1['vName']?>"><?=$value1['vValue']?></textarea>
                    <? } else { 
                          if($value1['vName'] == $CPageType.'_GRID_ICON_NAME' || $value1['vName'] == $CPageType.'_BANNER_IMG_NAME') {
                            ?>
                              <? if($value1['vValue'] != '') { ?>  
                                <div style="margin: 5px 0;" id="<?=$value1['vName']?>_IMG">
                                <img src="<?=$tconfig['tsite_upload_images_vehicle_category']."/".$value1['vValue'];?>" style="height:100px;"></div>
                              <?}?>
                              <input type="file" name="<?=$value1['vName']?>" class="form-control" value="<?=$value1['vValue']?>" id="<?=$value1['vName']?>">
                              <?php if($value1['vName'] ==  $CPageType.'_GRID_ICON_NAME') { ?>
                                <div class="note_icon">[Note: Upload only png image size of 360px*360px.]</div>
                              <?php } else { ?>
                                <div class="note_banner">[Note: Recommended dimension for banner image is 2880 * 1620.]</div>
                              <?php } ?>
                            <?  }  else {?>
                          <input type="text" name="<?=$value1['vName']?>" class="form-control" value="<?=$value1['vValue']?>" >
                    <?  }
                      } ?>
                  </div>
                <?  }
              } ?>
            </div>
          </div>
          <?  if($count_all > 0) {
            foreach ($vValue1 as $k1 => $v1) {
              $vLabel_vTitle = $k1;
              if (strpos($vLabel_vTitle, 'ON_DEMAND') !== false) {
                $title_lang = 'Text on Banner';
                $class='bannerlabel';
              } else {
                $title_lang = 'Text Under Icon';
                $class='iconlabel';
              }
            ?>
            <div class="row" style="display: none;">
              <div class="col-lg-12">
                <label><?= $title_lang?></label>
              </div>
              <div class="col-lg-6">
                <input type="text" class="form-control" name="vLabel[]"  id="vTitle" value="<?=$vLabel_vTitle;?>" placeholder="Language Label" disabled>
              </div>
            </div>
            <? foreach ($v1 as $key => $db_data) {
                $vCode = $db_data['vCode'];
                $vTitle = $db_data['vTitle'];
                $eDefault = $db_data['eDefault'];
                $answervalue = $db_data['vValue'];
                $vValue = 'vValue_'.$vCode;

                if($vCode != $default_lang){
                  $vValue_arr[] = $vValue.'_'.$vLabel_vTitle;
                }

                $required = ($eDefault == 'Yes')?'required':'';
                $required_msg = ($eDefault == 'Yes')?'<span class="red"> *</span>':'';
              ?>
              <div class="row <?=$class?>">
                <div class="col-lg-12">
                  <label><?= $title_lang?> ( <?=$vTitle;?> ) <?php echo $required_msg; ?></label>
                </div>
                <div class="col-lg-6">
                  <input type="text" class="form-control" name="<?=$vValue;?>[<?=$vLabel_vTitle?>]" id="<?=$vValue.'_'.$vLabel_vTitle?>" value="<?=$answervalue;?>" placeholder="<?=$vTitle;?> Value" <?=$required;?>>
                </div>
                <?php if($vCode== $default_lang  && count($db_master) > 1){ ?>
                  <div class="col-lg-2">
                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('<?=$vLabel_vTitle?>');">Convert To All Language</button>
                  </div>
                  <div class="col-lg-2">
                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="CopyAllLanguageCode('<?=$vLabel_vTitle?>');">Copy <?=$def_lang_name?> To All</button>
                  </div>
                <?php } ?>
              </div>
              <? }
            }
          } ?>
          <?php if($PageType == 'food_app' || $PageType == 'grocery_app'  || $PageType == 'deliver_all_app'){  ?>
            <div>
              <div class="form-group">
                <? if($PageType == 'food_app'){?>
                  <h4>FoodApp Page Settings</h4>
                <?php } else if($PageType == 'grocery_app'){ ?>
                  <h4>GroceryApp Page Settings</h4>
                <?php } else if($PageType == 'deliver_all_app'){ ?>
                  <h4>Deliver All APP Page Settings</h4>
                <?php } ?>
                  <?  if($count_all > 0) {
                    foreach ($landingpagelabels as $k1 => $v1) {
                       $vLabel_vTitle = $k1;
                    ?>
                    <div class="row" style="display: none;">
                      <div class="col-lg-12">
                        <label><?= $title_lang?></label>
                      </div>
                      <div class="col-lg-6">
                        <input type="text" class="form-control" name="vLabel[]"  id="vTitle" value="<?=$vLabel_vTitle;?>" placeholder="Language Label" disabled>
                      </div>
                    </div>
                    <?  foreach ($v1 as $key => $db_data) {
                        $vCode = $db_data['vCode'];
                        $vTitle = $db_data['vTitle'];
                        $eDefault = $db_data['eDefault'];
                        $answervalue = $db_data['vValue'];
                        $vValue = 'vValue_'.$vCode;

                        if($vCode != $default_lang){
                          $vValue_arr[] = $vValue.'_'.$vLabel_vTitle;
                        }

                        $required = ($eDefault == 'Yes')?'required':'';
                        $required_msg = ($eDefault == 'Yes')?'<span class="red"> *</span>':'';
                        if($db_data['vLabel'] == 'LBL_FOOD_APP_INTRODUCING' || $db_data['vLabel'] == 'LBL_GROCERY_APP_INTRODUCING' || $db_data['vLabel'] == 'LBL_DELIVER_ALL_APP_INTRODUCING'){
                          $title_lang = 'Introduction';
                        } else if($db_data['vLabel'] == 'LBL_FOOD_APP_DETAIL_NOTE' || $db_data['vLabel'] == 'LBL_GROCERY_APP_DETAIL_NOTE' || $db_data['vLabel'] == 'LBL_DELIVER_ALL_APP_DETAIL_NOTE'){
                          $title_lang = 'Note';
                        } else if($db_data['vLabel'] == 'LBL_FOOD_APP_DOWNLOAD_NOW' || $db_data['vLabel'] == 'LBL_GROCERY_APP_DOWNLOAD_NOW' || $db_data['vLabel'] == 'LBL_DELIVER_ALL_APP_DOWNLOAD_NOW'){
                          $title_lang = 'Download Button Text';
                        }
                      ?>
                      <div class="row">
                        <div class="col-lg-12">
                          <label><?= $title_lang?> ( <?=$vTitle;?> ) <?php echo $required_msg; ?></label>
                        </div>
                        <div class="col-lg-6">
                          <input type="text" class="form-control" name="<?=$vValue;?>[<?=$vLabel_vTitle?>]" id="<?=$vValue.'_'.$vLabel_vTitle?>" value="<?=$answervalue;?>" placeholder="<?=$vTitle;?> Value" <?=$required;?>>
                        </div>
                        <?php if($vCode== $default_lang  && count($db_master) > 1){ ?>
                          <div class="col-lg-2">
                            <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('<?=$vLabel_vTitle?>');">Convert To All Language</button>
                          </div>
                          <div class="col-lg-2">
                            <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="CopyAllLanguageCode('<?=$vLabel_vTitle?>');">Copy <?=$def_lang_name?> To All</button>
                          </div>
                        <?php } ?>
                      </div>
                      <? }
                    }
                  } ?>
                  <div class="row">
                    <div class="col-lg-6">
                      <? foreach ($db_gen as $key => $value) { ?>
                          <input type="hidden" name="frm_type" value="<?=$key?>">
                          <? foreach ($value as $key1 => $val) { 
                              if($val['vName'] == 'FOOD_APP_SHOW_SELECTION' || $val['vName'] == 'FOOD_APP_BANNER_IMG_NAME' || $val['vName'] == 'FOOD_APP_GRID_ICON_NAME' || $val['vName'] == 'GROCERY_APP_SHOW_SELECTION' || $val['vName'] == 'GROCERY_APP_BANNER_IMG_NAME' || $val['vName'] == 'GROCERY_APP_GRID_ICON_NAME' || $val['vName'] == 'DELIVER_ALL_APP_SHOW_SELECTION' || $val['vName'] == 'DELIVER_ALL_APP_BANNER_IMG_NAME' || $val['vName'] == 'DELIVER_ALL_APP_GRID_ICON_NAME'){ 
                                      continue;
                              }
                          ?>
                            <div class="form-group">
                              <label id="<?=$val['vName']?>_LABEL"><?=$val['tDescription']?><?php if($val['tHelp']!=""){?> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='<?= htmlspecialchars($val['tHelp'], ENT_QUOTES, 'UTF-8') ?>'></i><?php }?></label>
                            <?php if ($val['eInputType'] == 'Select') {
                                $optionArr = explode(',', $val['tSelectVal']); ?> 
                                <select class="form-control" name="<?=$val['vName']?>" id="<?=$val['vName']?>">
                                <?php
                                  foreach ($optionArr as $oKey => $oValue) {
                                    $selected = $oValue==$val['vValue']?'selected':'';
                                    if($oValue == 'None'){ ?>
                                      <option value="<?=$oValue?>" <?=$selected?>> Disable/Hide </option>
                                <?php } else { ?>
                                      <option value="<?=$oValue?>" <?=$selected?>><?=$oValue?></option>
                                <?php  } 
                                } ?>
                                </select>
                                [Note : Option Disable/Hide - The service won't available for apps]
                            <? } else if($val['eInputType'] == 'Textarea') { ?>
                                <textarea class="form-control" rows="5" name="<?=$val['vName']?>"><?=$val['vValue']?></textarea>
                            <? } else {
                              if($PageType == 'food_app'){
                                $CPageTypenew = 'FOOD_APP_DETAIL';
                              } else if($PageType == 'grocery_app'){
                                $CPageTypenew = 'GROCERY_APP_DETAIL';
                              } else if($PageType == 'deliver_all_app'){
                                $CPageTypenew = 'DELIVER_ALL_APP_DETAIL';
                              }
                                  if($val['vName'] == $CPageTypenew.'_GRID_ICON_NAME' || $val['vName'] == $CPageTypenew.'_BANNER_IMG_NAME') {
                                    ?>
                                      <? if($val['vValue'] != '') { ?>  
                                        <div style="margin: 5px 0;" id="<?=$val['vName']?>_IMG">
                                        <img src="<?=$tconfig['tsite_upload_images_vehicle_category']."/".$val['vValue'];?>" style="height:100px;"></div>
                                      <?}?>
                                      <input type="file" name="<?=$val['vName']?>" class="form-control" value="<?=$val['vValue']?>" id="<?=$val['vName']?>">
                                      <?php if($val['vName'] ==  $CPageTypenew.'_BANNER_IMG_NAME') { ?>
                                         <div class="note_banner">[Note: Recommended dimension for banner image is 2880 * 2160.]</div>
                                      <?php } ?>
                                    <?  }  else {?>
                                  <input type="text" name="<?=$val['vName']?>" class="form-control" value="<?=$val['vValue']?>" >
                            <?  }
                              } ?>
                          </div>
                        <? }
                      } ?>
                    </div>
                  </div>
              </div>
            </div>
          <?php } ?>
            <div class="row">
              <div class="col-lg-6">
                <div class="form-group">
                  <?php if($userObj->hasPermission('edit-app-home-settings')){ ?>
                    <button class="btn btn-primary save-configuration" type="submit">Save Changes</button>
                  <?php }else{?>
                    <a href="app_home_settings.php" class="btn btn-default back_link">Cancel</a>
                  <?php } ?>
                </div>
              </div>
            </div>
         </form>
        </div>
      </div>
    </div>
  </div>
  <!--END PAGE CONTENT -->
</div>

<div class="row loding-action" id="imageIcon" style="display:none;">
  <div align="center">                                                                       
    <img src="default.gif">                                                              
    <span>Language Translation is in Process. Please Wait...</span>                       
  </div>                                                                                 
</div>
<!--END MAIN WRAPPER -->
<?php include_once('footer.php'); ?>
<script>
$(document).ready(function(){
    $('#imageIcon').hide();
}); 
var PageType ='<?= strtoupper($PageType)?>';
$(function() {
  var ride_delivery = $('#'+PageType+'_SHOW_SELECTION').val();
  if(ride_delivery == 'Icon') {
        $('#'+PageType+'_GRID_ICON_NAME').show();
        $('#'+PageType+'_GRID_ICON_NAME_LABEL').show();
        $('#'+PageType+'_GRID_ICON_NAME_IMG').show();
        $('#'+PageType+'_BANNER_IMG_NAME').hide();
        $('#'+PageType+'_BANNER_IMG_NAME_LABEL').hide();
        $('#'+PageType+'_BANNER_IMG_NAME_IMG').hide();
        $('.note_icon').show();
        $('.note_banner').hide();
        $('.iconlabel').show();
        $('.bannerlabel').hide();
    } else if(ride_delivery == 'Banner') {
      $('#'+PageType+'_GRID_ICON_NAME').show();
      $('#'+PageType+'_GRID_ICON_NAME_LABEL').show();
      $('#'+PageType+'_GRID_ICON_NAME_IMG').show();
      $('#'+PageType+'_BANNER_IMG_NAME').show();
      $('#'+PageType+'_BANNER_IMG_NAME_LABEL').show();
      $('#'+PageType+'_BANNER_IMG_NAME_IMG').show();
      $('.note_icon').show();
      $('.note_banner').show();
      $('.iconlabel').show();
      $('.bannerlabel').show();
    } else if(ride_delivery == 'Icon-Banner') {
        $('#'+PageType+'_GRID_ICON_NAME').show();
        $('#'+PageType+'_GRID_ICON_NAME_LABEL').show();
        $('#'+PageType+'_GRID_ICON_NAME_IMG').show();
        $('#'+PageType+'_BANNER_IMG_NAME').show();
        $('#'+PageType+'_BANNER_IMG_NAME_LABEL').show();
        $('#'+PageType+'_BANNER_IMG_NAME_IMG').show();
        $('.note_icon').show();
        $('.note_banner').show();
        $('.iconlabel').show();
        $('.bannerlabel').show();
    } else {
        $('#'+PageType+'_GRID_ICON_NAME').hide();
        $('#'+PageType+'_GRID_ICON_NAME_LABEL').hide();
        $('#'+PageType+'_GRID_ICON_NAME_IMG').hide();
        $('#'+PageType+'_BANNER_IMG_NAME').hide();
        $('#'+PageType+'_BANNER_IMG_NAME_LABEL').hide();
        $('#'+PageType+'_BANNER_IMG_NAME_IMG').hide();
        $('.note_icon').hide();
        $('.note_banner').hide();
        $('.iconlabel').hide();
        $('.bannerlabel').hide();
    }

  $('#'+PageType+'_SHOW_SELECTION').change(function(){
      if($('#'+PageType+'_SHOW_SELECTION').val() == 'Icon') {
          $('#'+PageType+'_GRID_ICON_NAME').show();
          $('#'+PageType+'_GRID_ICON_NAME_LABEL').show();
          $('#'+PageType+'_GRID_ICON_NAME_IMG').show();
          $('#'+PageType+'_BANNER_IMG_NAME').hide();
          $('#'+PageType+'_BANNER_IMG_NAME_LABEL').hide();
          $('#'+PageType+'_BANNER_IMG_NAME_IMG').hide();
          $('.note_icon').show();
          $('.note_banner').hide();
          $('.iconlabel').show();
          $('.bannerlabel').hide();
      } else if($('#'+PageType+'_SHOW_SELECTION').val() == 'Banner') {
        $('#'+PageType+'_GRID_ICON_NAME').show();
        $('#'+PageType+'_GRID_ICON_NAME_LABEL').show();
        $('#'+PageType+'_GRID_ICON_NAME_IMG').show();
        $('#'+PageType+'_BANNER_IMG_NAME').show();
        $('#'+PageType+'_BANNER_IMG_NAME_LABEL').show();
        $('#'+PageType+'_BANNER_IMG_NAME_IMG').show();
        $('.note_icon').show();
        $('.note_banner').show();
        $('.iconlabel').show();
        $('.bannerlabel').show();
      } else if($('#'+PageType+'_SHOW_SELECTION').val() == 'Icon-Banner') {
          $('#'+PageType+'_GRID_ICON_NAME').show();
          $('#'+PageType+'_GRID_ICON_NAME_LABEL').show();
          $('#'+PageType+'_GRID_ICON_NAME_IMG').show();
          $('#'+PageType+'_BANNER_IMG_NAME').show();
          $('#'+PageType+'_BANNER_IMG_NAME_LABEL').show();
          $('#'+PageType+'_BANNER_IMG_NAME_IMG').show();
          $('.note_icon').show();
          $('.note_banner').show();
          $('.iconlabel').show();
          $('.bannerlabel').show();
      } else {
        alert("Please make sure to disable vehicle types of selected service before disable/hide this feature.");
        $('#'+PageType+'_GRID_ICON_NAME').hide();
        $('#'+PageType+'_GRID_ICON_NAME_LABEL').hide();
        $('#'+PageType+'_GRID_ICON_NAME_IMG').hide();
        $('#'+PageType+'_BANNER_IMG_NAME').hide();
        $('#'+PageType+'_BANNER_IMG_NAME_LABEL').hide();
        $('#'+PageType+'_BANNER_IMG_NAME_IMG').hide();
        $('.note_icon').hide();
        $('.note_banner').hide();
        $('.iconlabel').hide();
        $('.bannerlabel').hide();
      }
  });
});
  
function getAllLanguageCode(vLabel_vTitle){
var def_lang = '<?=$default_lang?>';
var def_lang_name = '<?=$def_lang_name?>';
var getEnglishText = $('#vValue_'+def_lang+'_'+vLabel_vTitle).val();
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
              console.log(response);
                 $.each(response,function(name, Value){
                    $('#'+name+'_'+vLabel_vTitle).val(Value);
                 });
                 $('#imageIcon').hide();
            }
    });
  }
}

function CopyAllLanguageCode(vLabel_vTitle){
  var def_lang = '<?=$default_lang?>';
  var def_lang_name = '<?=$def_lang_name?>';
  var getEnglishText = $('#vValue_'+def_lang+'_'+vLabel_vTitle).val();
  var vNameArray = <?php echo json_encode($vValue_arr); ?>;

  if(getEnglishText != ''){
    jQuery.each( vNameArray, function( i, val ) {
        if(val.indexOf(vLabel_vTitle) != -1){
          document.getElementById(val).value = getEnglishText;
        }
    });
 
  } else {
      alert("Please Fill "+ def_lang_name +" value for copy text in other field.");
  }
}
</script>
</body>
<!-- END BODY-->
</html>