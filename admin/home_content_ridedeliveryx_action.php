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
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : "";

if (isset($_REQUEST['goback'])) {
    $goback = $_REQUEST['goback'];
}

$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive'; 

$script = 'homecontent_cubejekx';
$tbl_name = $generalobj->getAppTypeWiseHomeTable();

$iLanguageMasId = 0;

if(empty($vCode)) {
   $sql = "SELECT hc.vCode, lm.iLanguageMasId FROM $tbl_name as hc LEFT JOIN language_master as lm on lm.vCode = hc.vCode  WHERE hc.id = '" . $id . "'";
   $db_data = $obj->MySQLSelect($sql);
   $vCode = $db_data[0]['vCode'];
   if(empty($vCode)) {
      $vCode = $default_lang;
   }
   $iLanguageMasId = $db_data[0]['iLanguageMasId'];
}

$img_arr = $_FILES;

if (!empty($img_arr)) {
    foreach ($img_arr as $key => $value) {
        if($key == 'vHomepageLogo') continue;
        if (!empty($value['name'])) {
            $img_path = $tconfig["tsite_upload_apptype_page_images_panel"];
            
            $image_object = $value['tmp_name'];
            $img_name = explode('.',$value['name']);
            $image_name = $img_name[0]."_".strtotime(date("H:i:s")).".".$img_name[1];
            
            $second_gen_img = 0;
            if($key=='general_section_img_sec') {
                $second_gen_img = 1;
             }
            if($key=='how_it_work_section_img') $key = 'lHowitworkSection';
            else if($key=='download_section_img') $key = 'lDownloadappSection';
            else if($key=='secure_section_img') $key = 'lSecuresafeSection';
            else if($key=='safe_section_img') $key = 'lSafeSection';
            else if($key=='call_section_img') $key = 'lCalltobookSection';
            else if($key=='general_section_img') $key = 'lGeneralBannerSection';
            else if($key=='general_section_img_sec') $key = 'lGeneralBannerSection';
            else if($key=='calculate_section_img') $key = 'lCalculateSection';

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
    
    $check_file_query = "SELECT lHowitworkSection,lSecuresafeSection,lDownloadappSection,lCalltobookSection,lGeneralBannerSection,lCalculateSection,lSafeSection FROM $tbl_name where vCode='" . $vCode . "'";
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
    $download_section = $generalobj->getJsonFromAnArr($download_section_arr);

    $sectionData = json_decode($check_file[0]['lSecuresafeSection'],true);
    $secure_section_arr['title'] = isset($_POST['secure_section_title']) ? $_POST['secure_section_title'] : '';
    $secure_section_arr['desc'] = isset($_POST['secure_section_desc']) ? $_POST['secure_section_desc'] : '';
    $secure_section_arr['img'] = isset($sectionData['img']) ? $sectionData['img'] : '';
    $secure_section = $generalobj->getJsonFromAnArr($secure_section_arr);
    
   $sectionData = json_decode($check_file[0]['lSafeSection'],true);
    $safe_section_arr['title'] = isset($_POST['safe_section_title']) ? $_POST['safe_section_title'] : '';
    $safe_section_arr['desc'] = isset($_POST['safe_section_desc']) ? $_POST['safe_section_desc'] : '';
    $safe_section_arr['img'] = isset($sectionData['img']) ? $sectionData['img'] : '';
    $safe_section = $generalobj->getJsonFromAnArr($safe_section_arr);

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
    
   $sectionData = json_decode($check_file[0]['lCalculateSection'],true);
    //$calculate_section_arr['menu_title'] = isset($_POST['calculate_section_menu_title']) ? $_POST['calculate_section_menu_title'] : '';
   $calculate_section_arr['title'] = isset($_POST['calculate_section_title']) ? $_POST['calculate_section_title'] : '';
   $calculate_section_arr['desc'] = isset($_POST['calculate_section_desc']) ? $_POST['calculate_section_desc'] : '';
   $calculate_section_arr['img'] = isset($sectionData['img']) ? $sectionData['img'] : '';
   $calculate_section_arr = !(empty($sectionData)) ? array_merge($sectionData,$calculate_section_arr) : $calculate_section_arr;
   $calculate_section = $generalobj->getJsonFromAnArr($calculate_section_arr);
}

if (isset($_POST['submit'])) {
    if (SITE_TYPE == 'Demo') {
        //header("Location:home_action.php?success=2");
        header("Location:home_content_ridedeliveryx.php?id=" . $id . "&success=2");
        exit;
    }
    
    $q = "INSERT INTO ";
    $where = '';
    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `vCode` = '" . $vCode . "'";
    }
    
    $query = $q . " `" . $tbl_name . "` SET
    `lGeneralBannerSection` = '" . $general_section . "',
    `lHowitworkSection` = '" . $how_it_work_section . "',
	`lSecuresafeSection` = '" . $secure_section . "',
	`lDownloadappSection` = '" . $download_section . "',
    `lCalculateSection` = '" . $calculate_section . "',
    `lSafeSection` = '" . $safe_section . "',
	`lCalltobookSection` = '" . $call_section . "'" . $where; //die;
    $obj->sql_query($query);
    $id = ($id != '') ? $id : $obj->GetInsertId();
    
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
    $db_data = $obj->MySQLSelect($sql);
    $vLabel = $id;
    if (count($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $vCode = $value['vCode'];
            $title = $value['vTitle'];
            $eStatus = $value['eStatus'];
            $general_section = json_decode($value['lGeneralBannerSection'],true);
            $how_it_work_section = (array) json_decode($value['lHowitworkSection']);
            $secure_section = json_decode($value['lSecuresafeSection'],true);
            $download_section = json_decode($value['lDownloadappSection'],true);
            $call_section = json_decode($value['lCalltobookSection'],true);
            $calculate_section = json_decode($value['lCalculateSection'],true);
            $safe_section = json_decode($value['lSafeSection'],true);
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
                            <a href="home_content_ridedeliveryx.php" class="back_link">
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
                                <input type="hidden" name="backlink" id="backlink" value="home_content_ridedeliveryx.php"/>

                                <div class="body-div innersection">
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
                                                    <img src="<?= $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$general_section['img']; ?>" class="innerbg_image"/>
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
                                                    <img src="<?= $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$general_section['img_sec']; ?>" class="innerbg_image"/>
                                                <? } ?>
                                                <input type="file" class="form-control FilUploader" name="general_section_img_sec"  id="general_section_img_sec" accept=".png,.jpg,.jpeg,.gif">
                                                <br/>
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 609px * 547px.]</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>                                
                                
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
                                                    <img src="<?= $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$how_it_work_section['img']; ?>" class="innerbg_image"/>
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
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Subtitle</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="download_section_sub_title"  id="download_section_sub_title" value="<?= $download_section['subtitle']; ?>" placeholder="Subtitle">
                                            </div>
                                        </div>
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
                                                <label>Image</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <? if ($download_section['img'] != '') { ?>
                                                    <img src="<?= $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$download_section['img']; ?>" class="innerbg_image"/>
                                                <? } ?>
                                                <input type="file" class="form-control FilUploader" name="download_section_img"  id="download_section_img" accept=".png,.jpg,.jpeg,.gif">
                                                <br/>
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 1920px * 405px.]</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="body-div innersection">
                                    <div class="form-group">
                                       <div class="row"><div class="col-lg-12"><h3>Calculate Section</h3></div></div>
                                       <!--<div class="row">
                                           <div class="col-lg-12">
                                               <label>Menu Title</label>
                                           </div>
                                           <div class="col-lg-6">
                                               <input type="text" class="form-control" name="calculate_section_menu_title"  id="calculate_section_menu_title" value="<?= $calculate_section['menu_title']; ?>" placeholder="Menu Title">
                                           </div>
                                       </div>-->
                                       <div class="row">
                                           <div class="col-lg-12">
                                               <label>Title<span class="red"> *</span></label>
                                           </div>
                                           <div class="col-lg-6">
                                               <input type="text" class="form-control" name="calculate_section_title"  id="calculate_section_title" value="<?= $calculate_section['title']; ?>" placeholder="Title" required>
                                           </div>
                                       </div>
                                       <div class="row">
                                           <div class="col-lg-12">
                                               <label>Description</label>
                                           </div>
                                           <div class="col-lg-12">
                                               <textarea class="form-control ckeditor" rows="10" name="calculate_section_desc"  id="calculate_section_desc"  placeholder="Description"><?= $calculate_section['desc']; ?></textarea>
                                           </div>
                                       </div>
                                       <div class="row">
                                           <div class="col-lg-12">
                                               <label>Image</label>
                                           </div>
                                           <div class="col-lg-6">
                                               <? if ($calculate_section['img'] != '') { ?>
                                                   <img src="<?= $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$calculate_section['img']; ?>" class="innerbg_image"/>
                                               <? } ?>
                                               <input type="file" class="form-control FilUploader" name="calculate_section_img"  id="calculate_section_img" accept=".png,.jpg,.jpeg,.gif,.svg">
                                               <br/>
                                               <span class="notes">[Note: For Better Resolution Upload only image size of 860px * 445px.]</span>
                                           </div>
                                       </div>
                                    </div>
                                 </div>

                                <div class="body-div innersection">
                                    <div class="form-group">
                                        <div class="row"><div class="col-lg-12"><h3>Secure Section</h3></div></div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Title<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="secure_section_title"  id="secure_section_title" value="<?= $secure_section['title']; ?>" placeholder="Title" required>
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
                                                    <img src="<?= $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$secure_section['img']; ?>" class="innerbg_image" />
                                                <? } ?>
                                                <input type="file" class="form-control FilUploader" name="secure_section_img"  id="secure_section_img" accept=".png,.jpg,.jpeg,.gif">
                                                <br/>
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 564px * 570px.]</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="body-div innersection">
                                    <div class="form-group">
                                        <div class="row"><div class="col-lg-12"><h3>Safe Section</h3></div></div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Title<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="safe_section_title"  id="safe_section_title" value="<?= $safe_section['title']; ?>" placeholder="Title" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Description</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <textarea class="form-control ckeditor" rows="10" name="safe_section_desc"  id="safe_section_desc"  placeholder="Description"><?= $safe_section['desc']; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Image</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <? if ($safe_section['img'] != '') { ?>
                                                    <img src="<?= $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$safe_section['img']; ?>" class="innerbg_image" />
                                                <? } ?>
                                                <input type="file" class="form-control FilUploader" name="safe_section_img"  id="safe_section_img" accept=".png,.jpg,.jpeg,.gif">
                                                <br/>
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 564px * 570px.]</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="body-div innersection">
                                    <div class="form-group">
                                        <div class="row"><div class="col-lg-12"><h3>Call Section</h3></div></div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Title<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="call_section_title"  id="call_section_title" value="<?= $call_section['title']; ?>" placeholder="Title" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Description</label>
                                                <h5>[Note: Please use #SUPPORT_PHONE# predefined tags to display the support phone value. Please go to Settings >> General section to change the values of above predefined tags.]</h5>
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
                                                    <img src="<?= $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$call_section['img']; ?>" class="innerbg_image"/>
                                                <? } ?>
                                                <input type="file" class="form-control FilUploader" name="call_section_img"  id="call_section_img" accept=".png,.jpg,.jpeg,.gif">
                                                <br/>
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 609px * 547px.]</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                    
                                <div class="row">
                                    <div class="col-lg-12">
                                        <input type="submit" class=" btn btn-default" name="submit" id="submit" value="<?= $action; ?> Home Content">
                                        <!--<input type="reset" value="Reset" class="btn btn-default">-->
                                        <!-- 									<a href="javascript:void(0);" onclick="reset_form('_home_content_form');" class="btn btn-default">Reset</a> -->
                                        <a href="home_content_ridedeliveryx.php" class="btn btn-default back_link">Cancel</a>
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
                var referrer;
<?php if ($goback == 1) { ?>
                    alert('<?php echo $var_msg; ?>');
                    //history.go(-1);
                    window.location.href = "home_content_ridedeliveryx_action.php?id=<?php echo $id ?>";


<?php } ?>
                if ($("#previousLink").val() == "") { //alert('pre1');
                    referrer = document.referrer;
                    // alert(referrer);
                } else { //alert('pre2');
                    referrer = $("#previousLink").val();
                }

                if (referrer == "") {
                    referrer = "home_content_ridedeliveryx.php";
                } else { //alert('hi');
                    //$("#backlink").val(referrer);
                    referrer = "home_content_ridedeliveryx.php";
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
