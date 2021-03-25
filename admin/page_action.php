<?
include_once('../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();

require_once(TPATH_CLASS . "Imagecrop.class.php");

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';

$cubexthemeon = 'No';
if($generalobj->checkXThemOn() == 'Yes') {
    $cubexthemeon = 'Yes';
}

if($cubexthemeon == 'Yes'){
    if ($id == 1 && !isset($_POST['submit'])){
      header("Location:page_action.php?id=52");
      exit();
    }
}

$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';

$tbl_name = 'pages';
$script = 'page';


$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

// fetch all lang from language_master table
$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = count($db_master);

// set all variables with either post (when submit) either blank (when insert)
$iPageId = isset($_POST['iPageId']) ? $_POST['iPageId'] : $id;
$vPageName = isset($_REQUEST['vPageName']) ? $_REQUEST['vPageName'] : '';
$vTitle = isset($_REQUEST['vTitle']) ? $_REQUEST['vTitle'] : '';
$tMetaKeyword = isset($_REQUEST['tMetaKeyword']) ? $_REQUEST['tMetaKeyword'] : '';
$tMetaDescription = isset($_REQUEST['tMetaDescription']) ? $_REQUEST['tMetaDescription'] : '';
$vImage = isset($_POST['vImage']) ? $_POST['vImage'] : '';
$vImage1 = isset($_POST['vImage1']) ? $_POST['vImage1'] : '';
$vImage2 = isset($_POST['vImage2']) ? $_POST['vImage2'] : '';
$iOrderBy = isset($_POST['iOrderBy']) ? $_POST['iOrderBy'] : ''; //added by SP for pages orderby,active/inactive functionality
$thumb = new thumbnail();

$pageArray = array('48','50');
$pageidCubexImage = array('48','49','50');

if($cubexthemeon == 'Yes' && $iPageId==1){
    $vPageName = isset($_REQUEST['vPageName_1']) ? $_REQUEST['vPageName_1'] : '';
    $vTitle = isset($_REQUEST['vTitle_1']) ? $_REQUEST['vTitle_1'] : '';
    $tMetaKeyword = isset($_REQUEST['tMetaKeyword_1']) ? $_REQUEST['tMetaKeyword_1'] : '';
    $tMetaDescription = isset($_REQUEST['tMetaDescription_1']) ? $_REQUEST['tMetaDescription_1'] : '';
    $iOrderBy = isset($_POST['iOrderBy_1']) ? $_POST['iOrderBy_1'] : '';
}

 if($cubexthemeon == 'Yes' && $iPageId!=53) {
    //if(empty($template)) $template = 'Cubex';
    $Photo_Gallery_folder = $tconfig["tsite_upload_apptype_images_panel"] . '/'. $template .'/' ;
    $images = $tconfig['tsite_upload_apptype_images'].$template .'/';
 } else {
    $Photo_Gallery_folder = $tconfig["tsite_upload_page_images_panel"] . '/';
    $images = $tconfig['tsite_upload_page_images'];
 }

if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
        $vPageTitle = 'vPageTitle_' . $db_master[$i]['vCode'];
        $$vPageTitle = isset($_POST[$vPageTitle]) ? $_POST[$vPageTitle] : '';
        $tPageDesc = 'tPageDesc_' . $db_master[$i]['vCode'];
        $$tPageDesc = isset($_POST[$tPageDesc]) ? $_POST[$tPageDesc] : '';

        if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {

            $vPageSubTitle = 'vPageTitle_' . $db_master[$i]['vCode'];
            $$vPageSubTitle = isset($_POST[$vPageSubTitle]) ? $_POST[$vPageSubTitle] : '';

            $vUserPageTitle = 'vUserPageTitle_' . $db_master[$i]['vCode'];
            $$vUserPageTitle = isset($_POST[$vUserPageTitle]) ? $_POST[$vUserPageTitle] : '';
            $tUserPageDesc = 'tUserPageDesc_' . $db_master[$i]['vCode'];
            $$tUserPageDesc = isset($_POST[$tUserPageDesc]) ? $_POST[$tUserPageDesc] : '';

            $vProviderPageTitle = 'vProviderPageTitle_' . $db_master[$i]['vCode'];
            $$vProviderPageTitle = isset($_POST[$vProviderPageTitle]) ? $_POST[$vProviderPageTitle] : '';
            $tProviderPageDesc = 'tProviderPageDesc_' . $db_master[$i]['vCode'];
            $$tProviderPageDesc = isset($_POST[$tProviderPageDesc]) ? $_POST[$tProviderPageDesc] : '';

            $vCompanyPageTitle = 'vCompanyPageTitle_' . $db_master[$i]['vCode'];
            $$vCompanyPageTitle = isset($_POST[$vCompanyPageTitle]) ? $_POST[$vCompanyPageTitle] : '';
            $tCompanyPageDesc = 'tCompanyPageDesc_' . $db_master[$i]['vCode'];
            $$tCompanyPageDesc = isset($_POST[$tCompanyPageDesc]) ? $_POST[$tCompanyPageDesc] : '';
            
            $vRestaurantPageTitle = 'vRestaurantPageTitle_' . $db_master[$i]['vCode'];
            $$vRestaurantPageTitle = isset($_POST[$vRestaurantPageTitle]) ? $_POST[$vRestaurantPageTitle] : '';
            $tRestaurantPageDesc = 'tRestaurantPageDesc_' . $db_master[$i]['vCode'];
            $$tRestaurantPageDesc = isset($_POST[$tRestaurantPageDesc]) ? $_POST[$tRestaurantPageDesc] : '';

            $vOrgPageTitle = 'vOrgPageTitle_' . $db_master[$i]['vCode'];
            $$vOrgPageTitle = isset($_POST[$vOrgPageTitle]) ? $_POST[$vOrgPageTitle] : '';
            $tOrgPageDesc = 'tOrgPageDesc_' . $db_master[$i]['vCode'];
            $$tOrgPageDesc = isset($_POST[$tOrgPageDesc]) ? $_POST[$tOrgPageDesc] : '';
            
            $vHotelPageTitle = 'vHotelPageTitle_' . $db_master[$i]['vCode'];
            $$vHotelPageTitle = isset($_POST[$vHotelPageTitle]) ? $_POST[$vHotelPageTitle] : '';
            $tHotelPageDesc = 'tHotelPageDesc_' . $db_master[$i]['vCode'];
            $$tHotelPageDesc = isset($_POST[$tHotelPageDesc]) ? $_POST[$tHotelPageDesc] : '';

            $vPageTitle = $vPageTitle;
            $$vPageTitle = $generalobj->getJsonFromAnArr(array("user"=>$$vUserPageTitle,"provider"=>$$vProviderPageTitle,"company"=>$$vCompanyPageTitle,"restaurant"=>$$vRestaurantPageTitle,"org"=>$$vOrgPageTitle,"hotel"=>$$vHotelPageTitle));

            $tPageDesc = $tPageDesc;
            $$tPageDesc = $generalobj->getJsonFromAnArr(array("user"=>$$tUserPageDesc,"provider"=>$$tProviderPageDesc,"company"=>$$tCompanyPageDesc,"restaurant"=>$$tRestaurantPageDesc,"org"=>$$tOrgPageDesc,"hotel"=>$$tHotelPageDesc));
        }
        
        if($cubexthemeon == 'Yes' && $iPageId==52) {
            $vPageSubTitle = 'vPageTitle_' . $db_master[$i]['vCode'];
            $$vPageSubTitle = isset($_POST[$vPageSubTitle]) ? $_POST[$vPageSubTitle] : '';
            
            $tPageSecDesc = 'tPageSecDesc_' . $db_master[$i]['vCode'];
            $$tPageSecDesc = isset($_POST[$tPageSecDesc]) ? $_POST[$tPageSecDesc] : '';
            $tPageThirdDesc = 'tPageThirdDesc_' . $db_master[$i]['vCode'];
            $$tPageThirdDesc = isset($_POST[$tPageThirdDesc]) ? $_POST[$tPageThirdDesc] : '';
        }

        if($cubexthemeon == 'Yes' && $iPageId==1){
            $vPageTitle = 'vPageTitle_' . $db_master[$i]['vCode'].'_1';
            $$vPageTitle = isset($_POST[$vPageTitle]) ? $_POST[$vPageTitle] : '';
            $tPageDesc = 'tPageDesc_' . $db_master[$i]['vCode'].'_1';
            $$tPageDesc = isset($_POST[$tPageDesc]) ? $_POST[$tPageDesc] : '';
        }
    }
}

if (isset($_POST['submit'])) {

    if ($action == "Add" && !$userObj->hasPermission('create-pages')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create page.';
        header("Location:page.php");
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission('edit-pages')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update page.';
        header("Location:page.php");
        exit;
    }


    if (SITE_TYPE == "Demo") {
        header("Location:page_action.php?id=" . $iPageId . '&success=2');
        exit;
    }
    $vPageSubTitleArr = array();
    if (count($db_master) > 0) {
        $str = '';
        for ($i = 0; $i < count($db_master); $i++) {

            if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {

                $vPageSubTitleArr["pageSubtitle_".$db_master[$i]['vCode']] = $_REQUEST['vPageSubTitle'][$db_master[$i]['vCode']];

                $vUserPageTitle = 'vUserPageTitle_' . $db_master[$i]['vCode'];
                $$vUserPageTitle = $_REQUEST[$vUserPageTitle];
                $tUserPageDesc = 'tUserPageDesc_' . $db_master[$i]['vCode'];
                $$tUserPageDesc = $_REQUEST[$tUserPageDesc];

                $vProviderPageTitle = 'vProviderPageTitle_' . $db_master[$i]['vCode'];
                $$vProviderPageTitle = $_REQUEST[$vProviderPageTitle];
                $tProviderPageDesc = 'tProviderPageDesc_' . $db_master[$i]['vCode'];
                $$tProviderPageDesc = $_REQUEST[$tProviderPageDesc];

                $vCompanyPageTitle = 'vCompanyPageTitle_' . $db_master[$i]['vCode'];
                $$vCompanyPageTitle = $_REQUEST[$vCompanyPageTitle];
                $tCompanyPageDesc = 'tCompanyPageDesc_' . $db_master[$i]['vCode'];
                $$tCompanyPageDesc = $_REQUEST[$tCompanyPageDesc];

                $vRestaurantPageTitle = 'vRestaurantPageTitle_' . $db_master[$i]['vCode'];
                $$vRestaurantPageTitle = $_REQUEST[$vRestaurantPageTitle];
                $tRestaurantPageDesc = 'tRestaurantPageDesc_' . $db_master[$i]['vCode'];
                $$tRestaurantPageDesc = $_REQUEST[$tRestaurantPageDesc];

                $vOrgPageTitle = 'vOrgPageTitle_' . $db_master[$i]['vCode'];
                $$vOrgPageTitle = $_REQUEST[$vOrgPageTitle];
                $tOrgPageDesc = 'tOrgPageDesc_' . $db_master[$i]['vCode'];
                $$tOrgPageDesc = $_REQUEST[$tOrgPageDesc];
                
                $vHotelPageTitle = 'vHotelPageTitle_' . $db_master[$i]['vCode'];
                $$vHotelPageTitle = $_REQUEST[$vHotelPageTitle];
                $tHotelPageDesc = 'tHotelPageDesc_' . $db_master[$i]['vCode'];
                $$tHotelPageDesc = $_REQUEST[$tHotelPageDesc];

                $vPageTitle = 'vPageTitle_' . $db_master[$i]['vCode'];
                $$vPageTitle = $generalobj->getJsonFromAnArr(array("user"=>$$vUserPageTitle,"provider"=>$$vProviderPageTitle,"company"=>$$vCompanyPageTitle,"restaurant"=>$$vRestaurantPageTitle,"org"=>$$vOrgPageTitle,"hotel"=>$$vHotelPageTitle));

                $tPageDesc = 'tPageDesc_' . $db_master[$i]['vCode'];
                $$tPageDesc = $generalobj->getJsonFromAnArr(array("user"=>$$tUserPageDesc,"provider"=>$$tProviderPageDesc,"company"=>$$tCompanyPageDesc,"restaurant"=>$$tRestaurantPageDesc,"org"=>$$tOrgPageDesc,"hotel"=>$$tHotelPageDesc));

                $str .= " " . $vPageTitle . " = '" . $$vPageTitle . "', " . $tPageDesc . " = '" . $$tPageDesc . "', ";

            } else if($cubexthemeon == 'Yes' && $iPageId == 52) {
                $vPageTitle = 'vPageTitle_' . $db_master[$i]['vCode'];
                $$vPageTitle = $_REQUEST[$vPageTitle];
                
                $vPageSubTitleArr['pageSubtitle_'.$db_master[$i]['vCode']] = $_REQUEST['vPageSubTitle'][$db_master[$i]['vCode']];
                
                $tPageDesc = 'tPageDesc_' . $db_master[$i]['vCode'];
                $$tPageDesc = $_REQUEST[$tPageDesc];
                $tPageSecDesc = 'tPageSecDesc_' . $db_master[$i]['vCode'];
                $$tPageSecDesc = $_REQUEST[$tPageSecDesc];
                $tPageThirdDesc = 'tPageThirdDesc_' . $db_master[$i]['vCode'];
                $$tPageThirdDesc = $_REQUEST[$tPageThirdDesc];
                
                $$tPageDesc = $generalobj->getJsonFromAnArr(array("FirstDesc"=>$$tPageDesc,"SecDesc"=>$$tPageSecDesc,"ThirdDesc"=>$$tPageThirdDesc));

                $tPageDesc = 'tPageDesc_' . $db_master[$i]['vCode'];
                $str .= " " . $vPageTitle . " = '" . $$vPageTitle . "', " . $tPageDesc . " = '" . $$tPageDesc . "', ";
                
            } else if($cubexthemeon == 'Yes' && $iPageId==1){

                $vPageTitlekey = 'vPageTitle_' . $db_master[$i]['vCode'];
                $vPageTitle = 'vPageTitle_' . $db_master[$i]['vCode'].'_1';
                $$vPageTitle = isset($_REQUEST[$vPageTitle]) ? $_REQUEST[$vPageTitle] : '';
                $tPageDesckey = 'tPageDesc_' . $db_master[$i]['vCode'];
                $tPageDesc = 'tPageDesc_' . $db_master[$i]['vCode'].'_1';
                $$tPageDesc = isset($_REQUEST[$tPageDesc]) ? $_REQUEST[$tPageDesc] : '';
                $str .= " " . $vPageTitlekey . " = '" . $$vPageTitle . "', " . $tPageDesckey . " = '" . $$tPageDesc . "', ";

            } else {
                $vPageTitle = 'vPageTitle_' . $db_master[$i]['vCode'];

                $$vPageTitle = $_REQUEST[$vPageTitle];

                $tPageDesc = 'tPageDesc_' . $db_master[$i]['vCode'];
                $$tPageDesc = $_REQUEST[$tPageDesc];

                $str .= " " . $vPageTitle . " = '" . $$vPageTitle . "', " . $tPageDesc . " = '" . $$tPageDesc . "', ";
            }

        }
    }
    
    if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
        $vPageSubTitle = json_encode($vPageSubTitleArr);
        $str .= " pageSubtitle = '". $vPageSubTitle."', ";
    }
    if($cubexthemeon == 'Yes' && $iPageId==52) {
        $vPageSubTitle = $generalobj->getJsonFromAnArr($vPageSubTitleArr);
        $str .= " pageSubtitle = '". $vPageSubTitle."', ";
    }

    $image_object = $_FILES['vImage']['tmp_name'];
    $image_name = $_FILES['vImage']['name'];
    $image_name = str_replace(' ', '', $image_name);
    //echo "<pre>";print_r( $_FILES);print_r($_POST);echo "</pre>";exit;
    if ($image_name != "") {
        $filecheck = basename($_FILES['vImage']['name']);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[count($fileextarr) - 1]);
        $flag_error = 0;
        if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp" && $ext1 != "svg") {
            $flag_error = 1;
            $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .svg";
        }
        if ($_FILES['vImage']['size'] > 2097152) {
            $flag_error = 1;
            $var_msg = "Image size is too large";
        }
        if ($flag_error == 1) {
            $generalobj->getPostForm($_POST, $var_msg, $tconfig['tsite_url_main_admin'] . "page_action.php?id=".$id."&success=3");
            exit;
        } else {
            //$Photo_Gallery_folder = $tconfig["tsite_upload_page_images_panel"] . '/';
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
            }
            $img = $generalobj->fileupload($Photo_Gallery_folder, $image_object, $image_name, '', 'jpg,png,gif,jpeg,svg');
            $vImage = $img[0];
            // echo "<pre>";print_r($img);exit;
        }
    }

    $image_object1 = $_FILES['vImage1']['tmp_name'];
    $image_name1 = $_FILES['vImage1']['name'];
    $image_name1 = str_replace(' ', '', $image_name1);
    //echo "<pre>";print_r( $_FILES);echo "</pre>";exit;
    if ($image_name1 != "") {
        $filecheck1 = basename($_FILES['vImage1']['name']);
        $fileextarr1 = explode(".", $filecheck1);
        $ext1 = strtolower($fileextarr1[count($fileextarr1) - 1]);
        $flag_error1 = 0;
        if ($ext1 != "jpg" && $ext1 != "gif" && $ext1 != "png" && $ext1 != "jpeg" && $ext1 != "bmp" && $ext1 != "svg") {
            $flag_error1 = 1;
            $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .svg";
        }
        if ($_FILES['vImage1']['size'] > 2097152) {
            $flag_error1 = 1;
            $var_msg = "Image size is too large";
        }
        if ($flag_error1 == 1) {
            $generalobj->getPostForm($_POST, $var_msg, $tconfig['tsite_url_main_admin'] . "page_action.php?id=".$id."&success=3");
            exit;
        } else {
            //$Photo_Gallery_folder = $tconfig["tsite_upload_page_images_panel"] . '/';
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
            }
            $img1 = $generalobj->fileupload($Photo_Gallery_folder, $image_object1, $image_name1, '', 'jpg,png,gif,jpeg,svg');
            // echo "<pre>";print_r($img);exit;
            $vImage1 = $img1[0];
        }
    }
    
   // if(!empty($vImage2)) {
        $image_object2 = $_FILES['vImage2']['tmp_name'];
        $image_name2 = $_FILES['vImage2']['name'];
        $image_name2 = str_replace(' ', '', $image_name2);
        //echo "<pre>";print_r( $_FILES);echo "</pre>";exit;
        if ($image_name2 != "") {
            $filecheck2 = basename($_FILES['vImage2']['name']);
            $fileextarr2 = explode(".", $filecheck2);
            $ext2 = strtolower($fileextarr2[count($fileextarr2) - 1]);
            $flag_error2 = 0;
            if ($ext2 != "jpg" && $ext2 != "gif" && $ext2 != "png" && $ext2 != "jpeg" && $ext2 != "bmp" && $ext2 != "svg") {
                $flag_error2= 1;
                $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .svg";
            }
            if ($_FILES['vImage1']['size'] > 2097152) {
                $flag_error2 = 1;
                $var_msg = "Image size is too large";
            }
            if ($flag_error2 == 1) {
                $generalobj->getPostForm($_POST, $var_msg, $tconfig['tsite_url_main_admin'] . "page_action.php?id=".$id."&success=3");
                exit;
            } else {
                //$Photo_Gallery_folder = $tconfig["tsite_upload_page_images_panel"] . '/';
                if (!is_dir($Photo_Gallery_folder)) {
                    mkdir($Photo_Gallery_folder, 0777);
                }
                $img2 = $generalobj->fileupload($Photo_Gallery_folder, $image_object2, $image_name2, '', 'jpg,png,gif,jpeg,svg');
                // echo "<pre>";print_r($img);exit;
                $vImage2 = $img2[0];
            }
        }
   // }

    $q = "INSERT INTO ";
    $where = '';

    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `iPageId` = '" . $iPageId . "'";
    }

    $query = $q . " `" . $tbl_name . "` SET " . $str . "
        `vPageName` = '" . $vPageName . "',
        `vTitle` = '" . $vTitle . "',
        `tMetaKeyword` = '" . $tMetaKeyword . "',
        `tMetaDescription` = '" . $tMetaDescription . "',
        `iOrderBy` = '" . $iOrderBy . "'"; //added by SP for pages orderby,active/inactive functionality

    if ($image_name != '') {
        $query .= ", vImage = '" . $vImage . "'";
    }
    if ($image_name1 != '') {
        $query .= ", vImage1 = '" . $vImage1 . "'";
    }
     if ($image_name2 != '') {
        $query .= ", vImage2 = '" . $vImage2 . "'";
    }
    $query .= $where;
    //echo"<pre>";
    //echo $query;die;
    $Id = $obj->sql_query($query);
    //$s = $obj->GetInsertId();
    if ($action == 'Add') {
        $iPageId = $obj->GetInsertId();
    }

    //header("Location:page_action.php?id=".$iPageId.'&success=1');
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
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iPageId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    //echo '<pre>'; print_R($db_data); echo '</pre>'; exit;
    $vLabel = $id;


    if (count($db_data) > 0) {
        for ($i = 0; $i < count($db_master); $i++) {
            foreach ($db_data as $key => $value) {

                $vPageTitle = 'vPageTitle_' . $db_master[$i]['vCode'];
                $$vPageTitle = $value[$vPageTitle];
                $tPageDesc = 'tPageDesc_' . $db_master[$i]['vCode'];
                $$tPageDesc = $value[$tPageDesc];

                if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
                    $pageSubtitle = $value['pageSubtitle'];
                    $pageSubtitleArr = json_decode($pageSubtitle, true);
                }
                if($cubexthemeon == 'Yes' && $iPageId==52) {
                    $pageSubtitle = $value['pageSubtitle'];
                    $pageSubtitleArr = json_decode($pageSubtitle, true);
                }
                
                $vPageName = $value['vPageName'];
                $vTitle = $value['vTitle'];
                $tMetaKeyword = $value['tMetaKeyword'];
                $tMetaDescription = $value['tMetaDescription'];
                $vImage = $value['vImage'];
                $vImage1 = $value['vImage1'];
                $vImage2 = $value['vImage2'];
                $iOrderBy = $value['iOrderBy']; //added by SP for pages orderby,active/inactive functionality
            }
        }
    }
}

$serviceArray = $serviceIdArray = array();
$serviceArray = json_decode(serviceCategories, true);
$serviceIdArray = array_column($serviceArray, 'iServiceId');

$become_restaurant = '';
if(strtoupper(DELIVERALL) == "YES") {
    if (count($serviceIdArray) == 1 && $serviceIdArray[0]==1) {
        $become_restaurant = $langage_lbl_admin['LBL_RESTAURANT_TXT'];
    } else {
        $become_restaurant = $langage_lbl_admin['LBL_STORE'];
    }
}
$activetab = 'usertab';
$hotelPanel = isHotelPanelEnable();
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title>Admin | Static Page <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />

        <? include_once('global_files.php'); ?>
        <!-- PAGE LEVEL STYLES -->
        <link rel="stylesheet" href="../assets/plugins/Font-Awesome/css/font-awesome.css" />
        <link rel="stylesheet" href="../assets/plugins/wysihtml5/dist/bootstrap-wysihtml5-0.0.2.css" />
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
            <? include_once('header.php'); ?>
            <? include_once('left_menu.php'); ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2><?= $action; ?> Static Page</h2>
                            <a href="page.php" class="back_link">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />
                    <?php include('valid_msg.php'); ?>
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
                            <? if($cubexthemeon == 'Yes' && in_array($iPageId,array('1','52'))) { 
                             include_once('aboutus.php');
                            } else { ?>
                                <form method="post" action="" name="_page_form" id="_page_form"  enctype="multipart/form-data">
                                    <input type="hidden" name="id" value="<?= $id; ?>"/>
                                    <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                    <input type="hidden" name="backlink" id="backlink" value="page.php"/>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Page/Section</label>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="text" class="form-control" name="vPageName"  id="vPageName" value="<?= htmlspecialchars($vPageName); ?>" placeholder="Page Name">
                                        </div>
                                    </div>
                                    <?php if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {

                                    if($iPageId==48) {
                                    if ($count_all > 0) {
                                        for ($i = 0; $i < $count_all; $i++) {
                                            $vCode = $db_master[$i]['vCode'];
                                            $vLTitle = $db_master[$i]['vTitle'];
                                            $eDefault = $db_master[$i]['eDefault'];

                                            $vPageSubTitleS = "vPageSubTitle_$vCode";

                                            $vPageSubTitle = "vPageSubTitle[$vCode]";

                                            $required = ($eDefault == 'Yes') ? 'required' : '';
                                            $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                            ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Page Title (<?= $vLTitle; ?>) <?= $required_msg; ?></label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <input type="text" class="form-control" name="<?= $vPageSubTitle; ?>"  id="<?= $vPageSubTitleS; ?>" value="<?= $pageSubtitleArr["pageSubtitle_".$vCode]; ?>" placeholder="<?= $vPageSubTitleS; ?> Value" <?= $required; ?>>
                                                </div>
                                            </div>
                                            <?
                                        }
                                    }
                                    }
                                    ?>
                                    <input type="hidden" class="form-control" name="vTitle"  id="vTitle" value="<?= $vPageName; ?>" placeholder="Meta Title">
                                    <ul class="nav nav-tabs">
                                        <li class="">
                                            <a data-toggle="tab" href="#"></a>
                                        </li>
                                        <li class="<?php if($activetab=='usertab') { ?> active <?php }  ?>">
                                            <a data-toggle="tab" href="#usertab"><?=$langage_lbl_admin['LBL_RIDER'];?></a>
                                        </li>
                                        <li class="<?php if($activetab=='drivertab') { ?> active <?php }  ?>">
                                            <a data-toggle="tab" href="#drivertab"><?=$langage_lbl_admin['LBL_SIGNIN_DRIVER'];?></a>
                                        </li>
                                        <? if(strtoupper(ONLYDELIVERALL) != "YES") { ?><li class="<?php if($activetab=='companytab') { ?> active <?php }  ?>">
                                            <a data-toggle="tab" href="#companytab"><?=$langage_lbl_admin['LBL_COMPANY_SIGNIN'];?></a>
                                        </li>
                                        <? } if (!empty($become_restaurant)) { ?><li class="<?php if($activetab=='restauranttab') { ?> active <?php }  ?>">
                                            <a data-toggle="tab" href="#restauranttab"><?=$become_restaurant;?></a>
                                        </li>
                                        <? } if(strtoupper($ENABLE_CORPORATE_PROFILE)=='YES') { ?><li class="<?php if($activetab=='organizationtab') { ?> active <?php }  ?>">
                                            <a data-toggle="tab" href="#organizationtab"><?=$langage_lbl_admin['LBL_ORGANIZATION'];?></a>
                                        </li>
                                        <?php } if($iPageId==48) { ?> 
                                        <?php if($hotelPanel > 0) { ?><li class="<?php if($activetab=='hoteltab') { ?> active <?php }  ?>">
                                            <a data-toggle="tab" href="#hoteltab"><?=$langage_lbl_admin['LBL_HOTEL_LOGIN'];?></a>
                                        </li>
                                        <?php } } ?>
                                    </ul>
                                    <div class="tab-content">
                                        <div id="usertab" class="tab-pane <?php if($activetab=='usertab') { ?> active <?php }  ?>">

                                            <?php $style_v = "";
                                            if (in_array($iPageId, array('29', '30'))) {
                                                $style_v = "style = 'display:none;'";
                                            }
                                            if ($count_all > 0) {
                                                for ($i = 0; $i < $count_all; $i++) {
                                                    $vCode = $db_master[$i]['vCode'];
                                                    $vLTitle = $db_master[$i]['vTitle'];
                                                    $eDefault = $db_master[$i]['eDefault'];

                                                    $vPageTitleU = 'vUserPageTitle_' . $vCode;
                                                    $tPageDescU = 'tUserPageDesc_' . $vCode;

                                                    $vPageTitle = 'vPageTitle_' . $vCode;
                                                    $tPageDesc = 'tPageDesc_' . $vCode;

                                                    if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
                                                        $pagetitlearr = json_decode($$vPageTitle,true);
                                                        $pagedescarr = json_decode($$tPageDesc,true);
                                                        $titleval = $pagetitlearr['user'];
                                                        $descval = $pagedescarr['user'];
                                                    } else {
                                                        $titleval = $$vPageTitle;
                                                        $descval = $$tPageDesc;
                                                    }

                                                    //$required = ($eDefault == 'Yes') ? 'required' : '';
                                                   // $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                    ?>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <label>Page Sub Description (<?= $vLTitle; ?>) <?= $required_msg; ?></label>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <textarea class="form-control" name="<?= $vPageTitleU; ?>"  id="<?= $vPageTitleU; ?>" placeholder="<?= $vPageTitle; ?> Value" <?= $required; ?>><?= $titleval; ?></textarea>
                                                            <!--<input type="text" class="form-control" name="<?= $vPageTitleU; ?>"  id="<?= $vPageTitleU; ?>" value="<?= $titleval; ?>" placeholder="<?= $vPageTitle; ?> Value" <?= $required; ?>>-->
                                                        </div>
                                                    </div>

                                                    <!--- Editor -->
                                                    <div class="row" <?= $style_v ?>>
                                                        <div class="col-lg-12">
                                                            <label> Page Description (<?= $vLTitle; ?>) <?= $required_msg; ?></label>
                                                        </div>
                                                        <div class="col-lg-12">
                                                            <textarea class="form-control ckeditor" rows="10" name="<?= $tPageDescU; ?>"  id="<?= $tPageDescU; ?>"  placeholder="<?= $tPageDesc; ?> Value" <?= $required; ?>> <?= $descval; ?></textarea>
                                                        </div>
                                                    </div>

                                                    <!--- Editor -->
                                                    <?
                                                }
                                            } ?>
                                        </div>
                                        <div id="drivertab" class="tab-pane <?php if($activetab=='drivertab') { ?> active <?php }  ?>">

                                            <?php $style_v = "";
                                            if (in_array($iPageId, array('29', '30'))) {
                                                $style_v = "style = 'display:none;'";
                                            }
                                            if ($count_all > 0) {
                                                for ($i = 0; $i < $count_all; $i++) {
                                                    $vCode = $db_master[$i]['vCode'];
                                                    $vLTitle = $db_master[$i]['vTitle'];
                                                    $eDefault = $db_master[$i]['eDefault'];

                                                    $vPageTitleP = 'vProviderPageTitle_' . $vCode;
                                                    $tPageDescP = 'tProviderPageDesc_' . $vCode;
                                                    
                                                    $vPageTitle = 'vPageTitle_' . $vCode;
                                                    $tPageDesc = 'tPageDesc_' . $vCode;

                                                    if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
                                                        $pagetitlearr = json_decode($$vPageTitle,true);
                                                        $pagedescarr = json_decode($$tPageDesc,true);
                                                        $titleval = $pagetitlearr['provider'];
                                                        $descval = $pagedescarr['provider'];
                                                    } else {
                                                        $titleval = $$vPageTitle;
                                                        $descval = $$tPageDesc;
                                                    }

                                                    //$required = ($eDefault == 'Yes') ? 'required' : '';
                                                    //$required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                    ?>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <label>Page Sub Description (<?= $vLTitle; ?>) <?= $required_msg; ?></label>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <textarea class="form-control" name="<?= $vPageTitleP; ?>"  id="<?= $vPageTitleP; ?>" placeholder="<?= $vPageTitle; ?> Value" <?= $required; ?>><?= $titleval; ?></textarea>
                                                            <!--<input type="text" class="form-control" name="<?= $vPageTitleP; ?>"  id="<?= $vPageTitleP; ?>" value="<?= $titleval; ?>" placeholder="<?= $vPageTitle; ?> Value" <?= $required; ?>>-->
                                                        </div>
                                                    </div>

                                                    <!--- Editor -->
                                                    <div class="row" <?= $style_v ?>>
                                                        <div class="col-lg-12">
                                                            <label> Page Description (<?= $vLTitle; ?>) <?= $required_msg; ?></label>
                                                        </div>
                                                        <div class="col-lg-12">
                                                            <textarea class="form-control ckeditor" rows="10" name="<?= $tPageDescP; ?>"  id="<?= $tPageDescP; ?>"  placeholder="<?= $tPageDesc; ?> Value" <?= $required; ?>> <?= $descval; ?></textarea>
                                                        </div>
                                                    </div>

                                                    <!--- Editor -->
                                                    <?
                                                }
                                            } ?>
                                        </div>
                                        <div id="companytab" class="tab-pane <?php if($activetab=='companytab') { ?> active <?php }  ?>">
                                            <?php $style_v = "";
                                            if (in_array($iPageId, array('29', '30'))) {
                                                $style_v = "style = 'display:none;'";
                                            }
                                            if ($count_all > 0) {
                                                for ($i = 0; $i < $count_all; $i++) {
                                                    $vCode = $db_master[$i]['vCode'];
                                                    $vLTitle = $db_master[$i]['vTitle'];
                                                    $eDefault = $db_master[$i]['eDefault'];

                                                    $vPageTitleC = 'vCompanyPageTitle_' . $vCode;
                                                    $tPageDescC = 'tCompanyPageDesc_' . $vCode;

                                                    $vPageTitle = 'vPageTitle_' . $vCode;
                                                    $tPageDesc = 'tPageDesc_' . $vCode;

                                                    if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
                                                        $pagetitlearr = json_decode($$vPageTitle,true);
                                                        $pagedescarr = json_decode($$tPageDesc,true);
                                                        $titleval = $pagetitlearr['company'];
                                                        $descval = $pagedescarr['company'];
                                                    } else {
                                                        $titleval = $$vPageTitle;
                                                        $descval = $$tPageDesc;
                                                    }

                                                    //$required = ($eDefault == 'Yes') ? 'required' : '';
                                                    //$required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                    ?>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <label>Page Sub Description (<?= $vLTitle; ?>) <?= $required_msg; ?></label>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <textarea class="form-control" name="<?= $vPageTitleC; ?>"  id="<?= $vPageTitleC; ?>" placeholder="<?= $vPageTitle; ?> Value" <?= $required; ?>><?= $titleval; ?></textarea>
                                                            <!--<input type="text" class="form-control" name="<?= $vPageTitleC; ?>"  id="<?= $vPageTitleC; ?>" value="<?= $titleval; ?>" placeholder="<?= $vPageTitle; ?> Value" <?= $required; ?>>-->
                                                        </div>
                                                    </div>

                                                    <!--- Editor -->
                                                    <div class="row" <?= $style_v ?>>
                                                        <div class="col-lg-12">
                                                            <label> Page Description (<?= $vLTitle; ?>) <?= $required_msg; ?></label>
                                                        </div>
                                                        <div class="col-lg-12">
                                                            <textarea class="form-control ckeditor" rows="10" name="<?= $tPageDescC; ?>"  id="<?= $tPageDescC; ?>"  placeholder="<?= $tPageDesc; ?> Value" <?= $required; ?>> <?= $descval; ?></textarea>
                                                        </div>
                                                    </div>

                                                    <!--- Editor -->
                                                    <?
                                                }
                                            } ?>
                                        </div>
                                        <div id="restauranttab" class="tab-pane <?php if($activetab=='restauranttab') { ?> active <?php }  ?>">
                                            <?php $style_v = "";
                                            if (in_array($iPageId, array('29', '30'))) {
                                                $style_v = "style = 'display:none;'";
                                            }
                                            if ($count_all > 0) {
                                                for ($i = 0; $i < $count_all; $i++) {
                                                    $vCode = $db_master[$i]['vCode'];
                                                    $vLTitle = $db_master[$i]['vTitle'];
                                                    $eDefault = $db_master[$i]['eDefault'];

                                                    $vPageTitleC = 'vRestaurantPageTitle_' . $vCode;
                                                    $tPageDescC = 'tRestaurantPageDesc_' . $vCode;

                                                    $vPageTitle = 'vPageTitle_' . $vCode;
                                                    $tPageDesc = 'tPageDesc_' . $vCode;

                                                    if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
                                                        $pagetitlearr = json_decode($$vPageTitle,true);
                                                        $pagedescarr = json_decode($$tPageDesc,true);
                                                        $titleval = $pagetitlearr['restaurant'];
                                                        $descval = $pagedescarr['restaurant'];
                                                    } else {
                                                        $titleval = $$vPageTitle;
                                                        $descval = $$tPageDesc;
                                                    }

                                                    //$required = ($eDefault == 'Yes') ? 'required' : '';
                                                    //$required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                    ?>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <label>Page Sub Description (<?= $vLTitle; ?>) <?= $required_msg; ?></label>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <textarea class="form-control" name="<?= $vPageTitleC; ?>"  id="<?= $vPageTitleC; ?>" placeholder="<?= $vPageTitle; ?> Value" <?= $required; ?>><?= $titleval; ?></textarea>
                                                            <!--<input type="text" class="form-control" name="<?= $vPageTitleC; ?>"  id="<?= $vPageTitleC; ?>" value="<?= $titleval; ?>" placeholder="<?= $vPageTitle; ?> Value" <?= $required; ?>>-->
                                                        </div>
                                                    </div>

                                                    <!--- Editor -->
                                                    <div class="row" <?= $style_v ?>>
                                                        <div class="col-lg-12">
                                                            <label> Page Description (<?= $vLTitle; ?>) <?= $required_msg; ?></label>
                                                        </div>
                                                        <div class="col-lg-12">
                                                            <textarea class="form-control ckeditor" rows="10" name="<?= $tPageDescC; ?>"  id="<?= $tPageDescC; ?>"  placeholder="<?= $tPageDesc; ?> Value" <?= $required; ?>> <?= $descval; ?></textarea>
                                                        </div>
                                                    </div>

                                                    <!--- Editor --> 
                                                    <?
                                                }
                                            } ?>
                                        </div>
                                        <div id="organizationtab" class="tab-pane <?php if($activetab=='organizationtab') { ?> active <?php }  ?>">
                                            <?php $style_v = "";
                                            if (in_array($iPageId, array('29', '30'))) {
                                                $style_v = "style = 'display:none;'";
                                            }
                                            if ($count_all > 0) {
                                                for ($i = 0; $i < $count_all; $i++) {
                                                    $vCode = $db_master[$i]['vCode'];
                                                    $vLTitle = $db_master[$i]['vTitle'];
                                                    $eDefault = $db_master[$i]['eDefault'];

                                                    $vPageTitleO = 'vOrgPageTitle_' . $vCode;
                                                    $tPageDescO = 'tOrgPageDesc_' . $vCode;

                                                    $vPageTitle = 'vPageTitle_' . $vCode;
                                                    $tPageDesc = 'tPageDesc_' . $vCode;

                                                    if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
                                                        $pagetitlearr = json_decode($$vPageTitle,true);
                                                        $pagedescarr = json_decode($$tPageDesc,true);
                                                        $titleval = $pagetitlearr['org'];
                                                        $descval = $pagedescarr['org'];
                                                    } else {
                                                        $titleval = $$vPageTitle;
                                                        $descval = $$tPageDesc;
                                                    }

                                                    //$required = ($eDefault == 'Yes') ? 'required' : '';
                                                    //$required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                    ?>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <label>Page Sub Description (<?= $vLTitle; ?>) <?= $required_msg; ?></label>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <textarea class="form-control" name="<?= $vPageTitleO; ?>"  id="<?= $vPageTitleO; ?>" placeholder="<?= $vPageTitle; ?> Value" <?= $required; ?>><?= $titleval; ?></textarea>
                                                            <!--<input type="text" class="form-control" name="<?= $vPageTitleO; ?>"  id="<?= $vPageTitleO; ?>" value="<?= $titleval; ?>" placeholder="<?= $vPageTitle; ?> Value" <?= $required; ?>>-->
                                                        </div>
                                                    </div>

                                                    <!--- Editor -->
                                                    <div class="row" <?= $style_v ?>>
                                                        <div class="col-lg-12">
                                                            <label> Page Description (<?= $vLTitle; ?>) <?= $required_msg; ?></label>
                                                        </div>
                                                        <div class="col-lg-12">
                                                            <textarea class="form-control ckeditor" rows="10" name="<?= $tPageDescO; ?>"  id="<?= $tPageDescO; ?>"  placeholder="<?= $tPageDesc; ?> Value" <?= $required; ?>> <?= $descval; ?></textarea>
                                                        </div>
                                                    </div>

                                                    <!--- Editor -->
                                                    <?
                                                }
                                            } ?>
                                        </div>
                                        <div id="hoteltab" class="tab-pane <?php if($activetab=='hoteltab') { ?> active <?php }  ?>">

                                            <?php $style_v = "";
                                            if (in_array($iPageId, array('29', '30'))) {
                                                $style_v = "style = 'display:none;'";
                                            }
                                            if ($count_all > 0) {
                                                for ($i = 0; $i < $count_all; $i++) { 
                                                    $vCode = $db_master[$i]['vCode'];
                                                    $vLTitle = $db_master[$i]['vTitle'];
                                                    $eDefault = $db_master[$i]['eDefault'];

                                                    $vPageTitleU = 'vHotelPageTitle_' . $vCode;
                                                    $tPageDescU = 'tHotelPageDesc_' . $vCode;

                                                    $vPageTitle = 'vPageTitle_' . $vCode;
                                                    $tPageDesc = 'tPageDesc_' . $vCode;

                                                    if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) { 
                                                        $pagetitlearr = json_decode($$vPageTitle,true);
                                                        $pagedescarr = json_decode($$tPageDesc,true);
                                                        $titleval = $pagetitlearr['hotel'];
                                                        $descval = $pagedescarr['hotel'];
                                                    } else {
                                                        $titleval = $$vPageTitle;
                                                        $descval = $$tPageDesc;
                                                    }

                                                    //$required = ($eDefault == 'Yes') ? 'required' : '';
                                                   // $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                    ?>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <label>Page Sub Description (<?= $vLTitle; ?>) <?= $required_msg; ?></label>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <textarea class="form-control" name="<?= $vPageTitleU; ?>"  id="<?= $vPageTitleU; ?>" placeholder="<?= $vPageTitle; ?> Value" <?= $required; ?>><?= $titleval; ?></textarea>
                                                            <!--<input type="text" class="form-control" name="<?= $vPageTitleU; ?>"  id="<?= $vPageTitleU; ?>" value="<?= $titleval; ?>" placeholder="<?= $vPageTitle; ?> Value" <?= $required; ?>>-->
                                                        </div>
                                                    </div>

                                                    <!--- Editor -->
                                                    <div class="row" <?= $style_v ?>>
                                                        <div class="col-lg-12">
                                                            <label> Page Description (<?= $vLTitle; ?>) <?= $required_msg; ?></label>
                                                        </div>
                                                        <div class="col-lg-12">
                                                            <textarea class="form-control ckeditor" rows="10" name="<?= $tPageDescU; ?>"  id="<?= $tPageDescU; ?>"  placeholder="<?= $tPageDesc; ?> Value" <?= $required; ?>> <?= $descval; ?></textarea>
                                                        </div>
                                                    </div>

                                                    <!--- Editor -->
                                                    <?
                                                }
                                            } ?>
                                        </div>
                                    </div>
                                    <?php } else { ?>
                                    
                                    <!--<textarea class="form-control ckeditor" rows="10" name="aaa"  id="editortest"  placeholder="aa Value"></textarea>-->
                                    <?
                                    $style_v = "";
                                    if (in_array($iPageId, array('29', '30','53'))) {
                                        $style_v = "style = 'display:none;'";
                                    }
                                    if ($count_all > 0) {
                                        for ($i = 0; $i < $count_all; $i++) {
                                            $vCode = $db_master[$i]['vCode'];
                                            $vLTitle = $db_master[$i]['vTitle'];
                                            $eDefault = $db_master[$i]['eDefault'];

                                            $vPageTitle = 'vPageTitle_' . $vCode;
                                            $tPageDesc = 'tPageDesc_' . $vCode;

                                            if($style_v=='') {
                                                $required = ($eDefault == 'Yes') ? 'required' : '';
                                                $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                            }
                                            ?>
                                            <div class="row" <?= $style_v ?>>
                                                <div class="col-lg-12">
                                                    <label>Page Title (<?= $vLTitle; ?>) <?= $required_msg; ?></label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <input type="text" class="form-control" name="<?= $vPageTitle; ?>"  id="<?= $vPageTitle; ?>" value="<?= htmlspecialchars($$vPageTitle); ?>" placeholder="<?= $vPageTitle; ?> Value" <?= $required; ?>>
                                                </div>
                                            </div>
                                            
                                            <!--- Editor -->
                                            <?php if($cubexthemeon == 'Yes' && $iPageId==52) {
                                                
                                                $vPageSubTitleS = "vPageSubTitle_$vCode";

                                                $vPageSubTitle = "vPageSubTitle[$vCode]";
                                            
                                                $pagedescarr = json_decode($$tPageDesc,true);
                                                $Firstdescval = $pagedescarr['FirstDesc'];
                                                $Secdescval = $pagedescarr['SecDesc'];
                                                $Thirddescval = $pagedescarr['ThirdDesc'];
                                                        
                                                $tPageSecDesc = 'tPageSecDesc_' . $vCode;    
                                                $tPageThirdDesc = 'tPageThirdDesc_' . $vCode;    
                                            ?>
                                            <div class="row">
                                                        <div class="col-lg-12">
                                                            <label>Page Sub Description (<?= $vLTitle; ?>) <?= $required_msg; ?></label>
                                                        </div>
                                                        <div class="col-lg-12">
                                                            <textarea class="form-control ckeditor" rows="10" name="<?= $vPageSubTitle; ?>"  id="<?= $vPageSubTitleS; ?>" placeholder="<?= $vPageSubTitleS; ?> Value" <?= $required; ?>><?= $pageSubtitleArr["pageSubtitle_".$vCode]; ?></textarea>
                                                        </div>
                                            </div>
                                            
                                            <div class="row" <?= $style_v ?>>
                                                <div class="col-lg-12">
                                                    <label> Page First Description (<?= $vLTitle; ?>) <?= $required_msg; ?></label>
                                                </div>
                                                <div class="col-lg-12">
                                                    <textarea class="form-control ckeditor" rows="10" name="<?= $tPageDesc; ?>"  id="<?= $tPageDesc; ?>"  placeholder="<?= $tPageDesc; ?> Value" <?= $required; ?>> <?= $Firstdescval; ?></textarea>
                                                </div>
                                            </div>
                                            
                                            <div class="row" <?= $style_v ?>>
                                                <div class="col-lg-12">
                                                    <label> Page Second Description (<?= $vLTitle; ?>) <?= $required_msg; ?></label>
                                                </div>
                                                <div class="col-lg-12">
                                                    <textarea class="form-control ckeditor" rows="10" name="<?= $tPageSecDesc; ?>"  id="<?= $tPageSecDesc; ?>"  placeholder="<?= $tPageSecDesc; ?> Value" <?= $required; ?>> <?= $Secdescval; ?></textarea>
                                                </div>
                                            </div>
                                            <div class="row" <?= $style_v ?>>
                                                <div class="col-lg-12">
                                                    <label> Page Third Description (<?= $vLTitle; ?>) <?= $required_msg; ?></label>
                                                </div>
                                                <div class="col-lg-12">
                                                    <textarea class="form-control ckeditor" rows="10" name="<?= $tPageThirdDesc; ?>"  id="<?= $tPageThirdDesc; ?>"  placeholder="<?= $tPageThirdDesc; ?> Value" <?= $required; ?>> <?= $Thirddescval; ?></textarea>
                                                </div>
                                            </div>
                                            <?php } else { ?>
                                            <div class="row" <?= $style_v ?>>
                                                <div class="col-lg-12">
                                                    <label> Page Description (<?= $vLTitle; ?>) <?= $required_msg; ?></label>
                                                </div>
                                                <div class="col-lg-12">
                                                    <textarea class="form-control ckeditor" rows="10" name="<?= $tPageDesc; ?>"  id="<?= $tPageDesc; ?>"  placeholder="<?= $tPageDesc; ?> Value" <?= $required; ?>> <?= $$tPageDesc; ?></textarea>
                                                </div>
                                            </div>

                                            <!--- Editor -->
                                            <? }
                                        }
                                    }
                                    }
                                    if (!in_array($iPageId, array('23', '24', '25', '26', '27','48','49','50'))) {
                                        ?>
                                        <div class="row" <?= $style_v ?>>
                                            <div class="col-lg-12">
                                                <label>Meta Title</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="vTitle"  id="vTitle" value="<?= htmlspecialchars($vTitle); ?>" placeholder="Meta Title">
                                            </div>
                                        </div>
                                        <div class="row" <?= $style_v ?>>
                                            <div class="col-lg-12">
                                                <label>Meta Keyword</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="tMetaKeyword"  id="tMetaKeyword" value="<?= htmlspecialchars($tMetaKeyword); ?>" placeholder="Meta Keyword">
                                            </div>
                                        </div>

                                        <div class="row" <?= $style_v ?>>
                                            <div class="col-lg-12">
                                                <label>Meta Description</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <textarea class="form-control" rows="10" name="tMetaDescription"  id="<?= $tMetaDescription; ?>"  placeholder="<?= $tMetaDescription; ?> Value" <?= $required; ?>> <?= $tMetaDescription; ?></textarea>
                                            </div>
                                        </div>

                                        <?php
                                    } if (!in_array($iPageId, array('1', '2', '7', '4', '3', '6', '23', '27', '33','44'))) { 
                                        ?>
                                        <?php
                                        if ($cubexthemeon == 'Yes' && in_array($iPageId, $pageidCubexImage)) { 
                                            ?>
                                            <br><br>
                                            <?php if($iPageId!=50) { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Image (Left side shown)</label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <? if ($vImage != '') { ?>
                                                        <a target="_blank" href="<?= $images . $vImage; ?>"><img src="<?= $images . $vImage; ?>" style="width:200px;height:100px;"></a>
                                                    <? } ?>
                                                    <input type="file" name="vImage" id="vImage" />
                                                </div>
                                            </div>
                                            <?php } ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Background Image</label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <? if ($vImage1 != '') { ?>
                                                        <a target="_blank" href="<?= $images . $vImage1; ?>"><img src="<?= $images . $vImage1; ?>" style="width:200px;height:100px;"></a>
                                                    <? } ?>
                                                    <input type="file" name="vImage1" id="vImage1" />
                                                </div>
                                            </div>
                                        <?php } else if($cubexthemeon == 'Yes' && $iPageId==52) { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>First Image (Left side shown)</label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <? if ($vImage != '') { ?>
                                                        <a target="_blank" href="<?= $images . $vImage; ?>"><img src="<?= $images . $vImage; ?>" style="width:200px;height:100px;"></a>
                                                    <? } ?>
                                                    <input type="file" name="vImage" id="vImageaaa2" />
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Second Image (Right side shown)</label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <? if ($vImage1 != '') { ?>
                                                        <a target="_blank" href="<?= $images . $vImage1; ?>"><img src="<?= $images . $vImage1; ?>" style="width:200px;height:100px;"></a>
                                                    <? } ?>
                                                    <input type="file" name="vImage1" id="vImagea1" />
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Third Image (Left side shown)</label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <? if ($vImage2 != '') { ?>
                                                        <a target="_blank" href="<?= $images . $vImage2; ?>"><img src="<?= $images . $vImage2; ?>" style="width:200px;height:100px;"></a>
                                                    <? } ?>
                                                    <input type="file" name="vImage2" id="vImagea2" />
                                                </div>
                                            </div>
                                        <?php } else if($cubexthemeon == 'Yes' && $iPageId==22) { ?>
                                        <div class="row" style="<?= $style_vimage ?>">
                                                <div class="col-lg-12">
                                                    <label>Image</label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <? if ($vImage1 != '') { ?>
                                                        <a target="_blank" href="<?= $images . $vImage1; ?>"><img src="<?= $images . $vImage1; ?>" style="width:200px;height:100px;"></a>
                                                    <? } ?>
                                                    <input type="file" name="vImage1" id="vImagen1" />
                                                </div>
                                            </div>
                                        <?php } else {
                                            $style_vimage = "";
                                            if (!in_array($iPageId, array('53'))) {
                                                $style_vimage = "style = 'display:none;'";
                                            }
                                            ?>
                                            <div class="row" style="<?= $style_vimage ?>">
                                                <div class="col-lg-12">
                                                    <label>Image</label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <? if ($vImage != '') { ?>
                                                        <a target="_blank" href="<?= $images . $vImage; ?>"><img src="<?= $images . $vImage; ?>" style="width:200px;height:100px;"></a>
                                                    <? } ?>
                                                    <input type="file" name="vImage" id="vImage" />
                                                </div>
                                            </div>
                                        <?php } ?>
                                        
                                        
                                        
                                    <?php } if($iPageId!='48' && $iPageId != '49' && $iPageId != '50') { ?>
                                    <!--                                added by SP for pages orderby,active/inactive functionality  -->
                                    <div class="row" <?= $style_v ?>>
                                        <div class="col-lg-12">
                                            <label>Display Order</label>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="number" class="form-control" name="iOrderBy" id="iOrderBy" value="<?= $iOrderBy; ?>" placeholder="Page displayed according to this number" min="0">
                                        </div>
                                    </div>  
                                    <?php } ?>        
                                            
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <?php if (($action == 'Edit' && $userObj->hasPermission('edit-pages')) || ($action == 'Add' && $userObj->hasPermission('create-pages'))) { ?>
                                                <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?= $action; ?> Static Page">
                                                <input type="reset" value="Reset" class="btn btn-default">
                                            <?php } ?>
                                            <!-- <a href="javascript:void(0);" onclick="reset_form('_page_form');" class="btn btn-default">Reset</a> -->
                                            <a href="page.php" class="btn btn-default back_link">Cancel</a>
                                        </div>
                                    </div>
                                </form>
                            <? } ?>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->


        <? include_once('footer.php'); ?>

        <!-- PAGE LEVEL SCRIPTS -->
        <script src="../assets/plugins/ckeditor/ckeditor.js"></script>
        <script src="../assets/plugins/ckeditor/config.js"></script>
        
        <script>
            /* CKEDITOR.replace( 'ckeditor',{
             allowedContent : {
             i:{
             classes:'fa*'
             },
             span: true
             }
             } ); */
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
                    referrer = "page.php";
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
