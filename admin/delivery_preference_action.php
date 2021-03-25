<?php
    include_once('../common.php');

    if (!isset($generalobjAdmin)) {
        require_once(TPATH_CLASS . "class.general_admin.php");
        $generalobjAdmin = new General_admin();
    }

    if(!isDeliveryPreferenceEnable()){
        $userObj->redirect();
    }
    
    $tbl_name = 'delivery_preferences';
    $script = 'DeliveryPreferences';
    
    $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
    $success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
    $action = ($id != '') ? 'Edit' : 'Add';
    $backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
    $previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
    
    $eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : '';
    $iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : '';
    $eImageUpload = isset($_POST['eImageUpload']) ? $_POST['eImageUpload'] : 'No';
    $ePreferenceFor = isset($_POST['ePreferenceFor']) ? $_POST['ePreferenceFor'] : '';
    $sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
    $db_master = $obj->MySQLSelect($sql);
    $txtBoxNameArr = array("tTitle");
    $lableArr = array("Title");
    
    if (isset($_POST['btnsubmit'])) {
        if ($action == "Add" && !$userObj->hasPermission('create-delivery-preference')) {
            $_SESSION['success'] = 3;
            $_SESSION['var_msg'] = 'You do not have permission to create '. $langage_lbl_admin['LBL_DELIVERY_PREF'];
            header("Location:" . $redirectUrl);

            exit;
        }

        if ($action == "Edit" && !$userObj->hasPermission('edit-delivery-preference')) {
            $_SESSION['success'] = 3;
            $_SESSION['var_msg'] = 'You do not have permission to update ' . $langage_lbl_admin['LBL_DELIVERY_PREF'];
            header("Location:delivery_preferences.php");
            exit;
        }
        if (SITE_TYPE == 'Demo') {
            $_SESSION['success'] = '2';
            header("Location:delivery_preferences.php");
            exit;
        }

        for ($i = 0; $i < count($db_master); $i++) {
            $tCategoryName = $tDescription = "";
            if (isset($_POST['tTitle_' . $db_master[$i]['vCode']])) {
                $tCategoryName = $_POST['tTitle_' . $db_master[$i]['vCode']];
            }
            if (isset($_POST['tDescription_' . $db_master[$i]['vCode']])) {
                $tDescription = $_POST['tDescription_' . $db_master[$i]['vCode']];
            }
            $q = "INSERT INTO ";
            $where = '';

            if ($id != '') {
                $q = "UPDATE ";
                $where = " WHERE `iPreferenceId` = '" . $id . "'";
            }
            $vtitleArr["tTitle_" . $db_master[$i]['vCode']] = $tCategoryName;
            $descArr["tDescription_" . $db_master[$i]['vCode']] = $tDescription;
        }
        
        $str = '';
        /*if ($eImageUpload == '') {
            $str .= ", eImageUpload = 'No' ";
        } else {
            $str .= ", eImageUpload = '".$eImageUpload."'";
        }*/
        $str .= ", eImageUpload = 'No' ";
        
        if ($eStatus == '') {
            $str .= ", eStatus = 'Inactive' ";
        } else {
            $str .= ", eStatus = 'Active'";
        }

        if (count($vtitleArr) > 0) {
            $jsonTitle = $generalobj->getJsonFromAnArr($vtitleArr);
            $jsonDesc = $generalobj->getJsonFromAnArr($descArr);
            $query = $q . " `" . $tbl_name . "` SET `tTitle` = '" . $jsonTitle . "',`tDescription` = '" . $jsonDesc . "', `iDisplayOrder` = '" . $iDisplayOrder . "', `ePreferenceFor` = '" . $ePreferenceFor . "' $str " . $where;
            $obj->sql_query($query);
            $id = ($id != '') ? $id : $obj->GetInsertId();
        }
        
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        $_SESSION['success'] = "1";
        header("location:" . $backlink);
        exit;
    }
    // for Edit
    $userEditDataArr = array();
    if ($action == 'Edit') {
        $sql = "SELECT * FROM " . $tbl_name . " WHERE iPreferenceId = '" . $id . "'";
        $db_data = $obj->MySQLSelect($sql);
    
        if (count($db_data) > 0) {
            $tTitle = json_decode($db_data[0]['tTitle'], true);
            foreach ($tTitle as $key => $value) {
                $userEditDataArr[$key] = $value;
            }
            $tDescription = json_decode($db_data[0]['tDescription'], true);
            foreach ($tDescription as $key4 => $value4) {
                $userEditDataArr[$key4] = $value4;
            }
            $eImageUpload = $db_data[0]['eImageUpload'];
            $eStatus = $db_data[0]['eStatus'];
            $iDisplayOrder = $db_data[0]['iDisplayOrder'];
            $ePreferenceFor = $db_data[0]['ePreferenceFor'];
            $eContactLess = $db_data[0]['eContactLess'];
        }
    }
    $display_order = array();
    $sqlall = "SELECT iDisplayOrder FROM " . $tbl_name;
    $db_data_all = $obj->MySQLSelect($sqlall);
    foreach ($db_data_all as $d) {
        $display_order[] = $d['iDisplayOrder'];
    }
    $max_usage_order = max($display_order) + 1;
    ?>
<!DOCTYPE html>
<!--[if IE 8]> 
<html lang="en" class="ie8">
    <![endif]-->
    <!--[if IE 9]> 
    <html lang="en" class="ie9">
        <![endif]-->
        <!--[if !IE]><!--> 
        <html lang="en">
            <!--<![endif]-->
            <!-- BEGIN HEAD-->
            <head>
                <meta charset="UTF-8" />
                <title><?= $SITE_NAME ?> |  <?=  $langage_lbl_admin['LBL_DELIVERY_PREF'].' '.$action; ?></title>
                <meta content="width=device-width, initial-scale=1.0" name="viewport" />
                <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
                <? include_once('global_files.php'); ?>
                <!-- On OFF switch -->
                <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
                <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
                <!-- PAGE LEVEL STYLES -->
                <link rel="stylesheet" href="../assets/plugins/Font-Awesome/css/font-awesome.css" />
                <link rel="stylesheet" href="../assets/plugins/wysihtml5/dist/bootstrap-wysihtml5-0.0.2.css" />
                <link rel="stylesheet" href="../assets/css/Markdown.Editor.hack.css" />
                <link rel="stylesheet" href="../assets/plugins/CLEditor1_4_3/jquery.cleditor.css" />
                <link rel="stylesheet" href="../assets/css/jquery.cleditor-hack.css" />
                <link rel="stylesheet" href="../assets/css/bootstrap-wysihtml5-hack.css" />
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
                    <?
                        include_once('header.php');
                        include_once('left_menu.php');
                        ?>
                    <!--PAGE CONTENT -->
                    <div id="content">
                        <div class="inner">
                            <div class="row">
                                <div class="col-lg-12">
                                    <h2> <?= $action.' '. $langage_lbl_admin['LBL_DELIVERY_PREF']; ?></h2>
                                    <a href="javascript:void(0);" class="back_link">
                                    <input type="button" value="Back to Listing" class="add-btn">
                                    </a>
                                </div>
                            </div>
                            <hr />
                            <div class="body-div">
                                <div class="form-group">
                                    <? if ($success == 1) { ?>
                                        <div class="alert alert-success alert-dismissable msgs_hide">
                                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                            <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                                        </div>
                                        <br/>
                                    <? } elseif ($success == 2) { ?>
                                        <div class="alert alert-danger alert-dismissable ">
                                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                            <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                        </div>
                                        <br/>
                                    <? } else if ($success == 3) { ?>
                                        <div class="alert alert-danger alert-dismissable">
                                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                            <?php echo $_REQUEST['varmsg']; ?> 
                                        </div>
                                        <br/> 
                                    <? } ?>
                                    <? if (isset($_REQUEST['var_msg']) && $_REQUEST['var_msg'] != Null) { ?>
                                        <div class="alert alert-danger alert-dismissable">
                                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                            Record  Not Updated .
                                        </div>
                                        <br/>
                                    <? } ?>                   
                                    <form id="_vehicleType_form" name="_vehicleType_form" method="post" action="" enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="<?= $id; ?>"/>
                                        <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                        <input type="hidden" name="backlink" id="backlink" value="store_category.php"/>
                                        <div class="row">
                                            <div class="col-lg-12" id="errorMessage"></div>
                                        </div>
                                                                                
                                        <?php if (count($db_master) > 0) {
                                            for ($i = 0; $i < count($db_master); $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $descVal = 'tDescription_' . $vCode;
                                                $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                for ($l = 0; $l < count($txtBoxNameArr); $l++) {
                                                    $lableText = $lableArr[$l];
                                                    $lableName = $txtBoxNameArr[$l] . '_' . $vCode;
                                                    $required = ($eDefault == 'Yes') ? 'required' : '';
                                                    ?>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <label><?= $lableText; ?> (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <!-- <?= $lableName; ?> -->
                                                        <input type="text" class="form-control" name="<?= $lableName; ?>" id="<?= $lableName; ?>" value="<?= $userEditDataArr[$lableName]; ?>" placeholder="<?= $vTitle; ?> Value" <?= $required; ?>>
                                                    </div>
                                                    <? if ($vCode == $default_lang && count($db_master) > 1) { ?>
                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('<? echo $txtBoxNameArr[$l]; ?>');">Convert To All Language</button>
                                                    <?php } ?>
                                                </div>
                                            <?php } ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Description (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <textarea class="form-control" name="<?= $descVal; ?>" id="<?= $descVal; ?>" placeholder="<?= $vTitle; ?> Value" ><?= $userEditDataArr[$descVal]; ?></textarea>
                                                    <div class="desc_counter pull-right" style="margin-top: 5px">100/100</div>
                                                </div>
                                                <? if ($vCode == $default_lang && count($db_master) > 1) { ?>
                                                <div class="col-lg-6">
                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tDescription');">Convert To All Language</button>
                                                </div>
                                                <?php } ?>
                                            </div>
                                        <?php }
                                            }
                                            ?>
                                            <div class="row">
                                            <div class="col-lg-12">
                                                <label>Display Order<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">

                                                <select class="form-control" name='iDisplayOrder' id="iDisplayOrder" >
                                                <?php
                                                    $html = '';
                                                    for ($i = 1; $i <= $max_usage_order; $i++) {
                                                        if ($action == "Add") {
                                                            if ($i == $max_usage_order) {
                                                                $selected = " selected";
                                                            } else {
                                                                $selected = " ";
                                                            }
                                                        } else {
                                                            if ($iDisplayOrder == $i) {
                                                                $selected = " selected";
                                                            } else {
                                                                $selected = " ";
                                                            }
                                                        }
                                                        $html .= '<option value = "' . $i . '" ' . $selected . '>' . $i . '</option>';
                                                    }
                                                    $html .= '</select>';
                                                    echo $html;

                                                    ?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Deliver Preference For</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <select class="form-control" name="ePreferenceFor" id="ePreferenceFor">
                                                    <option value="Store" <?= ($ePreferenceFor == "Store") ? "selected" : "" ?>>Store</option>
                                                    <option value="Provider" <?= ($ePreferenceFor == "Provider") ? "selected" : "" ?>>Provider</option>
                                                </select>
                                            </div>
                                        </div>
                                        <?php /*if($action == "Edit" && $eContactLess == "No") { ?>
                                        <div class="row" id="image_upload_pref" <?php if($ePreferenceFor != "Provider") { ?> style="display: none;" <?php } ?>>
                                            <div class="col-lg-12">
                                                <label>Image Upload</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="make-switch" data-on="success" data-off="warning">
                                                    <input type="checkbox" name="eImageUpload" id="eImageUpload" <?= ($eImageUpload == 'Yes') ? 'checked' : ''; ?> value="Yes" />
                                                </div>
                                            </div>
                                        </div>
                                        <?php }*/ ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Status</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="make-switch" data-on="success" data-off="warning">
                                                    <input type="checkbox" name="eStatus" id="eStatus" <?= ($eStatus == 'Active') ? 'checked' : ''; ?> value="Active" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <input type="submit" class="btn btn-default" name="btnsubmit" id="btnsubmit" value="<?= $action; ?> Delivery Preference" >
                                                <input type="reset" value="Reset" class="btn btn-default">
                                                <a href="store_category.php" class="btn btn-default back_link">Cancel</a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                </div>
                <!--END PAGE CONTENT -->
                <!--END MAIN WRAPPER -->
                <div class="row loding-action" id="imageIcon" style="display:none;">
                    <div align="center">                                                                       
                        <img src="default.gif">                                                              
                        <span>Language Translation is in Process. Please Wait...</span>                       
                    </div>
                </div>
                <?
                    include_once('footer.php');
                    ?>  
                <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
                <script>
                    $('[data-toggle="tooltip"]').tooltip();
                    var successMSG1 = '<?php echo $success; ?>';
                    if (successMSG1 != '') {
                        setTimeout(function () {
                            $(".msgs_hide").hide(1000)
                        }, 5000);
                    }
                </script>
                <!--For Faretype End--> 
                <script type="text/javascript" language="javascript">
                    function getAllLanguageCode(textBoxId) {
                        var def_lang = '<?= $default_lang ?>';
                        var def_lang_name = '<?= $def_lang_name ?>';
                        var getEnglishText = $('#' + textBoxId + '_' + def_lang).val();
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
                                        $('#' + textBoxId + '_' + key[1]).val(Value);
                                    });
                                    $('#imageIcon').hide();
                                }
                            });
                        }
                    }
                    
                    $(document).ready(function () {
                        var referrer;
                        if ($("#previousLink").val() == "") {
                            referrer = document.referrer;
                        } else {
                            referrer = $("#previousLink").val();
                        }
                        if (referrer == "") {
                            referrer = "store_category.php";
                        } else {
                            $("#backlink").val(referrer);
                        }
                        $(".back_link").attr('href', referrer);
                    });

                    $('textarea').keyup(function(e) {
                        var tval = $(this).val(),
                            tlength = tval.length,
                            set = 100,
                            remain = parseInt(set - tlength);                            
                        if(tlength > 0)
                        {
                            $(this).closest('.col-lg-6').find('.desc_counter').text(remain + "/100");
                            
                            if (remain <= 0) {
                                $(this).val((tval).substring(0, tlength-1));
                                $(this).closest('.col-lg-6').find('.desc_counter').text("0/100");
                                return false;
                            }
                        }
                        else{
                            $(this).closest('.col-lg-6').find('.desc_counter').text("100/100");
                            return false;
                        }
                    });

                    $('#ePreferenceFor').change(function() {
                        if($(this).val() == "Provider")
                        {
                            $('#image_upload_pref').show();
                        }
                        else{
                            $('#image_upload_pref').hide();
                            $('#eImageUpload').prop('checked', false);
                            $('#image_upload_pref').find('.switch-animate').removeClass('switch-on').addClass('switch-off');
                        }
                    });
                </script>
                <!--END MAIN WRAPPER -->
                <!-- GLOBAL SCRIPTS -->
                <!--<script src="../assets/plugins/jquery-2.0.3.min.js"></script>-->
                <script src="../assets/plugins/bootstrap/js/bootstrap.min.js"></script>
                <script src="../assets/plugins/modernizr-2.6.2-respond-1.1.0.min.js"></script>
                <!-- END GLOBAL SCRIPTS -->
                <!-- PAGE LEVEL SCRIPTS -->
                <script src="../assets/plugins/wysihtml5/lib/js/wysihtml5-0.3.0.js"></script>
                <script src="../assets/plugins/bootstrap-wysihtml5-hack.js"></script>
                <script src="../assets/plugins/CLEditor1_4_3/jquery.cleditor.min.js"></script>
                <script src="../assets/plugins/pagedown/Markdown.Converter.js"></script>
                <script src="../assets/plugins/pagedown/Markdown.Sanitizer.js"></script>
                <script src="../assets/plugins/Markdown.Editor-hack.js"></script>
                <script src="../assets/js/editorInit.js"></script>
                <script>
                    $(function () {
                        formWysiwyg();
                    });
                </script>
            </body>
            <!-- END BODY-->
        </html>