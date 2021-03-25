<?php
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}

$sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = "";
//$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

$message_print_id = $id;
$vCode = isset($_POST['vCode']) ? $_POST['vCode'] : '';
$tbl_name = $script = 'homecontent';
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : "";

$third_mid_image_three1 = $third_mid_title_three1 = $third_mid_title_three = $third_mid_desc_three1 = $mobile_app_bg_img1 = $third_mid_desc_one1 = '';

if (isset($_REQUEST['goback'])) {
    $goback = $_REQUEST['goback'];
}

$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive'; 

$cubexthemeonh = 0;
if($generalobj->checkCubeJekXThemOn()=='Yes') {
    $cubexthemeonh = 1;
}
if (isset($_POST['removeicon']) && $_POST['removeicon'] == 'remove') {
    $_POST['removeicon'] = "";
    $removeiconid = isset($_POST['removeid']) ? $_POST['removeid'] : 0;
    $img_path = $tconfig["tsite_upload_home_page_service_images_panel"];
    $temp_gallery = $img_path . '/';
    $image_object = $value['tmp_name'];
    $image_name = $value['name'];
    $check_file_query = "SELECT vHomepageLogo FROM ".$sql_vehicle_category_table_name." where iVehicleCategoryId='" . $removeiconid . "'";
    $check_file = $obj->MySQLSelect($check_file_query);
    if (!empty($check_file[0]['vHomepageLogo'])) {

        $check_file = $img_path . '/' . $check_file[0]['vHomepageLogo'];
        if ($check_file != '' && file_exists($check_file)) {
            @unlink($check_file);
        }
        $sql = "UPDATE ".$sql_vehicle_category_table_name." SET vHomepageLogo='' WHERE iVehicleCategoryId = '" . $removeiconid . "'";
        $obj->sql_query($sql);
    }
    $_SESSION['success'] = '1';
    $_SESSION['var_msg'] = $langage_lbl_admin['LBL_ICON_RMOVE_SUCESSFULLY'];
}

if($cubexthemeonh == 1) {
    $script = 'homecontent_cubejekx';
    $tbl_name = $generalobj->getAppTypeWiseHomeTable();

    $header_first_label = $third_sec_desc = $third_mid_desc_two1 = $home_banner_left_image = $mobile_app_right_title = $mobile_app_right_desc = $taxi_app_left_img = $manual_order_first_label = $manual_order_second_label = $manual_order_button_label = $manual_order_desc = '';
    $iLanguageMasId = 0;
    
    if(empty($vCode)) {
       $sql = "SELECT hc.vCode, lm.iLanguageMasId FROM $tbl_name as hc LEFT JOIN language_master as lm on lm.vCode = hc.vCode  WHERE hc.id = '" . $id . "'";
       $db_data = $obj->MySQLSelect($sql);
       $vCode = $db_data[0]['vCode'];
       $iLanguageMasId = $db_data[0]['iLanguageMasId'];
    }
    
    $earnBusinessDetailsquery = "SELECT learnServiceCatSection,lbusinessServiceCatSection FROM $tbl_name where vCode='".$vCode."'";
    $earnBusinessData = $obj->MySQLSelect($earnBusinessDetailsquery);

    if(empty($earnBusinessData[0]['learnServiceCatSection'])) {
        $earnDetails['earn']['title'] = 'Earn';
        $earnDetails['earn']['subtitle'] = 'Earn Handsome Commission';
        $earnDetails['earn']['desc'] = "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled.";
        $earnDetails['earn']['images'] = 'earn.svg';

        $earnDetailsJson = $obj->SqlEscapeString(json_encode($earnDetails));
        $query = "UPDATE `" . $tbl_name . "` SET
        `learnServiceCatSection` = '" . $earnDetailsJson . "' WHERE `vCode` = '".$vCode."'";
        $id = $obj->sql_query($query);
    }
    
    if(empty($earnBusinessData[0]['lbusinessServiceCatSection'])) {
        $businessDetails['business']['title'] = 'Business';
        $businessDetails['business']['subtitle'] = 'Business';
        $businessDetails['business']['desc'] = "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled.";
        $businessDetails['business']['images'] = 'business.svg';

        $businessDetailsJson = $obj->SqlEscapeString(json_encode($businessDetails));
        $query = "UPDATE `" . $tbl_name . "` SET
        `lbusinessServiceCatSection` = '" . $businessDetailsJson . "' WHERE `vCode` = '".$vCode."'";
        $id = $obj->sql_query($query);
    }

    $img_arr = $_FILES;
    //print_R($_FILES);
    //exit;
    //$img_arr['call_section_img'] = $_FILES['call_section_img'];
    if (!empty($img_arr)) {
        //$img_arr['call_section_img'] = $_FILES['call_section_img'];
        foreach ($img_arr as $key => $value) {
            if($key == 'vHomepageLogo') continue;
            if (!empty($value['name'])) {
                $img_path = $tconfig["tsite_upload_apptype_page_images_panel"];
                //$temp_gallery = $img_path . '/';
                $image_object = $value['tmp_name'];
                $img_name = explode('.',$value['name']);
                $image_name = $img_name[0]."_".strtotime(date("H:i:s")).".".$img_name[1];
                
                $second_gen_img = $second_down_img = 0;
                if($key=='general_section_img_sec') {
                    $second_gen_img = 1;
                 }
                 if($key=='download_section_img_first') {
                    $second_down_img = 1;
                 }
                 if($key=='download_section_img_sec') {
                    $second_down_img = 2;
                 }
                 if($key=='download_section_img_third') {
                    $second_down_img = 3;
                 }

                if($key=='how_it_work_section_img') $key = 'lHowitworkSection';
                else if($key=='download_section_img' || $key=='download_section_img_first' || $key=='download_section_img_sec' || $key=='download_section_img_third') $key = 'lDownloadappSection';
                else if($key=='secure_section_img') $key = 'lSecuresafeSection';
                else if($key=='call_section_img') $key = 'lCalltobookSection';
                else if($key=='general_section_img') $key = 'lGeneralBannerSection';
                else if($key=='general_section_img_sec') $key = 'lGeneralBannerSection';

                $check_file_query = "SELECT " . $key . " FROM $tbl_name where vCode='" . $vCode . "'";
                $check_file = $obj->MySQLSelect($check_file_query);
                $sectionData = json_decode($check_file[0][$key],true);

                if($second_gen_img==1) {
                    if ($message_print_id != "" && $sectionData['img_sec']!='') {
                        $check_file = $img_path . $template .'/' . $sectionData['img_sec'];
                        if ($check_file != '' && file_exists($check_file)) {
                            @unlink($check_file);
                        }
                    }
                } else if($second_down_img==1) {
                    if ($message_print_id != "" && $sectionData['img_first']!='') {
                        $check_file = $img_path . $template .'/' . $sectionData['img_first'];
                        if ($check_file != '' && file_exists($check_file)) {
                            @unlink($check_file);
                        }
                    }
                } else if($second_down_img==2) {
                    if ($message_print_id != "" && $sectionData['img_sec']!='') {
                        $check_file = $img_path . $template .'/' . $sectionData['img_sec'];
                        if ($check_file != '' && file_exists($check_file)) {
                            @unlink($check_file);
                        }
                    }
                } else if($second_down_img==3) {
                    if ($message_print_id != "" && $sectionData['img_third']!='') {
                        $check_file = $img_path . $template .'/' . $sectionData['img_third'];
                        if ($check_file != '' && file_exists($check_file)) {
                            @unlink($check_file);
                        }
                    }
                } else {
                    if ($message_print_id != "" && $sectionData['img']!='') {
                        $check_file = $img_path . $template .'/' . $sectionData['img'];
                        if ($check_file != '' && file_exists($check_file)) {
                            @unlink($check_file);
                        }
                    }   
                }
                 
                $Photo_Gallery_folder = $img_path . $template . "/";
                if (!is_dir($Photo_Gallery_folder)) {
                    mkdir($Photo_Gallery_folder, 0777);
                }

                $img = $generalobj->fileupload_home($Photo_Gallery_folder, $image_object, $image_name, '', 'png,jpg,jpeg,gif', $vCode);

                if ($img[2] == "1") {
                    $_SESSION['success'] = '0';
                    $_SESSION['var_msg'] = $img[1];
                    header("location:" . $backlink);
                }

                if (!empty($img[0])) {
                    if($second_gen_img==1) {
                        $sectionData['img_sec'] = $img[0];
                    } else if($second_down_img==1) {
                        $sectionData['img_first'] = $img[0];
                    } else if($second_down_img==2) {
                        $sectionData['img_sec'] = $img[0];
                    } else if($second_down_img==3) {
                        $sectionData['img_third'] = $img[0];
                    } else {
                        $sectionData['img'] = $img[0];
                    }
                    $sectionDatajson = $generalobj->getJsonFromAnArr($sectionData);
                    $sql = "UPDATE " . $tbl_name . " SET " . $key . " = '" . $sectionDatajson . "' WHERE `vCode` = '" . $vCode . "'";
                    $obj->sql_query($sql);
                }
            }
        }
    }

    if(isset($_POST['submit'])) {
        
        $check_file_query = "SELECT lHowitworkSection,lSecuresafeSection,lDownloadappSection,lCalltobookSection,lGeneralBannerSection FROM $tbl_name where vCode='" . $vCode . "'";
        $check_file = $obj->MySQLSelect($check_file_query);
        $sectionData = json_decode($check_file[0]['lHowitworkSection'],true);

        $how_it_work_section_arr['title'] = isset($_POST['how_it_work_section_title']) ? $_POST['how_it_work_section_title'] : '';
        $how_it_work_section_arr['desc'] = isset($_POST['how_it_work_section_desc']) ? $_POST['how_it_work_section_desc'] : '';
        $how_it_work_section_arr['img'] = isset($sectionData['img']) ? $sectionData['img'] : '';
        $how_it_work_section = $generalobj->getJsonFromAnArr($how_it_work_section_arr);

        $sectionData = json_decode($check_file[0]['lDownloadappSection'],true);
        $download_section_arr['title'] = isset($_POST['download_section_title']) ? $_POST['download_section_title'] : '';
        $download_section_arr['subtitle'] = isset($_POST['download_section_sub_title']) ? $_POST['download_section_sub_title'] : '';
        $download_section_arr['desc'] = isset($_POST['download_section_desc']) ? $_POST['download_section_desc'] : '';
        $download_section_arr['img'] = isset($sectionData['img']) ? $sectionData['img'] : '';
        $download_section_arr['img_first'] = isset($sectionData['img_first']) ? $sectionData['img_first'] : '';
        $download_section_arr['img_sec'] = isset($sectionData['img_sec']) ? $sectionData['img_sec'] : '';
        $download_section_arr['img_third'] = isset($sectionData['img_third']) ? $sectionData['img_third'] : '';
        $download_section = $generalobj->getJsonFromAnArr($download_section_arr);

        $sectionData = json_decode($check_file[0]['lSecuresafeSection'],true);
        $secure_section_arr['title'] = isset($_POST['secure_section_title']) ? $_POST['secure_section_title'] : '';
        $secure_section_arr['desc'] = isset($_POST['secure_section_desc']) ? $_POST['secure_section_desc'] : '';
        $secure_section_arr['img'] = isset($sectionData['img']) ? $sectionData['img'] : '';
        $secure_section = $generalobj->getJsonFromAnArr($secure_section_arr);

        $sectionData = json_decode($check_file[0]['lCalltobookSection'],true);
        $call_section_arr['title'] = isset($_POST['call_section_title']) ? $_POST['call_section_title'] : '';
        $call_section_arr['desc'] = isset($_POST['call_section_desc']) ? $_POST['call_section_desc'] : '';
        $call_section_arr['img'] = isset($sectionData['img']) ? $sectionData['img'] : '';
        $call_section = $generalobj->getJsonFromAnArr($call_section_arr);

        $sectionData = json_decode($check_file[0]['lGeneralBannerSection'],true);
        $general_section_arr['title'] = isset($_POST['general_section_title']) ? $_POST['general_section_title'] : '';
        $general_section_arr['desc'] = isset($_POST['general_section_desc']) ? $_POST['general_section_desc'] : '';
        $general_section_arr['img'] = isset($sectionData['img']) ? $sectionData['img'] : '';
        $general_section_arr['img_sec'] = isset($sectionData['img_sec']) ? $sectionData['img_sec'] : '';
        $general_section = $generalobj->getJsonFromAnArr($general_section_arr);
        
        $vearnbusiness_category = isset($_POST['vearnbusiness_category']) ? $_POST['vearnbusiness_category'] : '';
        $vearnbusiness_category = json_encode($vearnbusiness_category);
        
        $lServiceSection = isset($_POST['lServiceSection']) ? $_POST['lServiceSection'] : '';

        //$vearnbusiness_category = isset($sectionData['vearnbusiness_category']) ? $sectionData['vearnbusiness_category'] : '';
        //$call_section = stripslashes(json_encode($call_section_arr));
        
        if(isset($_POST['vehicleHomeIcon'])) {
            $vehicle_category_ids = implode(',',array_keys($_POST['vehicleHomeIcon']));
        } else {
            $vehicle_category_ids = '';
        }
        if(isset($_POST['bookHomeIcon'])) {
            $booking_ids = implode(',',array_keys($_POST['bookHomeIcon']));
        } else {
            $booking_ids = '';
        }
    }
} else {
}

if (isset($_POST['catlogo'])) { 
    if (SITE_TYPE == 'Demo') {
        header("Location:home_content_cubejekx.php?id=" . $id . "&success=2");
        exit;
    }
    if (isset($_FILES['vHomepageLogo']) && $_FILES['vHomepageLogo']['name'] != "") {
        $filecheck = basename($_FILES['vHomepageLogo']['name']);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[count($fileextarr) - 1]);
        $flag_error = 0;
        $data = getimagesize($_FILES['vHomepageLogo']['tmp_name']);
        $width = $data[0];
        $height = $data[1];
        if ($flag_error == 1) {
            $_SESSION['success'] = '';
            $_SESSION['var_msg'] = '';
            header("Location:home_content_cubejekx_action.php?id=" . $id . "&var_msg=" . $var_msg . "&goback=1");
            exit;
        }
    }
    $vacategoryid = isset($_POST['aid']) ? $_POST['aid'] : '';
    $img_arr = $_FILES['vHomepageLogo'];

//    if($cubexthemeonh != 1) {
    if (!empty($img_arr)) {
        //foreach ($img_arr as $key => $value) {
            if (!empty($img_arr['name'])) {
                $img_path = $tconfig["tsite_upload_home_page_service_images_panel"];
                //$temp_gallery = $img_path . '/';
                $image_object = $img_arr['tmp_name'];
                $image_name = $img_arr['name'];
                $check_file_query = "SELECT " . $key . " FROM ".$sql_vehicle_category_table_name." where iVehicleCategoryId='" . $vacategoryid . "'";

                $check_file = $obj->MySQLSelect($check_file_query);
                if ($message_print_id != "") {
                    $check_file = $img_path . '/' . $check_file[0][$key];
                    if ($check_file != '' && file_exists($check_file[0][$key])) {
                        @unlink($check_file);
                    }
                }
                $Photo_Gallery_folder = $img_path . '/';
                if (!is_dir($Photo_Gallery_folder)) {
                    mkdir($Photo_Gallery_folder, 0777);
                }
                
                //echo $Photo_Gallery_folder."===".$image_object."*****".$image_name;exit;
                $img = $generalobj->imageupload($Photo_Gallery_folder, $image_object, $image_name, '', 'png,jpg,jpeg,gif');
                //$img = $generalobj->fileupload_home($Photo_Gallery_folder,$image_object,$image_name,'','png,jpg,jpeg,gif','');
                if ($img[2] == "1") {
                    $_SESSION['success'] = '0';
                    $_SESSION['var_msg'] = $img[1];
                    header("location:" . $backlink);
                }
                if (!empty($img[0])) {
                    $sql = "UPDATE ".$sql_vehicle_category_table_name." SET " . $key . " = '" . $img[0] . "' WHERE iVehicleCategoryId = '" . $vacategoryid . "'";
                    $obj->sql_query($sql);
                    $_SESSION['success'] = '1';
                    $_SESSION['var_msg'] = $img[1];
                } else { 
                    $_SESSION['success'] = '0';
                    $_SESSION['var_msg'] = $img[1];
                }
            }
        //}
    }
    //}
}

if (isset($_POST['submit'])) {
    if (SITE_TYPE == 'Demo') {
        //header("Location:home_action.php?success=2");
        header("Location:home_content_cubejekx.php?id=" . $id . "&success=2");
        exit;
    }
    
    $q = "INSERT INTO ";
    $where = '';
    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `vCode` = '" . $vCode . "'";
    }
    //$call_section = $obj->SqlEscapeString($call_section);
    
    $query = $q . " `" . $tbl_name . "` SET
	`vehicle_category_ids` = '" . $vehicle_category_ids . "',
	`booking_ids` = '" . $booking_ids . "',
	`vearnbusiness_category` = '" . $vearnbusiness_category . "',
	`lHowitworkSection` = '" . $how_it_work_section . "',
	`lSecuresafeSection` = '" . $secure_section . "',
	`lDownloadappSection` = '" . $download_section . "',
    `lServiceSection` = '" . $lServiceSection . "',
    `lGeneralBannerSection` = '" . $general_section . "',
	`lCalltobookSection` = '" . $call_section . "'" . $where; //die;
    $obj->sql_query($query);
    $id = ($id != '') ? $id : $obj->GetInsertId();
    //header("Location:make_action.php?id=".$id.'&success=1');
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

    $sql = "SELECT hc.*,lm.vTitle FROM $tbl_name as hc LEFT JOIN language_master as lm on lm.vCode = hc.vCode  WHERE hc.id = '" . $id . "'";
   //$sql = "SELECT hc.*,lm.vTitle FROM homecontent as hc LEFT JOIN language_master as lm on lm.vCode = hc.vCode  WHERE hc.id = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    $vLabel = $id;
    if (count($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $vCode = $value['vCode'];
            
            $eStatus = $value['eStatus'];
            $title = $value['vTitle'];
            $vehicle_category_ids = $value['vehicle_category_ids'];
            $booking_ids = $value['booking_ids'];
            $vearnbusiness_category = $value['vearnbusiness_category'];

            $how_it_work_section = (array) json_decode($value['lHowitworkSection']);
            $secure_section = json_decode($value['lSecuresafeSection'],true);
            $download_section = json_decode($value['lDownloadappSection'],true);
            $call_section = json_decode($value['lCalltobookSection'],true);
            $general_section = json_decode($value['lGeneralBannerSection'],true);
            
            $learnServiceCatSection = json_decode($earnBusinessData[0]['learnServiceCatSection'],true);
            $lbusinessServiceCatSection = json_decode($earnBusinessData[0]['lbusinessServiceCatSection'],true);
            
            $lServiceSection = $value['lServiceSection'];
            
        }
    }
}

$catquery = "SELECT iVehicleCategoryId,vHomepageLogo,vCategory_EN FROM  `".$sql_vehicle_category_table_name."` WHERE iParentId = 0 and eStatus = 'Active' ORDER BY iDisplayOrderHomepage";
$vcatdata = $obj->MySQLSelect($catquery);

if (isset($_POST['submit']) && $_POST['submit'] == 'submit') {
    $required = 'required';
} else if (isset($_POST['catlogo']) && $_POST['catlogo'] == 'catlogo') {
    $required = '';
}
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title>Admin | Home Content <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <? include_once('global_files.php'); ?>
        <!-- On OFF switch -->
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
        <style>
            .body-div.innersection {
                box-shadow: -1px -2px 73px 2px #dedede;
                float: none;
            }
            .innerbg_image {
                width:auto;margin:10px 0;height: 150px;
            }
            .notes {
                font-weight: 700;font-style: italic;
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
                            <h2><?= $action; ?> Home Content (<?php echo $title; ?>)</h2>
                            <a href="home_content_cubejekx.php" class="back_link">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <?php 
                    include('valid_msg.php');
                    ?>
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
                            <form method="post" name="_home_content_form" id="_home_content_form" action="" enctype='multipart/form-data'>
                                <input type="hidden" name="id" value="<?= $id; ?>"/>
                                <input type="hidden" name="vCode" value="<?= $vCode; ?>">
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="home_content_cubejekx.php"/>

                                <?php if($cubexthemeonh != 1) { } else { ?>
                                <div class="body-div innersection">
                                    <div class="form-group">
                                        <div class="row"><div class="col-lg-12"><h3>How It work section</h3></div></div>
                                        <div class="row">

                                            <div class="col-lg-12">
                                                <label>Title<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="how_it_work_section_title"  id="how_it_work_section_title" value="<?= $how_it_work_section['title']; ?>" placeholder="Title" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Description</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <textarea class="form-control ckeditor" rows="10" name="how_it_work_section_desc"  id="how_it_work_section_desc"  placeholder="Description"><?= $how_it_work_section['desc']; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Image</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <? if ($how_it_work_section['img'] != '') { ?>
                                                    <!-- <img src="<?= $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$how_it_work_section['img']; ?>" class="innerbg_image"/> -->

                                                    <img src="<?= $tconfig["tsite_url"].'resizeImg.php?h=300&src='.$tconfig["tsite_upload_apptype_page_images"].$template.'/'.$how_it_work_section['img']; ?>" class="innerbg_image"/>
                                                <? } ?>
                                                <input type="file" class="form-control FilUploader" name="how_it_work_section_img"  id="how_it_work_section_img" accept=".png,.jpg,.jpeg,.gif">
                                                <br/>
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 493px * 740px.]</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="body-div innersection">
                                    <div class="form-group">
                                        <div class="row"><div class="col-lg-12"><h3>Download Section</h3></div></div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Title<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="download_section_title"  id="download_section_title" value="<?= $download_section['title']; ?>" placeholder="Title" required>
                                            </div>
                                        </div>
										<?php 
										if(strtoupper(ENABLE_CUBEJEK_X_THEME) != "YES"){
										?>
										<div class="row">
                                            <div class="col-lg-12">
                                                <label>Subtitle<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="download_section_sub_title"  id="download_section_sub_title" value="<?= $download_section['subtitle']; ?>" placeholder="Subtitle" required>
                                            </div>
                                        </div>
										<?php
										}
										?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Description</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <textarea class="form-control ckeditor" rows="10" name="download_section_desc"  id="download_section_desc"  placeholder="Description"><?= $download_section['desc']; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Background Image</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <? if ($download_section['img'] != '') { ?>
                                                    <!-- <img src="<?= $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$download_section['img']; ?>" class="innerbg_image"/> -->

                                                    <img src="<?= $tconfig["tsite_url"].'resizeImg.php?h=300&src='.$tconfig["tsite_upload_apptype_page_images"].$template.'/'.$download_section['img']; ?>" class="innerbg_image"/>

                                                <? } ?>
                                                <input type="file" class="form-control FilUploader" name="download_section_img"  id="download_section_img" accept=".png,.jpg,.jpeg,.gif">
                                                <br/>
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 1920px * 405px.]</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>First Image</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <? if ($download_section['img_first'] != '') { ?>
                                                   <!--  <img src="<?= $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$download_section['img_first']; ?>" class="innerbg_image"/> -->

                                                   <img src="<?= $tconfig["tsite_url"].'resizeImg.php?h=300&src='.$tconfig["tsite_upload_apptype_page_images"].$template.'/'.$download_section['img_first']; ?>" class="innerbg_image"/>

                                                <? } ?>
                                                <input type="file" class="form-control FilUploader" name="download_section_img_first"  id="download_section_img_first" accept=".png,.jpg,.jpeg,.gif">
                                                <br/>
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 300px * 602px.]</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Second Image</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <? if ($download_section['img_sec'] != '') { ?>
                                                    <!-- <img src="<?= $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$download_section['img_sec']; ?>" class="innerbg_image"/> -->

                                                    <img src="<?= $tconfig["tsite_url"].'resizeImg.php?h=300&src='.$tconfig["tsite_upload_apptype_page_images"].$template.'/'.$download_section['img_sec']; ?>" class="innerbg_image"/>

                                                <? } ?>
                                                <input type="file" class="form-control FilUploader" name="download_section_img_sec"  id="download_section_img_sec" accept=".png,.jpg,.jpeg,.gif">
                                                <br/>
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 300px * 430px.]</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Third Image</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <? if ($download_section['img_third'] != '') { ?>
                                                    <!-- <img src="<?= $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$download_section['img_third']; ?>" class="innerbg_image"/> -->

                                                    <img src="<?= $tconfig["tsite_url"].'resizeImg.php?h=300&src='.$tconfig["tsite_upload_apptype_page_images"].$template.'/'.$download_section['img_third']; ?>" class="innerbg_image"/>

                                                <? } ?>
                                                <input type="file" class="form-control FilUploader" name="download_section_img_third"  id="download_section_img_third" accept=".png,.jpg,.jpeg,.gif">
                                                <br/>
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 300px * 310px.]</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="body-div innersection" style="display:none">
                                    <div class="form-group">
                                        <div class="row"><div class="col-lg-12"><h3>Secure Section</h3></div></div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Title</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="secure_section_title"  id="secure_section_title" value="<?= $secure_section['title']; ?>" placeholder="Title">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Description</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <textarea class="form-control ckeditor" rows="10" name="secure_section_desc"  id="secure_section_desc"  placeholder="Description"><?= $secure_section['desc']; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Image</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <? if ($secure_section['img'] != '') { ?>
                                                    <!-- <img src="<?= $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$secure_section['img']; ?>" class="innerbg_image" /> -->

                                                    <img src="<?= $tconfig["tsite_url"].'resizeImg.php?h=300&src='.$tconfig["tsite_upload_apptype_page_images"].$template.'/'.$secure_section['img']; ?>" class="innerbg_image" />

                                                <? } ?>
                                                <input type="file" class="form-control FilUploader" name="secure_section_img"  id="secure_section_img" accept=".png,.jpg,.jpeg,.gif">
                                                <br/>
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 564px * 570px.]</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="body-div innersection" style="display:none">
                                    <div class="form-group">
                                        <div class="row"><div class="col-lg-12"><h3>Call Section</h3></div></div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Title</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="call_section_title"  id="call_section_title" value="<?= $call_section['title']; ?>" placeholder="Title">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Description</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <textarea class="form-control ckeditor" rows="10" name="call_section_desc"  id="call_section_desc"  placeholder="Description"><?= $call_section['desc']; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Image</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <? if ($call_section['img'] != '') { ?>
                                                    <!-- <img src="<?= $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$call_section['img']; ?>" class="innerbg_image"/> -->

                                                    <img src="<?= $tconfig["tsite_url"].'resizeImg.php?h=300&src='.$tconfig["tsite_upload_apptype_page_images"].$template.'/'.$call_section['img']; ?>" class="innerbg_image"/>
                                                <? } ?>
                                                <input type="file" class="form-control FilUploader" name="call_section_img"  id="call_section_img" accept=".png,.jpg,.jpeg,.gif">
                                                <br/>
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 609px * 547px.]</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Start Home icons area-->
                                <div class="body-div innersection">
                                    <div class="row">
                                        <div class="col-lg-12"><h3>Banner Section & Service Section</h3></div>
                                        <div class="col-lg-6">
                                            <input type="text" class="form-control" name="lServiceSection"  id="lServiceSection" value="<?= $lServiceSection; ?>" placeholder="Service Section Title" required>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Note : Click on edit icon for entering more details about the banner section details and service section title and background image which shown in the service section<!--<br>A - shown in 'Banner Section', and B - shown in 'Our Service Section'--></label>
                                        <br/>
                                        <div class="col-lg-10row" style="height:500px;overflow:scroll;">
                                            <table class="table table-striped table-bordered table-hover">
                                                <thead>
                                                    <tr>
                                                        <!--<th style="width:20px">A</th>-->
                                                        <!--<th style="width:20px">B</th>-->
                                                        <th style="width:20px">Shown in 'Our Service Section'-</th>
                                                        <th style="text-align:center;">Icon</th>
                                                        <th>Name</th>
                                                        <th align="center" style="text-align:center;">Edit</th>
                                                    </tr>
                                                </thead>
                                            <?php 
                                            if(!empty($learnServiceCatSection)) {
                                                    $checkeb = "";
                                                    $vearnbusiness_categoryJson = json_decode($vearnbusiness_category,true);
                                                    $disabledb = 'disabled';
                                                    //$earnBusinessData[0]['learnServiceCatSection']
                                                    if (isset($vearnbusiness_categoryJson['earn']) && $vearnbusiness_categoryJson['earn']==1) { $checkeb = "checked"; }
                                                    ?>
                                                    <tbody><!--<td><input type="checkbox" name="vearnbusiness_category[earn]" id="vearnbusiness_category" value="1" <?=$checkeb;?>></td>-->
                                                    <td><input type="checkbox" name="bookHomeIcon[<?= $vcatdata[$i]['iVehicleCategoryId']; ?>]" id="bookHomeIcon" value="1" <?=$checkb;?><?=$disabledb;?>></td>
                                                        <td align="center">	
                                                            <?php if ($learnServiceCatSection['vHomepageLogo'] != '') { ?>
                                                                <!-- <img src="<?= $tconfig["tsite_upload_home_page_service_images"] . '/' . $learnServiceCatSection['vHomepageLogo'] ?>"  style="width:35px;height:35px;"> -->
 
                                                                 <img src="<?= $tconfig["tsite_url"].'resizeImg.php?w=70&h=70&src='.$tconfig["tsite_upload_home_page_service_images"] . '/' . $learnServiceCatSection['vHomepageLogo'] ?>"  style="width:35px;height:35px;"> 

                                                            <? } ?>														
                                                        </td>
                                                        <td>
                                                            <?= $learnServiceCatSection['vCatNameHomepage']; ?>
                                                        </td>
                                                        <!--<td align="center">-->
                                                        <!--    <a href="<?php echo $tconfig["tsite_url_main_admin"]."static_servicecat_page.php?page=earn&id=".$_REQUEST['id']; ?>" data-toggle="tooltip" title="" data-original-title="Edit"><img src="img/edit-icon.png" alt="Edit"></a>-->
                                                        <!--</td>-->
                                                        <td align="center" class="action-btn001">
                                                                <div class="share-button openHoverAction-class" style="display: block;">
                                                                    <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>
                                                                    <div class="social show-moreOptions for-five openPops_<?= $data_drv[$i]['iDriverId']; ?> openHoverDetails openHoverDetailsNew">
                                                                        <ul>
                                                                            <!--<li class="entypo-twitter entypo-twitter-new" data-network="twitter"><a href="<?php echo $tconfig["tsite_url_main_admin"]."static_servicecat_page.php?page=earn&id=".$_REQUEST['id']; ?>">Edit Details</a></li>-->
                                                                            <li class="entypo-twitter entypo-twitter-new" data-network="twitter"><a href="<?php echo $tconfig["tsite_url_main_admin"]."home_content_earn_action.php?id=".$iLanguageMasId ?>">
                                                                            Edit Inner Page
                                                                                    <!--<img src="img/edit-icon.png" alt="Edit">-->
                                                                            </a></li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                        </td>
                                                    </tbody>
                                                <?php }
                                                /*$checkeb = "";$disabledb = 'disabled';
                                                if (isset($vearnbusiness_categoryJson['business']) && $vearnbusiness_categoryJson['business']==1) { $checkeb = "checked"; }
                                                if(!empty($lbusinessServiceCatSection)) { ?> 
                                                    <tbody><td><input type="checkbox" name="vearnbusiness_category[business]" id="vearnbusiness_category" value="1" <?=$checkeb;?>></td>
                                                    <td><input type="checkbox" name="bookHomeIcon[<?= $vcatdata[$i]['iVehicleCategoryId']; ?>]" id="bookHomeIcon" value="1" <?=$checkb;?><?=$disabledb;?>></td>
                                                        <td align="center">
                                                            <?php if ($lbusinessServiceCatSection['vHomepageLogo'] != '') { ?>
                                                                <img src="<?= $tconfig["tsite_upload_home_page_service_images"] . '/' . $lbusinessServiceCatSection['vHomepageLogo'] ?>"  style="width:35px;height:35px;">
                                                            <? } ?>
                                                        </td>
                                                        <td>
                                                            <?= $lbusinessServiceCatSection['vCatNameHomepage']; ?>
                                                        </td>
                                                        <td align="center">
                                                            <a href="<?php echo $tconfig["tsite_url_main_admin"]."static_servicecat_page.php?page=business&id=".$_REQUEST['id']; ?>" data-toggle="tooltip" title="" data-original-title="Edit"><img src="img/edit-icon.png" alt="Edit"></a>
                                                        </td></tbody>
                                                <?php }*/
                                                
                                                if (!empty($vcatdata)) {
                                                    $bookarray = array('174','178','175','276');
                                                    $selectvehiclecat = explode(',',$vehicle_category_ids);
                                                    $booking_ids = explode(',',$booking_ids);
                                                    $urlCat = array('174'=>'home_content_taxi_action.php','178'=>'home_content_delivery_action.php','175'=>'home_content_moto_action.php','276'=>'home_content_fly_action.php','182'=>'home_content_food_action.php','183'=>'home_content_grocery_action.php');
                                                    for ($i = 0; $i < count($vcatdata); $i++) {  
                                                        $check = $disabled = $checkbooking = "";
                                                        if (in_array($vcatdata[$i]['iVehicleCategoryId'], $selectvehiclecat)) { $check = "checked"; }
                                                        //if (!in_array($vcatdata[$i]['iVehicleCategoryId'], $bookarray)) { $disabled = "disabled"; }
                                                        if (in_array($vcatdata[$i]['iVehicleCategoryId'], $booking_ids)) { $checkbooking = "checked"; }
                                                        if(empty($urlCat[$vcatdata[$i]['iVehicleCategoryId']])) $urlCat[$vcatdata[$i]['iVehicleCategoryId']] = 'home_content_otherservices_action.php';
                                                        ?>
                                                        <tbody>
                                                        <!--<td><input type="checkbox" name="vehicleHomeIcon[<?= $vcatdata[$i]['iVehicleCategoryId']; ?>]" id="vehicleHomeIcon" value="1" <?=$check;?>></td>-->
                                                        <td><input type="checkbox" name="bookHomeIcon[<?= $vcatdata[$i]['iVehicleCategoryId']; ?>]" id="bookHomeIcon" value="1" <?=$checkbooking;?><?=$disabled;?>></td>
                                                        <td align="center">	
                                                            <?php if ($vcatdata[$i]['vHomepageLogo'] != '') { ?>
                                                                <!-- <img src="<?= $tconfig["tsite_upload_home_page_service_images"] . '/' . $vcatdata[$i]['vHomepageLogo'] ?>"  style="width:35px;height:35px;"> -->

                                                                <img src="<?= $tconfig["tsite_url"].'resizeImg.php?w=70&h=70&src='.$tconfig["tsite_upload_home_page_service_images"] . '/' . $vcatdata[$i]['vHomepageLogo'] ?>"  style="width:35px;height:35px;">

                                                            <? } ?>														
                                                        </td>
                                                        <td>
                                                            <?= $vcatdata[$i]['vCategory_EN']; ?>
                                                        </td>
                                                        <td align="center" class="action-btn001">
                                                                <div class="share-button openHoverAction-class" style="display: block;">
                                                                    <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>
                                                                    <div class="social show-moreOptions for-five openPops_<?= $data_drv[$i]['iDriverId']; ?> openHoverDetails openHoverDetailsNew">
                                                                        <ul>
                                                                            <li class="entypo-twitter entypo-twitter-new" data-network="twitter"><a href="<?php echo $tconfig["tsite_url_main_admin"]."vehicle_category_action.php?id=".$vcatdata[$i]['iVehicleCategoryId']."&homepage=1"; ?>">Edit Details</a></li>
                                                                            <li class="entypo-twitter entypo-twitter-new" data-network="twitter"><a href="<?php echo $tconfig["tsite_url_main_admin"].$urlCat[$vcatdata[$i]['iVehicleCategoryId']]."?id=".$iLanguageMasId; ?>">Edit Inner Page
                                                                            </a></li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                        </td>
                                                        <!--<td align="center">
                                                            <a href="<?php echo $tconfig["tsite_url_main_admin"]."vehicle_category_action.php?id=".$vcatdata[$i]['iVehicleCategoryId']."&homepage=1"; ?>" data-toggle="tooltip" title="" data-original-title="Edit" target="blank"><img src="img/edit-icon.png" alt="Edit"></a>
                                                        </td>-->
                                                        </tbody>
                                                        <?php
                                                    }
                                                } ?>
                                            </table>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group general_section">
                                        <div class="row"><div class="col-lg-12"><h3>General Banner Section</h3></div></div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Title</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="general_section_title"  id="general_section_title" value="<?= $general_section['title']; ?>" placeholder="Title">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Description</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <textarea class="form-control ckeditor" rows="10" name="general_section_desc"  id="general_section_desc"  placeholder="Description"><?= $general_section['desc']; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>First Image(Background image)</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <? if ($general_section['img'] != '') { ?>
                                                    <!-- <img src="<?= $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$general_section['img']; ?>" class="innerbg_image"/> -->

                                                    <img src="<?= $tconfig["tsite_url"].'resizeImg.php?h=300&src='.$tconfig["tsite_upload_apptype_page_images"].$template.'/'.$general_section['img']; ?>" class="innerbg_image"/>

                                                <? } ?>
                                                <input type="file" class="form-control FilUploader" name="general_section_img"  id="general_section_img" accept=".png,.jpg,.jpeg,.gif">
                                                <br/>
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 609px * 547px.]</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Second Image</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <? if ($general_section['img_sec'] != '') { ?>
                                                   <!--  <img src="<?= $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$general_section['img_sec']; ?>" class="innerbg_image"/> -->

                                                    <img src="<?= $tconfig["tsite_url"].'resizeImg.php?h=300&src='.$tconfig["tsite_upload_apptype_page_images"].$template.'/'.$general_section['img_sec']; ?>" class="innerbg_image"/>

                                                <? } ?>
                                                <input type="file" class="form-control FilUploader" name="general_section_img_sec"  id="general_section_img_sec" accept=".png,.jpg,.jpeg,.gif">
                                                <br/>
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 609px * 547px.]</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Note : The Category name will be set in all other language defined from the Admin - Service Category Module.
                                                Uploading of Icon for any 1 language will be set for all the languages.<br>All services which have icons that will be shown in the 'Our Service Section' , here when click on delete icon, it will delete icon of that service and not shown in front page</label>
                                        <div class="col-lg-10row" style="height:500px;overflow:scroll;">
                                            <br/>
                                            <table class="table table-striped table-bordered table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Icon</th>
                                                        <th>Name</th>
                                                        <th align="center">Upload Icon</th>
                                                        <th align="center">Remove Icon</th>
                                                    </tr>
                                                </thead>
                                                <?php
                                                if (!empty($vcatdata)) {
                                                    for ($i = 0; $i < count($vcatdata); $i++) {
                                                        ?>
                                                        <tbody>
                                                        <td align="center">	
                                                            <?php if ($vcatdata[$i]['vHomepageLogo'] != '') { ?>
                                                                <!-- <img src="<?= $tconfig["tsite_upload_home_page_service_images"] . '/' . $vcatdata[$i]['vHomepageLogo'] ?>"  style="width:35px;height:35px;"> -->

                                                                <img src="<?= $tconfig["tsite_url"].'resizeImg.php?w=70&h=70&src='.$tconfig["tsite_upload_home_page_service_images"] . '/' . $vcatdata[$i]['vHomepageLogo'] ?>"  style="width:35px;height:35px;">


                                                            <?php } ?>														
                                                        </td>
                                                        <td>
                                                            <?= $vcatdata[$i]['vCategory_EN']; ?>
                                                        </td>
                                                        <td align="center">
                                                        <center><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#<?= $vcatdata[$i]['iVehicleCategoryId']; ?>">Upload</button></center>
                                                        </td>

                                                        <td align="center">
                                                            <?php if ($vcatdata[$i]['vHomepageLogo'] != '') { ?>

                                                            <center>
                                                                <form method="post" name="test1"   action="" enctype='multipart/form-data'>
                                                                    <input type="hidden" name="removeid" value="<?= $vcatdata[$i]['iVehicleCategoryId']; ?>"/>
                                                                    <!--<input type="hidden" name="backlink" id="backlink" value="home_content_new.php"/>-->
                                                                    <button type="submit" style="display: none;" id="removeIconFrom_<?= $vcatdata[$i]['iVehicleCategoryId']; ?>" style="background-color: transparent; border: none; outline: none;" name="removeicon" id="removeicon" value="remove"><img src="img/delete-icon.png" alt="Delete Icon"></button>
                                                                    <button type="submit" id="removeIconFrom_<?= $vcatdata[$i]['iVehicleCategoryId']; ?>" style="background-color: transparent; border: none; outline: none;" data-id="<?= $vcatdata[$i]['iVehicleCategoryId']; ?>" name="removeicon" id="removeicon" value="remove" onclick="return deleteIcon(this);"><img src="img/delete-icon.png" alt="Delete Icon"></button>
                                                                </form>
                                                            </center>
                                                        <?php } ?>

                                                        </td>

                                                        </tbody>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Home icons area-->
                                <?php } ?>

                                <!-- End Home Header area-->
                                <div class="row" style="display: none;">
                                    <div class="col-lg-12">
                                        <label>Status</label>
                                    </div>
                                    <div class="col-lg-6" >
                                        <div class="make-switch" data-on="success" data-off="warning">
                                            <input type="checkbox" name="eStatus" <?= ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?>/>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <input type="submit" class=" btn btn-default" name="submit" id="submit" value="<?= $action; ?> Home Content">
                                        <!--<input type="reset" value="Reset" class="btn btn-default">-->
                                        <!-- 									<a href="javascript:void(0);" onclick="reset_form('_home_content_form');" class="btn btn-default">Reset</a> -->
                                        <a href="home_content_cubejekx.php" class="btn btn-default back_link">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
        <?php
        if (!empty($vcatdata)) {
            for ($i = 0; $i < count($vcatdata); $i++) {
                ?>
                <div class="modal fade" id="<?= $vcatdata[$i]['iVehicleCategoryId']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <form method="post" name="test" id="test" action="" enctype='multipart/form-data'>
                        <input type="hidden" name="aid" value="<?php echo $vcatdata[$i]['iVehicleCategoryId'] ?>" /> 
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">x</button>
                                    <h4 class="modal-title">Service Icon</h4>

                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <?php
                                            if (!empty($vcatdata[$i]['vHomepageLogo'])) {
                                                ?>
                                                <!-- <img src="<?= $tconfig["tsite_upload_home_page_service_images"] . '/'.$vcatdata[$i]['vHomepageLogo']; ?>" class="innerbg_image" /> -->

                                                <img src="<?= $tconfig["tsite_url"].'resizeImg.php?h=300&src='.$tconfig["tsite_upload_home_page_service_images"] . '/'.$vcatdata[$i]['vHomepageLogo']; ?>" class="innerbg_image" />
                                            <?php } ?>
                                        </div>
                                        <div class="col-lg-12">
                                            <span><b><?= $vcatdata[$i]['vCategory_EN']; ?></b></span>
                                        </div>
                                        <div class="clearfix">&nbsp;</div>
                                        <div class="col-lg-12">
                                            <input type="file" class="form-control FilUploader" name="vHomepageLogo" id="vHomepageLogo" accept=".png,.jpg,.jpeg,.gif" required>
                                        </div>
                                        <br/>
                                        <div class="col-lg-12">
                                            <span>Note: For Better Resolution Upload only image size of 360px*360px.</span>
                                        </div>
                                        <br/>

                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="catlogo" value="catlogo" class="btn btn-primary">Save changes</button>
                                    <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <?php
            }
        }
        ?>
        <div data-backdrop="static" data-keyboard="false" class="modal fade" id="service_icon_modal" tabindex="-1" role="dialog" aria-labelledby="ServiceIconModel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <input type="hidden" name="removeidmodel" id="removeidmodel" />
                    <div class="modal-header"><h4>Remove Icon?</h4></div>
                    <div class="modal-body"><p>Are you sure you cant to remove icon?</p></div>
                    <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button><a class="btn btn-success btn-ok action_modal_submit">Ok</a></div>
                </div>
            </div>
        </div>
        <? include_once('footer.php'); ?>
        <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
        <script src="../assets/plugins/ckeditor/ckeditor.js"></script>
        <script src="../assets/plugins/ckeditor/config.js"></script>
        <script>
                                                            CKEDITOR.replace('ckeditor', {
                                                                allowedContent: {
                                                                    i: {
                                                                        classes: 'fa*'
                                                                    },
                                                                    span: true
                                                                }
                                                            });
        </script>
<script>
$(document).ready(function () {
     var checked = 0;   
    $('input[id="vehicleHomeIcon"]:checked').each(function() {
        checked = 1;
    });
    if(checked==1) {
        $(".general_section").hide();
    } else {
        $(".general_section").show();
    }
    $('input[id="vehicleHomeIcon"]').click(function() {
        checked = 0;   
        $('input[id="vehicleHomeIcon"]:checked').each(function() {
            checked = 1;
        });
        if(checked==1) {
            $(".general_section").hide();
        } else {
            $(".general_section").show();
        }
    });    
});
</script>
        <script>
            $(document).ready(function () {
                var referrer;
<?php if ($goback == 1) { ?>
                    alert('<?php echo $var_msg; ?>');
                    //history.go(-1);
                    window.location.href = "home_content_cubejekx_action.php?id=<?php echo $id ?>";


<?php } ?>
                if ($("#previousLink").val() == "") { //alert('pre1');
                    referrer = document.referrer;
                    // alert(referrer);
                } else { //alert('pre2');
                    referrer = $("#previousLink").val();
                }

                if (referrer == "") {
                    referrer = "home_content_cubejekx.php";
                } else { //alert('hi');
                    //$("#backlink").val(referrer);
                    referrer = "home_content_cubejekx.php";
                    // alert($("#backlink").val(referrer));
                }
                $(".back_link").attr('href', referrer);
                //alert($(".back_link").attr('href',referrer));	
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
            $(".FilUploader").change(function () {
                var fileExtension = ['jpeg', 'jpg', 'png', 'gif'];
                if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                    alert("Only formats are allowed : " + fileExtension.join(', '));
                    $(this).val('');
                    return false;

                }
            });

            function deleteIcon(ele) {
                var id = $(ele).attr('data-id');
                $('#removeidmodel').val(id);

                $('#service_icon_modal').modal('show');

                return false;

            }
            $(".action_modal_submit").unbind().click(function () {
                var id = $('#removeidmodel').val();
                $('#removeidmodel').val('');
                $('#removeIconFrom_' + id).click();
                return true;

            });
             $('.entypo-export').click(function (e) {
                e.stopPropagation();
                var $this = $(this).parent().find('div');
                $(".openHoverAction-class div").not($this).removeClass('active');
                $this.toggleClass('active');
            });

            $(document).on("click", function (e) {
                if ($(e.target).is(".openHoverAction-class,.show-moreOptions,.entypo-export") === false) {
                    $(".show-moreOptions").removeClass("active");
                }
            });
        </script>
    </body>
    <!-- END BODY-->
</html>
