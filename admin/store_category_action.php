<?php
    include_once('../common.php');

    if (!isset($generalobjAdmin)) {
        require_once(TPATH_CLASS . "class.general_admin.php");
        $generalobjAdmin = new General_admin();
    }

    if(!$userObj->hasPermission('view-store-categories')){
        $userObj->redirect();
    }
    
    $tbl_name = 'store_categories';
    $script = 'ManageStoreCategories';
    
    $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
    $success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
    $action = ($id != '') ? 'Edit' : '';
    $backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
    $previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
    
    $eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : '';
    $iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : '';
    $iDaysRange = isset($_POST['iDaysRange']) ? $_POST['iDaysRange'] : '';
    $sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
    $db_master = $obj->MySQLSelect($sql);
    $txtBoxNameArr = array("tCategoryName");
    $lableArr = array("Category Name");
    
    if (isset($_POST['btnsubmit'])) {
        if ($action == "Edit" && !$userObj->hasPermission('edit-store-categories')) {
            $_SESSION['success'] = 3;
            $_SESSION['var_msg'] = 'You do not have permission to update ' . $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'];
            header("Location:store_category.php");
            exit;
        }
        if (SITE_TYPE == 'Demo' || $id == '') {
            $_SESSION['success'] = '2';
            header("Location:store_category.php");
            exit;
        }

        for ($i = 0; $i < count($db_master); $i++) {
            $tCategoryName = $tDescription = "";
            if (isset($_POST['tCategoryName_' . $db_master[$i]['vCode']])) {
                $tCategoryName = $_POST['tCategoryName_' . $db_master[$i]['vCode']];
            }
            if (isset($_POST['tCategoryDescription_' . $db_master[$i]['vCode']])) {
                $tDescription = $_POST['tCategoryDescription_' . $db_master[$i]['vCode']];
            }
            $q = "INSERT INTO ";
            $where = '';

            if ($id != '') {
                $q = "UPDATE ";
                $where = " WHERE `iCategoryId` = '" . $id . "'";
            }
            $vtitleArr["tCategoryName_" . $db_master[$i]['vCode']] = $tCategoryName;
            $descArr["tCategoryDescription_" . $db_master[$i]['vCode']] = $tDescription;
        }
        if ($eStatus == '') {
            $str = ", eStatus = 'Inactive' ";
        } else {
            $str = ", eStatus = 'Active'";
        }

        if (count($vtitleArr) > 0) {
            $jsonTitle = $generalobj->getJsonFromAnArr($vtitleArr);
            $jsonDesc = $generalobj->getJsonFromAnArr($descArr);
            $query = $q . " `" . $tbl_name . "` SET `tCategoryName` = '" . $jsonTitle . "',`tCategoryDescription` = '" . $jsonDesc . "', `iDisplayOrder` = '" . $iDisplayOrder . "', `iDaysRange` = '" . $iDaysRange . "' $str " . $where;
            $obj->sql_query($query);
            $id = ($id != '') ? $id : $obj->GetInsertId();
        }
        // for image upload
        if ($_FILES['tCategoryImage']['name'] != '') {
            $img_path = $tconfig["tsite_upload_images_store_categories_path"];
            
            $temp_gallery = $img_path . '/';
            $image_object = $_FILES['tCategoryImage']['tmp_name'];
            $image_name = $_FILES['tCategoryImage']['name'];

            $filecheck = basename($_FILES['tCategoryImage']['name']);
            $fileextarr = explode(".", $filecheck);
            $ext = strtolower($fileextarr[count($fileextarr) - 1]);
            $flag_error = 0;
            if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
                $flag_error = 1;
                $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png";
            }

            $dataimg = getimagesize($_FILES['tCategoryImage']['tmp_name']);
            $imgwidth = $dataimg[0];
            $imgheight = $dataimg[1];
            if ($imgwidth < 1024) {
                echo"<script>alert('Your Image upload size is less than recommended. Image will look stretched.');</script>";
            }
            $check_file_query = "select tCategoryImage from store_categories where iCategoryId=" . $id;
            $check_file = $obj->sql_query($check_file_query);
            $oldImage = $check_file[0]['tCategoryImage'];
            $check_file = $img_path . '/' . $oldImage;
            if ($oldImage != '' && file_exists($check_file)) {
                @unlink($img_path . '/' . $oldImage);
            }



            if ($flag_error == 1) {

                $_SESSION['success'] = '3';
                $_SESSION['var_msg'] = $var_msg;

                header("location:store_category.php");
            } else {
                // echo "here"; exit;
                $Photo_Gallery_folder = $img_path . '/' . $iCategoryId . '/';
                if (!is_dir($Photo_Gallery_folder)) {
                    mkdir($Photo_Gallery_folder, 0777);
                }
                $img1 = $generalobj->fileupload($Photo_Gallery_folder, $image_object, $image_name, '', 'jpg,png,gif,jpeg');
                $tCategoryImage = $img1[0];

                $sql1 = "UPDATE store_categories SET `tCategoryImage` = '" . $tCategoryImage . "' WHERE `iCategoryId` = '" . $id . "'";
                $obj->sql_query($sql1);
            }
        }
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        $_SESSION['success'] = "1";
        header("location:" . $backlink);
        exit;
    }
    // for Edit
    $userEditDataArr = array();
    if ($action == 'Edit') {
        $sql = "SELECT * FROM " . $tbl_name . " WHERE iCategoryId = '" . $id . "'";
        $db_data = $obj->MySQLSelect($sql);
    
        if (count($db_data) > 0) {
            $tCategoryName = json_decode($db_data[0]['tCategoryName'], true);
            foreach ($tCategoryName as $key => $value) {
                $userEditDataArr[$key] = $value;
            }
            $tDescription = json_decode($db_data[0]['tCategoryDescription'], true);
            foreach ($tDescription as $key4 => $value4) {
                $userEditDataArr[$key4] = $value4;
            }
            $tCategoryImage = $db_data[0]['tCategoryImage'];
            $eStatus = $db_data[0]['eStatus'];
            $iDisplayOrder = $db_data[0]['iDisplayOrder'];
            $iDaysRange = ($db_data[0]['iDaysRange'] != "") ? $db_data[0]['iDaysRange'] : 30;
        }

        $display_order = array();
        $sqlall = "SELECT iDisplayOrder FROM " . $tbl_name;
        $db_data_all = $obj->MySQLSelect($sqlall);
        foreach ($db_data_all as $d) {
            $display_order[] = $d['iDisplayOrder'];
        }
        $max_usage_order = max($display_order) + 1;
    }
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
                <title><?= $SITE_NAME ?> | Store Category <?= $action; ?></title>
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
                                    <h2> <?= $action; ?> Store Category </h2>
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
                                                $descVal = 'tCategoryDescription_' . $vCode;
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
                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tCategoryDescription');">Convert To All Language</button>
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
                                        <?php if($id == 4) { ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Select Days<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <select class="form-control" name='iDaysRange' id="iDaysRange" >
                                                <?php for ($i = 1; $i <= 60; $i++) { ?>
                                                    <option value="<?= $i ?>" <?= ($i == $iDaysRange) ? 'selected' : '' ?>><?= $i ?> Day<?= ($i > 1) ? 's' : '' ?></option>
                                                <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <?php } ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Image</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <? if ($tCategoryImage != '') { ?>
                                                    <img src="<?= $tconfig['tsite_upload_images_store_categories'] . "/" . $tCategoryImage; ?>" style="width:100px;height:100px;">
                                                <? } ?>
                                                <input type="file" class="form-control" name="tCategoryImage" id="tCategoryImage" value="<?= $tCategoryImage; ?>">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Status</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="make-switch" data-on="success" data-off="warning">
                                                    <input type="checkbox" name="eStatus" id="eStatus" <?= ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?> />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <?php if ($userObj->hasRole(1) || ($action == "Edit" && $userObj->hasPermission('edit-store-categories'))) { ?>
                                                <input type="submit" class="btn btn-default" name="btnsubmit" id="btnsubmit" value="<?= $action; ?> Store Category" >
                                                <input type="reset" value="Reset" class="btn btn-default">
                                                <?php } ?>
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
                    })
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