<?php
include_once('../common.php');
define("SERVICE_CATEGORIES", "service_categories");
define("CUISINE", "cuisine");
define("COMPANY", "company");
define("COMPANY_CUISINE", "company_cuisine");
define("FOOD_MENU", "food_menu");
define("MENU_ITEMS", "menu_items");
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
//ini_set("display_errors", 1);
//error_reporting(E_ALL);
$printHirarchyHtml = 0; // 1- Print,0-Not Print
$cusineUnknownFields = array("SI", "TA", "VI", "TL", "KM", "BN");
$foodMenuNewLang = array("SI", "TA", "VI", "TL", "KM", "BN");
$menuItemNewLang = array("SI", "TA", "VI", "TL", "KM", "BN");
$itemPriceArr = array("199.00", "249.00");
if (isset($_POST['submit'])) {
    //echo "<pre>";
    //print_r($_POST);die;
    $sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
	$sql = "SELECT * FROM `language_master` where vCode='EN' ORDER BY `iDispOrder`";
    $db_master = $obj->MySQLSelect($sql);
    $count_all = count($db_master);
    $catNameArr = $cuisineNameArr = $foodMenuArr = $foodMenuDescArr = $menuItemsArr = $menuItemDescArr = array();
    if ($count_all > 0) {
        for ($i = 0; $i < $count_all; $i++) {
            $vValue = 'vServiceName_' . $db_master[$i]['vCode'];
            $cusineValue = 'cuisineName_' . $db_master[$i]['vCode'];
            $foodValue = 'vMenu_' . $db_master[$i]['vCode'];
            $foodDesc = 'vMenuDesc_' . $db_master[$i]['vCode'];
            $itemValue = 'vItemType_' . $db_master[$i]['vCode'];
            $itemDesc = 'vItemDesc_' . $db_master[$i]['vCode'];
            $vTitle_store[] = $vValue;
            if (!in_array($db_master[$i]['vCode'], $cusineUnknownFields)) {
                $cuisineNameArr[] = $cusineValue;
            }
            if (!in_array($db_master[$i]['vCode'], $foodMenuNewLang)) {
                $foodMenuArr[] = $foodValue;
                $foodMenuDescArr[] = $foodDesc;
            }
            if (!in_array($db_master[$i]['vCode'], $menuItemNewLang)) {
                $menuItemsArr[] = $itemValue;
                $menuItemDescArr[] = $itemDesc;
            }
        }
    }
    $insertStoreRecord = $insertCategoryRecord = $insertItemRecord = 2;
    $vRestuarantLocation = $vCaddress = "Prahlad Nagar, Ahmedabad, Gujarat, India";
    $vRestuarantLocationLat = "23.0120338";
    $vRestuarantLocationLong = "72.510754";
    $serviceCategory = $hirarchyHtml = "";
    $vZip = 380015;
    $vCountry = "IN";
    $vCurrencyCompany = "INR";
    $vPhone = "1234567890";
    $vLang = "EN";
    $vFromMonFriTimeSlot1 = "00:05:00";
    $vToMonFriTimeSlot1 = "23:05:00";
    $vFromSatSunTimeSlot1 = "12:06:00";
    $vToSatSunTimeSlot1 = "23:55:00";
    $iMaxItemQty = 15;
    $fPrepareTime = 30;
    $fOfferAppyType = "None";
    $eFoodType = "";
    $eAvailable = "Yes";
    $eSystem = "DeliverAll";
    $eBestSeller = $eRecommended = "No";
    $q = "INSERT INTO ";
    if (isset($_POST['servicecat'])) {
        $serviceCategory = trim($_POST['servicecat']);
        if ($serviceCategory != "") {
            $catNames = $cuisines = "";
            $hirarchyHtml .= "<b>Entered Service Category Name : " . $serviceCategory . "</b><br>";
            for ($i = 0; $i < count($vTitle_store); $i++) {
                if (isset($vTitle_store[$i])) {
                    $catNames .= "`" . $vTitle_store[$i] . "`='" . $serviceCategory . "',";
                }
                if (isset($cuisineNameArr[$i])) {
                    $cuisines .= "`" . $cuisineNameArr[$i] . "`='" . $serviceCategory . "',";
                }
            }
            $cat_sql = $q . " `" . SERVICE_CATEGORIES . "` SET $catNames `vService`='".$serviceCategory."',`vImage` = 'dummy_store_banner.jpg',`eStatus`= 'Active'";
            $obj->sql_query($cat_sql); // Remove Comment - 1
            $cat_id = $obj->GetInsertId(); // Remove Comment -2
            $sql = "SELECT count(iFaqcategoryId) as total FROM `banners` ORDER BY `iDisplayOrder`";
            $db_banner = $obj->MySQLSelect($sql);
            $banner_total = $db_banner[0]['total'];
            $banner_sql = $q . " `banners` SET `iServiceId`= $cat_id,`eStatus`= 'Active',`vTitle` = 'banner',`vImage` = 'dummy_store_banner.jpg' ,`vCode` = 'EN',iUniqueId=$banner_total";
            $obj->sql_query($banner_sql); // Remove Comment - 1
            //$cat_id = 10; //Add Comment - 1
            if ($cat_id > 0) {
                $hirarchyHtml .= "  =>Entered Service Category Inserted Successfully : iServiceId(" . $cat_id . ")</b><br>";
                $wherecatId = "`iServiceId`='" . $cat_id . "'";
                $update_cat = "UPDATE " . SERVICE_CATEGORIES . " SET iDisplayOrder='" . $cat_id . "' WHERE $wherecatId";
                $obj->sql_query($update_cat);
                $cuisine_sql = $q . " `" . CUISINE . "` SET $cuisines  `cuisineName`='" . $serviceCategory . "',`iServiceId` = '" . $cat_id . "',`eStatus`= 'Active'";
                $obj->sql_query($cuisine_sql); // Remove Comment - 3
                $cuisine_id = $obj->GetInsertId(); // Remove Comment -4
                //$cuisine_id = 32; //Add Comment - 1
                $hirarchyHtml .= "  =>Cuisine Inserted Successfully : cuisineId(" . $cuisine_id . ")</b><br>";
                $vPassword = $generalobj->encrypt_bycrypt("123456");
                if ($cuisine_id > 0) {
                    for ($c = 0; $c < $insertStoreRecord; $c++) {
                        $cCount = $c + 1;
                        $vCompany = $serviceCategory . " Store " . $cCount;
                        $vContactName = "Store User " . $cCount;
                        $vEmail = strtolower(str_replace(" ", "", $vCompany . "@gmail.com"));
                        $num0 = (rand(10, 100));
                        $num1 = date("Ymd");
                        $vPhone = $num0 . $num1;

                        $sample_image_name = 'dummy_store.png';
                        $sample_vImage_name1 = str_replace(" ", "_", trim($sample_image_name));
                        $smaple_img_arr = explode(".", $sample_vImage_name1);
                        $sample_image_extension_fileextension = strtolower($smaple_img_arr[count($smaple_img_arr) - 1]);
                        $time_val = time();
                        $sample_filename_rand = mt_rand(11111, 99999);

                        $sample_image_filename = $time_val . "_" . $sample_filename_rand . "." . $sample_image_extension_fileextension;

                        $company_sql = $q . " `" . COMPANY . "` SET `iServiceId`='" . $cat_id . "',`vCompany`='" . $vCompany . "',`vEmail` = '" . $vEmail . "',`vPassword`='" . $vPassword . "',`eStatus`= 'Active',`vRestuarantLocation`='" . $vRestuarantLocation . "',`vRestuarantLocationLat`='" . $vRestuarantLocationLat . "',`vRestuarantLocationLong`='" . $vRestuarantLocationLong . "',`vCaddress`='" . $vCaddress . "',`vZip`='" . $vZip . "',`vCountry`='" . $vCountry . "',`vCurrencyCompany`='" . $vCurrencyCompany . "',`vContactName`='" . $vContactName . "',`vCode`='91',`vPhone`='" . $vPhone . "',`vLang`='" . $vLang . "',`vFromMonFriTimeSlot1`='" . $vFromMonFriTimeSlot1 . "',`vToMonFriTimeSlot1`='" . $vToMonFriTimeSlot1 . "',`vFromSatSunTimeSlot1`='" . $vFromSatSunTimeSlot1 . "',`vToSatSunTimeSlot1`='" . $vToSatSunTimeSlot1 . "',`iMaxItemQty`='" . $iMaxItemQty . "',`fPrepareTime`='" . $fPrepareTime . "',`fOfferAppyType`='" . $fOfferAppyType . "',`eSystem`='" . $eSystem . "',`vImage`='".$sample_image_filename."'";
                        $obj->sql_query($company_sql); // Remove Comment - 5
                        $store_id = $obj->GetInsertId(); // Remove Comment -6
                        
                        $img_path = $tconfig["tsite_upload_images_compnay_path"];
                        $menu_items_img_path = $tconfig["tsite_upload_images_menu_item_path"];
                        $sample_store_image_path = $tconfig["tsite_sample_images_store_path"];

                        $Photo_Gallery_folder = $img_path . '/' . $store_id . '/';
                        if (!is_dir($Photo_Gallery_folder)) {
                            mkdir($Photo_Gallery_folder, 0777);
                        }

                        copy($sample_store_image_path.'dummy_store.png', $Photo_Gallery_folder.$sample_image_filename);
                        copy($sample_store_image_path.'1_dummy_store.png', $Photo_Gallery_folder."1_".$sample_image_filename); 
                        copy($sample_store_image_path.'2_dummy_store.png', $Photo_Gallery_folder."2_".$sample_image_filename); 
                        copy($sample_store_image_path.'3_dummy_store.png', $Photo_Gallery_folder."3_".$sample_image_filename);  
                        
                        copy($sample_store_image_path.'1_dymmy_store_item.png', $menu_items_img_path."/1_".$sample_image_filename);
                        copy($sample_store_image_path.'2_dymmy_store_item.png', $menu_items_img_path."/2_".$sample_image_filename);
                        copy($sample_store_image_path.'3_dymmy_store_item.png', $menu_items_img_path."/3_".$sample_image_filename);
                        copy($sample_store_image_path.'dymmy_store_item.png', $menu_items_img_path."/".$sample_image_filename);  




                        // rmdir('images/old-folder');
                        //$store_id = 55; //Add Comment - 1
                        $hirarchyHtml .= "      =>" . $vCompany . " : Store Created Successfully : iCompanyId(" . $store_id . ")</b><br>";
                        if ($store_id > 0) {
                            $company_cuisine_sql = $q . " `" . COMPANY_CUISINE . "` SET `iCompanyId`='" . $store_id . "',`cuisineId`='" . $cuisine_id . "'";
                            $obj->sql_query($company_cuisine_sql); // Remove Comment - 7
                            $company_cuisineId = $obj->GetInsertId(); // Remove Comment -8
                            //$company_cuisineId = 2401; //Add Comment - 1
                            $hirarchyHtml .= "      =>Company Cuisine Created Successfully : ccId(" . $company_cuisineId . ")</b><br>";
                            for ($f = 0; $f < $insertCategoryRecord; $f++) {
                                $fCount = $f + 1;
                                $foodNames = $foodDesc = $engFoodName = "";
                                for ($fn = 0; $fn < count($foodMenuArr); $fn++) {
                                    if (isset($foodMenuArr[$fn])) {
                                        if ($engFoodName == "") {
                                            $engFoodName = $serviceCategory . " Category $fCount";
                                        }
                                        $foodNames .= "`" . $foodMenuArr[$fn] . "`='" . $serviceCategory . " Category $fCount',";
                                    }
                                    if (isset($foodMenuDescArr[$fn])) {
                                        $foodDesc .= "`" . $foodMenuDescArr[$fn] . "`='" . $serviceCategory . " Category $fCount',";
                                    }
                                }
                                $food_sql = $q . " `" . FOOD_MENU . "` SET $foodNames $foodDesc  `iCompanyId`='" . $store_id . "',`eStatus`= 'Active',`iDisplayOrder`='" . $fCount . "'";
                                $obj->sql_query($food_sql); // Remove Comment - 9
                                $food_id = $obj->GetInsertId(); // Remove Comment -10
                                //$food_id = 196; //Add Comment - 1
                                $hirarchyHtml .= "          =>" . $engFoodName . " : Food Menu Inserted Successfully : iFoodMenuId(" . $food_id . ")</b><br>";
                                if ($food_id > 0) {
                                    $whereFoodId = "`iFoodMenuId`='" . $food_id . "'";
                                    //$update_food = "UPDATE " . FOOD_MENU . " SET iDisplayOrder='" . $food_id . "' WHERE $whereFoodId";
                                    //$obj->sql_query($update_food);
                                    for ($m = 0; $m < $insertItemRecord; $m++) {
                                        $menuItems = $menuItemDesc = $engItemName = "";
                                        $mCount = $m + 1;
                                        for ($fi = 0; $fi < count($menuItemsArr); $fi++) {
                                            if (isset($menuItemsArr[$fi])) {
                                                if ($engItemName == "") {
                                                    $engItemName = $serviceCategory . " $mCount";
                                                }
                                                $menuItems .= "`" . $menuItemsArr[$fi] . "`='" . $serviceCategory . " $mCount',";
                                            }
                                            if (isset($menuItemDescArr[$fi])) {
                                                $menuItemDesc .= "`" . $menuItemDescArr[$fi] . "`='" . $serviceCategory . " $mCount',";
                                            }
                                        }
                                        $fPrice = "222.00";
                                        $vImage = $sample_image_filename;
                                        if (isset($itemPriceArr[$m]) && $itemPriceArr[$m] > 0) {
                                            $fPrice = $itemPriceArr[$m];
                                        }
                                        $menu_items = $q . " `" . MENU_ITEMS . "` SET $menuItems $menuItemDesc  `iFoodMenuId`='" . $food_id . "',`fPrice`='" . $fPrice . "',`eFoodType`='" . $eFoodType . "',`eStatus`= 'Active',`eAvailable`= '" . $eAvailable . "',`eBestSeller`= '" . $eBestSeller . "',`eRecommended`= '" . $eRecommended . "',`vImage`= '" . $vImage . "',`iDisplayOrder`= '" . $mCount . "'";
                                        $obj->sql_query($menu_items); // Remove Comment - 11
                                        $item_id = $obj->GetInsertId(); // Remove Comment -12
                                        //$item_id = 74; //Add Comment - 1
                                        $hirarchyHtml .= "              =>" . $engItemName . " : Menu Item Inserted Successfully : iMenuItemId(" . $item_id . ")</b><br>";
                                        if ($item_id > 0) {
                                            $whereItemId = "`iMenuItemId`='" . $item_id . "'";
                                            //$update_item = "UPDATE " . MENU_ITEMS . " SET iDisplayOrder='" . $item_id . "' WHERE $whereItemId";
                                            //$obj->sql_query($update_item);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = 'Auto Store created successfuly.';
        if ($printHirarchyHtml == 1) {
            echo $hirarchyHtml . "<br><br><br>";
            echo "Auto Store created successfuly.";
            die;
        } else {
            header("Location:auto_store_create_script.php");
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
        <title>Admin | Auto Create Store </title>
        <meta content="width = device-width, initial-scale = 1.0" name="viewport" />
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
                            <h2>Create Store</h2>
                        </div>
                    </div>
                    <hr />
                    <div class="body-div">
                        <div class="form-group">
                            <? if (isset($success) && $success == 1) { ?>
                                <div class="alert alert-success alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">�</button>
                                    <?php
                                    if ($ksuccess == "1") {
                                        echo $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
                                    } else {
                                        echo $langage_lbl_admin['LBL_Record_Updated_successfully'];
                                    }
                                    ?>

                                </div><br/>
<? } ?>
                            <div class="col-lg-12">
                                <form name="_admin_form" id="_admin_form" method="post" action="" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Service Category<span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="text" class="form-control" name="servicecat"  id="servicecat" placeholder="Service Category">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <input type="submit" class="btn btn-default" name="submit" id="submit" value="Create" >
                                            <input type="reset" value="Reset" class="btn btn-default">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
        <?
        include_once('footer.php');
        ?>
        <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
    </body>
    <!-- END BODY-->
</html>