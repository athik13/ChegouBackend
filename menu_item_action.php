<?php
include_once('common.php');
//added by SP for cubex changes on 07-11-2019
if ($generalobj->checkXThemOn() == 'Yes') {
    include_once("cx-menu_item_action.php");
    exit;
}

require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();
$generalobj->check_member_login();
$abc = 'company';
$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$generalobj->setRole($abc,$url);
if($_SESSION["sess_eSystem"] != "DeliverAll")
{
    header('Location:profile.php');
}
$script = 'MenuItems';
$tbl_name = 'menu_items';
$tbl_name1 = 'menuitem_options';
$sql = "select vName,vSymbol from currency where eDefault = 'Yes'";
$db_currency = $obj->MySQLSelect($sql);
$iCompanyId = $_SESSION['sess_iUserId'];

function check_diff($arr1, $arr2) {
    $check = (is_array($arr1) && count($arr1) > 0) ? true : false;
    $result = ($check) ? ((is_array($arr2) && count($arr2) > 0) ? $arr2 : array()) : array();
    if ($check) {
        foreach ($arr1 as $key => $value) {
            if (isset($result[$key])) {
                $result[$key] = array_diff($value, $result[$key]);
            } else {
                $result[$key] = $value;
            }
        }
    }
    return $result;
}

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

$iFoodMenuId = isset($_POST['iFoodMenuId']) ? $_POST['iFoodMenuId'] : '0';
$fPrice = isset($_POST['fPrice']) ? $_POST['fPrice'] : '';
$iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : '';
$eFoodType = isset($_POST['eFoodType']) ? $_POST['eFoodType'] : '';
$vHighlightName = isset($_POST['vHighlightName']) ? $_POST['vHighlightName'] : '';
$fOfferAmt = isset($_POST['fOfferAmt']) ? $_POST['fOfferAmt'] : '';
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'on';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';

$eAvailable_check = isset($_POST['eAvailable']) ? $_POST['eAvailable'] : 'off';
$eAvailable = ($eAvailable_check == 'on') ? 'Yes' : 'No';

$eRecommended_check = isset($_POST['eRecommended']) ? $_POST['eRecommended'] : 'off';
$eRecommended = ($eRecommended_check == 'on') ? 'Yes' : 'No';

$prescription_required_chk = isset($_POST['prescription_required']) ? $_POST['prescription_required'] : 'off';
$prescription_required = ($prescription_required_chk == 'on') ? 'Yes' : 'No';

$oldImage = isset($_POST['oldImage']) ? $_POST['oldImage'] : '';
$vImageTest = isset($_POST['vImageTest']) ? $_POST['vImageTest'] : '';

$BaseOptions = isset($_POST['BaseOptions']) ? $_POST['BaseOptions'] : '';
$OptPrice = isset($_POST['OptPrice']) ? $_POST['OptPrice'] : '';
$optType = isset($_POST['optType']) ? $_POST['optType'] : '';
$OptionId = isset($_POST['OptionId']) ? $_POST['OptionId'] : '';
$eDefault = isset($_POST['eDefault']) ? $_POST['eDefault'] : '';

foreach ($BaseOptions as $key => $value) {
    if (trim($value) != "") {
        $base_array[$key]['vOptionName'] = $value;
        $base_array[$key]['fPrice'] = $OptPrice[$key];
        $base_array[$key]['eOptionType'] = $optType[$key];
        $base_array[$key]['iOptionId'] = $OptionId[$key];
        $base_array[$key]['eDefault'] = $eDefault[$key];
        $base_array[$key]['eStatus'] = 'Active';
    }
}

$AddonOptions = isset($_POST['AddonOptions']) ? $_POST['AddonOptions'] : '';
$AddonPrice = isset($_POST['AddonPrice']) ? $_POST['AddonPrice'] : '';
$optTypeaddon = isset($_POST['optTypeaddon']) ? $_POST['optTypeaddon'] : '';
$addonId = isset($_POST['addonId']) ? $_POST['addonId'] : '';

foreach ($AddonOptions as $key => $value) {
    $addon_array[$key]['vOptionName'] = $value;
    $addon_array[$key]['fPrice'] = $AddonPrice[$key];
    $addon_array[$key]['eOptionType'] = $optTypeaddon[$key];
    $addon_array[$key]['iOptionId'] = $addonId[$key];
    $addon_array[$key]['eStatus'] = 'Active';
}
$vTitle_store = $vItemDesc_store = array();
$sql = "SELECT * FROM `language_master` where eStatus='Active' ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = count($db_master);
if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
        $vValue = 'vItemType_' . $db_master[$i]['vCode'];
        $vValue_desc = 'vItemDesc_' . $db_master[$i]['vCode'];

        array_push($vTitle_store, $vValue);
        $$vValue = isset($_POST[$vValue]) ? $_POST[$vValue] : '';

        array_push($vItemDesc_store, $vValue_desc);
        $$vValue_desc = isset($_POST[$vValue_desc]) ? $_POST[$vValue_desc] : '';
    }
}

if (isset($_POST['btnsubmit'])) {
    $img_path = $tconfig["tsite_upload_images_menu_item_path"];
    $temp_gallery = $img_path . '/';
    $image_object = $_FILES['vImage']['tmp_name'];
    $image_name = $_FILES['vImage']['name'];
    $vImgName = "";
    if ($image_name != "") {
        $oldFilePath = $temp_gallery . $oldImage;
        if ($oldImage != '' && file_exists($oldFilePath)) {
            unlink($img_path . '/' . $oldImage);
        }
        $filecheck = basename($_FILES['vImage']['name']);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[count($fileextarr) - 1]);
        $flag_error = 0;
        if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
            $flag_error = 1;
            $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png";
        }
        if ($flag_error == 1) {
            $generalobj->getPostForm($_POST, $var_msg, "menu_item_action.php?success=0&var_msg=" . $var_msg);
            exit;
        } else {
            $Photo_Gallery_folder = $img_path . '/';
            $img1 = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, '', '', '', '', '', '', 'Y', '', $Photo_Gallery_folder);
            $oldImage = $img1;
        }
    }

    if ($id != "") {
        $sql = "SELECT iDisplayOrder FROM `menu_items` where iMenuItemId = '$id'";
        $displayOld = $obj->MySQLSelect($sql);
        $oldDisplayOrder = $displayOld[0]['iDisplayOrder'];

        if ($oldDisplayOrder > $iDisplayOrder) {
            $sql = "SELECT * FROM `menu_items` where iFoodMenuId = '$iFoodMenuId' AND iDisplayOrder >= '$iDisplayOrder' AND iDisplayOrder < '$oldDisplayOrder' ORDER BY iDisplayOrder ASC";
            $db_orders = $obj->MySQLSelect($sql);
            if (!empty($db_orders)) {
                $j = $iDisplayOrder + 1;
                for ($i = 0; $i < count($db_orders); $i++) {
                    $query = "UPDATE menu_items SET iDisplayOrder = '$j' WHERE iMenuItemId = '" . $db_orders[$i]['iMenuItemId'] . "'";
                    $obj->sql_query($query);
                    echo $j;
                    $j++;
                }
            }
        } else if ($oldDisplayOrder < $iDisplayOrder) {
            $sql = "SELECT * FROM `menu_items` where iFoodMenuId = '$iFoodMenuId' AND iDisplayOrder > '$oldDisplayOrder' AND iDisplayOrder <= '$iDisplayOrder' ORDER BY iDisplayOrder ASC";
            $db_orders = $obj->MySQLSelect($sql);
            if (!empty($db_orders)) {
                $j = $iDisplayOrder;
                for ($i = 0; $i < count($db_orders); $i++) {
                    $query = "UPDATE menu_items SET iDisplayOrder = '$j' WHERE iMenuItemId = '" . $db_orders[$i]['iMenuItemId'] . "'";
                    $obj->sql_query($query);
                    echo $j;
                    $j++;
                }
            }
        }
    } else {
        $sql = "SELECT * FROM `menu_items` where iFoodMenuId = '$iFoodMenuId' AND iDisplayOrder >= '$iDisplayOrder' ORDER BY iDisplayOrder ASC";
        $db_orders = $obj->MySQLSelect($sql);

        if (!empty($db_orders)) {
            $j = $iDisplayOrder + 1;
            for ($i = 0; $i < count($db_orders); $i++) {
                $query = "UPDATE menu_items SET iDisplayOrder = '$j' WHERE iMenuItemId = '" . $db_orders[$i]['iMenuItemId'] . "'";
                $obj->sql_query($query);
                $j++;
            }
        }
    }
    $editItemDesc = $where = "";
    for ($i = 0; $i < count($vTitle_store); $i++) {
        $vValue = 'vItemType_' . $db_master[$i]['vCode'];
        $vValue_desc = 'vItemDesc_' . $db_master[$i]['vCode'];
        $strItemDesc = $obj->SqlEscapeString(htmlspecialchars_decode(html_entity_decode($_POST[$vItemDesc_store[$i]]), ENT_QUOTES));
        $strItemTitle = $obj->SqlEscapeString(htmlspecialchars_decode(html_entity_decode($_POST[$vTitle_store[$i]]), ENT_QUOTES));
        //echo $vValue_desc;die;
        //$editItemDesc .= '`' . $vValue_desc . "`='" . $strItemDesc . "','`" . $vValue . "`='" . $strItemTitle . "',";
        $editItemDesc .= "`" . $vValue_desc . "`='" . $strItemDesc . "',`" . $vValue . "`='" . $strItemTitle . "',";
    }
    if ($editItemDesc != "") {
        $editItemDesc = trim($editItemDesc, ",");
    }
    $q = "INSERT INTO ";
    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `iMenuItemId` = '" . $id . "'";
    }
    $query = $q . " `" . $tbl_name . "` SET
          `iFoodMenuId` = '" . $iFoodMenuId . "',
          `vImage` = '" . $oldImage . "',
          `iDisplayOrder` = '" . $iDisplayOrder . "',
          `fPrice` = '" . $fPrice . "',
          `fOfferAmt` = '" . $fOfferAmt . "',
          `eFoodType` = '" . $eFoodType . "',
          `vHighlightName` = '" . $vHighlightName . "',
           `eAvailable` = '" . $eAvailable . "',
           `eRecommended`= '" . $eRecommended . "',
           `prescription_required` = '" . $prescription_required . "', " . $editItemDesc . ""
            . $where;
    $obj->sql_query($query);
    $id = ($id != '') ? $id : $obj->GetInsertId();
    if (!empty($id)) {
        $baseOptionOldData = $obj->MySQLSelect("SELECT * FROM menuitem_options WHERE iMenuItemId ='" . $id . "' AND eOptionType='Options'");
        if (count($baseOptionOldData) > 0) {
            $BaseOptionsDiffres = check_diff($baseOptionOldData, $base_array);
            foreach ($BaseOptionsDiffres as $k => $BaseOptionsVal) {
                if (!empty($BaseOptionsVal['iOptionId'])) {
                    $newoptioidsArr[$k]['iOptionId'] = $BaseOptionsVal['iOptionId'];
                    $newoptioidsArr[$k]['iMenuItemId'] = $BaseOptionsVal['iMenuItemId'];
                }
            }
            if (count($newoptioidsArr) > 0) {
                foreach ($newoptioidsArr as $ky => $optionidArr) {
                    $q = "UPDATE ";
                    $where = " WHERE `iOptionId` = '" . $optionidArr['iOptionId'] . "' AND `iMenuItemId` = '" . $optionidArr['iMenuItemId'] . "'";
                    $baseupdatequery = $q . " `" . $tbl_name1 . "` SET `eStatus` = 'Inactive'" . $where;
                    $obj->sql_query($baseupdatequery);
                }
            }

            if (count($base_array) > 0) {
                foreach ($base_array as $key => $value) {
                    if ($value['iOptionId'] == '') {
                        $q = "INSERT INTO ";
                        $where = '';
                    } else {
                        $q = "UPDATE ";
                        $where = " WHERE `iOptionId` = '" . $value['iOptionId'] . "'";
                    }
                    $basequery = $q . " `" . $tbl_name1 . "` SET
                        `iMenuItemId`= '" . $id . "',
                        `vOptionName` = '" . $value['vOptionName'] . "',
                        `fPrice` = '" . $value['fPrice'] . "',
                        `eDefault` = '" . $value['eDefault'] . "',
                        `eStatus` = '" . $value['eStatus'] . "',
                        `eOptionType` = '" . $value['eOptionType'] . "'"
                            . $where;
                    $obj->sql_query($basequery);
                }
            }
        } else {
            if (count($base_array) > 0) {
                foreach ($base_array as $key => $value) {
                    $q = "INSERT INTO ";
                    $where = '';
                    $basequery = $q . " `" . $tbl_name1 . "` SET
                    `iMenuItemId`= '" . $id . "',
                    `vOptionName` = '" . $value['vOptionName'] . "',
                    `fPrice` = '" . $value['fPrice'] . "',
                    `eDefault` = '" . $value['eDefault'] . "',
                        `eStatus` = '" . $value['eStatus'] . "',
                    `eOptionType` = '" . $value['eOptionType'] . "'"
                            . $where;
                    $obj->sql_query($basequery);
                }
            }
        }
    }

    if (!empty($id)) {
        $q = "SELECT * FROM menuitem_options WHERE iMenuItemId ='" . $id . "' AND eOptionType='Addon'";
        $addonOptionOldData = $obj->MySQLSelect($q);
        if (count($addonOptionOldData) > 0) {
            $addonOptionDiffres = check_diff($addonOptionOldData, $addon_array);
            foreach ($addonOptionDiffres as $j => $AddonOptionsVal) {
                if (!empty($AddonOptionsVal['iOptionId'])) {
                    $newoptioidsAddonArr[$j]['iOptionId'] = $AddonOptionsVal['iOptionId'];
                    $newoptioidsAddonArr[$j]['iMenuItemId'] = $AddonOptionsVal['iMenuItemId'];
                }
            }
            if (count($newoptioidsAddonArr) > 0) {
                foreach ($newoptioidsAddonArr as $ky => $addonoptionidArr) {
                    $q = "UPDATE ";
                    $where = " WHERE `iOptionId` = '" . $addonoptionidArr['iOptionId'] . "' AND `iMenuItemId` = '" . $addonoptionidArr['iMenuItemId'] . "'";

                    $addonupdatequery = $q . " `" . $tbl_name1 . "` SET
                        `eStatus` = 'Inactive'"
                            . $where;
                    $obj->sql_query($addonupdatequery);
                }
            }

            if (count($addon_array) > 0) {
                foreach ($addon_array as $key => $value) {
                    if ($value['iOptionId'] == '') {
                        $q = "INSERT INTO ";
                        $where = '';
                    } else {
                        $q = "UPDATE ";
                        $where = " WHERE `iOptionId` = '" . $value['iOptionId'] . "'";
                    }
                    $addonquery = $q . " `" . $tbl_name1 . "` SET
                        `iMenuItemId`= '" . $id . "',
                        `vOptionName` = '" . $value['vOptionName'] . "',
                        `fPrice` = '" . $value['fPrice'] . "',
                        `eStatus` = '" . $value['eStatus'] . "',
                        `eOptionType` = '" . $value['eOptionType'] . "'"
                            . $where;
                    $obj->sql_query($addonquery);
                }
            }
        } else {
            if (count($addon_array) > 0) {
                foreach ($addon_array as $key => $value) {
                    $q = "INSERT INTO ";
                    $where = '';
                    $addonquery = $q . " `" . $tbl_name1 . "` SET
                    `iMenuItemId`= '" . $id . "',
                    `vOptionName` = '" . $value['vOptionName'] . "',
                    `fPrice` = '" . $value['fPrice'] . "',
                    `eStatus` = '" . $value['eStatus'] . "',
                    `eOptionType` = '" . $value['eOptionType'] . "'"
                            . $where;
                    $obj->sql_query($addonquery);
                }
            }
        }
    }
    //header("Location:menu_item_action.php?id=" . $id . '&success=1');
    if ($action == "Add") {
        $var_msg = 'Item Insert Successfully.';
    } else {
        $var_msg = 'Item Updated Successfully.';
    }
    header("Location:menuitems.php?success=1&var_msg=" . $var_msg);
    //header("Location:".$backlink);exit;
}

// for Edit
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iMenuItemId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);

    $sql1 = "SELECT * FROM " . $tbl_name1 . " WHERE iMenuItemId = '" . $id . "' AND eOptionType = 'Options' AND eStatus = 'Active'";
    $db_optionsdata = $obj->MySQLSelect($sql1);

    $sql2 = "SELECT * FROM " . $tbl_name1 . " WHERE iMenuItemId = '" . $id . "' AND eOptionType = 'Addon' AND eStatus = 'Active'";
    $db_addonsdata = $obj->MySQLSelect($sql2);

    $vLabel = $id;
    if (count($db_data) > 0) {
        for ($i = 0; $i < count($db_master); $i++) {
            foreach ($db_data as $key => $value) {
                $vValue = 'vItemType_' . $db_master[$i]['vCode'];
                $$vValue = $value[$vValue];
                $vValue_desc = 'vItemDesc_' . $db_master[$i]['vCode'];
                $$vValue_desc = $value[$vValue_desc];
                $iFoodMenuId = $value['iFoodMenuId'];
                $oldImage = $value['vImage'];
                $iDisplayOrder = $value['iDisplayOrder'];
                $fPrice = $value['fPrice'];
                $eAvailable = $value['eAvailable'];
                $eStatus = $value['eStatus'];
                $eRecommended = $value['eRecommended'];
                $fOfferAmt = $value['fOfferAmt'];
                $eFoodType = $value['eFoodType'];
                $vHighlightName = $value['vHighlightName'];
                $prescription_required = $value['prescription_required'];
            }
        }
    }
}
$sql_cat = "SELECT fm.*,c.vCompany,c.iServiceId FROM food_menu AS fm LEFT JOIN `company` as c ON c.iCompanyId=fm.iCompanyId WHERE fm.iCompanyId = $iCompanyId AND fm.eStatus = 'Active'";
$db_menu = $obj->MySQLSelect($sql_cat);
if (!empty($db_menu[0]['iServiceId'])) {
    $iServiceId = $db_menu[0]['iServiceId'];
    $sql = "SELECT prescription_required FROM `service_categories` WHERE iServiceId = '" . $iServiceId . "'";
    $db_prescription = $obj->MySQLSelect($sql);
    $prescriptionchkbox_required = $db_prescription[0]['prescription_required'];
}
$helpText = "This feature can be used when you want to provide different options for the same product. The price would be added to the base price.For E.G.: Regular Pizza, Double Cheese Pizza etc.";
if ($iServiceId > 1) {
    $helpText = "This feature can be used when you want to provide different options for the same product.";
}
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_MENU_ITEM_FRONT']; ?> <?= $action; ?></title>
        <!-- Default Top Script and css -->
        <?php include_once("top/top_script.php"); ?>
        <!-- End: Default Top Script and css-->
        <link rel="stylesheet" href="assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
    </head>
    <body>
        <!-- home page -->
        <div id="main-uber-page">
            <!-- Left Menu -->
            <?php include_once("top/left_menu.php"); ?>
            <!-- End: Left Menu-->
            <!-- Top Menu -->
            <?php include_once("top/header_topbar.php"); ?>
            <!-- End: Top Menu-->
            <!-- contact page-->
            <div class="page-contant ">
                <div class="page-contant-inner page-trip-detail">
                    <h2 class="header-page trip-detail food-detail1"><?= $action_lbl; ?> <?= $langage_lbl['LBL_MENU_ITEM_FRONT']; ?> <?= $vName; ?>
                        <a href="menuitems.php">
                            <img src="assets/img/arrow-white.png" alt=""> <?= $langage_lbl['LBL_BACK_To_Listing_WEB']; ?>
                        </a>
                    </h2>
                    <!-- login in page -->
                    <div class="food-action-page">
                        <? if ($success == 1) { ?>
                            <div class="alert alert-success alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?php echo $langage_lbl['LBL_Record_Updated_successfully']; ?>
                            </div>
                        <? } else if ($success == 2) { ?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?php echo $langage_lbl['LBL_EDIT_DELETE_RECORD']; ?>
                            </div>
                        <?php } ?>
                        <div style="clear:both;"></div>
                        <form id="menuItem_form" name="menuItem_form" class="menuItemFormFront" method="post" action="" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?= $id; ?>"/>
                            <input  type="hidden" name="oldImage" value="<?php echo $oldImage; ?>">
                            <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                            <input type="hidden" name="backlink" id="backlink" value="menuitems.php"/>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?php echo $langage_lbl['LBL_MENU_CATEGORY_WEB_TXT'] ?><span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <select  class="form-control" name = 'iFoodMenuId' required onChange="changeDisplayOrder(this.value, '<?php echo $id; ?>');" >
                                                    <option value=""><?php echo $langage_lbl['LBL_SELECT_CATEGORY'] ?></option>
                                                    <?php foreach ($db_menu as $dbmenu) { ?>
                                                        <option value = "<?= $dbmenu['iFoodMenuId'] ?>" <?= ($dbmenu['iFoodMenuId'] == $iFoodMenuId) ? 'selected' : ''; ?> <?php if (count($dbmenu['menuItems']) > 0) { ?> <?php } ?> ><?= $dbmenu['vMenu_' . $_SESSION['sess_lang']]; ?></option>
                                                    <? } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <?php
                                        if ($count_all > 0) {
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];

                                                $vValue = 'vItemType_' . $vCode;
                                                $vValue_desc = 'vItemDesc_' . $vCode;

                                                $required = ($eDefault == 'Yes') ? 'required' : '';
                                                $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                ?>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <label><?php echo $langage_lbl['LBL_MENU_ITEM_FRONT'] ?> (<?= $vTitle; ?>) <?= $required_msg; ?></label>
                                                    </div>
                                                    <div class="col-lg-12">
                                                        <input type="text" class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" value="<?= $$vValue; ?>" placeholder="<?= $vTitle; ?>Value" <?= $required; ?>>

                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <label><?php echo $langage_lbl['LBL_MENU_ITEM_DESCRIPTION'] ?>(<?= $vTitle; ?>)</label>
                                                    </div>
                                                    <div class="col-lg-12">
                                                        <textarea class="form-control" name="<?= $vValue_desc; ?>" id="<?= $vValue_desc; ?>" ><?= $$vValue_desc; ?></textarea>
                                                    </div>
                                                </div>
                                                <?
                                            }
                                        }
                                        ?>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?php echo $langage_lbl['LBL_DISPLAY_ORDER_FRONT'] ?><span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12" id="showDisplayOrder001">
                                                <?php if ($action == 'Add') { ?>
                                                    <input type="hidden" name="total" value="<?php echo $count + 1; ?>" >
                                                    <select name="iDisplayOrder" id="iDisplayOrder" class="form-control" required>
                                                        <?php for ($i = 1; $i <= $count + 1; $i++) { ?>
                                                            <option value="<?php echo $i ?>" 
                                                            <?php
                                                            if ($i == $count + 1)
                                                                echo 'selected';
                                                            ?>> <?php echo $i ?> </option>
                                                                <?php } ?>
                                                    </select>
                                                <?php }else { ?>
                                                    <input type="hidden" name="total" value="<?php echo $iDisplayOrder; ?>">
                                                    <select name="iDisplayOrder" id="iDisplayOrder" class="form-control" required>
                                                        <?php for ($i = 1; $i <= $count; $i++) { ?>
                                                            <option value="<?php echo $i ?>"
                                                            <?php
                                                            if ($i == $iDisplayOrder)
                                                                echo 'selected';
                                                            ?>
                                                                    > <?php echo $i ?> </option>
                                                                <?php } ?>
                                                    </select>
                                                <?php } ?>
                                            </div>
                                        </div>

                                        <!--    <div class="row">
                                            <div class="col-lg-12">
                                              <label>Status</label>
                                            </div>
                                            <div class="col-lg-12">
                                              <div class="make-switch" data-on="success" data-off="warning" id="mySwitch">
                                                <input type="checkbox" name="eStatus" <?= ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?> id="eStatus"/>
                                              </div>
                                            </div>
                                            </div> -->

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?php echo $langage_lbl['LBL_MENU_ITEM_IMAGE'] ?><span class="red" id="req_recommended"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="imageupload">
                                                    <div class="file-tab">
                                                        <span id="single_img001">
                                                            <?php
                                                            $imgpth = $tconfig["tsite_upload_images_menu_item_path"] . '/' . $oldImage;
                                                            $imgUrl = $tconfig["tsite_upload_images_menu_item"] . '/' . $oldImage;
                                                            if ($oldImage != "" && file_exists($imgpth)) {
                                                                ?>
                                                                <img src="<?php echo $imgUrl; ?>" alt="Image preview" class="thumbnail" style="max-width: 250px; max-height: 250px">
                                                            <?php } ?>
                                                        </span>
                                                        <div>
                                                            <input type="hidden" name="vImageTest" value="" >
                                                            <input type="hidden" id="imgnameedit" value="<?= trim($oldImage); ?>">
                                                            <input name="vImage" onchange="preview_mainImg(event);" type="file" class="form-control" <?= ($id=='' || ($id != '' && $eRecommended == 'Yes')) ? 'required' : ''; ?>>
                                                            <b>[Note: Recommended dimension is 2048px * 2048px and if this item is set as recommended then the item image is required.]</b><!--added by SP for required validation add in menu item image when recommended is on on 26-07-2019 -->]</b>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="row" style="">
                                            <div class="col-lg-12">
                                                <label><?php echo $langage_lbl['LBL_PRICE_FOR_MENU_ITEM'] ?> (In <?= $db_currency[0]['vName'] ?>) <span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" onkeyup="updateOptionPrice();" class="form-control" name="fPrice"  id="fPrice" value="<?= $fPrice; ?>" required>
                                                <small>[<?php echo $langage_lbl['LBL_NOTE_FRONT'] ?> <?php echo $langage_lbl['LBL_NOTE_FOR_PRICE_MENU_ITEM'] ?> ]</small>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="panel panel-default">
                                                    <div class="panel-heading">
                                                        <div class="row" style="padding-bottom:0;padding-top: 0;">
                                                            <div class="col-lg-6"><h5><b><?php echo $langage_lbl['LBL_OPTIONS_MENU_ITEM'] ?></b> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='<?= $helpText; ?>'></i></h5></div>
                                                            <div class="col-lg-6 text-right"><button class="btn btn-success" type="button"  onclick="options_fields();"> <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> </button></div>
                                                        </div>
                                                    </div>
                                                    <div class="panel-body" style="padding: 25px;">
                                                        <div id="options_fields"> 
                                                            <?
                                                            if (count($db_optionsdata) > 0) {
                                                                $opt = 0;
                                                                foreach ($db_optionsdata as $k => $option) {
                                                                    $opt++;
                                                                    ?>
                                                                    <?php if ($option['eDefault'] == 'Yes') { ?>
                                                                        <div class="form-group eDefault"> 
                                                                            <div class="col-sm-5">
                                                                                <div class="form-group"> 
                                                                                    <input type="text" class="form-control" id="BaseOptions" name="BaseOptions[]" required="required" value="<?= $option['vOptionName'] ?>" placeholder="Option Name" >
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-5">
                                                                                <div class="form-group"> 
                                                                                    <input type="text" class="form-control" id="OptPrice" name="OptPrice[]"  value="<?= $option['fPrice'] ?>" placeholder="Price" readonly required="required">
                                                                                    <input type="hidden" name="optType[]" value="Options" />
                                                                                    <input type="hidden" name="OptionId[]" value="<?= $option['iOptionId'] ?>" /><input type="hidden" name="eDefault[]" value="Yes"/>
                                                                                </div>
                                                                            </div>
                                                                            <div class="clear"></div>
                                                                        </div>
                                                                    <?php } else { ?>
                                                                        <div class="form-group removeclass<?= $opt ?>"> 
                                                                            <div class="col-sm-5">
                                                                                <div class="form-group"> 
                                                                                    <input type="text" class="form-control" id="BaseOptions" name="BaseOptions[]" required="required" value="<?= $option['vOptionName'] ?>" placeholder="Option Name" >
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-5">
                                                                                <div class="form-group"> 
                                                                                    <input type="text" class="form-control" id="OptPrice" name="OptPrice[]" required="required" value="<?= $option['fPrice'] ?>" placeholder="Price">
                                                                                    <input type="hidden" name="optType[]" value="Options" />
                                                                                    <input type="hidden" name="OptionId[]" value="<?= $option['iOptionId'] ?>" /><input type="hidden" name="eDefault[]" value="No"/>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-2">
                                                                                <div class="form-group">
                                                                                    <div class="input-group">
                                                                                        <div class="input-group-btn"> 
                                                                                            <button class="btn btn-danger" type="button" onclick="remove_options_fields('<?= $opt ?>');"> <span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="clear"></div>
                                                                        </div>
                                                                        <?
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="panel panel-default" <?php if ($iServiceId != '1') { ?> style="display:none;" <?php } ?>>
                                                    <div class="panel-heading">
                                                        <div class="row" style="padding-bottom:0;padding-top: 0;">
                                                            <div class="col-lg-6"><h5><b><?php echo $langage_lbl['LBL_ADDON_FRONT'] ?> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Addon/Topping Price will be additional amount which will added in base price'></i></b></h5></div>
                                                            <div class="col-lg-6 text-right"><button class="btn btn-success" type="button"  onclick="addon_fields();"> <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> </button></div>
                                                        </div>
                                                    </div>
                                                    <div class="panel-body" style="padding: 25px;">
                                                        <div id="addon_fields">
                                                            <?
                                                            if (count($db_addonsdata) > 0) {
                                                                $a = 0;
                                                                foreach ($db_addonsdata as $k => $addon) {
                                                                    $a++;
                                                                    ?>
                                                                    <div class="form-group removeclassaddon<?= $a ?>"> 
                                                                        <div class="col-sm-5">
                                                                            <div class="form-group"> 
                                                                                <input type="text" class="form-control" id="AddonOptions" name="AddonOptions[]" value="<?= $addon['vOptionName'] ?>" placeholder="Topping Name" required>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-sm-5">
                                                                            <div class="form-group"> 
                                                                                <input type="text" class="form-control" id="AddonPrice" name="AddonPrice[]" value="<?= $addon['fPrice'] ?>" placeholder="Price" required>
                                                                                <input type="hidden" name="optTypeaddon[]" value="Addon" />
                                                                                <input type="hidden" name="addonId[]" value="<?= $addon['iOptionId'] ?>" />
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-sm-2">
                                                                            <div class="form-group">
                                                                                <div class="input-group">
                                                                                    <div class="input-group-btn"> 
                                                                                        <button class="btn btn-danger" type="button" onclick="remove_addon_fields('<?= $a ?>');"> <span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="clear"></div>
                                                                    </div>
                                                                    <?
                                                                }
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row" >
                                            <div class="col-lg-12">
                                                <label><?php echo $langage_lbl['LBL_OFFER_AMOUNT_MENU_ITEM'] ?>(%) <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Set Offer amount on an item, if you want to show discounted/strikeout amount. E.g If Item Price is $100 but you want to sell it for $80, then set Offer Amount = 20%, hence the final price of this item is $80'></i></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="fOfferAmt"  id="fOfferAmt" value="<?= $fOfferAmt; ?>" />
                                                <small><?php echo $langage_lbl['LBL_NOTE_FRONT']." ".$langage_lbl['LBL_DISCOUNT_NOTE'] ?></small>
                                            </div>
                                        </div>
                                        <div class="row servicecatresponsive" <?php if ($iServiceId != '1') { ?> style="display:none;" <?php } ?>>
                                            <div class="col-lg-12">
                                                <label>Food Type<span class="red">*</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <select class="form-control" name="eFoodType"  id="eFoodType">
                                                    <option value="">--Select--</option>										
                                                    <option value="Veg" <?
                                                    if ($eFoodType == 'Veg') {
                                                        echo 'selected';
                                                    }
                                                    ?>>Veg Food</option>
                                                    <option value="NonVeg" <?
                                                    if ($eFoodType == 'NonVeg') {
                                                        echo 'selected';
                                                    }
                                                    ?>>Non Veg Food</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label> <label><?php echo $langage_lbl['LBL_ITEM_IN_STOCK_WEB'] ?> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="If this item is set On by the restaurant then it will be available for user\'s to order it, Set it off when the item is out of stock"></i></label></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="make-switch" data-on="success" data-off="warning">
                                                    <input type="checkbox" name="eAvailable" <?= ($id != '' && $eAvailable == 'No') ? '' : 'checked'; ?> id="eAvailable" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?php echo $langage_lbl['LBL_IS_ITEM_RECOMMENDED'] ?> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Suggest the user's to order this item. The recommended items will be highlighted in the user app with the image and display at the top section"></i></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="make-switch" data-on="success" data-off="warning">
                                                    <input type="checkbox" name="eRecommended" <?= ($id != '' && $eRecommended == 'No') ? '' : 'checked'; ?> id="eRecommended" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?php echo $langage_lbl['LBL_ITEM_TAG_NAME'] ?> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Set the tag name to this item. Like, Best Seller, Most Popular"></i></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <select class="form-control" name="vHighlightName"  id="vHighlightName">
                                                    <option value="">Select Tag</option>
                                                    <option value="LBL_BESTSELLER" <?
                                                    if ($vHighlightName == 'LBL_BESTSELLER') {
                                                        echo 'selected';
                                                    }
                                                    ?>><?php echo $langage_lbl['LBL_BESTSELLER'] ?></option>
                                                    <option value="LBL_NEWLY_ADDED" <?
                                                    if ($vHighlightName == 'LBL_NEWLY_ADDED') {
                                                        echo 'selected';
                                                    }
                                                    ?>><?php echo $langage_lbl['LBL_NEWLY_ADDED'] ?></option>
                                                    <option value="LBL_PROMOTED" <?
                                                    if ($vHighlightName == 'LBL_PROMOTED') {
                                                        echo 'selected';
                                                    }
                                                    ?>><?php echo $langage_lbl['LBL_PROMOTED'] ?></option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- For Prescription required start added by sneha  -->
                                        <?php
                                        if ($id == "" && $prescription_required == "No") {
                                            $checked_prescription = "";
                                        } else if ($id != "" && $prescription_required == "No") {
                                            $checked_prescription = "";
                                        } else if ($prescription_required == "Yes") {
                                            $checked_prescription = "checked";
                                        }
                                        ?>
                                        <div class="row" id="prescription_div" style="display:<?php if ($prescriptionchkbox_required == 'Yes') { ?>block<?php } else { ?>none<?php } ?>">
                                            <div class="col-lg-12">
                                                <label><?php echo $langage_lbl_admin['LBL_IS_PRESCRIPTION_REQUIRED'] ?> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="This will allow user to upload the precription while placing the order"></i></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="make-switch" data-on="success" data-off="warning" data-on-text="Yes" data-off-text="No" id="mySwitch1" >
                                                    <input type="checkbox" name="prescription_required" <?php echo $checked_prescription; ?> id="prescription_required" />
                                                </div>
                                            </div>
                                        </div>
                                        <!-- For Prescription required end added by sneha  -->

                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <input type="submit" class="save-but" name="btnsubmit" id="btnsubmit" value="<?= $action; ?> <?php echo $langage_lbl['LBL_MENU_ITEM_FRONT']; ?>" >
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div style="clear:both;"></div>
            </div>
        </div>
        <!-- footer part -->
        <?php include_once('footer/footer_home.php'); ?>
        <!-- footer part end -->
        <!-- End:contact page-->
        <div style="clear:both;"></div>
        <!-- home page end-->
        <!-- Footer Script -->
        <?php
        include_once('top/footer_script.php');
        $lang = get_langcode($_SESSION['sess_lang']);
        ?>
        <style>
            span.help-block{
                margin:0;
                padding: 0;
            }
        </style>
        <script type="text/javascript" src="<?php echo $tconfig["tsite_url_main_admin"] ?>js/validation/jquery.validate.min.js" ></script>
        <?php if ($lang != 'en') { ?>
            <!-- <script type="text/javascript" src="assets/js/validation/localization/messages_<?= $lang; ?>.js" ></script> -->
            <? include_once('otherlang_validation.php');?>
        <?php } ?>
        <script type="text/javascript" src="assets/js/validation/additional-methods.js" ></script>
        <script src="assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
        <link href="assets/css/imageUpload/bootstrap-imageupload.css" rel="stylesheet">
        <script>
        function changeDisplayOrder(foodId, menuId, parentId)
        {
            var itemParentId = '';
            if (parentId != '') {
                itemParentId = parentId
            }
            $.ajax({
                type: "POST",
                url: 'ajax_display_order.php',
                data: {iFoodMenuId: foodId, page: 'items', iMenuItemId: menuId},
                success: function (response)
                {
                    $("#showDisplayOrder001").html('');
                    $("#showDisplayOrder001").html(response);
                }
            });
            $.ajax({
                type: 'post',
                url: 'ajax_display_order.php',
                data: {method: 'getParentItems', page: 'items', iFoodMenuId: foodId, itemParentId: itemParentId},
                success: function (response) {
                    $("#iParentId").html(response);
                },
                error: function (response) {
                }
            });
        }
        $(document).ready(function () {
            changeDisplayOrder('<?php echo $iFoodMenuId; ?>', '<?php echo $id; ?>', '<?php echo $menuiParentId; ?>');
        });
        function preview_mainImg(event)
        {
            $("#single_img001").html('');
            $('#single_img001').append("<img src='" + URL.createObjectURL(event.target.files[0]) + "' class='thumbnail' style='max-width: 250px; max-height: 250px' >");
            $(".changeImg001").text('Change');
            $(".remove_main").show();
        }

        <? if (count($db_optionsdata) > 0) { ?>
                    var optionid = '<?= count($db_optionsdata) ?>';
        <? } else { ?>
                    var optionid = 0;
        <? } ?>

        function options_fields() {
            var container_div = document.getElementById('options_fields');
            var count = container_div.getElementsByTagName('div').length;
            var serviceId = '<?= $iServiceId; ?>';
            var basePrice = 0;
            var baseOptionValue = "Regular";
            if (serviceId > 1) {
                baseOptionValue = "";
                var basePrice = $("#fPrice").val();
            }
            if (count == 0) {
                optionid = 0;
            }
            optionid++;

            var objTo = document.getElementById('options_fields')
            var divtest = document.createElement("div");
            divtest.setAttribute("class", "form-group removeclass" + optionid);

            if (optionid == '1') {
                var divtest1 = document.createElement("div");
                divtest1.setAttribute("class", "form-group eDefault");
                divtest1.innerHTML = '<div class="col-sm-5"><div class="form-group"> <input type="text" class="form-control" id="BaseOptions" name="BaseOptions[]" value="' + baseOptionValue + '" placeholder="Option Name" required="required"></div></div><div class="col-sm-5"><div class="form-group"> <input type="text" class="form-control" id="OptPrice" name="OptPrice[]" required="required" value="' + basePrice + '" placeholder="Price" readonly><input type="hidden" name="OptionId[]" value="" /><input type="hidden" name="optType[]" value="Options" /><input type="hidden" name="eDefault[]" value="Yes"/></div></div><div class="clear"></div>';
                objTo.appendChild(divtest1);

                divtest.innerHTML = '<div class="col-sm-5"><div class="form-group"> <input type="text" class="form-control" id="BaseOptions" name="BaseOptions[]" required="required" value="" placeholder="Option Name"></div></div><div class="col-sm-5"><div class="form-group"><input type="text" class="form-control" id="OptPrice" name="OptPrice[]" value="" required="required" placeholder="Price (In <?= $db_currency[0]['vName'] ?>)"><input type="hidden" name="OptionId[]" value="" /><input type="hidden" name="optType[]" value="Options" /><input type="hidden" name="eDefault[]" value="No"/></div></div><div class="col-sm-2"><div class="form-group"><div class="input-group"><div class="input-group-btn"> <button class="btn btn-danger" type="button" onclick="remove_options_fields(' + optionid + ');"> <span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button></div></div></div></div><div class="clear"></div>';
            } else {
                divtest.innerHTML = '<div class="col-sm-5"><div class="form-group"> <input type="text" class="form-control" id="BaseOptions" name="BaseOptions[]" value="" required="required" placeholder="Option Name"></div></div><div class="col-sm-5"><div class="form-group"><input type="text" class="form-control" id="OptPrice" name="OptPrice[]" value="" required="required" placeholder="Price (In <?= $db_currency[0]['vName'] ?>)" ><input type="hidden" name="OptionId[]" value="" /><input type="hidden" name="optType[]" value="Options" /><input type="hidden" name="eDefault[]" value="No"/></div></div><div class="col-sm-2"><div class="form-group"><div class="input-group"><div class="input-group-btn"> <button class="btn btn-danger" type="button" onclick="remove_options_fields(' + optionid + ');"> <span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button></div></div></div></div><div class="clear"></div>';
            }
            objTo.appendChild(divtest);
        }
        function remove_options_fields(rid) {
            var container_div = document.getElementById('options_fields');
            var count = container_div.getElementsByTagName('div').length;
            //alert(count);
            if (count == 16) {
                $('.eDefault').remove();
                $('.removeclass' + rid).remove();
                var optionid = 0;
            } else {
                $('.removeclass' + rid).remove();
            }
        }


        <? if (count($db_addonsdata) > 0) { ?>
                    var addonid = '<?= count($db_addonsdata) ?>';
        <? } else { ?>
                    var addonid = 0;
        <? } ?>
        function addon_fields() {
            addonid++;
            var objTo = document.getElementById('addon_fields')
            var divtest = document.createElement("div");
            divtest.setAttribute("class", "form-group removeclassaddon" + addonid);
            divtest.innerHTML = '<div class="col-sm-5"><div class="form-group"> <input type="text" class="form-control" id="AddonOptions" name="AddonOptions[]" value="" placeholder="Topping Name" required></div></div><div class="col-sm-5"><div class="form-group"> <input type="text" class="form-control" id="AddonPrice" name="AddonPrice[]" value="" placeholder="Price (In <?= $db_currency[0]['vName'] ?>)" required><input type="hidden" name="addonId[]" value="" /><input type="hidden" name="optTypeaddon[]" value="Addon" /></div></div><div class="col-sm-2"><div class="form-group"><div class="input-group"><div class="input-group-btn"> <button class="btn btn-danger" type="button" onclick="remove_addon_fields(' + addonid + ');"> <span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button></div></div></div></div><div class="clear"></div>';

            objTo.appendChild(divtest)
        }
        function remove_addon_fields(rid) {
            $('.removeclassaddon' + rid).remove();
        }
        $(document).ready(function () {
            //added by SP for required validation add in menu item image when recommended is on on 26-07-2019 start
            $("#eRecommended").change(function () {
                var recommended_sel = '';
                recommended_sel = $("input[name='eRecommended']:checked").val();
                if(recommended_sel=='on') {
                    $('input[name="vImage"]').attr("required", "required");
                    $('#req_recommended').show();
                } else {
                    $('input[name="vImage"]').removeAttr("required");
                    $('input[name="vImage"]').parents('.row').removeClass('has-error');
                    $('#vImage-error').remove();
                    $('#req_recommended').hide();
                }
            });
            //added by SP for required validation add in menu item image when recommended is on on 26-07-2019 end

            $("#iServiceId").change(function () {
                var iServiceid = $(this).val();
                if (iServiceid == '1') {
                    $(".servicecatresponsive").show();
                } else {
                    $(".servicecatresponsive").hide();

                }
            });

            //Added By HJ On 19-12-2019 For Remove Required Image Validation If Image exists Start
            var oldImageName = $("#imgnameedit").val();
                            if (oldImageName != "") {
                            $('input[name="vImage"]').removeAttr("required");
                            $('input[name="vImage"]').parents('.row').removeClass('has-error');
                            $('#vImage-error').remove();
                }
             //Added By HJ On 19-12-2019 For Remove Required Image Validation If Image exists End


        });
        </script>
        <script>
            $('[data-toggle="tooltip"]').tooltip();
            var successMSG1 = '<?php echo $success; ?>';
            if (successMSG1 != '') {
                setTimeout(function () {
                    $(".msgs_hide").hide(1000)
                }, 5000);
            }
        </script>
        <script>
            var errormessage;
            if ($('#menuItem_form').length !== 0) {
                $('#menuItem_form').validate({
                    ignore: 'input[type=hidden]',
                    errorClass: 'help-block error',
                    errorElement: 'span',
                    onkeyup: function (element) {
                        $(element).valid()
                    },
                    highlight: function (e) {
                        if ($(e).attr("name") == "OptPrice[]" || $(e).attr("name") == "AddonOptions[]" || $(e).attr("name") == "BaseOptions[]") {

                            $(e).closest('.row .form-group').removeClass('has-success has-error').addClass('has-error');
                        } else {
                            $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                        }
                        $(e).closest('.help-block').remove();
                    },
                    success: function (e) {
                        e.closest('.row .form-group').removeClass('has-success has-error');
                        e.closest('.row').removeClass('has-success has-error');
                        e.closest('.help-block').remove();
                        e.closest('.help-inline').remove();
                    },
                    rules: {
                        iCompanyId: {required: true},
                        iFoodMenuId: {required: true},
                        fPrice: {required: true, number: true},
                        fOfferAmt: {number: true},
                        'BaseOptions[]': {required: true},
                        'OptPrice[]': {required: true, number: true},
                        'AddonOptions[]': {required: true}
                    },
                    messages: {
                        iCompanyId: {
                            //required: 'This field is required.'
                        },
                        iFoodMenuId: {
                           // required: 'This field is required.'
                        },
                        fPrice: {
                           // required: 'This field is required.'
                        }
                    },
                    submitHandler: function (form) {
                        if ($(form).valid())
                            form.submit();
                        return false; // prevent normal form posting
                    }
                });
            }
            function updateOptionPrice() {
                var serviceId = '<?= $iServiceId; ?>';
                var basePrice = 0;
                if (serviceId > 1) {
                    basePrice = $("#fPrice").val();
                }
                $("#OptPrice").val(basePrice);
            }
        </script>
    </body>
</html>

