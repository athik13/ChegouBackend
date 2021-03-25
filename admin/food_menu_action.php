<?php
include_once('../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
// For Languages
$sql = "SELECT * FROM `language_master` where eStatus='Active' ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$action = ($id != '') ? 'Edit' : 'Add';
$tbl_name = 'food_menu';
$script = 'FoodMenu';
// set all variables with either post (when submit) either blank (when insert)
$iCompanyId = isset($_POST['iCompanyId']) ? $_POST['iCompanyId'] : '';
$iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : '';
$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'Active';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$vMenu_store = array();
$count_all = count($db_master);
if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
        $vValue = 'vMenu_' . $db_master[$i]['vCode'];
        array_push($vMenu_store, $vValue);
        $$vValue = isset($_POST[$vValue]) ? $_POST[$vValue] : '';
    }
}
if (isset($_POST['submit'])) {
    if ($action == "Add" && !$userObj->hasPermission('create-item-categories')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create Item Category.';
        header("Location:food_menu.php");
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission('edit-item-categories')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update Item Category.';
        header("Location:food_menu.php");
        exit;
    }
    if (!empty($id) && SITE_TYPE == 'Demo') {
        $_SESSION['success'] = 2;
        header("Location:food_menu.php?id=" . $id);
        exit;
    }
    if ($id != "") {
        $sql = "SELECT iDisplayOrder FROM `food_menu` where iFoodMenuId = '$id'";
        $displayOld = $obj->MySQLSelect($sql);
        $oldDisplayOrder = $displayOld[0]['iDisplayOrder'];
        if ($oldDisplayOrder > $iDisplayOrder) {
            $sql = "SELECT * FROM `food_menu` where iCompanyId = '$iCompanyId' AND iDisplayOrder >= '$iDisplayOrder' AND iDisplayOrder < '$oldDisplayOrder' ORDER BY iDisplayOrder ASC";
            $db_orders = $obj->MySQLSelect($sql);
            if (!empty($db_orders)) {
                $j = $iDisplayOrder + 1;
                for ($i = 0; $i < count($db_orders); $i++) {
                    $query = "UPDATE food_menu SET iDisplayOrder = '$j' WHERE iFoodMenuId = '" . $db_orders[$i]['iFoodMenuId'] . "'";
                    $obj->sql_query($query);
                    $j++;
                }
            }
        } else if ($oldDisplayOrder < $iDisplayOrder) {
            $sql = "SELECT * FROM `food_menu` where iCompanyId = '$iCompanyId' AND iDisplayOrder > '$oldDisplayOrder' AND iDisplayOrder <= '$iDisplayOrder' ORDER BY iDisplayOrder ASC";
            $db_orders = $obj->MySQLSelect($sql);
            if (!empty($db_orders)) {
                $j = $oldDisplayOrder;
                for ($i = 0; $i < count($db_orders); $i++) {
                    $query = "UPDATE food_menu SET iDisplayOrder = '$j' WHERE iFoodMenuId = '" . $db_orders[$i]['iFoodMenuId'] . "'";
                    $obj->sql_query($query);
                    $j++;
                }
            }
        }
    } else {
        $sql = "SELECT * FROM `food_menu` WHERE iCompanyId = '$iCompanyId' AND iDisplayOrder >= '$iDisplayOrder' ORDER BY iDisplayOrder ASC";
        $db_orders = $obj->MySQLSelect($sql);

        if (!empty($db_orders)) {
            $j = $iDisplayOrder + 1;
            for ($i = 0; $i < count($db_orders); $i++) {
                $query = "UPDATE food_menu SET iDisplayOrder = '$j' WHERE iFoodMenuId = '" . $db_orders[$i]['iFoodMenuId'] . "'";
                $obj->sql_query($query);
                $j++;
            }
        }
    }
    for ($i = 0; $i < count($vMenu_store); $i++) {
        $q = "INSERT INTO ";
        $where = '';
        if ($id != '') {
            $q = "UPDATE ";
            $where = " WHERE `iFoodMenuId` = '" . $id . "'";
        }
        $eStatus_query = '';
        if ($action == "Add") {
            $eStatus_query = "  `eStatus` = '" . $eStatus . "',";
        }
        $vValue = 'vMenu_' . $db_master[$i]['vCode'];
        //$vValue_desc = 'vMenuDesc_'.$db_master[$i]['vCode'];
        $query = $q . " `" . $tbl_name . "` SET
        `iCompanyId` = '" . $iCompanyId . "',
        `iDisplayOrder` = '" . $iDisplayOrder . "',
        $eStatus_query
        `" . $vValue . "` = '" . $_POST[$vMenu_store[$i]] . "'"
                . $where;
        $obj->sql_query($query);
        $id = ($id != '') ? $id : $obj->GetInsertId();
    }

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
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iFoodMenuId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);

    if (count($db_data) > 0) {
        for ($i = 0; $i < count($db_master); $i++) {
            foreach ($db_data as $key => $value) {
                $vValue = 'vMenu_' . $db_master[$i]['vCode'];
                $$vValue = $value[$vValue];
                $iCompanyId = $value['iCompanyId'];
                $iDisplayOrder = $value['iDisplayOrder'];
                $eStatus = $value['eStatus'];
                $iFoodMenuId = $value['iFoodMenuId'];
            }
        }
    }
}
$qry_cat = "SELECT c.iServiceId FROM `food_menu` AS f LEFT JOIN company AS c ON c.iCompanyId = f.iCompanyId WHERE c.iCompanyId = '" . $iCompanyId . "' and  c.eStatus!='Deleted'";
$db_chk = $obj->MySQLSelect($qry_cat);
$EditServiceIdNew = $db_chk[0]['iServiceId'];
$catdata = serviceCategories;
$allservice_cat_data = json_decode($catdata, true);
foreach ($allservice_cat_data as $k => $val) {
    $iServiceIdArr[] = $val['iServiceId'];
}
$serviceIds = implode(",", $iServiceIdArr);
$service_category = "SELECT iServiceId,vServiceName_" . $default_lang . " as servicename,eStatus FROM service_categories WHERE iServiceId IN (" . $serviceIds . ") AND eStatus = 'Active'";
$service_cat_list = $obj->MySQLSelect($service_category);

$ssql = " AND iServiceId IN(".$enablesevicescategory.")";
$sql = "SELECT * FROM `company` where eStatus !='Deleted' $ssql ORDER BY `vCompany`";
$db_company = $obj->MySQLSelect($sql);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | Item Category  <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <? include_once('global_files.php'); ?>
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
                            <h2><?= $action; ?> Item Category</h2>
                            <a href="javascript:void(0);" class="back_link">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />
                    <div class="body-div">
                        <div class="form-group">
                            <form id="food_category_form" name="food_category_form" method="post" action="" enctype="multipart/form-data">
                                <input type="hidden" name="id" id="iFoodMenuId" value="<?= $id; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="food_menu.php"/>


                                <?php
                                if ($action == 'Add') {
                                    if (count($allservice_cat_data) <= 1) {
                                        ?>
                                        <input name="iServiceId" type="hidden" class="create-account-input" value="<?php echo $service_cat_list[0]['iServiceId']; ?>"/>
                                    <?php } else { ?>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Service Type<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <select class="form-control" name = 'iServiceId' id="iServiceId" required onchange="changeserviceCategory(this.value)">
                                                    <option value="">Select</option>
                                                    <?php //foreach($db_company as $dbcm) { ?>
                                                    <? for ($i = 0; $i < count($service_cat_list); $i++) { ?>
                                                        <option value = "<?= $service_cat_list[$i]['iServiceId'] ?>"><?= $service_cat_list[$i]['servicename'] ?></option>
                                                    <? } ?>
                                                    <?php //} ?>
                                                </select>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label><?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?><span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select name="iCompanyId" class="form-control" id="iCompanyId" required  onchange="changeDisplayOrderCompany(this.value, '<?php echo $id; ?>')" <?php if ($action == 'Edit') { ?> readonly style="pointer-events:none;"<?php } ?> >
                                            <option value="" >Select <?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?></option>
                                            <?php foreach ($db_company as $dbc) { ?>
                                                <option value="<?php echo $dbc['iCompanyId']; ?>"<? if ($dbc['iCompanyId'] == $iCompanyId) { ?>selected<? } ?>><?php echo $generalobjAdmin->clearName($dbc['vCompany']); ?> - ( <?php echo $generalobjAdmin->clearEmail($dbc['vEmail']); ?> )</option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>

                                <?
                                if ($count_all > 0) {
                                    for ($i = 0; $i < $count_all; $i++) {
                                        $vCode = $db_master[$i]['vCode'];
                                        $vTitle = $db_master[$i]['vTitle'];
                                        $eDefault = $db_master[$i]['eDefault'];

                                        $vValue = 'vMenu_' . $vCode;
                                        //$vValue_desc = 'vMenuDesc_'.$vCode;
                                        $required = ($eDefault == 'Yes') ? 'required' : '';
                                        $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                        ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Menu Category (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>  
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" value="<?= $generalobjAdmin->clearName($$vValue); ?>" <?= $required; ?>> 
                                            </div>
                                            <? if ($vCode == $default_lang && count($db_master) > 1) { ?>
                                                <div class="col-lg-6">
                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode();">Convert To All Language</button>
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <!--                               <div class="row">
                                                                        <div class="col-lg-12">
                                                                          <label>Menu Category Description(<?= $vTitle; ?>)</label>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                          <textarea class="form-control" name="<?= $vValue_desc; ?>" id="<?= $vValue_desc; ?>" ><?= $$vValue_desc; ?></textarea>
                                                                        </div>
                                                                      </div> -->
                                        <?
                                    }
                                }
                                ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Display Order <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6" id="showDisplayOrder001">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <?php if (($action == 'Edit' && $userObj->hasPermission('edit-item-categories')) || ($action == 'Add' && $userObj->hasPermission('create-item-categories'))) { ?>
                                            <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?= $action; ?> Item Category" >
                                            <input type="reset" value="Reset" class="btn btn-default">
                                        <?php } ?>
                                        <a href="food_menu.php" class="btn btn-default back_link">Cancel</a>
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
        <script type='text/javascript' src='../assets/js/jquery-ui.min.js'></script>
        <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
        <script>
                                            function changeDisplayOrderCompany(companyId, foodId)
                                            {
                                                $.ajax({
                                                    type: "POST",
                                                    url: 'ajax_display_order.php',
                                                    data: {iCompanyId: companyId, method: 'getOrder', iFoodMenuId: foodId, iParentId: '0'},
                                                    success: function (response)
                                                    {
                                                        $("#hiddenParent001").hide();
                                                        $("#showDisplayOrder001").html('');
                                                        $("#showDisplayOrder001").html(response);
                                                    }
                                                });

                                            }

                                            $(document).ready(function () {
                                                $('#imageIcon').hide();
                                                changeDisplayOrderCompany('<?php echo $iCompanyId; ?>', '<?php echo $id; ?>');
                                                var action = "<?= $action ?>";
                                                if (action == 'Add') {
                                                    var iServiceIdNew = $("#iServiceId").val();
                                                } else {
                                                    var iServiceIdNew = "<?= $EditServiceIdNew ?>";
                                                }
                                                var servicecounts = '<? echo count($service_cat_list) ?>';
                                                if (servicecounts > '1') {
                                                    changeserviceCategory(iServiceIdNew);
                                                }
                                            });

                                            function changeserviceCategory(iServiceId) {
                                                var iCompanyId = '<?php echo $iCompanyId; ?>';
                                                $.ajax({
                                                    type: "POST",
                                                    url: 'ajax_get_restorantcat_filter.php',
                                                    data: {iServiceIdNew: iServiceId, iCompanyId: iCompanyId},
                                                    success: function (response)
                                                    {
                                                        //console.log(response);
                                                        $("#iCompanyId").html('');
                                                        $("#iCompanyId").html(response);
                                                    }
                                                });
                                            }




                                            $(document).ready(function () {
                                                var referrer;
                                                if ($("#previousLink").val() == "") {
                                                    referrer = document.referrer;
                                                } else {
                                                    referrer = $("#previousLink").val();
                                                }
                                                if (referrer == "") {
                                                    referrer = "food_menu.php";
                                                } else {
                                                    $("#backlink").val(referrer);
                                                }
                                                $(".back_link").attr('href', referrer);
                                                var date = new Date();
                                                var currentMonth = date.getMonth();
                                                var currentDate = date.getDate();
                                                var currentYear = date.getFullYear();
                                            });
                                            function getAllLanguageCode() {
                                                var def_lang = '<?= $default_lang ?>';
                                                var def_lang_name = '<?= $def_lang_name ?>';
                                                var getEnglishText = $('#vMenu_' + def_lang).val();
                                                var error = false;
                                                var msg = '';

                                                if (getEnglishText == '') {
                                                    msg += '<div class="alert alert-danger fade in"><a href="#" class="close" data-dismiss="alert"><icon class="fa fa-close"></icon></a><strong>Please Enter ' + def_lang_name + ' Value</strong></div> <br>';
                                                    error = true;
                                                }

                                                if (error == true) {
                                                    $('#errorMessage').html(msg);
                                                    return false;
                                                } else {
                                                    $('#imageIcon').show();
                                                    $.ajax({
                                                        url: "ajax_get_all_language_translate.php",
                                                        type: "post",
                                                        data: {'englishText': getEnglishText},
                                                        dataType: 'json',
                                                        success: function (response) {
                                                            $.each(response, function (name, Value) {
                                                                var key = name.split('_');
                                                                $('#vMenu_' + key[1]).val(Value);
                                                            });
                                                            $('#imageIcon').hide();
                                                        }
                                                    });
                                                }
                                            }
        </script>
    </body>
    <!-- END BODY-->
</html>
