<?php
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}

if ($default_lang == "") {
    $default_lang = "EN";
}
$tbl_name = 'app_screen_language_label';
$script = 'Manage App Screen';
$currnt_file='screen_label.php';


//GET INFo
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$usedCount = isset($_REQUEST['usedcount']) ? $_REQUEST['usedcount'] : 0;
$goback = 0;
$message_print_id = $id;
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : "";
$action = ($id != '') ? 'Edit' : 'Add';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

//POST INFo
$vLabel = isset($_POST['vLabel']) ? $_POST['vLabel'] : '';
$lPage_id = isset($_POST['lPage_id']) ? $_POST['lPage_id'] : '';
$vValue = isset($_POST['vValue']) ? $_POST['vValue'] : '';
$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : '';

$select_order = $obj->MySQLSelect("SELECT count(LanguageLabelId) AS iDispOrder FROM " . $tbl_name);
$iDispOrder = isset($select_order[0]['iDisplayOrder']) ? $select_order[0]['iDisplayOrder'] : 0;
$iDispOrder = $iDispOrder + 1; // Maximum order number
$iDispOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : $iDispOrder;
if (isset($_REQUEST['goback'])) {
    $goback = $_REQUEST['goback'];
}

$redirectUrl = $tconfig["tsite_url_main_admin"] . $currnt_file;


if (isset($_POST['btnsubmit'])) {

    if ($action == "Add" && !$userObj->hasPermission('create-app-screen-label')) {

        $_SESSION['success'] = 3;

        $_SESSION['var_msg'] = 'You do not have permission to create app screen.';

        header("Location:" . $redirectUrl);

        exit;
    }

    if ($action == "Edit" && !$userObj->hasPermission('edit-app-screen-label')) {

        $_SESSION['success'] = 3;

        $_SESSION['var_msg'] = 'You do not have permission to update app Screen.';

        header("Location:" . $redirectUrl);

        exit;
    }

    if (SITE_TYPE == 'Demo') {

        header("Location:$currnt_file?id=" . $id . "&usedcount=" . $usedCount . "&usedcount=" . $usedCount . "&success=2");

        exit;
    }

    $q = "INSERT INTO ";

    $where = $whereDispOrder = '';

    if ($id != '') {

        $q = "UPDATE ";
        $where = " WHERE `LanguageLabelId` = '" . $id . "'";
        $whereDispOrder = " AND `LanguageLabelId` != '" . $id . "'";
    }
  
    $query = $q . " `" . $tbl_name . "` SET `vLabel` = '" . $vLabel . "',`lPage_id` = '" . $lPage_id . "',`vValue` = '" . $vValue . "',`eStatus` = '" . $eStatus . "',`vCode` = '" . $default_lang . "'" . $where;

    $obj->sql_query($query);

    $id = ($id != '') ? $id : $obj->GetInsertId();
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
$db_data = array();

if ($action == 'Edit') {

    $sql = "SELECT * FROM " . $tbl_name . " WHERE LanguageLabelId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    $vLabel = $id;

    if (count($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $eStatus = $value['eStatus'];
            $vLabel = $value['vLabel'];
            $lPage_id = $value['lPage_id'];

            $vValue =$value['vValue'];

        }
    }

}
$select =  "SELECT * FROM `app_screen_master` where eStatus= 'Active' ";
$select_boxinfo = $obj->MySQLSelect($select);
    
$required_msg = '<span class="red"> *</span>';
?>

<!DOCTYPE html>

<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->

<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->

<!--[if !IE]><!--> 
<html lang="en"> <!--<![endif]-->

    <!-- BEGIN HEAD-->

    <head>

        <meta charset="UTF-8" />

        <title>Admin | app screen <?= $action; ?></title>

        <meta content="width=device-width, initial-scale=1.0" name="viewport" />

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

                            <h2>Screen Label</h2>

                            <a href="<?php echo $redirectUrl; ?>">

                                <input type="button" value="Back to Listing" class="add-btn">

                            </a>

                        </div>

                    </div>

                    <hr />

                    <div class="body-div">

                        <div class="form-group">

                            <? if ($success == 1) { ?>

                                <div class="alert alert-success alert-dismissable">

                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>

                                    <?= $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>

                                </div><br/>

                            <? } elseif ($success == 2) { ?>

                                <div class="alert alert-danger alert-dismissable">

                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                </div><br/>

                                <?
                            }

                            if (isset($_REQUEST['var_msg']) && $_REQUEST['var_msg'] != "") {
                                ?>

                                <div class="alert alert-danger alert-dismissable">

                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>

                                    <?php echo $_REQUEST['var_msg']; ?>

                                </div><br/>  

                            <?php }
                            ?>



                            <div id="price1" ></div>

                            <div id="price" ></div>

                            <form id="vtype" method="post" action="" enctype="multipart/form-data">

                                <input type="hidden" name="id" value="<?= $id; ?>"/> 

                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>

                                <input type="hidden" name="backlink" id="backlink" value="<?=$currnt_file?>"/>

                                 <div class="row">

                                    <div class="col-lg-12">

                                        <label>App Screen Page Name</label>

                                    </div>

                                    <div class="col-lg-6">

                                      

                                           <select  class="form-control" name = 'lPage_id'  id= 'lPage_id' required>
                                             <option value="0" <?php if($db_data[0]['lPage_id']=="0"){ ?>selected <?php } ?>>Page Name</option>
                                             <?php foreach ($select_boxinfo as $sinfo) { ?>
                                                <option value="<?= $sinfo['lPage_id'] ?>" 
                                                    <? if (isset($db_data[0]['lPage_id']) && $db_data[0]['lPage_id'] == $sinfo['lPage_id']) { ?>selected<? } ?>    
                                                    ><?= $sinfo['vScreenName'] ?></option>    
                                             <?php }  ?>
                                            


                                            </select>   


                                    </div>

                                </div>


                                <div class="row">

                                    <div class="col-lg-12">

                                        <label>Code <?php echo $required_msg; ?></label>

                                    </div>

                                    <div class="col-lg-6">

                                        <input type="text" class="form-control" name="vLabel" id="vLabel" value="<?= $vLabel; ?>" placeholder="Screen Code" required="">

                                    </div>

                                </div>

                                <div class="row Banner imagebox">

                                    <div class="col-lg-12">

                                        <label>Value In English Language <?php echo $required_msg; ?></label>

                                    </div>

                                    <div class="col-lg-6">

                                          <input type="text" class="form-control" name="vValue" id="vValue" value="<?= $vValue; ?>" placeholder="Screen Value" required="">

                                       

                                    </div>

                                </div>
                            <?php if ($userObj->hasPermission('update-status-app-screen-label')) { ?>

                                    <div class="row">

                                        <div class="col-lg-12">

                                            <label>Status<span class="red"> *</span></label>

                                        </div>

                                        <div class="col-lg-6">

                                            <select  class="form-control" name = 'eStatus'  id= 'eStatus' required>                                   

                                                <option value="Active" <? if (isset($db_data[0]['eStatus']) && $db_data[0]['eStatus'] == "Active") { ?>selected<? } ?>>Active</option>

                                                <option value="Inactive" <? if (isset($db_data[0]['eStatus']) && $db_data[0]['eStatus'] == "Inactive") { ?>selected<? } ?>>Inactive</option>                                                      

                                                </option>                                                    

                                            </select>

                                        </div>

                                    </div>

                                <?php } ?>

                                <div class="row">

                                    <div class="col-lg-12">

                                <?php if (($action == 'Edit' && $userObj->hasPermission('edit-app-screen-label')) || ($action == 'Add' && $userObj->hasPermission('create-app-screen-label'))) { ?>

                                            <input type="submit" class="save btn-info" name="btnsubmit" id="btnsubmit" value="<?= $action; ?> App Screen">

                                            <input type="reset" value="Reset" class="btn btn-default">

                                        <?php } ?>

                                        <a href="app_screen.php" class="btn btn-default back_link">Cancel</a>

                                    </div>

                                </div>

                            </form>

                        </div>

                    </div>

                    <div style="clear:both;"></div>

                </div>

            </div>

            <!--END PAGE CONTENT -->

        </div>

        <!--END MAIN WRAPPER -->

        <div class="row loding-action" id="imageIcon" style="display:none;">

            <div align="center">                                                                       

                <img src="default.gif">                                                              

                <span>Language Translation is in Process. Please Wait...</span>                       

            </div>                                                                                 

        </div>

        <? include_once('footer.php'); ?>

        <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>

        <script type='text/javascript' src='../assets/js/jquery-ui.min.js'></script>

        <script>

                                            $(document).ready(function () {

                                                $('#dStartDate').datepicker({

                                                    dateFormat: "yy-mm-dd",

                                                    minDate: 'today'

                                                }).on('dp.change', function (e) {

                                                    $('#dStartDate').data("DateTimePicker").minDate(moment().add(5, 'm'))

                                                });

                                                $('#dExpiryDate').datepicker({

                                                    dateFormat: "yy-mm-dd",

                                                    minDate: $('#dStartDate').val()

                                                }).on('dp.change', function (e) {

                                                    $('#dExpiryDate').data("DateTimePicker").minDate(moment().add(5, 'm'))

                                                });

                                                var referrer;

<?php if ($goback == 1) { ?>

                                                    alert('<?php echo $var_msg; ?>');

                                                    history.go(-1);

<?php } ?>

                                                if ($("#previousLink").val() == "") {

                                                    referrer = document.referrer;

                                                    //alert(referrer);

                                                } else {

                                                    referrer = $("#previousLink").val();

                                                }

                                                if (referrer == "") {

                                                    referrer = "vehicles.php";

                                                } else {

                                                    $("#backlink").val(referrer);

                                                }

                                                $(".back_link").attr('href', referrer);

                                                showhidedate('<?php echo $eValidityType; ?>');

                                                showhideimpression('<?php echo $eImpression; ?>');

                                            });

                                            function showhidedate(val) {

                                                if (val == "Custom") {

                                                    document.getElementById("date1").style.display = '';

                                                    document.getElementById("date2").style.display = '';

                                                    $("#dStartDate").attr("required", "true");

                                                    $("#dExpiryDate").attr("required", "true");

                                                } else {

                                                    document.getElementById("date1").style.display = 'none';

                                                    document.getElementById("date2").style.display = 'none';

                                                    document.getElementById("dStartDate").required = false;

                                                    document.getElementById("dExpiryDate").required = false;

                                                }

                                            }

                                            function showhideimpression(val) {

                                                if (val == "Limited") {

                                                    document.getElementById("impression").style.display = '';

                                                    $("#iImpression").attr("required", "true");

                                                } else {

                                                    document.getElementById("impression").style.display = 'none';

                                                    document.getElementById("iImpression").required = false;

                                                }

                                            }

        </script>

    </body>

    <!-- END BODY-->

</html>