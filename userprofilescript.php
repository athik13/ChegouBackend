<?php
include_once("common.php");
$generalobj->go_to_home();
$meta_arr = $generalobj->getsettingSeo(6);
$sql = "SELECT * from language_master ORDER BY vTitle ASC ";
$db_lang = $obj->MySQLSelect($sql);
$tbl_name = 'user_profile_master';
$script = "User Profile";
if (isset($_POST['SUBMIT'])) {
    //echo "<pre>";
    $vTitle = isset($_POST['vtitle']) ? $_POST['vtitle'] : '';
    $vSubTitle = isset($_POST['vSubTitle']) ? $_POST['vSubTitle'] : '';
    $vScreenHeading = isset($_POST['vScreenHeading']) ? $_POST['vScreenHeading'] : '';
    $vScreenTitle = isset($_POST['vScreenTitle']) ? $_POST['vScreenTitle'] : '';
    $tDescription = isset($_POST['tDescription']) ? $_POST['tDescription'] : '';
    $vScreenButtonText = isset($_POST['vScreenButtonText']) ? $_POST['vScreenButtonText'] : '';
    $vProfileName = isset($_POST['vProfileName']) ? $_POST['vProfileName'] : '';
    $vShortProfileName = isset($_POST['vShortProfileName']) ? $_POST['vShortProfileName'] : '';
    $eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : '';
    $q = "INSERT INTO ";
    $where = '';
    $vtitleArr = $vSubTitleArr = $vHeadArr = $vScreenTitleArr = $descArr = $buttonTxtArr = $profileNameArr = $profileShortNameArr = array();
    for ($i = 0; $i < count($db_lang); $i++) {
        $vtitleArr["vTitle_" . $db_lang[$i]['vCode']] = $vTitle;
        $vSubTitleArr["vSubTitle_" . $db_lang[$i]['vCode']] = $vSubTitle;
        $vHeadArr["vScreenHeading_" . $db_lang[$i]['vCode']] = $vScreenHeading;
        $vScreenTitleArr["vScreenTitle_" . $db_lang[$i]['vCode']] = $vScreenTitle;
        $descArr["tDescription_" . $db_lang[$i]['vCode']] = $tDescription;
        $buttonTxtArr["vScreenButtonText_" . $db_lang[$i]['vCode']] = $vScreenButtonText;
        $profileNameArr["vProfileName_" . $db_lang[$i]['vCode']] = $vProfileName;
        $profileShortNameArr["vShortProfileName_" . $db_lang[$i]['vCode']] = $vShortProfileName;
    }
    if (count($vtitleArr) > 0) {
        $query = $q . " `" . $tbl_name . "` SET `vTitle` = '" . json_encode($vtitleArr) . "',`vSubTitle` = '" . json_encode($vSubTitleArr) . "',`vScreenHeading` = '" . json_encode($vHeadArr) . "',`vScreenTitle` = '" . json_encode($vScreenTitleArr) . "',`tDescription` = '" . json_encode($descArr) . "',`vScreenButtonText` = '" . json_encode($buttonTxtArr) . "',`vProfileName` = '" . json_encode($profileNameArr) . "',`vShortProfileName` = '" . json_encode($profileShortNameArr) . "',`eStatus` = '" . $eStatus . "'" . $where;
        $obj->sql_query($query);
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
       <!-- <title><?= $COMPANY_NAME ?>| Signup</title>-->
        <title><?php echo $meta_arr['meta_title']; ?></title>
        <meta name="keywords" value="<?= $meta_arr['meta_keyword']; ?>"/>
        <meta name="description" value="<?= $meta_arr['meta_desc']; ?>"/>
        <!-- Default Top Script and css -->
        <?php include_once("top/top_script.php"); ?>
        <link href="assets/css/checkbox.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/radio.css" rel="stylesheet" type="text/css" />
        <?php include_once("top/validation.php"); ?>
        <!-- End: Default Top Script and css-->
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
            <div class="page-contant">
                <div class="page-contant-inner">
                    <form name="frmsignup" id="frmsignup" method="post" action="" class="clearfix">
                        <div class="driver-signup-page">
                            <?php
                            if ($_REQUEST['error']) {
                                ?>
                                <div class="row">
                                    <div class="col-sm-12 alert alert-danger">
                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                        <?= $_REQUEST['var_msg']; ?>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                            <div class="create-account">
                                <h3><?= $langage_lbl['LBL_HEADER_PROFILE_TXT']; ?></h3>
                                <span class="newrow">
                                    <strong>
                                        <label>Profile Short Name<span class="red">*</span></label>
                                        <input name="vShortProfileName" type="text" class="create-account-input create-account-input1" placeholder="Profile Short Name" id="vShortProfileName" required="required"/>
                                    </strong>
                                    <strong>
                                        <label>Profile Name<span class="red">*</span></label>
                                        <input name="vProfileName" type="text" class="create-account-input create-account-input1" placeholder="Profile Name" id="vProfileName" required="required"/>
                                    </strong>
                                </span>
                                <span class="newrow">
                                    <strong>
                                        <label>Title<span class="red">*</span></label>
                                        <input name="vtitle" type="text" class="create-account-input" placeholder="Title" id="vtitle" required="required"/>
                                    </strong>
                                    <strong>
                                        <label>Sub Title<span class="red">*</span></label>
                                        <input name="vSubTitle" type="text" class="create-account-input create-account-input1" placeholder="Sub Title" id="vSubTitle" required="required"/>
                                    </strong>
                                </span> 
                                <span class="newrow">
                                    <strong>
                                        <label>Screen Heading<span class="red">*</span></label>
                                        <input name="vScreenHeading" type="text" class="create-account-input" placeholder="Screen Heading" id="vScreenHeading" required="required"/>
                                    </strong>
                                    <strong>
                                        <label>Screen Title<span class="red">*</span></label>
                                        <input name="vScreenTitle" type="text" class="create-account-input create-account-input1" placeholder="Screen Title" id="vScreenTitle" required="required"/>
                                    </strong>
                                </span> 
                                <span class="newrow">
                                    <strong>
                                        <label>Description<span class="red">*</span></label>
                                        <textarea id="tDescription" name="tDescription" class="create-account-input" placeholder="Description" required="required"></textarea>
                                    </strong>
                                    <strong>
                                        <label>Screen Button Text<span class="red">*</span></label>
                                        <input name="vScreenButtonText" type="text" class="create-account-input create-account-input1" placeholder="Screen Button Text" id="vScreenButtonText" required="required"/>
                                    </strong>
                                </span> 
                                <span class="newrow">
                                    <strong>
                                        <label>Status<span class="red">*</span></label>
                                        <select name="eStatus" id="eStatus" class="create-account-input create-account-input1">
                                            <option value="Active">Active</option>
                                            <option value="Inactive">Inactive</option>
                                        </select>
                                    </strong>
                                </span>
                                <p><button type="submit" class="submit" name="SUBMIT"><?= $langage_lbl['LBL_BTN_SUBMIT_TXT']; ?></button></p>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- footer part -->
                <?php include_once('footer/footer_home.php'); ?>
                <!-- footer part end -->
                <!-- -->
                <div style="clear:both;"></div>
            </div>
            <!-- home page end-->
            <!-- Footer Script -->
            <?php
            include_once('top/footer_script.php');
            $lang = get_langcode($_SESSION['sess_lang']);
            ?>
            <script type="text/javascript" src="assets/js/validation/jquery.validate.min.js" ></script>
            <?php if ($lang != 'en') { ?>
                <!-- <script type="text/javascript" src="assets/js/validation/localization/messages_<?= $lang; ?>.js" ></script> -->
                <? include_once('otherlang_validation.php');?>
            <?php } ?>
            <script type="text/javascript" src="assets/js/validation/additional-methods.js" ></script>
            <!-- End: Footer Script -->
    </body>
</html>