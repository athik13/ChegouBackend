<?php
include_once('../common.php');

if ($APP_TYPE == "Ride-Delivery-UberX" && strtoupper(ONLYDELIVERALL) == "YES" && ((!empty($service_categories_ids_arr) && count($service_categories_ids_arr) > 1) || (!empty($service_categories_ids_arr) && count($service_categories_ids_arr) == 1 /* && !in_array(1, $service_categories_ids_arr) */) )) {
    require_once('homecontent_action_deliverall.php');
    exit;
}

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$message_print_id = $id;
$vCode = isset($_POST['vCode']) ? $_POST['vCode'] : 'EN';
// $tbl_name = 'homecontentfood';
$tbl_name = $generalobj->getAppTypeWiseHomeTable();
$script = 'homecontent';

$BannerBgImage = isset($_POST['BannerBgImage']) ? $_POST['BannerBgImage'] : '';
$BannerBigTitle = isset($_POST['BannerBigTitle']) ? $_POST['BannerBigTitle'] : '';
$BannerSmallTitle = isset($_POST['BannerSmallTitle']) ? $_POST['BannerSmallTitle'] : '';
$BannerContent = isset($_POST['BannerContent']) ? $_POST['BannerContent'] : '';
$FirstSectionLeftImage = isset($_POST['FirstSectionLeftImage']) ? $_POST['FirstSectionLeftImage'] : '';
$FirstSectionHeading = isset($_POST['FirstSectionHeading']) ? $_POST['FirstSectionHeading'] : '';
$FirstParaTitle = isset($_POST['FirstParaTitle']) ? $_POST['FirstParaTitle'] : '';
$FirstParaContent = isset($_POST['FirstParaContent']) ? $_POST['FirstParaContent'] : '';
$SecondParaTitle = isset($_POST['SecondParaTitle']) ? $_POST['SecondParaTitle'] : '';
$SecondParaContent = isset($_POST['SecondParaContent']) ? $_POST['SecondParaContent'] : '';
$ThirdParaTitle = isset($_POST['ThirdParaTitle']) ? $_POST['ThirdParaTitle'] : '';
$ThirdParaContent = isset($_POST['ThirdParaContent']) ? $_POST['ThirdParaContent'] : '';
$MidFirstImage = isset($_POST['MidFirstImage']) ? $_POST['MidFirstImage'] : '';
$MidFirstTitle = isset($_POST['MidFirstTitle']) ? $_POST['MidFirstTitle'] : '';
$MidFirstContent = isset($_POST['MidFirstContent']) ? $_POST['MidFirstContent'] : '';
$MidSecImage = isset($_POST['MidSecImage']) ? $_POST['MidSecImage'] : '';
$MidSecTitle = isset($_POST['MidSecTitle']) ? $_POST['MidSecTitle'] : '';
$MidSecContent = isset($_POST['MidSecContent']) ? $_POST['MidSecContent'] : '';
$MidThirdImage = isset($_POST['MidThirdImage']) ? $_POST['MidThirdImage'] : '';
$MidThirdTitle = isset($_POST['MidThirdTitle']) ? $_POST['MidThirdTitle'] : '';
$MidThirdContent = isset($_POST['MidThirdContent']) ? $_POST['MidThirdContent'] : '';
$ThirdLeftImg1 = isset($_POST['ThirdLeftImg1']) ? $_POST['ThirdLeftImg1'] : '';
$ThirdLeftImg2 = isset($_POST['ThirdLeftImg2']) ? $_POST['ThirdLeftImg2'] : '';
$ThirdLeftImg3 = isset($_POST['ThirdLeftImg3']) ? $_POST['ThirdLeftImg3'] : '';
$ThirdRightTitle = isset($_POST['ThirdRightTitle']) ? $_POST['ThirdRightTitle'] : '';
$ThirdRightContent = isset($_POST['ThirdRightContent']) ? $_POST['ThirdRightContent'] : '';
$PlayStoreImg = isset($_POST['PlayStoreImg']) ? $_POST['PlayStoreImg'] : '';
$AppStoreImg = isset($_POST['AppStoreImg']) ? $_POST['AppStoreImg'] : '';
$AboutUsBgImage = isset($_POST['AboutUsBgImage']) ? $_POST['AboutUsBgImage'] : '';
$AboutUsTitle = isset($_POST['AboutUsTitle']) ? $_POST['AboutUsTitle'] : '';
$AboutUsSecondTitle = isset($_POST['AboutUsSecondTitle']) ? $_POST['AboutUsSecondTitle'] : '';
$AboutUsContent = isset($_POST['AboutUsContent']) ? $_POST['AboutUsContent'] : '';
$HomeRestuarantSectionLabel = isset($_POST['HomeRestuarantSectionLabel']) ? $_POST['HomeRestuarantSectionLabel'] : '';
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';

if (isset($_POST['submit'])) {

    if ($action == "Add" && !$userObj->hasPermission('create-home-page-content')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create Home page content.';
        header("Location:homecontent.php");
        exit;
    }

    if ($action == "Edit" && !$userObj->hasPermission('edit-home-page-content')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update Home page content.';
        header("Location:homecontent.php");
        exit;
    }



    if (SITE_TYPE == 'Demo') {
        header("Location:homecontent_action.php?id=" . $id . "&success=2");
        exit;
    }

    $q = "INSERT INTO ";
    $where = '';

    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `vCode` = '" . $vCode . "'";
    }

    $query = $q . " `" . $tbl_name . "` SET
	`BannerBigTitle` = '" . $BannerBigTitle . "', 
	`BannerSmallTitle` = '" . $BannerSmallTitle . "', 
	`BannerContent` = '" . $BannerContent . "',  
	`FirstSectionHeading` = '" . $FirstSectionHeading . "', 
	`FirstParaTitle` = '" . $FirstParaTitle . "', 
	`FirstParaContent` = '" . $FirstParaContent . "', 
	`SecondParaTitle` = '" . $SecondParaTitle . "', 
	`SecondParaContent` = '" . $SecondParaContent . "', 
	`ThirdParaTitle` = '" . $ThirdParaTitle . "',  
	`ThirdParaContent` = '" . $ThirdParaContent . "',  
	`MidFirstTitle` = '" . $MidFirstTitle . "', 
	`MidFirstContent` = '" . $MidFirstContent . "', 
	`MidSecTitle` = '" . $MidSecTitle . "', 
	`MidSecContent` = '" . $MidSecContent . "', 
	`MidThirdTitle` = '" . $MidThirdTitle . "', 
	`MidThirdContent` = '" . $MidThirdContent . "', 
	`ThirdRightTitle` = '" . $ThirdRightTitle . "',  
	`ThirdRightContent` = '" . $ThirdRightContent . "',  
	`AboutUsTitle` = '" . $AboutUsTitle . "', 
	`AboutUsSecondTitle` = '" . $AboutUsSecondTitle . "', 
	`AboutUsContent` = '" . $AboutUsContent . "',
	`HomeRestuarantSectionLabel` = '" . $HomeRestuarantSectionLabel . "'"
            . $where;
    $obj->sql_query($query);

    $id = ($id != '') ? $id : $obj->GetInsertId();

    $img_arr = $_FILES;
    if (!empty($img_arr)) {
        foreach ($img_arr as $key => $value) {
            $currrent_upload_time = time();
            if (!empty($value['name'])) {
                $img_path = $tconfig["tsite_upload_apptype_page_images_panel"];
                $temp_gallery = $img_path . '/';
                $image_object = $value['tmp_name'];
                $image_name = $value['name'];
                $check_file_query = "SELECT " . $key . " FROM ".$tbl_name." where vCode='" . $vCode . "'";
                $check_file = $obj->MySQLSelect($check_file_query);

                if ($id != "") {
                    $check_file[$key] = $img_path . $template . '/' . $check_file[0][$key];

                    if ($check_file[$key] != '' && file_exists($check_file[$key])) {
                        @unlink($img_path . $template . '/' . $check_file[0][$key]);
                    }
                }

                $Photo_Gallery_folder = $img_path . $template . "/";
                if (!is_dir($Photo_Gallery_folder)) {
                    mkdir($Photo_Gallery_folder, 0777);
                }
                $img = $generalobj->fileuploadhome($Photo_Gallery_folder, $image_object, $image_name, '', 'png,jpg,jpeg,gif', $vCode);
                if ($img[2] == "1") {
                    $_SESSION['success'] = '0';
                    $_SESSION['var_msg'] = $img[1];
                    header("location:" . $backlink);
                }

                if (!empty($img[0])) {
                    $sql = "UPDATE " . $tbl_name . " SET " . $key . " = '" . $img[0] . "' WHERE `vCode` = '" . $vCode . "'";
                    $obj->sql_query($sql);
                }
            }
        }
    }

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
    $sql = "SELECT hc.*,lm.vTitle FROM ".$tbl_name." as hc LEFT JOIN language_master as lm on lm.vCode = hc.vCode  WHERE hc.id = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    $vLabel = $id;
    if (count($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $vCode = $value['vCode'];
            $BannerBgImage = $value['BannerBgImage'];
            $BannerBigTitle = $value['BannerBigTitle'];
            $BannerSmallTitle = $value['BannerSmallTitle'];
            $BannerContent = $value['BannerContent'];
            $FirstSectionLeftImage = $value['FirstSectionLeftImage'];
            $FirstSectionHeading = $value['FirstSectionHeading'];
            $FirstParaTitle = $value['FirstParaTitle'];
            $FirstParaContent = $value['FirstParaContent'];
            $SecondParaTitle = $value['SecondParaTitle'];
            $SecondParaContent = $value['SecondParaContent'];
            $ThirdParaTitle = $value['ThirdParaTitle'];
            $ThirdParaContent = $value['ThirdParaContent'];
            $MidFirstImage = $value['MidFirstImage'];
            $MidFirstTitle = $value['MidFirstTitle'];
            $MidFirstContent = $value['MidFirstContent'];
            $MidSecImage = $value['MidSecImage'];
            $MidSecTitle = $value['MidSecTitle'];
            $MidSecContent = $value['MidSecContent'];
            $MidThirdImage = $value['MidThirdImage'];
            $MidThirdTitle = $value['MidThirdTitle'];
            $MidThirdContent = $value['MidThirdContent'];
            $ThirdLeftImg1 = $value['ThirdLeftImg1'];
            $ThirdLeftImg2 = $value['ThirdLeftImg2'];
            $ThirdLeftImg3 = $value['ThirdLeftImg3'];
            $ThirdRightTitle = $value['ThirdRightTitle'];
            $ThirdRightContent = $value['ThirdRightContent'];
            $PlayStoreImg = $value['PlayStoreImg'];
            $AppStoreImg = $value['AppStoreImg'];
            $AboutUsBgImage = $value['AboutUsBgImage'];
            $AboutUsTitle = $value['AboutUsTitle'];
            $AboutUsSecondTitle = $value['AboutUsSecondTitle'];
            $AboutUsContent = $value['AboutUsContent'];
            $HomeRestuarantSectionLabel = $value['HomeRestuarantSectionLabel'];
            $eStatus = $value['eStatus'];
            $title = $value['vTitle'];
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
        <?php include_once('global_files.php'); ?>
        <!-- On OFF switch -->
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
        <style>
            .body-div.innersection {
                box-shadow: -1px -2px 73px 2px #dedede;
                float: none;
            }
            .innerbg_image {
                width:auto;margin:10px 0;max-width: 50%;
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
            <?php include_once('header.php'); ?>
            <?php include_once('left_menu.php'); ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2><?= $action; ?> Home Content <?php if (!empty($title)) { ?> (<?php echo $title; ?>)<?php } ?></h2>
                            <a href="homecontent.php" class="back_link">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />
                    <div class="body-div">
                        <div class="form-group">
                            <?php if ($success == 1) { ?>
                                <div class="alert alert-success alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                                </div><br/>
                            <?php } elseif ($success == 2) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                </div><br/>
                            <?php } ?>
                            <form method="post" name="_home_content_form" id="_home_content_form" action="" enctype='multipart/form-data'>
                                <input type="hidden" name="id" value="<?= $id; ?>"/>
                                <input type="hidden" name="vCode" value="<?= $vCode; ?>">
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="homecontent.php"/>

                                <!-- Start Home Banner area-->
                                <div class="body-div innersection">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home Banner Image <span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <?php if ($BannerBgImage != '') { ?>
                                                    <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . "/" . $BannerBgImage; ?>" class="innerbg_image"/>
                                                <?php } ?>
                                                <input type="file" class="form-control" name="BannerBgImage"  id="BannerBgImage" accept=".png,.jpg,.jpeg,.gif">
                                                <br/>
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 1300px * 600px.]</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home Banner First Title<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="BannerBigTitle"  id="BannerBigTitle" value="<?= $BannerBigTitle; ?>" placeholder="Home Banner First Title" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home Banner Second Title<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="BannerSmallTitle"  id="BannerSmallTitle" value="<?= $BannerSmallTitle; ?>" placeholder="Home Banner Second Title" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home Banner Content<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <textarea class="form-control ckeditor" rows="10" name="BannerContent"  id="BannerContent"  placeholder="Home Banner Content" required><?= $BannerContent; ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Home Banner area-->
                                <!-- Start Home First Section-->
                                <div class="body-div innersection">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home First Section Left Image <span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <?php if ($FirstSectionLeftImage != '') { ?>
                                                    <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . "/" . $FirstSectionLeftImage; ?>" class="innerbg_image"/>
                                                <?php } ?>
                                                <input type="file" class="form-control" name="FirstSectionLeftImage"  id="FirstSectionLeftImage" accept=".png,.jpg,.jpeg,.gif">
                                                <br/>
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 485px * 655px.]</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home First Section Title<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="FirstSectionHeading"  id="FirstSectionHeading" value="<?= $FirstSectionHeading; ?>" placeholder="Home First Section Title" required>
                                            </div>
                                        </div>
                                        <!-- First Section-->
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home First Section Paragraph One Title<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="FirstParaTitle"  id="FirstParaTitle" value="<?= $FirstParaTitle; ?>" placeholder="Home First Section Paragraph One Title" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home First Section Paragraph One Description<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <textarea class="form-control ckeditor" rows="10" name="FirstParaContent"  id="FirstParaContent"  placeholder="Home First Section Paragraph One Description" required><?= $FirstParaContent; ?></textarea>
                                            </div>
                                        </div>
                                        <!-- First Section End-->
                                        <!-- Second Section-->
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home First Section Paragraph Second Title<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="SecondParaTitle"  id="SecondParaTitle" value="<?= $SecondParaTitle; ?>" placeholder="Home First Section Paragraph Second Title" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home First Section Paragraph Second Description<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <textarea class="form-control ckeditor" rows="10" name="SecondParaContent"  id="SecondParaContent" required><?= $SecondParaContent; ?></textarea>
                                            </div>
                                        </div>
                                        <!-- Second Section End-->
                                        <!-- Third Section Start-->
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home First Section Paragraph Third Title<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="ThirdParaTitle"  id="ThirdParaTitle" value="<?= $ThirdParaTitle; ?>" placeholder="Home First Section Paragraph Third Title" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home First Section Paragraph Third Description<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <textarea class="form-control ckeditor" rows="10" name="ThirdParaContent"  id="ThirdParaContent" required><?= $ThirdParaContent; ?></textarea>
                                            </div>
                                        </div>
                                        <!-- Third Section End-->
                                    </div>
                                </div>
                                <!-- End Home First Section-->
                                <!-- Start Home Middle Section-->
                                <div class="body-div innersection">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home Middle Section Image One <span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <?php if ($MidFirstImage != '') { ?>
                                                    <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . "/" . $MidFirstImage; ?>" class="innerbg_image"/>
                                                <?php } ?>
                                                <input type="file" class="form-control" name="MidFirstImage"  id="MidFirstImage" accept=".png,.jpg,.jpeg,.gif">
                                                <br/>
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 485px * 655px.]</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home Middle Section Title One<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="MidFirstTitle"  id="MidFirstTitle" value="<?= $MidFirstTitle; ?>" placeholder="Home Middle Section Title One" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home Middle Section Description One<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <textarea class="form-control ckeditor" rows="10" name="MidFirstContent"  id="MidFirstContent"  placeholder="Home Middle Section Description One" required><?= $MidFirstContent; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home Middle Section Image Two<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <?php if ($MidSecImage != '') { ?>
                                                    <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . "/" . $MidSecImage; ?>" class="innerbg_image"/>
                                                <?php } ?>
                                                <input type="file" class="form-control" name="MidSecImage"  id="MidSecImage" accept=".png,.jpg,.jpeg,.gif">
                                                <br/>
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 400px * 225px.]</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home Middle Section Title Two<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="MidSecTitle"  id="MidSecTitle" value="<?= $MidSecTitle; ?>" placeholder="Home Middle Section Title Two" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home Middle Section Description Two<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <textarea class="form-control ckeditor" rows="10" name="MidSecContent" id="MidSecContent" placeholder="Home Middle Section Description Two" required><?= $MidSecContent; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home Third Middle Section Image Three<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <?php if ($MidThirdImage != '') { ?>
                                                    <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . "/" . $MidThirdImage; ?>" class="innerbg_image"/>
                                                <?php } ?>
                                                <input type="file" class="form-control" name="MidThirdImage"  id="MidThirdImage" accept=".png,.jpg,.jpeg,.gif">
                                                <br/>
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 400px * 225px.]</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home Third Middle Section Title Three<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="MidThirdTitle"  id="MidThirdTitle" value="<?= $MidThirdTitle; ?>" placeholder="Home Third Middle Section Title Three" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home Third Middle Section Description Three<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <textarea class="form-control ckeditor" rows="10" name="MidThirdContent" id="MidThirdContent" placeholder="Home Third Middle Section Description Three" required><?= $MidThirdContent; ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Home Middle area-->
                                <!-- Start Home Third Section-->
                                <div class="body-div innersection">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home Third Section Image One<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <?php if ($ThirdLeftImg1 != '') { ?>
                                                    <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . "/" . $ThirdLeftImg1; ?>" class="innerbg_image"/>
                                                <?php } ?>
                                                <input type="file" class="form-control" name="ThirdLeftImg1"  id="ThirdLeftImg1" accept=".png,.jpg,.jpeg,.gif">
                                                <br/>
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 1350px * 650px.]</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home Third Section Image Two<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <?php if ($ThirdLeftImg2 != '') { ?>
                                                    <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . "/" . $ThirdLeftImg2; ?>" class="innerbg_image"/>
                                                <?php } ?>
                                                <input type="file" class="form-control" name="ThirdLeftImg2"  id="ThirdLeftImg2" accept=".png,.jpg,.jpeg,.gif">
                                                <br/>
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 550px * 600px.]</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home Third Section Image Three<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <?php if ($ThirdLeftImg3 != '') { ?>
                                                    <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . "/" . $ThirdLeftImg3; ?>" class="innerbg_image"/>
                                                <?php } ?>
                                                <input type="file" class="form-control" name="ThirdLeftImg3"  id="ThirdLeftImg3" accept=".png,.jpg,.jpeg,.gif">
                                                <br/>
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 550px * 600px.]</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home Third Section Right Title<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="ThirdRightTitle"  id="ThirdRightTitle" value="<?= $ThirdRightTitle; ?>" placeholder="Home Third Section Right Title" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home Third Section Right Description<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <textarea class="form-control ckeditor" rows="10" name="ThirdRightContent"  id="ThirdRightContent"  placeholder="ome Third Section Right Description" required><?= $ThirdRightContent; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Play Store Image<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <?php if ($PlayStoreImg != '') { ?>
                                                    <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . "/" . $PlayStoreImg; ?>" class="innerbg_image"/>
                                                <?php } ?>
                                                <input type="file" class="form-control" name="PlayStoreImg"  id="PlayStoreImg" accept=".png,.jpg,.jpeg,.gif">
                                                <br/>
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 550px * 600px.]</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>APP Store Image<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <?php if ($AppStoreImg != '') { ?>
                                                    <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . "/" . $AppStoreImg; ?>" class="innerbg_image"/>
                                                <?php } ?>
                                                <input type="file" class="form-control" name="AppStoreImg"  id="AppStoreImg" accept=".png,.jpg,.jpeg,.gif">
                                                <br/>
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 550px * 600px.]</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Home Third Section-->
                                <!-- Start About Us Section-->
                                <div class="body-div innersection">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home About Us Section Background Image<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <?php if ($AboutUsBgImage != '') { ?>
                                                    <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . "/" . $AboutUsBgImage; ?>" class="innerbg_image"/>
                                                <?php } ?>
                                                <input type="file" class="form-control" name="AboutUsBgImage"  id="AboutUsBgImage" accept=".png,.jpg,.jpeg,.gif">
                                                <br/>
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 20px * 500px.]</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home About Us Section Title<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="AboutUsTitle"  id="AboutUsTitle" value="<?= $AboutUsTitle; ?>" placeholder="Home About Us Section Title" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home About Us Section Second Title<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="AboutUsSecondTitle"  id="AboutUsSecondTitle" value="<?= $AboutUsSecondTitle; ?>" placeholder="Home About Us Section Second Title" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home About Us Section Description<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <textarea class="form-control ckeditor" rows="10" name="AboutUsContent"  id="AboutUsContent"  placeholder="Home About Us Section Description" required><?= $AboutUsContent; ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- End About Us Section-->
                                <!-- Start Home Restuarant Section-->
                                <div class="body-div innersection">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Home Restaurant Section Title<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="HomeRestuarantSectionLabel"  id="HomeRestuarantSectionLabel" value="<?= $HomeRestuarantSectionLabel; ?>" placeholder="Home Restaurant Section Title" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Start Home Restuarant Section-->
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
                                        <?php if (($action == 'Edit' && $userObj->hasPermission('edit-home-page-content')) || ($action == 'Add' && $userObj->hasPermission('create-home-page-content'))) { ?>
                                            <input type="submit" class=" btn btn-default" name="submit" id="submit" value="<?= $action; ?> Home Content">
                                            <input type="reset" value="Reset" class="btn btn-default">
                                        <?php } ?>
                                        <a href="homecontent.php" class="btn btn-default back_link">Cancel</a>
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
        <?php include_once('footer.php'); ?>
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
                if ($("#previousLink").val() == "") {
                    referrer = document.referrer;
                } else {
                    referrer = $("#previousLink").val();
                }

                if (referrer == "") {
                    referrer = "homecontent.php";
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
    </body>
    <!-- END BODY-->
</html>