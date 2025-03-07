<?php
include_once 'common.php';
$script = "Organization-Profile";
//added by SP for cubex changes on 07-11-2019
if($generalobj->checkXThemOn() == 'Yes') {
        include_once("cx-profile_organization.php");
        exit;
}

$user = isset($_SESSION["sess_user"]) ? $_SESSION["sess_user"] : '';
$success = isset($_REQUEST["success"]) ? $_REQUEST["success"] : '';
$var_msg = isset($_REQUEST["var_msg"]) ? $_REQUEST["var_msg"] : '';
$new = '';
$db_doc = array();

if (isset($_SESSION['sess_new'])) {
    $new = $_SESSION['sess_new'];
    unset($_SESSION['sess_new']);
}
$generalobj->check_member_login();

$abc = 'organization';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$generalobj->setRole($abc, $url);

// Start :: Get country name
$sql = "select * from country where eStatus = 'Active' ORDER BY vCountry ASC ";
$db_country = $obj->MySQLSelect($sql);

$sql = "select * from currency where eStatus = 'Active' ORDER BY iDispOrder ASC ";
$db_currency = $obj->MySQLSelect($sql);
// Start :: Get country name

$sql = "select * from organization where iOrganizationId = '" . $_SESSION['sess_iOrganizationId'] . "'";
$db_user = $obj->MySQLSelect($sql);

if (empty($SHOW_CITY_FIELD)) {
    $SHOW_CITY_FIELD = $generalobj->getConfigurations("configurations", "SHOW_CITY_FIELD");
}

$orgTypeName = "";
$default_lang = $generalobj->get_default_lang();
$profileName = "vProfileName_" . $default_lang;
if (isset($db_user[0]['iUserProfileMasterId']) && $db_user[0]['iUserProfileMasterId'] > 0) {
    $business_type_sql = $obj->MySQLSelect("SELECT * FROM user_profile_master WHERE iUserProfileMasterId='" . $db_user[0]['iUserProfileMasterId'] . "'");

    $decodevProfileName = json_decode($business_type_sql[0]['vProfileName']);
    $orgTypeName = $decodevProfileName->$profileName;
}

$sql = "select * from language_master where eStatus = 'Active' ORDER BY iDispOrder ASC ";
$db_lang = $obj->MySQLSelect($sql);
$lang = "";
for ($i = 0; $i < count($db_lang); $i++) {
    if ($db_user[0]['vLang'] == $db_lang[$i]['vCode']) {
        $lang_user = $db_lang[$i]['vTitle'];
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= $SITE_NAME ?> |<?= $langage_lbl['LBL_HEADER_PROFILE_TXT']; ?> </title>
        <!-- Default Top Script and css -->
        <?php include_once "top/top_script.php"; ?>
        <link rel="stylesheet" href="assets/css/bootstrap-fileupload.min.css" >

        <?php if ($_SESSION['sess_user'] == 'driver' && $APP_TYPE == 'UberX') { ?>
            <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&language=en&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>"></script>
        <?php } ?>
        <!-- End: Default Top Script and css-->
    </head>
    <body>
        <!-- home page -->
        <div id="main-uber-page">
            <!-- Left Menu -->
            <?php include_once "top/left_menu.php"; ?>
            <!-- End: Left Menu-->
            <!-- Top Menu -->
            <?php include_once "top/header_topbar.php"; ?>
            <!-- End: Top Menu-->
            <!-- contact page-->
            <div class="page-contant">
                <div class="page-contant-inner">
                    <h2 class="header-page-p"><?= $langage_lbl['LBL_PROFILE_HEADER_PROFILE_TXT']; ?></h2>
                    <?php
                    if ($db_user[0]['eStatus'] == 'Inactive') {
                        ?>
                        <div class="demo-warning">
                            <p><?= $langage_lbl['LBL_WELCOME_TO'] . ' ' . $COMPANY_NAME; ?></p> 
                            <p> <?= $langage_lbl['LBL_INACTIVE_ORGANIZATION_MESSAGE']; ?>
                            </p>
                        </div>
                        <?php
                    }
                    ?> 
                    <!-- profile page -->
                    <div class="driver-profile-page">
                        <?php if ($success == 1) { ?>
                            <div class="demo-success msgs_hide">
                                <button class="demo-close" type="button">×</button>
                                <?= $var_msg ?>
                            </div>
                            <?php
                        } else if ($success == 2) {
                            ?>
                            <div class="demo-danger msgs_hide">
                                <button class="demo-close" type="button">×</button>
                                <?= $langage_lbl['LBL_EDIT_DELETE_RECORD']; ?>
                            </div>
                            <?php
                        } else if ($success == 0 && $var_msg != "") {
                            ?>
                            <div class="demo-danger msgs_hide">
                                <button class="demo-close" type="button">×</button>
                                <?= $var_msg; ?>
                            </div>
                        <?php } ?>

                        <div class="driver-profile-top-part" id="hide-profile-div">
                            <div class="driver-profile-info edit-organization">
                                <h3>
                                    <?php echo $generalobj->cleanall(htmlspecialchars($db_user[0]['vCompany'])); ?> 
                                </h3>
                                <p><?= $db_user[0]['vEmail'] ?></p>
                                <p>
                                    <?php if (!empty($db_user[0]['vPhone'])) { ?>
                                        (+<?= $db_user[0]['vCode'] ?>) <?= $db_user[0]['vPhone'] ?>
                                    <?php } ?>
                                </p>
                                <?php if (!empty($db_user[0]['ePaymentBy'])) { ?>
                                    <p>
                                        <?php
                                        $payByName = $db_user[0]['ePaymentBy'];
                                        if ($db_user[0]['ePaymentBy'] == "") {
                                            $payByName = "User";
                                        }
                                        ?>
                                        <b><?= $langage_lbl['LBL_PAYMENT_METHOD_WEB']; ?> :</b> <?= $langage_lbl['LBL_PAY_BY_WEB']; ?> <?= $payByName ?>
                                    </p>
                                <?php } ?>
                                <?php if ($orgTypeName != "") { ?>
                                    <p>
                                        <b><?= $langage_lbl['LBL_ORGANIZATION_TYPE_WEB']; ?> :</b> <?= $orgTypeName ?>
                                    </p>
                                <?php } ?>

                                <span><a id="show-edit-profile-div"><i class="fa fa-pencil" aria-hidden="true"></i><?= $langage_lbl['LBL_PROFILE_EDIT']; ?></a></span>
                            </div>
                        </div>
                        <!-- form -->
                        <div class="edit-profile-detail-form" id="show-edit-profile" style="display: none;">
                            <form id="frm1" method="post" action="javascript:void(0);" >
                                <input  type="hidden" class="edit" name="action" value="login">
                                <div class="show-edit-profile-part">
                                    <span>

                                        <label><?= $langage_lbl['LBL_PROFILE_YOUR_EMAIL_ID']; ?> <span class="red">*</span></label>
                                        <input type="hidden" name="uid" id="u_id1" value="<?= $_SESSION['sess_iUserId']; ?>">

                                        <input type="hidden" name="user_type" id="user_type" value="<?= $_SESSION['sess_user']; ?>">
                                        <input type="email" id="in_email" class="edit-profile-detail-form-input" placeholder="<?= $langage_lbl['LBL_PROFILE_YOUR_EMAIL_ID']; ?>" value = "<?= $db_user[0]['vEmail'] ?>" name="email" <?= isset($db_user[0]['vEmail']) ? '' : ''; ?>  required >
                                        <!--onChange="validate_email(this.value,'<?php echo $id; ?>')"-->
                                        <div class="required-label" id="emailCheck"></div>
                                    </span>
                                    <span>
                                        <label><?= $langage_lbl['LBL_SELECT_CONTRY']; ?><span class="red">*</span></label>
                                         <?php 
                                                    if(count($db_country) > 1){ 
                                                          $style = "";
                                                         }else{
                                                        $style = " disabled=disabled";
                                                    } ?>
                                        <select <?= $style ?> class="custom-select-new vCountry" id="vCountry" name = 'vCountry' onChange="changeCode(this.value);" required>
                                            <?php if(count($db_country) > 1){ ?>
                                                                    <option value="">--select--</option>
                                                                <?php } ?>
                                            <?php for ($i = 0; $i < count($db_country); $i++) { ?>
                                                <option value = "<?= $db_country[$i]['vCountryCode'] ?>" <?php if ($db_user[0]['vCountry'] == $db_country[$i]['vCountryCode']) { ?>selected<?php } ?>><?= $db_country[$i]['vCountry'] ?></option>
                                            <?php } ?>
                                        </select>
                                        <div class="required-label" id="vCountryCheck"></div>
                                    </span>

                                    <span>
                                        <label><?= $langage_lbl['LBL_Phone_Number']; ?><span class="red">*</span></label>
                                        <input type="text" class="input-phNumber1" id="code" name="vCode" value="<?= $db_user[0]['vCode'] ?>" readonly >
                                        <input name="vPhone" id="vPhone" type="text" value="<?= $db_user[0]['vPhone'] ?>" class="edit-profile-detail-form-input input-phNumber2" placeholder="<?= $langage_lbl['LBL_Phone_Number']; ?>"  title="Please enter proper phone number." required/>
                                    </span>
                                 
                                    <span>
                                        <label><?= $langage_lbl['LBL_SELECT_CURRENCY']; ?><span class="red">*</span></label>

                                        <select class="custom-select-new vCurrency" name ='vCurrency' required>
                                            <option value="">--select--</option>
                                            <?php
                                            for ($i = 0; $i < count($db_currency); $i++) {
                                                ?>
                                                <option value = "<?= $db_currency[$i]['vName'] ?>" 
                                                        <?php if ($db_user[0]['vCurrency'] == $db_currency[$i]['vName']) { ?>selected<?php } ?>><?= $db_currency[$i]['vName'] ?></option>
                                                    <?php } ?>
                                        </select>
                                        <div class="required-label" id="vCurrencyCheck"></div>
                                    </span>
                              
                                    <p class="save-button11">
                                        <input name="save" id="validate_submit" type="submit" value="<?= $langage_lbl['LBL_Save']; ?>" class="save-but" > <!-- onClick="return validate_email_submit('login')" -->
                                        <input name="" id="hide-edit-profile-div" type="button" value="<?= $langage_lbl['LBL_BTN_PROFILE_CANCEL_TRIP_TXT']; ?>" class="cancel-but">
                                    </p>
                                    <div style="clear:both;"></div>
                                </div>
                            </form>
                        </div>
                        <!-- from -->
                        <div <?php if ($_SESSION['sess_user'] == 'driver') { ?> class="detail-driver driver-profile-mid-part"  <?php } else { ?> class='driver-profile-mid-part' <?php } ?>>
                            <ul >
                                <li <?php if ($_SESSION['sess_user'] == 'driver') { ?> class='driver-profile-mid-part-details' <?php } else { ?> class='company-profile-mid-part-details' <?php } ?> >
                                    <div class="driver-profile-mid-inner">
                                        <div class="profile-icon"><i class="fa fa-map-marker" aria-hidden="true"></i></div>
                                        <h3><?= $langage_lbl['LBL_PROFILE_ADDRESS']; ?></h3>
                                        <p><?php echo $generalobj->cleanall(htmlspecialchars($db_user[0]['vCaddress'])); ?></p>
                                        <span><a id="show-edit-address-div" class="hide-address-div hidev"><i class="fa fa-pencil" aria-hidden="true"></i><?= $langage_lbl['LBL_PROFILE_EDIT']; ?></a></span>
                                    </div>
                                </li>
                                <li <?php if ($_SESSION['sess_user'] == 'driver') { ?> class='driver-profile-mid-part-details' <?php } else { ?>class='company-profile-mid-part-details' <?php } ?>>
                                    <div class="driver-profile-mid-inner-a">
                                        <div class="profile-icon"><i class="fa fa-unlock-alt" aria-hidden="true"></i></div>
                                        <h3><?= $langage_lbl['LBL_PROFILE_PASSWORD_LBL_TXT']; ?></h3>
                                        <?php /* <p><? for ($i = 0; $i < strlen($generalobj->decrypt($db_user[0]['vPassword'])); $i++) echo '*'; ?></p> */ ?>
                                        <span><a id="show-edit-password-div" class="hide-password-div hidev"><i class="fa fa-pencil" aria-hidden="true"></i><?= $langage_lbl['LBL_PROFILE_EDIT']; ?></a></span>
                                    </div>
                                </li>
                                <li <?php if ($_SESSION['sess_user'] == 'driver') { ?> class='driver-profile-mid-part-details' <?php } else { ?>class='company-profile-mid-part-details'<?php } ?>>
                                    <div class="driver-profile-mid-inner">
                                        <div class="profile-icon"><i class="fa fa-language" aria-hidden="true"></i></div>
                                        <h3><?= $langage_lbl['LBL_PROFILE_LANGUAGE_TXT']; ?></h3>
                                        <p><?= $lang_user ?></p>
                                        <span><a id="show-edit-language-div" class="hide-language-div hidev"><i class="fa fa-pencil" aria-hidden="true"></i><?= $langage_lbl['LBL_PROFILE_EDIT']; ?></a></span>
                                    </div>
                                </li>
                                <?php if ($_SESSION['sess_user'] == 'driver') { ?>
                                    <li <?php if ($_SESSION['sess_user'] == 'driver') { ?> class='driver-profile-mid-part-details' <?php } else { ?>class='company-profile-mid-part-details' <?php } ?>>
                                        <div class="driver-profile-mid-inner">
                                            <div class="profile-icon"><i class="fa fa-bank" aria-hidden="true"></i></div>
                                            <h3><?= $langage_lbl['LBL_BANK_DETAILS_TXT']; ?></h3>
                                            <p><?= $db_user[0]['vBankName'] ?></p>
                                            <span><a id="show-edit-bankdetail-div" class="hide-bankdetail-div hidev"><i class="fa fa-pencil" aria-hidden="true"></i><?= $langage_lbl['LBL_PROFILE_EDIT']; ?></a></span>
                                        </div>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>

                        <!-- Address form -->
                        <div class="profile-Password showV" id="show-edit-address" style="display: none;">
                            <form id = "frm2" method = "post" onsubmit ="return editPro('address')">
                                <p class="address-pointer" ><img src="assets/img/pas-img1.jpg" alt=""></p>
                                <h3><i class="fa fa-map-marker" aria-hidden="true"></i><?= $langage_lbl['LBL_PROFILE_ADDRESS']; ?></h3>
                                <input  type="hidden" class="edit" name="action" value="address">
                                <div class="profile-address-part">
                                    <span>
                                        <label><?= $langage_lbl['LBL_ADDRESS_SIGNUP'] ?><span class="red">*</span></label>
                                        <input type="text" class="profile-address-input" placeholder="<?= $langage_lbl['LBL_PROFILE_ADDRESS']; ?> 1" value="<?= $generalobj->cleanall(htmlspecialchars($db_user[0]['vCaddress'])); ?>" name="address1" required>
                                    </span>
                                    <span>
                                        <label><?= $langage_lbl['LBL_COUNTRY_TXT'] ?><span class="red">*</span></label>

                                        <select class="form-control" name = 'vCountry' id="vCountry" onChange="setState(this.value, '');" required>
                                            <option value="">Select</option>
                                            <?php for ($i = 0; $i < count($db_country); $i++) { ?>
                                                <option  <?php if ($db_user[0]['vCountry'] == $db_country[$i]['vCountryCode']) { ?>selected<?php } ?> value = "<?= $db_country[$i]['vCountryCode'] ?>"><?= $db_country[$i]['vCountry'] ?></option>
                                            <?php } ?>
                                        </select>
                                        <div class="required-label" id="vCountryCheck"></div>
                                    </span>
                                    <span>
                                        <label><?= $langage_lbl['LBL_STATE_TXT'] ?> </label>
                                        <select class="form-control" name = 'vState' id="vState" onChange="setCity(this.value, '');" >
                                            <option value="">Select</option>
                                        </select>
                                    </span>
                                    <?php if($SHOW_CITY_FIELD=='Yes') { ?>
                                    <span>
                                        <label><?= $langage_lbl['LBL_CITY_TXT'] ?></label>
                                        <select class="form-control" name = 'vCity' id="vCity"  >
                                            <option value="">Select</option>
                                        </select>
                                    </span>
                                    <?php } ?> 
                                    <span>
                                        <label><?= $langage_lbl['LBL_ZIP_CODE']; ?><span class="red">*</span></label>
                                        <input type="text" class="profile-address-input" placeholder="<?= $langage_lbl['LBL_ZIP_CODE']; ?>" value="<?= $db_user[0]['vZip'] ?>" name="vZipcode" required></span>
                                </div>

                                <span>
                                    <b>
                                        <input name="save" type="submit" value="<?= $langage_lbl['LBL_Save']; ?>" class="profile-Password-save">
                                        <input name="" id="hide-edit-address-div" type="button" value="<?= $langage_lbl['LBL_BTN_PROFILE_CANCEL_TRIP_TXT']; ?>" class="profile-Password-cancel">
                                    </b>
                                </span>
                                <div style="clear:both;"></div>

                            </form>
                        </div>
                        <!-- End: Address Form -->

                        <!-- Password form -->
                        <div class="profile-Password showV" id="show-edit-password" style="display: none;">
                            <form id="frm3" method="post" action="javascript:void(0);" onSubmit="return <?= ($db_user[0]['vFbId'] >= 0 && $db_user[0]['vPassword'] != "") ? 'validate_password()' : 'validate_password_fb()'; ?>">
                                <p class="password-pointer"><img src="assets/img/pas-img1.jpg" alt=""></p>
                                <h3><i class="fa fa-unlock-alt" aria-hidden="true"></i><?= $langage_lbl['LBL_PROFILE_PASSWORD_LBL_TXT']; ?></h3>
                                <input type="hidden" name="action" id="action" value = "pass"/>
                                <div class="row">
                                    <?php if ($db_user[0]['vFbId'] >= 0 && $db_user[0]['vPassword'] != "") { ?>
                                        <div class="col-md-4">
                                            <span>
                                                <label><?= $langage_lbl['LBL_CURR_PASS_HEADER']; ?><span class="red">*</span></label>
                                                <input type="password" class="input-box" placeholder="<?= $langage_lbl['LBL_CURR_PASS_HEADER']; ?>" name="cpass" id="cpass" onkeyup="nospaces(this)"  required>
                                            </span>
                                        </div>
                                    <?php } ?>
                                    <div class="col-md-4">
                                        <span>
                                            <label><?= $langage_lbl['LBL_NEW_PASSWORD_TXT']; ?><span class="red">*</span> </label>
                                            <input type="password" class="input-box" placeholder="<?= $langage_lbl['LBL_UPDATE_PASSWORD_HEADER_TXT']; ?>" name="npass" id="npass" onkeyup="nospaces(this)"  required>
                                        </span>
                                    </div>
                                    <div class="col-md-4">
                                        <span>
                                            <label><?= $langage_lbl['LBL_Confirm_New_Password']; ?><span class="red">*</span></label>
                                            <input type="password" class="input-box" placeholder="<?= $langage_lbl['LBL_Confirm_New_Password']; ?>" name="ncpass" id="ncpass" onkeyup="nospaces(this)"  required>
                                        </span>
                                    </div>
                                </div>
                                <div class="btn-block">
                                    <input name="save" type="submit" value="<?= $langage_lbl['LBL_Save']; ?>" class="profile-Password-save">
                                    <input name="" id="hide-edit-password-div" type="button" value="<?= $langage_lbl['LBL_BTN_PROFILE_CANCEL_TRIP_TXT']; ?>" class="profile-Password-cancel">
                                </div>
                                <div style="clear:both;"></div>
                            </form>
                        </div>

                        <!-- End: Password Form -->
                        <!-- Language form -->
                        <div class="profile-Password showV" id="show-edit-language" style="display: none;">
                            <form id="frm4" method = "post">
                                <p class="language-pointer"><img src="assets/img/pas-img1.jpg" alt=""></p>
                                <h3><i class="fa fa-language" aria-hidden="true"></i><?= $langage_lbl['LBL_PROFILE_LANGUAGE_TXT']; ?></h3>
                                <input type="hidden" value= "lang1" name="action">
                                <div class="edit-profile-detail-form-password-inner profile-language-part">
                                    <span>
                                        <label><?= $langage_lbl['LBL_PROFILE_SELECT_LANGUAGE']; ?></label>
                                        <select name="lang1" class="custom-select-new profile-language-input">
                                            <?php
                                            for ($i = 0; $i < count($db_lang); $i++) {
                                                ?>
                                                <option value="<?= $db_lang[$i]['vCode'] ?>" <? if ($db_user[0]['vLang'] == $db_lang[$i]['vCode']) { ?> selected <? } ?> ><? echo $db_lang[$i]['vTitle']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </span>
                                </div>

                                <div class="btn-block">
                                    <input name="save" type="button" value="<?= $langage_lbl['LBL_Save']; ?>" class="profile-Password-save" onClick="editProfile('lang');">
                                    <input name="" id="hide-edit-language-div" type="button" value="<?= $langage_lbl['LBL_BTN_PROFILE_CANCEL_TRIP_TXT']; ?>" class="profile-Password-cancel">
                                </div>

                                <div style="clear:both;"></div>

                            </form>
                        </div>
                        <!-- End: Language Form -->

                        <!-- bank detail -->

                        <?php
                        if ($APP_TYPE == 'UberX') {
                            $class_name = 'driver-profile-bottom-part required-documents-bottom-part two-part-document';
                        } else {
                            $class_name = 'driver-profile-bottom-part required-documents-bottom-part';
                        }
                        ?>
                        <!-- end bank detail -->

                        <!-- end bank detail -->
                        <?php if ($SITE_VERSION == "v5" && $_SESSION['sess_user'] == "driver") { ?>
                            <div class="<?php echo $class_name; ?>">
                                <h3><?= $langage_lbl['LBL_PREFERENCES_TEXT'] ?> </h3>
                                <p>
                                <div class="driver-profile-info-aa col-md-5"> <?php foreach ($data_driver_pref as $val) { ?>
                                        <img data-toggle="tooltip" class="borderClass-aa border_class-bb" title="<?= $val['pref_Title'] ?>" src="<?= $tconfig["tsite_upload_preference_image_panel"] . $val['pref_Image'] ?>">
                                    <?php } ?>
                                </div>

                                <span class="col-md-5">
                                    <a href="preferences.php" id="show-edit-language-div" class="hide-language">
                                        <i class="fa fa-pencil" aria-hidden="true"></i>
                                        <?= $langage_lbl['LBL_PROFILE_EDIT'] ?></a>
                                </span>
                                </p>
                            </div>
                        <?php } ?>

                        <?php if ($count_all_doc != 0) { ?>
                            <div class="<?php echo $class_name; ?>">
                                <h3><?= $langage_lbl['LBL_REQUIRED_DOCS']; ?></h3>
                                <div class="profile-req-doc driver-document-action-page">
                                    <div class="profile-req-doc-inner pro-required">
                                        <?php
                                        for ($i = 0; $i < $count_all_doc; $i++) {
                                            if ($db_userdoc[$i]['eType'] == 'UberX') {
                                                $etypeName = 'Service';
                                            } else {
                                                $etypeName = $db_userdoc[$i]['eType'];
                                            }
                                            ?>
                                            <div class="panel panel-default upload-clicking">
                                                <input  type="hidden" id="ex_status" value="<?php echo $db_userdoc[$i]['ex_status']; ?>">
                                                <div class="panel-heading">
                                                    <div><?php echo $db_userdoc[$i]['d_name']; ?></div>
                                                    <?php if (($APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') && $_SESSION['sess_user'] == 'driver') { ?>

                                                                                                                                                                        <!--     <div style="font-size: 10px;">(For <?= $etypeName; ?>)</div> -->

                                                    <?php } ?>
                                                </div>
                                                <input type="hidden" id="doc_id" value="<?php $db_userdoc[$i]['doc_file']; ?>">
                                                <div class="panel-body">
                                                    <label>
                                                        <?php if ($db_userdoc[$i]['doc_file'] != '') { ?>
                                                            <?php
                                                            $file_ext = $generalobj->file_ext($db_userdoc[$i]['doc_file']);
                                                            if ($file_ext == 'is_image') {

                                                                if ($_SESSION['sess_user'] == 'organization') {

                                                                    $path = $tconfig["tsite_upload_compnay_doc"];
                                                                } else {

                                                                    $path = $tconfig["tsite_upload_driver_doc"];
                                                                }
                                                                ?>
                                                                <a href="<?= $path . '/' . $_SESSION['sess_iUserId'] . '/' . $db_userdoc[$i]['doc_file'] ?>" target="_blank"><img src = "<?= $path . '/' . $_SESSION['sess_iUserId'] . '/' . $db_userdoc[$i]['doc_file'] ?>" style="width:200px;cursor:pointer;" alt ="<?= $db_userdoc[$i]['d_name']; ?> Image" /></a>

                                                                <?php
                                                            } else {

                                                                if ($_SESSION['sess_user'] == 'organization') {
                                                                    $tconfig_new = $tconfig["tsite_upload_compnay_doc"];
                                                                } else {

                                                                    $tconfig_new = $tconfig["tsite_upload_driver_doc"];
                                                                }
                                                                ?>
                                                                <p><a href="<?= $tconfig_new . '/' . $_SESSION['sess_iUserId'] . '/' . $db_userdoc[$i]['doc_file'] ?>" target="_blank"><?php echo $db_userdoc[$i]['d_name']; ?></a></p>

                                                            <?php } ?>
                                                            <?php
                                                        } else {

                                                            echo '<p>' . $db_userdoc[$i]['d_name'] . " " . $langage_lbl['LBL_NOT_FOUND'] . '</p>';
                                                        }
                                                        ?>
                                                    </label>
                                                    <br/>
                                                    <b><button class="btn btn-info" data-toggle="modal" data-target="#uiModal" id="custId"  onClick="setModel001('<?php echo $db_userdoc[$i]['masterid']; ?>')" >

                                                            <?php
                                                            if ($db_userdoc[$i]['doc_file'] != '') {
                                                                echo $db_userdoc[$i]['d_name'];
                                                            } else {
                                                                echo $db_userdoc[$i]['d_name'];
                                                            }
                                                            ?>
                                                        </button></b>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-lg-12">
                            <div class="modal fade" id="uiModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                <div class="modal-content image-upload-1">
                                    <div class="fetched-data"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="clear:both;"></div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="modal fade" id="uiModal_4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-content image-upload-1 popup-box3">
                        <div class="upload-content">
                            <h4><?= $langage_lbl['LBL_PROFILE_PICTURE']; ?></h4>
                            <form class="form-horizontal frm9" id="frm9" method="post" enctype="multipart/form-data" action="upload_doc_organization.php" name="frm9">
                                <input type="hidden" name="action" value ="photo"/>
                                <input type="hidden" name="img_path" value ="
                                <?php
                                if ($_SESSION['sess_user'] == 'organization') {
                                    echo $tconfig["tsite_upload_images_organization_path"];
                                } else if ($_SESSION['sess_user'] == 'driver') {
                                    echo $tconfig["tsite_upload_images_driver_path"];
                                }
                                ?>"/>
                                <div class="form-group">
                                    <div class="col-lg-12">
                                        <div class="fileupload fileupload-new" data-provides="fileupload">
                                            <div class="fileupload-preview thumbnail">
                                                <?php if ($db_user[0]['vImage'] == 'NONE' || $db_user[0]['vImage'] == '') { ?>
                                                    <img src="assets/img/profile-user-img.png" alt="">
                                                    <?php
                                                } else {
                                                    if ($_SESSION['sess_user'] == 'organization') {
                                                        $img_path = $tconfig["tsite_upload_images_organization"];
                                                    } else if ($_SESSION['sess_user'] == 'driver') {
                                                        $img_path = $tconfig["tsite_upload_images_driver"];
                                                    }
                                                    ?>
                                                    <img src = "<?= $img_path . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_user[0]['vImage'] ?>" style="height:150px;"/>
                                                <?php } ?>
                                            </div>
                                            <div>
                                                <span class="btn btn-file btn-success"><span class="fileupload-new"><?= $langage_lbl['LBL_UPLOAD_PHOTO']; ?></span><span class="fileupload-exists"><?= $langage_lbl['LBL_CHANGE']; ?></span>
                                                    <input type="file" name="photo"/>
                                                    <input type="hidden" name="photo_hidden"  id="photo" value="<?php echo ($db_user[0]['vImage'] != "") ? $db_user[0]['vImage'] : ''; ?>" />
                                                </span>
                                                <a href="#" class="btn btn-danger" id="cancel-btn" data-dismiss="fileupload">X</a>
                                            </div>
                                            <div class="upload-error"><span class="file_error"></span></div>
                                        </div>
                                    </div>
                                </div>
                                <input type="submit" class="save" name="save" value="<?= $langage_lbl['LBL_Save']; ?>"><input type="button" class="cancel" data-dismiss="modal" name="cancel" value="<?= $langage_lbl['LBL_BTN_PROFILE_CANCEL_TRIP_TXT']; ?>">
                            </form>
                            <div style="clear:both;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content modal-content-profile">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                <h4 class="modal-title" id="H2"><?= $langage_lbl['LBL_NOTE_FOR_DEMO']; ?></h4>
                            </div>
                            <div class="modal-body">
                                <form role="form" name="verification" id="verification">
                                    <?php if ($_SESSION['sess_user'] == 'driver') { ?>
                                        <p><?= $langage_lbl['LBL_PROFILE_WE_SEE_YOU_HAVE_REGISTERED_AS_A_DRIVER']; ?></p>
                                        <p><?= $langage_lbl['LBL_SINCE_IT_IS_DEMO_VERSION_ADDVEHICLE']; ?></p>

                                        <p><?= $langage_lbl['LBL_HOWEVER_IN_REAL_SYSTEM_DRIVER']; ?></p>
                                        <? } else { ?>
                                        <p><?= $langage_lbl['LBL_WE_SEE_YOU_HAVE_REGISTERED_AS_A_COMPANY']; ?></p>
                                        <p><?= $langage_lbl['LBL_SINCE_IT_IS_DEMO_VERSION']; ?></p>

                                        <p><?= $langage_lbl['LBL_STEP1']; ?></p>
                                        <!--p><? //= $langage_lbl['LBL_STEP2'];         ?></p-->
                                        <p><?= $langage_lbl['LBL_STEP3']; ?></p>

                                        <p><?= $langage_lbl['LBL_HOWEVER_IN_REAL_SYSTEM']; ?></p>
                                    <?php } ?>
                                    <div class="form-group">

                                    </div>
                                    <p class="help-block" id="verification_error"></p>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <!-- footer part -->
            <?php include_once 'footer/footer_home.php'; ?>
            <!-- footer part end -->
            <!-- -->
            <div style="clear:both;"></div>
        </div>
        <!-- home page end-->
        <!-- Footer Script -->
        <?php
        include_once 'top/footer_script.php';
        $lang = get_langcode($_SESSION['sess_lang']);
        ?>
        <link rel="stylesheet" href="assets/plugins/datepicker/css/datepicker.css" />
        <style>
            .upload-error .help-block {
                color:#b94a48;
            }
        </style>
        <script src="assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
        <link rel="stylesheet" href="assets/validation/validatrix.css" />
        <script type="text/javascript" src="assets/plugins/jasny/js/bootstrap-fileupload.js"></script>

        <link rel="stylesheet" type="text/css" media="screen" href="<?php echo $tconfig["tsite_url_main_admin"] ?>css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
        <script type="text/javascript" src="<?php echo $tconfig["tsite_url_main_admin"] ?>js/moment.min.js"></script>
        <script type="text/javascript" src="<?php echo $tconfig["tsite_url_main_admin"] ?>js/bootstrap-datetimepicker.min.js"></script>
        <script type="text/javascript" src="<?php echo $tconfig["tsite_url_main_admin"] ?>js/validation/jquery.validate.min.js" ></script>
        <?php if ($lang != 'en') { ?>
          <!--   <script type="text/javascript" src="assets/js/validation/localization/messages_<?= $lang; ?>.js" ></script> -->
          <? include_once('otherlang_validation.php');?>
        <?php } ?>
        <script type="text/javascript" src="assets/js/validation/additional-methods.js" ></script>
        <!-- End: Footer Script -->
        <script type="text/javascript">
                                                function nospaces(t) {
                                                    if (t.value.match(/\s/g)) {
                                                        alert('Password should not contain whitespace.');
                                                        //t.value=t.value.replace(/\s/g,'');
                                                        t.value = '';
                                                    }
                                                }

                                                //$(".demo-success").hide(1000);
                                                //var successMsg = '<?php echo $var_msg; ?>';
                                                var successMSG1 = '<?php echo $success; ?>';

                                                if (successMSG1 != '') {
                                                    setTimeout(function () {
                                                        $(".msgs_hide").hide(1000)
                                                    }, 5000);
                                                }

                                                $("#dp3").datepicker();
                                                $("#dp3").datepicker({
                                                    dateFormat: "yy-mm-dd",
                                                    changeYear: true,
                                                    changeMonth: true,
                                                    yearRange: "-100:+10"
                                                });
                                                $(document).ready(function () {
                                                    $("#show-edit-profile-div").click(function () {
                                                        $("#hide-profile-div").hide();
                                                        $("#show-edit-profile").show();
                                                    });
                                                    $("#hide-edit-profile-div").click(function () {
                                                        $("#show-edit-profile").hide();
                                                        $("#hide-profile-div").show();
                                                        $("#frm1")[0].reset();
                                                        var selectedOption = $('.custom-select-new.vCountry').find(":selected").text();
                                                        var selectedOption1 = $('.custom-select-new.vCurrency').find(":selected").text();
                                                        if (selectedOption != "" || selectedOption1 != "") {
                                                            $('.custom-select-new.vCountry').next(".holder").text(selectedOption);
                                                            $('.custom-select-new.vCurrency').next(".holder").text(selectedOption1);
                                                        }
                                                    });

                                                    $("#show-edit-password-div").click(function () {
                                                        $('.hidev').show();
                                                        $('.showV').hide();
                                                        $(".hide-password-div").hide();
                                                        $("#show-edit-password").show(300);
                                                    });
                                                    $("#hide-edit-password-div").click(function () {
                                                        $('.hidev').show();
                                                        $('.showV').hide();
                                                        $("#show-edit-password").hide();
                                                        $(".hide-password-div").show();
                                                        $("#frm3")[0].reset();
                                                    });

                                                    $("#show-edit-address-div").click(function () {
                                                        $('.hidev').show();
                                                        $('.showV').hide();
                                                        $(".hide-address-div").hide();
                                                        $("#show-edit-address").show(300);
                                                    });
                                                    $("#hide-edit-address-div").click(function () {
                                                        $('.hidev').show();
                                                        $('.showV').hide();
                                                        $("#show-edit-address").hide();
                                                        $(".hide-address-div").show();
                                                        $("#frm2")[0].reset();
                                                        var selectedOption = $('#vCountry').find(":selected").text();
                                                        var selectedOption1 = $('#vState').find(":selected").text();
                                                        var selectedOption2 = $('#vCity').find(":selected").text();
                                                        if (selectedOption != "" || selectedOption1 != "" || selectedOption2 != "") {
                                                            $('#vCountry').next(".holder").text(selectedOption);
                                                            $('#vState').next(".holder").text(selectedOption1);
                                                            $('#vCity').next(".holder").text(selectedOption2);
                                                        }
                                                    });

                                                    $("#show-edit-language-div").click(function () {
                                                        $('.hidev').show();
                                                        $('.showV').hide();
                                                        $(".hide-language-div").hide();
                                                        $("#show-edit-language").show(300);
                                                    });
                                                    $("#hide-edit-language-div").click(function () {
                                                        $('.hidev').show();
                                                        $('.showV').hide();
                                                        $("#show-edit-language").hide();
                                                        $(".hide-language-div").show();
                                                        $("#frm4")[0].reset();
                                                        var selectedOption = $('.profile-language-input').find(":selected").text();
                                                        if (selectedOption != "") {
                                                            $('.profile-language-input').next(".holder").text(selectedOption);
                                                        }
                                                    });

                                                    $("#show-edit-bankdetail-div").click(function () {
                                                        $('.hidev').show();
                                                        $('.showV').hide();
                                                        $(".hide-bankdetail-div").hide();
                                                        $("#show-edit-bankdeatil").show(300);
                                                    });
                                                    $("#hide-edit-bankdetail-div").click(function () {
                                                        $('.hidev').show();
                                                        $('.showV').hide();
                                                        $("#show-edit-bankdeatil").hide();
                                                        $(".hide-bankdetail-div").show();
                                                        $("#frm6")[0].reset();
                                                    });

                                                    $("#show-edit-vat-div").click(function () {
                                                        $("#hide-vat-div").hide();
                                                        $("#show-edit-vat").show();
                                                    });
                                                    $("#hide-edit-vat-div").click(function () {
                                                        $("#show-edit-vat").hide();
                                                        $("#hide-vat-div").show();
                                                    });

                                                    $("#show-edit-accessibility-div").click(function () {
                                                        $("#hide-accessibility-div").hide();
                                                        $("#show-edit-accessibility").show();
                                                    });
                                                    $("#hide-edit-accessibility-div").click(function () {
                                                        $("#show-edit-accessibility").hide();
                                                        $("#hide-accessibility-div").show();
                                                    });

                                                    $('.demo-close').click(function (e) {
                                                        $(this).parent().hide(1000);
                                                    });

                                                    var user = '<?= SITE_TYPE; ?>';
                                                    if (user == 'Demo') {
                                                        var a = '<?= $new; ?>';
                                                        if (a != undefined && a != '') {
                                                            //$('#formModal').modal('show');
                                                        }
                                                        //$('#formModal').modal('show');
                                                    }

                                                    $('[data-toggle="tooltip"]').tooltip();

                                                    $('#cancel-btn').on('click', function () {
                                                        $('#photo').val('');
                                                    });

                                                    $('.frm9').validate({
                                                        ignore: 'input[type=hidden]',
                                                        errorClass: 'help-block',
                                                        errorElement: 'span',
                                                        errorPlacement: function (error, element) {
                                                            if (element.attr("name") == "photo")
                                                            {
                                                                error.insertAfter("span.file_error");
                                                            } else {
                                                                error.insertAfter(element);
                                                            }
                                                        },
                                                        rules: {
                                                            photo: {
                                                                required: {
                                                                    depends: function (element) {
                                                                        if ($("#photo").val() == "NONE" || $("#photo").val() == "") {
                                                                            return true;
                                                                        } else {
                                                                            return false;
                                                                        }
                                                                    }
                                                                },
                                                                extension: "jpg|jpeg|png|gif"
                                                            }
                                                        },
                                                        messages: {
                                                            photo: {
                                                                required: '<?= addslashes($langage_lbl['LBL_UPLOAD_IMG']); ?>',
                                                                extension: '<?= addslashes($langage_lbl['LBL_UPLOAD_IMG_ERROR']); ?>'
                                                            }
                                                        }
                                                    });
                                                });
                                                function setModel001(idVal) {
                                                    // $('#uiModal').on('show.bs.modal', function (e) {
                                                    // var rowid = $(e.relatedTarget).data('id');
                                                    var id = '<?php echo $_SESSION['sess_iUserId']; ?>';
                                                    var user = '<?php echo $_SESSION['sess_user']; ?>';

                                                    $.ajax({
                                                        type: 'post',
                                                        url: 'driver_document_fetch.php', //Here you will fetch records
                                                        data: 'rowid=' + idVal + '-' + id + '-' + user, //Pass $id
                                                        success: function (data) {
                                                            $('#uiModal').modal('show');
                                                            $('.fetched-data').html(data);//Show fetched data from database
                                                        }
                                                    });
                                                }

                                                function validate_password_fb() {
                                                    //var cpass = document.getElementById('cpass').value;
                                                    var npass = document.getElementById('npass').value;
                                                    var ncpass = document.getElementById('ncpass').value;
                                                    // var pass = '<?= $newp ?>';
                                                    var err = '';
                                                    if (npass == '') {
                                                        err += "<?php echo addslashes($langage_lbl['LBL_NEW_PASS_MSG']); ?><BR>";
                                                    }
                                                    if (npass.length < 6) {
                                                        err += "<?php echo addslashes($langage_lbl['LBL_PASS_LENGTH_MSG']); ?><BR>";
                                                    }
                                                    if (ncpass == '') {
                                                        err += "<?php echo addslashes($langage_lbl['LBL_REPASS_MSG']); ?><BR>";
                                                    }
                                                    if (err == "") {
                                                        if (npass != ncpass)
                                                            err += "<?php echo addslashes($langage_lbl['LBL_PASS_NOT_MATCH']); ?><BR>";
                                                    }
                                                    if (err == "")
                                                    {
                                                        editProfile('pass');
                                                        return false;
                                                    } else {
                                                        $('#npass').val('');
                                                        $('#ncpass').val('');
                                                        bootbox.dialog({
                                                            message: "<h3>" + err + "</h3>",
                                                            buttons: {
                                                                danger: {
                                                                    label: "Ok",
                                                                    className: "btn-danger",
                                                                },
                                                            }
                                                        });
                                                        //document.getElementById("err_password").innerHTML = '<div class="alert alert-danger">' + err + '</div>';
                                                        return false;
                                                    }
                                                }
                                                function validate_password() {
                                                    var cpass = document.getElementById('cpass').value;
                                                    var npass = document.getElementById('npass').value;
                                                    var ncpass = document.getElementById('ncpass').value;
                                                    var err = '';

                                                    if (cpass == '') {
                                                        err += "<?= addslashes($langage_lbl['LBL_CURRENT_PASS_MSG']); ?><br />";
                                                    }
                                                    if (npass == '') {
                                                        err += "<?= addslashes($langage_lbl['LBL_NEW_PASS_MSG']); ?><br />";
                                                    }
                                                    if (npass.length < 6) {
                                                        err += "<?= addslashes($langage_lbl['LBL_PASS_LENGTH_MSG']); ?><br />";
                                                    }
                                                    if (npass.length > 16) {
                                                        err += "<?= addslashes($langage_lbl['LBL_PASS__MAX_LENGTH_MSG']); ?><br />";
                                                    }
                                                    if (ncpass == '') {
                                                        err += "<?= addslashes($langage_lbl['LBL_REPASS_MSG']); ?><br />";
                                                    }

                                                    if (err == "") {
                                                        if (npass != ncpass)
                                                            err += "<?= addslashes($langage_lbl['LBL_PASS_NOT_MATCH']); ?><br />";
                                                    }
                                                    if (err == "")
                                                    {
                                                        $.ajax({
                                                            type: "POST",
                                                            url: 'ajax_check_password_a.php',
                                                            data: {cpass: cpass},
                                                            success: function (dataHtml)
                                                            {
                                                                if (dataHtml.trim() == 1) {
                                                                    editProfile('pass');
                                                                    return false;
                                                                } else {
                                                                    err += "<?= addslashes($langage_lbl['LBL_INCCORECT_CURRENT_PASS_ERROR_MSG']); ?><BR>";
                                                                    $('#cpass').val('');
                                                                    $('#npass').val('');
                                                                    $('#ncpass').val('');
                                                                    bootbox.dialog({
                                                                        message: "<h3>" + err + "</h3>",
                                                                        buttons: {
                                                                            danger: {
                                                                                label: "Ok",
                                                                                className: "btn-danger",
                                                                            },
                                                                        }
                                                                    });
                                                                    return false;
                                                                }
                                                            }
                                                        });
                                                    } else {
                                                        $('#cpass').val('');
                                                        $('#npass').val('');
                                                        $('#ncpass').val('');
                                                        bootbox.dialog({
                                                            message: "<h3>" + err + "</h3>",
                                                            buttons: {
                                                                danger: {
                                                                    label: "Ok",
                                                                    className: "btn-danger",
                                                                },
                                                            }
                                                        });

                                                        return false;
                                                    }
                                                }

                                                function editPro(action)
                                                {
                                                    editProfile(action);
                                                    return false;
                                                }

                                                function editProfile(action)
                                                {
                                                    var chk = '<?php echo SITE_TYPE; ?>';

                                                    if (action == 'login')
                                                    {
                                                        data = $("#frm1").serialize();
                                                    }
                                                    if (action == 'address')
                                                    {
                                                        data = $("#frm2").serialize();
                                                    }
                                                    if (action == 'pass')
                                                    {
                                                        data = $("#frm3").serialize();
                                                    }
                                                    if (action == 'lang')
                                                    {
                                                        data = $("#frm4").serialize();
                                                    }
                                                    if (action == 'vat')
                                                    {
                                                        data = $("#frm5").serialize();
                                                    }
                                                    if (action == 'access')
                                                    {
                                                        data = $("#frm10").serialize();
                                                    }
                                                    if (action == 'bankdetail')
                                                    {
                                                        data = $("#frm6").serialize();
                                                    }

                                                    var request = $.ajax({
                                                        type: "POST",
                                                        url: 'organization_profile_action.php',
                                                        data: data,
                                                        success: function (data)
                                                        {
                                                            console.log(data);
                                                            if (data == 0) {
                                                                window.location = "organization-profile?success=0&var_msg=" + "<?= addslashes($langage_lbl['LBL_MOBILE_EXIST']); ?>";
                                                                return false;
                                                            } else if (data == 2) {
                                                                window.location = "organization-profile?success=0&var_msg=" + "<?= addslashes($langage_lbl['LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT']); ?>";
                                                                return false;
                                                            } else {
                                                                window.location = 'organization-profile?success=1&var_msg=' + "<?= addslashes($langage_lbl['LBL_PORFILE_UPDATE_MSG']); ?>";
                                                                return false;
                                                            }
                                                        }
                                                    });

                                                    request.fail(function (jqXHR, textStatus) {
                                                        alert("Request failed: " + textStatus);
                                                        return true;
                                                    });
                                                }

                                                function changeCode(id)
                                                {
                                                    var request = $.ajax({
                                                        type: "POST",
                                                        url: 'change_code.php',
                                                        data: 'id=' + id,
                                                        success: function (data)
                                                        {
                                                            selectedCountryCode = id;
                                                            //$('select[name^="vCountry"]').removeAttr("selected");
                                                            //document.getElementById('vCountry').value = id;
                                                            //$('select[name^="vCountry"] option[value="'+id+'"]').attr("selected","selected");
                                                            document.getElementById("code").value = data;
                                                            //var input = document.getElementById("vPhone");
                                                            //input.focus();
                                                        }
                                                    });
                                                }
                                                function checkPhoneCountry() {
                                                    var user = '<?= $user ?>';
                                                    var vCountry = $("#vCountry").val();
                                                    var vPhone = $("#vPhone").val();
                                                    var dataapost = {};
                                                    if (user == 'company') {
                                                        dataapost.iCompanyId = "<?= $_SESSION['sess_iUserId']; ?>";
                                                    } else if (user == 'organization') {
                                                        dataapost.iOrganizationId = "<?= $_SESSION['sess_iUserId']; ?>";
                                                    } else {
                                                        dataapost.iDriverId = "<?= $_SESSION['sess_iUserId']; ?>";
                                                    }
                                                    dataapost.usertype = user;
                                                    dataapost.vCountry = vCountry;
                                                    dataapost.vPhone = vPhone;
                                                    if (vPhone == "") {
                                                        var errormessage = "<?= addslashes($langage_lbl['LBL_PHONE_VALID_MSG']); ?>";
                                                        alert(errormessage);
                                                        return false;
                                                    }
                                                    $.ajax({
                                                        type: "POST",
                                                        url: 'ajax_driver_mobile_new.php',
                                                        data: dataapost,
                                                        success: function (data)
                                                        {
                                                            if (data == 'deleted') {
                                                                var errormessage = "<?= addslashes($langage_lbl['LBL_MOBILE_EXIST']); ?>";
                                                                $("#vPhone").val('');
                                                                alert(errormessage);
                                                                return false;
                                                            } else if (data == 'false') {
                                                                var errormessage = "<?= addslashes($langage_lbl['LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT']); ?>";
                                                                //$("#vPhone").val('');
                                                                alert(errormessage);
                                                                return false;
                                                            } else {
                                                                return true;
                                                            }
                                                        }
                                                    });
                                                }
                                                function setCity(id, selected)
                                                {
                                                    var fromMod = 'driver';
                                                    var request = $.ajax({
                                                        type: "POST",
                                                        url: 'change_stateCity.php',
                                                        data: {stateId: id, selected: selected, fromMod: fromMod},
                                                        success: function (dataHtml)
                                                        {
                                                            $("#vCity").html(dataHtml);
                                                        }
                                                    });
                                                }

                                                function setState(id, selected)
                                                {
                                                    var fromMod = 'driver';
                                                    var request = $.ajax({
                                                        type: "POST",
                                                        url: 'change_stateCity.php',
                                                        data: {countryId: id, selected: selected, fromMod: fromMod},
                                                        success: function (dataHtml)
                                                        {
                                                            $("#vState").html(dataHtml);
                                                            if (selected == '')
                                                                setCity('', selected);
                                                        }
                                                    });
                                                }

                                                setState('<?php echo $db_user[0]['vCountry']; ?>', '<?php echo $db_user[0]['vState']; ?>');
                                                setCity('<?php echo $db_user[0]['vState']; ?>', '<?php echo $db_user[0]['vCity']; ?>');
        </script>
        <script type="text/javascript">
            user = '<?= $user ?>';
            var vCountry = $("#vCountry").val();
            var dataa = {};
            if (user == 'company') {
                dataa.iCompanyId = "<?= $_SESSION['sess_iUserId']; ?>";
            } else if (user == 'organization') {
                dataa.iOrganizationId = "<?= $_SESSION['sess_iUserId']; ?>";
            } else {
                dataa.iDriverId = "<?= $_SESSION['sess_iUserId']; ?>";
            }
            dataa.usertype = user;
            dataa.vCountry = vCountry;
            var errormessage;
            $('#frm1').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block error',
                errorElement: 'span',
                errorPlacement: function (error, element) {
                    if (element.attr("name") == "vCurrency")
                        error.appendTo('#vCurrencyCheck');
                    else if (element.attr("name") == "vCountry")
                        error.appendTo('#vCountryCheck');
                    else
                        error.insertAfter(element);
                },
                onkeyup: function (element, event) {
                    if (event.which === 9 && this.elementValue(element) === "") {
                        return;
                    } else {
                        this.element(element);
                    }
                },
                rules: {
                    email: {required: true, email: true,
                        remote: {
                            url: 'ajax_validate_email.php',
                            type: "post",
                            cache: false,
                            data: {
                                id: function (e) {
                                    return $('#in_email').val();
                                },
                                usr: function (e) {
                                    return user;
                                },
                                uid: function (e) {
                                    return $("#u_id1").val();
                                }
                            },
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "<?= addslashes($langage_lbl['LBL_CHECK_DELETE_ACCOUNT']); ?>";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "<?= addslashes($langage_lbl['LBL_EMAIL_EXISTS_MSG']); ?>";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                            async: false
                        }
                    },
                    name: {required: function (e) {
                            return $('input[name=user_type]').val() == 'driver';
                        }, minlength: function (e) {
                            if ($('input[name=user_type]').val() == 'driver') {
                                return 2;
                            } else {
                                return false;
                            }
                        }, maxlength: function (e) {
                            if ($('input[name=user_type]').val() == 'driver') {
                                return 30;
                            } else {
                                return false;
                            }
                        }},
                    lname: {required: function (e) {
                            return $('input[name=user_type]').val() == 'driver';
                        }, minlength: function (e) {
                            if ($('input[name=user_type]').val() == 'driver') {
                                return 2;
                            } else {
                                return false;
                            }
                        }, maxlength: function (e) {
                            if ($('input[name=user_type]').val() == 'driver') {
                                return 30;
                            } else {
                                return false;
                            }
                        }},
                    vCompany: {required: function (e) {
                            return $('input[name=user_type]').val() == 'company';
                        }, minlength: function (e) {
                            if ($('input[name=user_type]').val() == 'company') {
                                return 2;
                            } else {
                                return false;
                            }
                        }, maxlength: function (e) {
                            if ($('input[name=user_type]').val() == 'company') {
                                return 30;
                            } else {
                                return false;
                            }
                        }},
                    vPhone: {required: true, minlength: 3, digits: true,
                        /*remote: {
                         url: 'ajax_driver_mobile_new.php',
                         type: "post",
                         data: dataa,
                         dataFilter: function (response) {
                         //response = $.parseJSON(response);
                         if (response == 'deleted') {
                         errormessage = "<?= addslashes($langage_lbl['LBL_MOBILE_EXIST']); ?>";
                         return false;
                         } else if (response == 'false') {
                         errormessage = "<?= addslashes($langage_lbl['LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT']); ?>";
                         return false;
                         } else {
                         return true;
                         }
                         },
                         async: false
                         }*/
                    }
                },
                messages: {
                    email: {remote: function () {
                            return errormessage;
                        }},
                    vCompany: {
                        //required: 'Company Name is required.',
                        //minlength: 'Company Name at least 2 characters long.',
                        //maxlength: 'Please enter less than 30 characters.'
                    },
                    name: {
                        //required: 'First Name is required.',
                        //minlength: 'First Name at least 2 characters long.',
                        //maxlength: 'Please enter less than 30 characters.'
                    },
                    lname: {
                        //required: 'Last Name is required.',
                        //minlength: 'Last Name at least 2 characters long.',
                        //maxlength: 'Please enter less than 30 characters.'
                    },
                    vPhone: {
                        //minlength: 'Please enter at least three Number.', 
                        //digits: 'Please enter proper mobile number.', 
                        remote: function () {
                            return errormessage;
                        }}
                },
                submitHandler: function () {
                    $("#vCountry").prop('disabled',false);
                    if ($("#frm1").valid()) {
                        editProfile('login');
                    }
                }
            });

        </script>
    </body>
</html>
