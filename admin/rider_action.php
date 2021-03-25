<?php
include_once('../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
//$generalobjAdmin->check_member_login();

require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();

$sql = "SELECT * FROM country WHERE eStatus='Active' ORDER BY vCountry ASC";
// $sql = "SELECT vCountryCode,vCountry FROM country WHERE eStatus='Active' AND iCountryId='101' ORDER BY vCountry ASC";
$db_country = $obj->MySQLSelect($sql);

//For Currency
$sql = "SELECT * FROM currency WHERE eStatus='Active' ORDER BY vName ASC";
$db_currency = $obj->MySQLSelect($sql);

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$script = 'Rider';
$tbl_name = 'register_user';

$sql = "SELECT * FROM language_master WHERE eStatus = 'Active' ORDER BY vTitle ASC ";
$db_lang = $obj->MySQLSelect($sql);

$vName = isset($_POST['vName']) ? $_POST['vName'] : '';
$vLastName = isset($_POST['vLastName']) ? $_POST['vLastName'] : '';
$vEmail = isset($_POST['vEmail']) ? strtolower($_POST['vEmail']) : '';
$vPassword = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
$vPhone = isset($_POST['vPhone']) ? $_POST['vPhone'] : '';
$vPhoneCode = isset($_POST['vPhoneCode']) ? $_POST['vPhoneCode'] : '';
$vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : $DEFAULT_COUNTRY_CODE_WEB;
$vCity = isset($_POST['vCity']) ? $_POST['vCity'] : '';
$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'Inactive';
$vInviteCode = isset($_POST['vInviteCode']) ? $_POST['vInviteCode'] : '';
$oldImage = isset($_POST['oldImage']) ? $_POST['oldImage'] : '';
$vCurrencyPassenger = isset($_POST['vCurrencyPassenger']) ? $_POST['vCurrencyPassenger'] : '';
$vLang = isset($_POST['vLang']) ? $_POST['vLang'] : '';
$vPass = ($vPassword != "") ? $generalobj->encrypt_bycrypt($vPassword) : '';
$eGender = isset($_POST['eGender']) ? $_POST['eGender'] : '';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$eReftype = "Rider";
if (isset($_POST['submit'])) {
    if ($action == "Add" && !$userObj->hasPermission('create-users')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create ' . strtolower($langage_lbl_admin['LBL_RIDERS_ADMIN']) . '.';
        header("Location:rider.php");
        exit;
    }

    if ($action == "Edit" && !$userObj->hasPermission('edit-users')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update ' . strtolower($langage_lbl_admin['LBL_RIDERS_ADMIN']) . '.';
        header("Location:rider.php");
        exit;
    }


    if (!empty($id) && SITE_TYPE == 'Demo') {
        $_SESSION['success'] = 2;
        header("Location:rider.php?id=" . $id);
        exit;
    }

    require_once("library/validation.class.php");
    $validobj = new validation();
    $validobj->add_fields($_POST['vName'], 'req', ' Name is required.');
    $validobj->add_fields($_POST['vLastName'], 'req', 'Last name is required.');
    $validobj->add_fields(strtolower($_POST['vEmail']), 'req', 'Email address is required.');
    $validobj->add_fields(strtolower($_POST['vEmail']), 'email', '* Please enter valid Email Address.');
    if ($action == "Add") {
        $validobj->add_fields($_POST['vPassword'], 'req', 'Password is required.');
    }
    $validobj->add_fields($_POST['vPhone'], 'req', 'Phone number is required.');
    $validobj->add_fields($_POST['vCountry'], 'req', 'Country is required.');
    $validobj->add_fields($_POST['vLang'], 'req', 'Language is required.');
    $validobj->add_fields($_POST['vCurrencyPassenger'], 'req', 'Currency is required.');
    $error = $validobj->validate();

//Other Validations
//comment by Rs bcz check email,phone validation using checkMemberDataInfo function
    /* if ($vEmail != "") {
      if ($id != "") {
      $msg1 = $generalobj->checkDuplicateAdminNew('iUserId', $tbl_name, Array('vEmail'), $id, "");
      } else {
      $msg1 = $generalobj->checkDuplicateAdminNew('vEmail', $tbl_name, Array('vEmail'), "", "");
      }

      if ($msg1 == 1) {
      $error .= '* Email Address is already exists.<br>';
      }
      }
      /* Added by SP for phone unique validation start */

    /* if ($vPhone != "") {
      if ($id != "") {
      $msg1 = $generalobj->checkDuplicateAdminNew('iUserId', $tbl_name, Array('vPhone'), $id, "");
      } else {
      $msg1 = $generalobj->checkDuplicateAdminNew('vPhone', $tbl_name, Array('vPhone'), "", "");
      }
      if ($msg1 == 1) {
      //$error .= '* Phone number is already exists.<br>';
      }
      } */
    /* Added by SP for phone unique validation end */
    /* 06-09-219 check email,phone validation using member function added by Rs start(check phone number using country) */
    $eSystem = "";
    $checEmailExist = $generalobj->checkMemberDataInfo($vEmail, "", 'RIDER', $vCountry, $id, $eSystem);
    if ($checEmailExist['status'] == 0) {
        $error .= '* Email Address is already exists.<br>';
    } else if ($checEmailExist['status'] == 2) {
        $error .= $langage_lbl['LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT'];
    }
    $checPhoneExist = $generalobj->checkMemberDataInfo($vPhone, "", 'RIDER', $vCountry, $id, $eSystem);

    if ($checPhoneExist['status'] == 0) {
        $error .= '* Phone number already exists.<br>';
    } else if ($checPhoneExist['status'] == 2) {
        $error .= $langage_lbl['LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT'];
    }
    /* 06-09-219 check phone validation end */
    $error .= $validobj->validateFileType($_FILES['vImgName'], 'jpg,jpeg,png,gif,bmp', '* Image file is not valid.');

    if ($error) {
        $success = 3;
        $newError = $error;
    } else {
        $csql = "SELECT eZeroAllowed,vCountryCode FROM `country` WHERE vPhoneCode = '" . $vPhoneCode . "'";
        $CountryData = $obj->MySQLSelect($csql);
        $eZeroAllowed = $CountryData[0]['eZeroAllowed'];
        if ($eZeroAllowed == 'Yes') {
            $vPhone = $vPhone;
        } else {
            $first = substr($vPhone, 0, 1);
            if ($first == "0") {
                $vPhone = substr($vPhone, 1);
            }
        }
        $vRefCodePara = '';
        $strng = '';
        $q = "INSERT INTO ";
        $where = '';
        if ($id != '') {
            $q = "UPDATE ";
            $where = " WHERE `iUserId` = '" . $id . "'";

            $sql1 = "select vEmail,vPhone,eEmailVerified,ePhoneVerified,vPhoneCode from " . $tbl_name . $where;
            $edit_data = $obj->sql_query($sql1);

            if ($vEmail != $edit_data[0]['vEmail']) {
                $query3 = $q . " `" . $tbl_name . "` SET `eEmailVerified` = 'No' " . $where;
                $obj->sql_query($query3);
            }

            if ($vPhone != $edit_data[0]['vPhone']) {
                $query4 = $q . " `" . $tbl_name . "` SET `ePhoneVerified` = 'No' " . $where;
                $obj->sql_query($query4);
            }

            if ($vPhoneCode != $edit_data[0]['vPhoneCode']) {
                $query5 = $q . " `" . $tbl_name . "` SET `ePhoneVerified` = 'No' " . $where;
                $obj->sql_query($query5);
            }
        }

        $passPara = '';
        if ($vPass != "") {
            $passPara = "`vPassword` = '" . $vPass . "',";
        }

        if ($action == "Add") {
            $vRefCode = $generalobj->ganaraterefercode($eReftype);
            $vRefCodePara = "`vRefCode` = '" . $vRefCode . "',";
            $strng = "`tRegistrationDate` = '" . date("Y-m-d H:i:s") . "',";
            $sessionquery = " `tSessionId` = '" . session_id() . time() . "',";
        }

        $query = $q . " `" . $tbl_name . "` SET
			`vName` = '" . $vName . "',
			`vLastName` = '" . $vLastName . "',
			`vEmail` = '" . $vEmail . "',
			$passPara
			`vPhone` = '" . $vPhone . "',
			`vLang` = '" . $vLang . "',			
			`vCountry` = '" . $vCountry . "',
			`eGender` = '" . $eGender . "',
			`vPhoneCode` = '" . $vPhoneCode . "',
			$vRefCodePara
			`eStatus` = '" . $eStatus . "',
			`vImgName` = '" . $oldImage . "',
			`vCurrencyPassenger`='" . $vCurrencyPassenger . "',
      $strng
      $sessionquery
			`vInviteCode` = '" . $vInviteCode . "'" . $where;
        $obj->sql_query($query);

        if ($id == "") {
            $id = $obj->GetInsertId();
        }
        if ($_FILES['vImgName']['name'] != '') {
            $img_path = $tconfig["tsite_upload_images_passenger_path"];
            $temp_gallery = $img_path . '/';
            $image_object = $_FILES['vImgName']['tmp_name'];
            $image_name = $_FILES['vImgName']['name'];
            $check_file = $img_path . '/' . $id . '/' . $oldImage;
            if ($oldImage != '' && file_exists($check_file)) {
                @unlink($img_path . '/' . $id . '/' . $oldImage);
                @unlink($img_path . '/' . $id . '/1_' . $oldImage);
                @unlink($img_path . '/' . $id . '/2_' . $oldImage);
                @unlink($img_path . '/' . $id . '/3_' . $oldImage);
            }

            $Photo_Gallery_folder = $img_path . '/' . $id . '/';
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
            }
            $img1 = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, '', '', '', '', '', '', 'Y', '', $Photo_Gallery_folder);

            if ($img1 != '') {
                if (is_file($Photo_Gallery_folder . $img1)) {
                    include_once(TPATH_CLASS . "/SimpleImage.class.php");
                    $img = new SimpleImage();
                    list($width, $height, $type, $attr) = getimagesize($Photo_Gallery_folder . $img1);
                    $final_width = $height;
                    if ($width < $height) {
                        $final_width = $width;
                    }
                    $img->load($Photo_Gallery_folder . $img1)->crop(0, 0, $final_width, $final_width)->save($Photo_Gallery_folder . $img1);
                    $img1 = $generalobj->img_data_upload($Photo_Gallery_folder, $img1, $Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"], "");
                }
            }
            $vImgName = $img1;
            $sql = "UPDATE " . $tbl_name . " SET `vImgName` = '" . $vImgName . "' WHERE `iUserId` = '" . $id . "'";
            $obj->sql_query($sql);
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
}

if ($action == 'Edit') {
    $sql = "SELECT iUserId,vName,vLastName,vEmail,vPassword,vPhone,vPhoneCode,vCountry,vInviteCode,eStatus,vImgName,vCurrencyPassenger,vLang,eGender FROM " . $tbl_name . " WHERE iUserId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
// echo "<pre>";print_R($db_data);echo "</pre>";exit;
    if (count($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $vName = htmlentities($generalobjAdmin->clearName(" " . $value['vName']));
            $vLastName = $generalobjAdmin->clearName(" " . $value['vLastName']);
            $vEmail = $generalobjAdmin->clearEmail($value['vEmail']);
            $vPassword = $value['vPassword'];
// $vPass = $generalobj->decrypt($vPassword);
            $vPhone = $generalobjAdmin->clearPhone($value['vPhone']);
            $eGender = $value['eGender'];
            $vPhoneCode = $generalobjAdmin->clearPhone($value['vPhoneCode']);
            $vCountry = $value['vCountry'];
            $vInviteCode = $value['vInviteCode'];
            $eStatus = $value['eStatus'];
            $oldImage = $value['vImgName'];
            $vCurrencyPassenger = $value['vCurrencyPassenger'];
            $vLang = $value['vLang'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?>	 | <?php echo $langage_lbl_admin['LBL_EDIT_RIDERS_TXT_ADMIN']; ?>  <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />

        <?php include_once('global_files.php'); ?>
        <!-- On OFF switch -->
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
    </head>
    <!-- END HEAD-->
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
                            <h2><?= $action; ?> <?php echo $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?> <?= $vName; ?> <?= $vLastName; ?></h2>
                            <a class="back_link" href="rider.php">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />
                    <div class="body-div">
                        <div class="form-group">
                            <?php if ($success == 3) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php print_r($error); ?>
                                </div><br/>
                            <?php } ?>
                            <form method="post" action="" enctype="multipart/form-data" id="_rider_form" name="_rider_form">
                                <input type="hidden" name="actionOf" id="actionOf" value="<?php echo $action; ?>"/>
                                <input type="hidden" name="id" id="iUserId" value="<?= $id; ?>"/>
                                <input type="hidden" name="oldImage" value="<?= $oldImage; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="rider.php"/>

                                <?php if ($id) { ?>
                                    <div class="row" id="hide-profile-div">
                                        <div class="col-lg-4">
                                            <b>
                                                <?php if ($oldImage == 'NONE' || $oldImage == '') { ?>
                                                    <img src="../assets/img/profile-user-img.png" alt="">
                                                    <?php
                                                } else {
                                                    if (file_exists('../webimages/upload/Passenger/' . $id . '/3_' . $oldImage)) {
                                                        ?>
                                                        <!-- <img src = "<?php echo $tconfig["tsite_upload_images_passenger"] . '/' . $id . '/3_' . $oldImage ?>" style="height:150px;"/> -->

                                                        <img src = "<?= $tconfig["tsite_url"].'resizeImg.php?h=150&src='.$tconfig["tsite_upload_images_passenger"] . '/' . $id . '/3_' . $oldImage; ?>" style="height:150px;"/>

                                                    <? } else { ?>
                                                        <img src="../assets/img/profile-user-img.png" alt="">
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </b>
                                        </div>
                                    </div>
                                <?php } ?>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>First Name <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vName"  id="vName" value="<?= $vName; ?>" placeholder="First Name">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Last Name <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vLastName"  id="vLastName" value="<?= $vLastName; ?>" placeholder="Last Name">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Email <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vEmail"  id="vEmail" value="<?= $vEmail; ?>"  placeholder="Email"  />

                                    </div>
                                    <label id="emailCheck"><label>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Password<span class="red"> *</span>
                                                        <?php if ($action == 'Edit') { ?>
                                                            <span>&nbsp;[Leave blank to retain assigned password.]</span>
                                                        <?php } ?>
                                                    </label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <input type="password" class="form-control" name="vPassword"  id="vPassword" value="" placeholder="Password">
                                                </div>
                                            </div>
                                            <? if(ONLYDELIVERALL!='Yes' && APP_TYPE != "Delivery" && APP_TYPE != "UberX") { ?>                                            
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Gender</label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <input id="r4" name="eGender" type="radio" value="Male"
                                                    <?php
                                                    if ($eGender == 'Male' && $action != "Add") {
                                                        echo 'checked';
                                                    }
                                                    ?> >
                                                    <label for="r4">Male</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                                    <input id="r5" name="eGender" type="radio" value="Female" class="required" 
                                                    <?php
                                                    if ($eGender == 'Female' && $action != "Add") {
                                                        echo 'checked';
                                                    }
                                                    ?> >
                                                    <label for="r5">Female</label>
                                                </div>
                                            </div>
                                            <? } ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Profile Picture</label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <input type="file" class="form-control" name="vImgName"  id="vImgName" placeholder="Name Label" accept='image/*'>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Country <span class="red"> *</span></label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <?php 
                                                        if(count($db_country) > 1){ 
                                                                $style = "";
                                                             }else{
                                                                $style = " disabled=disabled";
                                                        } ?>
                                                    <select <?= $style ?> class="form-control" id ='vCountry' name ='vCountry' onChange="changeCode(this.value);">
                                                        <?php 
                                                        if(count($db_country) > 1){ ?>
                                                            <option value="">Select</option>
                                                         <?php } ?>
                                                        <?php for ($i = 0; $i < count($db_country); $i++) { ?>
                                                            <option value = "<?= $db_country[$i]['vCountryCode'] ?>" <?php if ($DEFAULT_COUNTRY_CODE_WEB == $db_country[$i]['vCountryCode'] && $action == 'Add') { ?> selected <?php } else if ($vCountry == $db_country[$i]['vCountryCode']) { ?>selected<?php } ?>><?= $db_country[$i]['vCountry'] ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12" style="width:30%">
                                                    <label>Phone<span class="red"> *</span></label>
                                                </div>
                                                <div class="col-lg-6"  style="width:50%">
                                                    <input type="text" class="form-select-2 form-select-21" id="code" readonly name="vPhoneCode" value="<?= $vPhoneCode ?>">
                                                    <input type="text" class="mobile-text form-control form-select-3" name="vPhone" id="vPhone" value="<?= $vPhone; ?>" placeholder="Phone">
                                                </div>
                                            </div>


                                            <?php if (count($db_lang) <= 1) { ?>
                                                <input name="vLang" type="hidden" class="create-account-input" value="<?php echo $db_lang[0]['vCode']; ?>"/>

                                            <?php } else { ?>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <label>Language<span class="red"> *</span></label>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <select  class="form-control" name ='vLang' id='vLang'>
                                                            <option value="">--select--</option>
                                                            <? for ($i = 0; $i < count($db_lang); $i++) { ?>
                                                                <option value = "<?= $db_lang[$i]['vCode'] ?>" <?= ($db_lang[$i]['vCode'] == $vLang) ? 'selected' : ''; ?>><?= $db_lang[$i]['vTitle'] ?> </option>
                                                            <? } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        <?php if(count($db_currency) <= 1) { ?>
                                        <input name="vCurrencyPassenger" type="hidden" class="create-account-input" value="<?php echo $db_currency[0]['vName']; ?>"/>
                                        <? } else { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Currency <span class="red"> *</span></label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <select class="form-control" name = 'vCurrencyPassenger' id='vCurrencyPassenger'>
                                                        <option value="">--select--</option>
                                                        <?php for ($i = 0; $i < count($db_currency); $i++) { ?>
                                                            <option value = "<?= $db_currency[$i]['vName'] ?>" <? if ($vCurrencyPassenger == $db_currency[$i]['vName']) { ?>selected<? } ?>><?= $db_currency[$i]['vName'] ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        <? } ?>
                                            <!--<div class="row">
                                                 <div class="col-lg-12">
                                                      <label>Promotional Code</label>
                                                 </div>
                                                 <div class="col-lg-6">
                                                      <input type="text" class="form-control" name="vInviteCode"  id="vInviteCode" value="<?= $vInviteCode; ?>" placeholder="Promotional Code">
                                                 </div>
                                            </div>-->
                                            <?php if ($eStatus != 'Deleted') { ?>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <label>Status</label>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="make-switch" data-on="success" data-off="warning">
                                                            <input type="checkbox" name="eStatus" id="eStatus" <?= ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?> value="1"/>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php } else { ?>
                                                <input type="hidden" name="eStatus" id="eStatus" value="Deleted"/>
                                            <?php } ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <?php if (($action == 'Edit' && $userObj->hasPermission('edit-users')) || ($action == 'Add' && $userObj->hasPermission('create-users'))) { ?>
                                                        <input type="submit" class="btn btn-default" name="submit" id="submit"  value="<?php if ($action == 'Add') { ?><?= $action; ?> <?php echo $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?><?php } else { ?>Update<?php } ?>">
                                                        <input type="reset" value="Reset" class="btn btn-default">
                                                    <?php } ?>
                                                    <!-- <a href="javascript:void(0);" onClick="reset_form('_rider_form');" class="btn btn-default">Reset</a> -->
                                                    <a href="rider.php" class="btn btn-default back_link">Cancel</a>
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
                                            <script>
                                                        $('#_rider_form').validate({
                                                            rules: {
                                                                vName: {
                                                                    required: true
                                                                },
                                                                vLastName: {
                                                                    required: true
                                                                },
                                                                vEmail: {
                                                                    required: true, email: true
                                                                },
<?php if ($id == '') { ?>vPassword: {required: true, noSpace: true, minlength: 6, maxlength: 16},<?php } ?>
                                                                vCountry: {
                                                                    required: true
                                                                },
                                                                vPhone: {
                                                                    required: true, minlength: 3, digits: true
                                                                },
                                                                vLang: {
                                                                    required: true
                                                                },
                                                                vCurrencyPassenger: {
                                                                    required: true
                                                                }
                                                            },
                                                            submitHandler: function (form) {
                                                                $("#vCountry").prop('disabled',false);
                                                                if ($(form).valid())
                                                                    form.submit();
                                                                return false; // prevent normal form posting
                                                            }
                                                        });
                                                        $(document).ready(function () {
                                                            var referrer;
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
                                                        });

                                                        function changeCode(id) {
                                                            var request = $.ajax({
                                                                type: "POST",
                                                                url: 'change_code.php',
                                                                data: 'id=' + id,
                                                                success: function (data)
                                                                {
                                                                    document.getElementById("code").value = data;
                                                                }
                                                            });
                                                        }
                                                        changeCode('<?php echo $vCountry; ?>');
                                            </script>
                                            </body>
                                            <!-- END BODY-->
                                            </html>
