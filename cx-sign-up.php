<?php
include_once("common.php");
$generalobj->go_to_home();
$script = "Driver Sign-Up";

$sql = "SELECT * FROM currency WHERE eStatus='Active' ORDER BY iDispOrder ASC";
$db_currency = $obj->MySQLSelect($sql);

$sql = "SELECT * FROM country WHERE eStatus = 'Active'";
$db_code = $obj->MySQLSelect($sql);

$sql = "SELECT * from language_master where eStatus = 'Active' ORDER BY iDispOrder ASC ";
$db_lang = $obj->MySQLSelect($sql);

$sqlUserProfileMaster = "SELECT iUserProfileMasterId,vProfileName FROM user_profile_master WHERE eStatus = 'Active' order by vProfileName  asc";
$dbUserProfileMaster = $obj->MySQLSelect($sqlUserProfileMaster);

if (empty($SHOW_CITY_FIELD)) {
    $SHOW_CITY_FIELD = $generalobj->getConfigurations("configurations", "SHOW_CITY_FIELD");
}

$profileName = "vProfileName_" . $_SESSION['sess_lang'];

$vlangCode = $_SESSION['sess_lang'];

$sqldef = "select * from  currency where eStatus='Active' && eDefault='Yes' ORDER BY  iDispOrder ASC";
$db_defcurrency = $obj->MySQLSelect($sqldef);
$defaultCurrency = $db_defcurrency[0]['vName'];

$meta_arr = $generalobj->getsettingSeo(5);
$Mobile = $MOBILE_VERIFICATION_ENABLE;
$error = isset($_REQUEST['error']) ? $_REQUEST['error'] : '';
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : '';
$sql = "SELECT * FROM country WHERE eStatus = 'Active' ORDER BY vCountry ASC";
$db_country = $obj->MySQLSelect($sql);
if (isset($_SESSION['postDetail'])) {
    $_REQUEST = $_SESSION['postDetail'];
    $user_type = isset($_REQUEST['user_type']) ? $_REQUEST['user_type'] : 'driver';
    $vEmail = isset($_REQUEST['vEmail']) ? $_REQUEST['vEmail'] : '';
    $vCountry = isset($_REQUEST['vCountry']) ? $_REQUEST['vCountry'] : '';
    $vCode = isset($_REQUEST['vCode']) ? $_REQUEST['vCode'] : '';
    $vPhone = isset($_REQUEST['vPhone']) ? $_REQUEST['vPhone'] : '';
    $vFirstName = isset($_REQUEST['vFirstName']) ? $_REQUEST['vFirstName'] : '';
    $vLastName = isset($_REQUEST['vLastName']) ? $_REQUEST['vLastName'] : '';
    $vCompany = isset($_REQUEST['vCompany']) ? $_REQUEST['vCompany'] : '';
    $vCaddress = isset($_REQUEST['vCaddress']) ? $_REQUEST['vCaddress'] : '';
    $vCadress2 = isset($_REQUEST['vCadress2']) ? $_REQUEST['vCadress2'] : '';
    $vState = isset($_REQUEST['vState']) ? $_REQUEST['vState'] : '';
    $vCity = isset($_REQUEST['vCity']) ? $_REQUEST['vCity'] : '';
    $vZip = isset($_REQUEST['vZip']) ? $_REQUEST['vZip'] : '';
    $vVat = isset($_REQUEST['vVat']) ? $_REQUEST['vVat'] : '';
    $vCurrencyPassenger = isset($_REQUEST['vCurrencyPassenger']) ? $_REQUEST['vCurrencyPassenger'] : '';
    /* $vDay = isset($_REQUEST['vDay']) ? $_REQUEST['vDay'] : '';
      $vMonth = isset($_REQUEST['vMonth']) ? $_REQUEST['vMonth'] : '';
      $vYear = isset($_REQUEST['vYear']) ? $_REQUEST['vYear'] : ''; */
    unset($_SESSION['postDetail']);
}
$vRefCode = isset($_REQUEST['vRefCode']) ? $_REQUEST['vRefCode'] : '';
if (!empty($_COOKIE['vUserDeviceTimeZone'])) {
    $vUserDeviceTimeZone = $_COOKIE['vUserDeviceTimeZone'];
    $sql = "SELECT vCountryCode FROM country WHERE vTimeZone LIKE '%" . $vUserDeviceTimeZone . "%' OR vAlterTimeZone LIKE '%" . $vUserDeviceTimeZone . "%' ORDER BY  vCountry ASC";
    $db_country_code = $obj->MySQLSelect($sql);
    if (!empty($db_country_code[0]['vCountryCode'])) {
        $DEFAULT_COUNTRY_CODE_WEB = $db_country_code[0]['vCountryCode'];
    }
}
//if(empty($template)) $template = 'Cubex';

$bg_reg_image = "assets/img/apptype/$template/login-bg.jpg";
$db_reg_src = "assets/img/apptype/$template/login-img.jpg";

$db_signup = $generalobj->getStaticPage(50, $_SESSION['sess_lang']);

$regpage_title = json_decode($db_signup['page_title'], true);
$regpage_desc = json_decode($db_signup['page_desc'], true);

if (empty($regpage_desc['user']) && empty($regpage_title['user'])) {
    $db_signup = $generalobj->getStaticPage(50, 'EN');
    $regpage_title = json_decode($db_signup['page_title'], true);
    $regpage_desc = json_decode($db_signup['page_desc'], true);
}

if (!empty($db_signup['vImage1']))
    $bg_reg_image = "assets/img/apptype/$template/" . $db_signup['vImage1'];
//if(!empty($db_signup['vImage'])) $db_reg_src = "assets/img/page/".$db_signup['vImage'];

$catdata = serviceCategories;
$service_cat_list = json_decode($catdata, true);

foreach ($service_cat_list as $k => $val) {
    $iServiceIdArr[] = $val['iServiceId'];
}
$serviceIds = implode(",", $iServiceIdArr);

$service_category = "select iServiceId,vServiceName_" . $default_lang . " as servicename from service_categories where iServiceId IN (" . $serviceIds . ") AND eStatus = 'Active'";
$db_service_category = $obj->MySQLSelect($service_category);

$become_restaurant = '';
if(strtoupper(DELIVERALL) == "YES") {
    //if (count($iServiceIdArr) > 1) {
    if (count($iServiceIdArr) == 1 && $iServiceIdArr[0]==1) {
        $become_restaurant = $langage_lbl['LBL_RESTAURANT_TXT'];
    } else {
        $become_restaurant = $langage_lbl['LBL_STORE'];
    }
}
$site_type=0;
if(SITE_TYPE=='Demo') {
    $site_type=1;    
}
$cubeDeliverallOnly = isDeliverAllOnlySystem(); // Added By HJ On 16-06-2020 For Custome Setup CubejekX Deliverall
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
        <title><?php echo $meta_arr['meta_title']; ?></title>
        <meta name="keywords" value="<?= $meta_arr['meta_keyword']; ?>"/>
        <meta name="description" value="<?= $meta_arr['meta_desc']; ?>"/>
        <!-- Default Top Script and css -->
        <?php include_once("top/top_script.php"); ?>
        <!-- End: Default Top Script and css-->
    </head>
    <body id="wrapper">
        <!-- home page -->
        <!-- home page -->
        <?php if ($template != 'taxishark') { ?>
            <div id="main-uber-page">
            <?php } ?>
            <!-- Left Menu -->
            <?php include_once("top/left_menu.php"); ?>
            <!-- End: Left Menu-->
            <!-- Top Menu -->
            <?php include_once("top/header_topbar.php"); ?>
            <!-- End: Top Menu-->
            <!-- First Section -->
            <?php include_once("top/header.php"); ?>
            <!-- End: First Section -->
            <div class="login-main parallax-window" style="background-image:url(<?php echo $bg_reg_image; ?>)">
                <div class="login-inner">
                    <div class="login-block">
                        <div class="login-block-heading  login-newblock">
                            <label class="loginlabel"><?= $langage_lbl['LBL_REGISTER_AS'] ?></label>
                            <div class="tabholder login-tabholder">
                                <ul class="tab-switch">
                                    <li class="active" data-id="user" data-desc="<?= $regpage_title['user']; ?>"><a href="JavaScript:void(0);"><?= $langage_lbl['LBL_SIGNIN_RIDER'] ?></a></li>
                                    <li data-id="provider" data-desc="<?= $regpage_title['provider'] ?>"><a href="JavaScript:void(0);"><?= $langage_lbl['LBL_SIGNIN_DRIVER'] ?></a></li>
                                    <? if(strtoupper(ONLYDELIVERALL) != "YES" && $cubeDeliverallOnly == false) { ?><li data-id="company" data-desc="<?= $regpage_title['company'] ?>"><a href="JavaScript:void(0);"><?= $langage_lbl['LBL_COMPANY_SIGNIN'] ?></a></li>
                                    <? } if (!empty($become_restaurant)) { ?><li data-id="restaurant1" data-desc="<?= $regpage_title['company'] ?>"><a href="JavaScript:void(0);"><?= $become_restaurant ?></a></li><? } ?>
                                    <? if(strtoupper($ENABLE_CORPORATE_PROFILE)=='YES') { ?><li data-id="organization" data-desc="<?= $regpage_title['org'] ?>"><a href="JavaScript:void(0);"><?= $langage_lbl['LBL_ORGANIZATION'] ?></a></li><? } ?>
                                </ul>
                            </div>
                        </div>
                        <div class="login-right full-width">
                            <div class="login-data-inner">
                                <input type="hidden" placeholder="" name="userType" id="userType" class="create-account-input" value="user" />
                                <!-- <p>Lorem Ipsum is simply dummy text of the printing and type setting industry. Lorem Ipsum has been the industry's.</p> -->
                                <div class="gen-forms user active">
                                    <form name="frmsignup" id="frmsignup" action="signuprider_a.php" method="POST">

                                        <?php if ($error != "" && ($_REQUEST['type'] == 'user' || empty($_REQUEST['type']))) { ?>
                                            <div class="row">
                                                <div class="col-sm-12 alert alert-danger">
                                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                                    <?= $var_msg; ?>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <div class="partation">
                                            <h1><?= $langage_lbl['LBL_ACC_INFO'] ?></h1>
                                            <div class="form-group onethird newrow">
                                                <label><?= $langage_lbl['LBL_EMAIL_TEXT_SIGNUP']; ?> <span class="red">*</span></label>
                                                <input type="email" Required name="vEmail" class="create-account-input" id="vEmail_verify" value="<?php echo $vEmail; ?>" />
                                            </div>

                                            <div class="form-group onethird newrow">
                                                <div class="relative_ele">
                                                    <label><?= $langage_lbl['LBL_PASSWORD']; ?> <span class="red">*</span></label>
                                                    <input id="pass" type="password" name="vPassword" class="create-account-input create-account-input1 " required value="" />
                                                    <!--<button type="button" onclick="showHidePassword('pass')" id="eye"><img src="assets/img/eye.png" alt="eye"/></button>-->
                                                </div>
                                            </div>
                                            <?php if ($REFERRAL_SCHEME_ENABLE == 'Yes') { ?>
                                                <div class="form-group onethird newrow">
                                                    <label id="referlbl"><?= $langage_lbl['LBL_SIGNUP_REFERAL_CODE']; ?></label>
                                                    <input id="vRefCode" type="text" name="vRefCode" class="create-account-input create-account-input1 vRefCode_verify" value="<?php echo $vRefCode; ?>" onBlur="validate_refercode(this.value)"/>
                                                    <input type="hidden" placeholder="" name="iRefUserId" id="iRefUserId"  class="create-account-input" value="" />
                                                    <input type="hidden" placeholder="" name="eRefType" id="eRefType" class="create-account-input" value=""  />
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <div class="partation">
                                            <h1><?= $langage_lbl['LBL_BASIC_INFO'] ?></h1>
                                            <div class="form-group onethird newrow">
                                                <label><?= $langage_lbl['LBL_SIGN_UP_FIRST_NAME_HEADER_TXT']; ?> <span class="red">*</span></label>
                                                <input name="vName" type="text" onkeypress="return IsAlphaNumeric(event, this.id);" class="create-account-input" id="vName"value="<?php echo $vFirstName; ?>" required />
                                                <span id="vName_spaveerror" style="color: Red; display: none;font-size: 11px;">* White space not allowed</span>
                                            </div>
                                            <div class="form-group onethird newrow">
                                                <label><?= $langage_lbl['LBL_SIGN_UP_LAST_NAME_HEADER_TXT']; ?> <span class="red">*</span></label>
                                                <input name="vLastName" type="text" onkeypress="return IsAlphaNumeric(event, this.id);" class="create-account-input create-account-input1" id="vLastName" value="<?php echo $vLastName; ?>" required />
                                                <span id="vLastName_spaveerror" style="color: Red; display: none;font-size: 11px;">* White space not allowed</span>
                                            </div>
                                            <div class="form-group onethird newrow">
                                                <select class="" required name='vCountry' id="vCountry" onChange="setState(this.value, '');" >
                                                    <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] ?></option>
                                                    <? for ($i = 0; $i < count($db_country); $i++) { ?>
                                                        <option value = "<?= $db_country[$i]['vCountryCode'] ?>" <? if ($DEFAULT_COUNTRY_CODE_WEB == $db_country[$i]['vCountryCode']) { ?>selected<? } ?>><?= $db_country[$i]['vCountry'] ?></option>
                                                    <? } ?>
                                                </select>
                                            </div>
                                            <div class="form-group onethird phone-column newrow">
                                                <label><?= $langage_lbl['LBL_SIGNUP_777-777-7777']; ?> <span class="red">*</span></label>
                                                <!--<select name="vPhoneCode" id="code">
                                                    <option value="91">+91</option>
                                                </select>-->
                                                <input type="text" name="vPhoneCode" readonly id="code" class="phonecode" />
                                                <input required type="text"  id="vPhone" value="<?php echo $vPhone; ?>" class="create-account-input create-account-input1 vPhone_verify" name="vPhone"/>
                                            </div>
                                            <div class="form-group onethird newrow">
                                                <select name="vLang" class="">
                                                    <? for ($i = 0; $i < count($db_lang); $i++) { ?>
                                                        <option value="<?= $db_lang[$i]['vCode'] ?>" <?
                                                        if ($db_lang[$i]['eDefault'] == 'Yes') {
                                                            echo 'selected';
                                                        }
                                                        ?>>
                                                        <?= $db_lang[$i]['vTitle'] ?>
                                                        </option>
<? } ?>
                                                </select>
                                            </div>
                                            <div class="form-group onethird newrow">
                                                <select class="" required name = 'vCurrencyPassenger'>
                                                        <?php for ($i = 0; $i < count($db_currency); $i++) { ?>
                                                        <option value = "<?= $db_currency[$i]['vName'] ?>" <? if ($db_currency[$i]['eDefault'] == "Yes") { ?>selected<? } ?>>
                                                        <?= $db_currency[$i]['vName'] ?>
                                                        </option>
<? } ?>
                                                </select>
                                            </div>

                                            <div class="form-group  captcha-column newrow">
<?php include_once("recaptcha.php"); ?>
                                                <!--<span id="recaptcha-msg" style="display: none;" class="error">This field is required.</span>-->
                                            </div>
                                            <div class="onethird check-combo">
                                                <div class="check-main newrow">
                                                    <span class="check-hold">
                                                        <input type="checkbox" name="remember-me" id="c1" value="remember">
                                                        <span class="check-button"></span>
                                                    </span>
                                                </div><label for="c1"><?php echo $langage_lbl['LBL_SIGNUP_Agree_to']; ?> <a href="terms_condition.php" target="_blank"><?= $langage_lbl['LBL_SIGN_UP_TERMS_AND_CONDITION']; ?></a></label>
                                            </div>
                                            <div class="button-block">
                                                <div class="btn-hold">
                                                    <input type="submit" name="SUBMIT" value="<?= $langage_lbl['LBL_REGISTER_SMALL']; ?>"/>
                                                    <img src="assets/img/apptype/<?php echo $template; ?>/arrow.svg" alt="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="member-txt">
                                        <?= $langage_lbl['LBL_ALREADY_HAVE_ACC']; ?> <a href="sign-in" tabindex="5" ><?= $langage_lbl['LBL_SIGN_IN']; ?></a>
                                        </div>
<?php if ($PASSENGER_FACEBOOK_LOGIN == "Yes" || $PASSENGER_GOOGLE_LOGIN == "Yes" || $PASSENGER_LINKEDIN_LOGIN == "Yes") { ?>
                                            <div class="aternate-login" data-name="OR"></div>
                                            <div class="soc-login-row">
                                                <label><?= $langage_lbl['LBL_REGISTER_WITH_SOCIAL_ACC']; ?></label>
                                                <ul class="social-list" id="rider-social">
                                                    <?php if ($PASSENGER_FACEBOOK_LOGIN == "Yes") { ?>
                                                        <li><a target="_blank" href="facebook-rider/rider" tabindex="6"><img src="assets/img/page/facebook-new.png" alt="Facebook"></a></li>
                                                    <?php } if ($PASSENGER_LINKEDIN_LOGIN == "Yes") { ?>
                                                        <li><a target="_blank" href="linkedin-rider/rider" tabindex="7"><img src="assets/img/page/linkedin-new.png" alt="Linkedin"></a></li>
                                                    <?php } if ($PASSENGER_GOOGLE_LOGIN == "Yes") { ?>
                                                        <li><a target="_blank" href="google/rider" tabindex="8"><img src="assets/img/page/google-new.png" alt="Google Plus"></a></li>
                                            <?php } ?>
                                                </ul>
                                            </div>
<?php } ?>
                                        <input type = 'reset' class = 'resetform' value = 'reset' style="display:none"/>
                                    </form>
                                </div>

                                <div class="gen-forms provider"><?php include("cx-sign-up-provider.php"); ?></div>


                                <div class="gen-forms company restaurant1"><?php include("cx-sign-up-company.php"); ?></div>
                                <!--<div class="gen-forms restaurant1"><?php //include("cx-sign-up-company.php");   ?></div>-->


                                <div class="gen-forms organization"><?php include("cx-sign-up-org.php"); ?></div>


                            </div>
                        </div>
                        <div class="login-block-footer for-registration">
                            <div class="login-caption active" id="user">
<?= $regpage_desc['user']; ?>
                                <p><?= $regpage_title['user']; ?></p>
                            </div>
                            <div class="login-caption" id="provider">
<?= $regpage_desc['provider']; ?>
                                <p><?= $regpage_title['provider']; ?></p>
                            </div>
                            <div class="login-caption" id="company">
<?= $regpage_desc['company']; ?>
                                <p><?= $regpage_title['company']; ?></p>
                            </div>
                            <div class="login-caption" id="restaurant1">
<?= $regpage_desc['restaurant']; ?>
                                <p><?= $regpage_title['restaurant']; ?></p>
                            </div>
                            <div class="login-caption" id="organization">
<?= $regpage_desc['org']; ?>
                                <p><?= $regpage_title['org']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- home page end-->
            <!-- footer part -->
            <?php include_once('footer/footer_home.php'); ?>

            <div style="clear:both;"></div>
        <?php if ($template != 'taxishark') { ?>
            </div>
        <?php } ?>
        <!-- footer part end -->
        <!-- Footer Script -->
        <?php
        include_once('top/footer_script.php');

        $lang = get_langcode($vlangCode);
        ?>
        <!--<script type="text/javascript" src="assets/js/validation/jquery.validate.min.js" ></script>-->
       <?php if ($lang != 'en') { 
           ?>
            <!-- <script type="text/javascript" src="assets/js/validation/localization/messages_<?= $lang; ?>.js" ></script> -->
            <? //include_once('otherlang_validation.php');?>
<?php } ?>
        <script type="text/javascript" src="assets/js/validation/additional-methods.js" ></script>

        <script>


                                                    var usertype = $("input[type=hidden][name=userType]").val();

                                                    $(document).ready(function () {

                                                        setState('<?php echo $DEFAULT_COUNTRY_CODE_WEB; ?>');
                                                        setStated('<?php echo $DEFAULT_COUNTRY_CODE_WEB; ?>');
                                                        setStatec('<?php echo $DEFAULT_COUNTRY_CODE_WEB; ?>');
                                                        setStateo('<?php echo $DEFAULT_COUNTRY_CODE_WEB; ?>');


                                                        var refcode = $('#vRefCode').val();
                                                        if (refcode != "") {
                                                            validate_refercode(refcode);
                                                        }

                                                        type = '<?php echo $_REQUEST['type'] ?>';
                                                        if (type != '') {
                                                            if (type == 'restaurant' || type == 'store') {
                                                                $('.tab-switch li[data-id="restaurant1"]').get(0).click();
                                                            } else if (type == 'rider' || type == 'user' || type == 'sender') {
                                                                $('.tab-switch li[data-id="user"]').get(0).click();
                                                            } else if (type == 'provider' || type == 'driver' || type == 'carrier') {
                                                                $('.tab-switch li[data-id="provider"]').get(0).click();
                                                            } else {
                                                                $('.tab-switch li[data-id="' + type + '"]').get(0).click();
                                                            }
                                                        }
                                                        //$(".resetform").click();
                                                        $(".error").html('');
                                                    });
                                                    var specialKeys = new Array();
                                                    function IsAlphaNumeric(e, inputId) {
                                                        var keyCode = e.keyCode == 0 ? e.charCode : e.keyCode;
                                                        if (keyCode == 32) {
                                                            var ret = ((keyCode >= 48 && keyCode <= 57) || (keyCode >= 65 && keyCode <= 90) || (keyCode >= 97 && keyCode <= 122) || (specialKeys.indexOf(e.keyCode) != -1 && e.charCode != e.keyCode));
                                                            $("#" + inputId + "_spaveerror").show();
                                                            setTimeout(function () {
                                                                $("#" + inputId + "_spaveerror").hide();
                                                            }, 5000);
                                                            return ret;
                                                        }
                                                    }
                                                    $(".tab-switch li").on("click", function () {
                                                        var dataId = $(this).attr("data-id");
                                                        $("#signinlink").attr("href", dataId);
                                                        if (dataId == 'provider') {
                                                            $("input[type=hidden][name=userType]").val('driver');
                                                            $("input[type=hidden][name=user_type]").val('driver');
                                                        } else {
                                                            $("input[type=hidden][name=userType]").val(dataId);
                                                            $("input[type=hidden][name=user_type]").val(dataId);
                                                        }
                                                        //$(".resetform").click();
                                                        $(".error").html('');
                                                        if (dataId == 'company') {
                                                            //document.getElementById("2").checked=true;
                                                            //$('input[name="user_type"]:checked').trigger('click');
                                                            //$("input:radio:second").click();
                                                            //$("#2").prop("checked", true);
                                                            $('#company_store').click();
                                                            $("#signinlink").attr("href", "sign-in?type=company");
                                                        }

                                                        if (dataId == 'restaurant1') {
                                                            $('#company_store1').click();
                                                            $("#company_store1").attr('checked', 'checked');
                                                            $("#company_store1").prop("checked", true);
                                                            $("#signinlink").attr("href", "sign-in?type=restaurant");
                                                        }

                                                        //grecaptcha.reset();
                                                        $.ajax({
                                                            type: "POST",
                                                            url: "<?= $tconfig["tsite_url"] ?>recaptcha.php?type=1234",
                                                            dataType: "html",
                                                            success: function (dataHtml2) {
                                                                if (dataHtml2 != "") {
                                                                    $('.captchauser').html(dataHtml2);
                                                                }
                                                            }, error: function (dataHtml2) {
                                                            }
                                                        });

                                                    });

                                                    function show_company_store(user) {
                                                        $("input[type=hidden][name=userType]").val(user);
                                                        $("input[type=hidden][name=user_type]").val(user);
                                                        if (user == 'company') {
                                                            $(".storedata").hide();
                                                            $(".comdata").show();
                                                            $("#frmsignupc").attr('action', 'signup_a.php');
                                                        } else if (user == 'store') {
                                                            $(".storedata").show();
                                                            $(".comdata").hide();
                                                            $("#frmsignupc").attr('action', 'signup_r.php');
                                                        }
                                                    }

                                                    var errormessage;

                                                    $('#frmsignup').validate({
                                                        ignore: 'input[type=hidden]',
                                                        errorClass: 'help-block error',
                                                        onkeypress: true,
                                                        errorElement: 'span',
                                                        errorPlacement: function (error, e) {
                                                            e.parents('.newrow').append(error);
                                                        },
                                                        highlight: function (e) {
                                                            $(e).closest('.newrow').removeClass('has-success has-error').addClass('has-error');
                                                            $(e).closest('.newrow input').addClass('has-shadow-error');
                                                            $(e).closest('.help-block').remove();
                                                        },
                                                        success: function (e) {
                                                            e.prev('input').removeClass('has-shadow-error');
                                                            e.closest('.newrow').removeClass('has-success has-error');
                                                            e.closest('.help-block').remove();
                                                            e.closest('.help-inline').remove();
                                                        },
                                                        rules: {
                                                            vEmail: {required: true, email: true,
                                                                remote: {
                                                                    url: 'ajax_validate_email_new.php',
                                                                    type: "post",
                                                                    data: {
                                                                        iDriverId: '',
                                                                        usertype: 'user'
                                                                    },
                                                                    dataFilter: function (response) {
                                                                        //response = $.parseJSON(response);
                                                                        //response = response.trim();
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
                                                                }
                                                            },
                                                            vPassword: {required: true, noSpace: true, minlength: 6, maxlength: 16},
                                                            vPhone: {required: true, minlength: 3, digits: true,
                                                                remote: {
                                                                    url: 'ajax_driver_mobile_new.php',
                                                                    type: "post",
                                                                    data: {
                                                                        iDriverId: '',
                                                                        usertype: function (e) {
                                                                            return $('input[name=userType]').val();
                                                                        },
                                                                        vCountry: function (e) {
                                                                            return $('#vCountry option:selected').val();
                                                                        },
                                                                    },
                                                                    dataFilter: function (response) {
                                                                        //response = $.parseJSON(response);
                                                                        if (response == 'deleted') {
                                                                            errormessage = "<?= addslashes($langage_lbl['LBL_PHONE_CHECK_DELETE_ACCOUNT']); ?>";
                                                                            return false;
                                                                        } else if (response == 'false') {
                                                                            errormessage = "<?= addslashes($langage_lbl['LBL_PHONE_EXIST_MSG']); ?>";
                                                                            return false;
                                                                        } else {
                                                                            return true;
                                                                        }
                                                                    },
                                                                }
                                                            },
                                                            vLastName: {required: true, minlength: 2, maxlength: 30},
                                                            'g-recaptcha-response': {required: function (e) {

                                                                    if (grecaptcha.getResponse() == '') {
                                                                        return true;
                                                                    } else {
                                                                        return false;
                                                                    }
                                                                }},
                                                            'remember-me': {required: true},
                                                        },
                                                        messages: {
                                                            vPassword: {
                                                                //maxlength: 'Please enter less than 16 characters.'
                                                            },
                                                            vEmail: {remote: function () {
                                                                    return errormessage;
                                                                }},
                                                            'remember-me': {required: '<?= addslashes($langage_lbl['LBL_AGREE_TERMS_MSG']); ?>'},
                                                            vPhone: {
                                                                //minlength: 'Please enter at least three Number.', 
                                                                //digits: 'Please enter proper mobile number.', 
                                                                remote: function () {
                                                                    return errormessage;
                                                                }},
                                                            vCompany: {
                                                                //required: 'This field is required.',
                    //minlength: 'Company Name at least 2 characters long.',
                    //maxlength: 'Please enter less than 30 characters.'
                                                            },
                                                            vFirstName: {
                                                                //required: 'This field is required.',
                   // minlength: 'First Name at least 2 characters long.',
                    //maxlength: 'Please enter less than 30 characters.'
                                                            },
                                                            vLastName: {
                                                                //required: 'This field is required.',
                    //minlength: 'Last Name at least 2 characters long.',
                    //maxlength: 'Please enter less than 30 characters.'
                                                            }
                                                        }
                                                    });


                                                    $('#verification').bind('keydown', function (e) {
                                                        if (e.which == 13) {
                                                            check_verification('verify');
                                                            return false;
                                                        }
                                                    });


                                                    function changeCode(id)
                                                    {

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
                                                    /*ajax for unique username*/


                                                    $(document).ready(function () {

                                                        $.validator.addMethod("noSpace", function (value, element) {
                                                            return this.optional(element) || /^\S+$/i.test(value);
                                                        }, "<?= addslashes($langage_lbl['LBL_NO_SPACE_ERROR_MSG']); ?>");

                                                        $("#radio_1").prop("checked", true)
                                                        $("#company_name").removeClass("required");

                                                        var newUser = $("input[name=user_type]:checked").val();
                                                        if (newUser == 'company')
                                                        {
                                                            //$(".company").show();
                                                            //$(".driver").hide();
                                                            /*$("#li_dob").hide();*/
                                                            $("#vRefCode").hide();
                                                            $("#referlbl").hide();
                                                            $('#div-phone').show();
                                                        } else if (newUser == 'driver')
                                                        {
                                                            //$(".company").hide();
                                                            //$(".driver").show();
                                                            /*$("#li_dob").show();*/
                                                            $("#vRefCode").show();
                                                            $("#referlbl").show();
                                                            $('#div-phone').hide();
                                                        }

                                                    });

                                                    function validate_refercode(id) {

                                                        if (id == "") {
                                                            return true;
                                                        } else {


                                                            var request = $.ajax({
                                                                type: "POST",
                                                                url: 'ajax_validate_refercode.php',
                                                                data: 'refcode=' + id,
                                                                success: function (data)
                                                                {
                                                                    if (data == 0) {
                                                                        $("#referCheck").remove();
                                                                        $(".vRefCode_verify").addClass('required-active');
                                                                        $('#refercodeCheck').append('<div class="required-label" id="referCheck" >* <?= addslashes($langage_lbl['LBL_REFER_CODE_ERROR']); ?></div>');
                                                                        $('#vRefCode').attr("placeholder", "<?= addslashes($langage_lbl['LBL_SIGNUP_REFERAL_CODE']); ?>");
                                                                        $('#vRefCode').val("");
                                                                        return false;
                                                                    } else {
                                                                        var reponse = data.split('|');
                                                                        $('#iRefUserId').val(reponse[0]);
                                                                        $('#eRefType').val(reponse[1]);
                                                                    }
                                                                }
                                                            });

                                                        }

                                                    }
                                                    function refreshCaptcha()
                                                    {
                                                        var img = document.images['captchaimg'];
                                                        img.src = img.src.substring(0, img.src.lastIndexOf("?")) + "?rand=" + Math.random() * 1000;
                                                    }
                                                    function setState(id, selected)
                                                    {
                                                        changeCode(id);

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
        </script>

        <script type="text/javascript">

            $(document).ready(function () {
                var refcode = $('#vRefCoded').val();
                if (refcode != "") {
                    validate_refercoded(refcode);
                }
            });


            var errormessage;
            //alert($('input[name=user_type]:checked').val());
            $('#frmsignupd').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block error',
                onkeypress: true,
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.newrow').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.newrow').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.newrow input').addClass('has-shadow-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.prev('input').removeClass('has-shadow-error');
                    e.closest('.newrow').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vEmaild: {required: true, email: true,
                        remote: {
                            url: 'ajax_validate_email_new.php',
                            type: "post",
                            data: {
                                iDriverId: '',
                                usertype: 'driver'
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
                        }
                    },
                    vPassword: {required: true, noSpace: true, minlength: 6, maxlength: 16},
                    vPhone: {required: true, minlength: 3, digits: true,
                        remote: {
                            url: 'ajax_driver_mobile_new.php',
                            type: "post",
                            data: {
                                iDriverId: '',
                                usertype: function (e) {
                                    return $('input[name=userType]').val();
                                },
                                vCountry: function (e) {
                                    return $('#vCountryd option:selected').val();
                                },
                            },
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "<?= addslashes($langage_lbl['LBL_PHONE_CHECK_DELETE_ACCOUNT']); ?>";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "<?= addslashes($langage_lbl['LBL_PHONE_EXIST_MSG']); ?>";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                        }
                    },
                    vFirstName: {required: true, minlength: 2, maxlength: 30},
                    vLastName: {required: true, minlength: 2, maxlength: 30},
                    'g-recaptcha-response': {required: function (e) {
                            if (grecaptcha.getResponse() == '') {
                                //$('#recaptcha-msg').css('display', 'block');
                                return true;
                            } else {
                                //$('#recaptcha-msg').css('display', 'none');
                                return false;
                            }
                        }},
                    'remember-me': {required: true},
                },
                messages: {
                    vPassword: {
                    //maxlength: 'Please enter less than 16 characters.'
                },
                    vEmaild: {remote: function () {
                            return errormessage;
                        }},
                    'remember-me': {required: '<?= addslashes($langage_lbl['LBL_AGREE_TERMS_MSG']); ?>'},
                    vPhone: {
                        //minlength: 'Please enter at least three Number.',
                         //digits: 'Please enter proper mobile number.', 
                         remote: function () {
                            return errormessage;
                        }},
                    vCompany: {
                        //required: 'This field is required.',
                        //minlength: 'Company Name at least 2 characters long.',
                        //maxlength: 'Please enter less than 30 characters.'
                    },
                    vFirstName: {
                        //required: 'This field is required.',
                        //minlength: 'First Name at least 2 characters long.',
                        //maxlength: 'Please enter less than 30 characters.'
                    },
                    vLastName: {
                        //required: 'This field is required.',
                        //minlength: 'Last Name at least 2 characters long.',
                        //maxlength: 'Please enter less than 30 characters.'
                    }
                }
            });


            $('#verificationd').bind('keydown', function (e) {
                if (e.which == 13) {
                    check_verification('verify');
                    return false;
                }
            });


            function changeCoded(id)
            {

                var request = $.ajax({
                    type: "POST",
                    url: 'change_code.php',
                    data: 'id=' + id,
                    success: function (data)
                    {
                        document.getElementById("coded").value = data;
                        //window.location = 'profile.php';
                    }
                });
            }
            /*ajax for unique username*/


            $(document).ready(function () {

                $("#companyd").hide();
                $("#radio_1d").prop("checked", true)
                $("#company_named").removeClass("required");

                var newUser = $("input[name=user_type]:checked").val();
                //$("input[type=hidden][name=userType]").val(newUser);
                if (newUser == 'company')
                {
                    //$(".company").show();
                    //$(".driver").hide();
                    /*$("#li_dob").hide();*/
                    $("#vRefCoded").hide();
                    $("#referlbld").hide();
                    $('#div-phoned').show();
                } else if (newUser == 'driver')
                {
                    //$(".company").hide();
                    //$(".driver").show();
                    /*$("#li_dob").show();*/
                    $("#vRefCoded").show();
                    $("#referlbld").show();
                    $('#div-phoned').hide();
                }

                $("input[type=hidden][name=userType]").val('user');
            });

            function validate_refercoded(id) {

                if (id == "") {
                    return true;
                } else {


                    var request = $.ajax({
                        type: "POST",
                        url: 'ajax_validate_refercode.php',
                        data: 'refcode=' + id,
                        success: function (data)
                        {
                            if (data == 0) {
                                $("#referCheckd").remove();
                                $(".vRefCode_verify").addClass('required-active');
                                $('#refercodeCheckd').append('<div class="required-label" id="referCheck" >* <?= addslashes($langage_lbl['LBL_REFER_CODE_ERROR']); ?></div>');
                                $('#vRefCoded').attr("placeholder", "<?= addslashes($langage_lbl['LBL_SIGNUP_REFERAL_CODE']); ?>");
                                $('#vRefCoded').val("");
                                return false;
                            } else {
                                var reponse = data.split('|');
                                $('#iRefUserIdd').val(reponse[0]);
                                $('#eRefTyped').val(reponse[1]);
                            }
                        }
                    });

                }

            }


            function refreshCaptchad()
            {
                var img = document.images['captchaimgd'];
                img.src = img.src.substring(0, img.src.lastIndexOf("?")) + "?rand=" + Math.random() * 1000;
            }

            function setStated(id, selected)
            {
                changeCoded(id);

                var fromMod = 'driver';
                var request = $.ajax({
                    type: "POST",
                    url: 'change_stateCity.php',
                    data: {countryId: id, selected: selected, fromMod: fromMod},
                    success: function (dataHtml)
                    {
                        $("#vStated").html(dataHtml);
                        if (selected == '')
                            setCity('', selected);
                    }
                });
            }

            function setCityd(id, selected)
            {
                var fromMod = 'driver';
                var request = $.ajax({
                    type: "POST",
                    url: 'change_stateCity.php',
                    data: {stateId: id, selected: selected, fromMod: fromMod},
                    success: function (dataHtml)
                    {
                        $("#vCityd").html(dataHtml);
                    }
                });
            }



            $(document).ready(function () {
                var refcode = $('#vRefCodec').val();
                if (refcode != "") {
                    validate_refercodec(refcode);
                }
            });


            var errormessage;

            $('#frmsignupc').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block error',
                onkeypress: true,
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.newrow').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.newrow').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.newrow input').addClass('has-shadow-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.prev('input').removeClass('has-shadow-error');
                    e.closest('.newrow').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vEmailc: {required: true, email: true,
                        remote: {
                            url: 'ajax_validate_email_new.php',
                            type: "post",
                            data: {
                                iDriverId: '',
                                usertype: 'company',
                                usertype_store: function (e) {
                                    return $('input[name=user_type]').val();
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
                        }
                    },
                    vPassword: {required: true, noSpace: true, minlength: 6, maxlength: 16},
                    vPhone: {required: true, minlength: 3, digits: true,
                        remote: {
                            url: 'ajax_driver_mobile_new.php',
                            type: "post",
                            data: {
                                iDriverId: '',
                                usertype: function (e) {
                                    return $('input[name=user_type]').val();
                                },
                                vCountry: function (e) {
                                    return $('#vCountryc option:selected').val();
                                },
                            },
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "<?= addslashes($langage_lbl['LBL_PHONE_CHECK_DELETE_ACCOUNT']); ?>";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "<?= addslashes($langage_lbl['LBL_PHONE_EXIST_MSG']); ?>";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                        }
                    },
                    vCompany: {required: function (e) {
                            return $('input[name=user_type]').val() == 'company' || $('input[name=user_type]').val() == 'store';
                        }, minlength: function (e) {
                            if ($('input[name=user_type]').val() == 'company' || $('input[name=user_type]').val() == 'store') {
                                return 2;
                            } else {
                                return false;
                            }
                        }, maxlength: function (e) {
                            //console.log($('input[name=user_type]').val() + "AaaaA");
                            if ($('input[name=user_type]').val() == 'company' || $('input[name=user_type]').val() == 'store') {
                                return 30;
                            } else {
                                return false;
                            }
                        }},
                    vCaddress: {
                        required: function (e) {
                            if ($('input[name=user_type]:checked').val() == 'company') {
                                return true;
                            } else {
                                return false;
                            }
                        }, minlength: 2},
                    //vCity: {required: true},
                    vZip: {required: function (e) {
                            if ($('input[name=user_type]:checked').val() == 'company') {
                                return true;
                            } else {
                                return false;
                            }
                        }},
                    // eGender: {required: true},
                    'g-recaptcha-response': {required: function (e) {
                            if (grecaptcha.getResponse() == '') {
                                //$('#recaptcha-msg').css('display', 'block');
                                return true;
                            } else {
                                //$('#recaptcha-msg').css('display', 'none');
                                return false;
                            }
                        }},
                    'remember-me': {required: true},
                },
                messages: {
                    vPassword: {
                        //maxlength: 'Please enter less than 16 characters.'
                    },
                    vEmailc: {remote: function () {
                            return errormessage;
                        }},
                    'remember-me': {required: '<?= addslashes($langage_lbl['LBL_AGREE_TERMS_MSG']); ?>'},
                    vPhone: {
                        //minlength: 'Please enter at least three Number.', 
                        //digits: 'Please enter proper mobile number.', 
                        remote: function () {
                            return errormessage;
                        }},
                    vCompany: {
                       // required: 'This field is required.',
                       // minlength: 'Company Name at least 2 characters long.',
                       // maxlength: 'Please enter less than 30 characters.'
                    },
                    vFirstName: {
                        //required: 'This field is required.',
                        //minlength: 'First Name at least 2 characters long.',
                        //maxlength: 'Please enter less than 30 characters.'
                    },
                    vLastName: {
                       // required: 'This field is required.',
                        //minlength: 'Last Name at least 2 characters long.',
                        //maxlength: 'Please enter less than 30 characters.'
                    }

                }
            });


            $('#verificationc').bind('keydown', function (e) {
                if (e.which == 13) {
                    check_verification('verify');
                    return false;
                }
            });


            function changeCodec(id)
            {

                var request = $.ajax({
                    type: "POST",
                    url: 'change_code.php',
                    data: 'id=' + id,
                    success: function (data)
                    {
                        document.getElementById("codec").value = data;
                        //window.location = 'profile.php';
                    }
                });
            }
            /*ajax for unique username*/


            $(document).ready(function () {

                $("#companyc").hide();
                $("#radio_1c").prop("checked", true)
                $("#company_namec").removeClass("required");
                //show_companyd('driver');

                var newUser = $("input[name=user_type]:checked").val();
                //$("input[type=hidden][name=userType]").val(newUser);
                if (newUser == 'company')
                {
                    //$(".company").show();
                    //$(".driver").hide();
                    /*$("#li_dob").hide();*/
                    $("#vRefCodec").hide();
                    $("#referlblc").hide();
                    $('#div-phonec').show();
                } else if (newUser == 'driver')
                {
                    //$(".company").hide();
                    //$(".driver").show();
                    /*$("#li_dob").show();*/
                    $("#vRefCodec").show();
                    $("#referlblc").show();
                    $('#div-phonec').hide();
                }

            });

            function validate_refercodec(id) {

                if (id == "") {
                    return true;
                } else {


                    var request = $.ajax({
                        type: "POST",
                        url: 'ajax_validate_refercode.php',
                        data: 'refcode=' + id,
                        success: function (data)
                        {
                            if (data == 0) {
                                $("#referCheckc").remove();
                                $(".vRefCode_verify").addClass('required-active');
                                $('#refercodeCheckc').append('<div class="required-label" id="referCheck" >* <?= addslashes($langage_lbl['LBL_REFER_CODE_ERROR']); ?></div>');
                                $('#vRefCodec').attr("placeholder", "<?= addslashes($langage_lbl['LBL_SIGNUP_REFERAL_CODE']); ?>");
                                $('#vRefCodec').val("");
                                return false;
                            } else {
                                var reponse = data.split('|');
                                $('#iRefUserIdc').val(reponse[0]);
                                $('#eRefTypec').val(reponse[1]);
                            }
                        }
                    });

                }

            }


            function refreshCaptchac()
            {
                var img = document.images['captchaimgc'];
                img.src = img.src.substring(0, img.src.lastIndexOf("?")) + "?rand=" + Math.random() * 1000;
            }

            function setStatec(id, selected)
            {
                changeCodec(id);

                var fromMod = 'driver';
                var request = $.ajax({
                    type: "POST",
                    url: 'change_stateCity.php',
                    data: {countryId: id, selected: selected, fromMod: fromMod},
                    success: function (dataHtml)
                    {
                        $("#vStatec").html(dataHtml);
                        if (selected == '')
                            setCity('', selected);
                    }
                });
            }

            function setCityc(id, selected)
            {
                var fromMod = 'driver';
                var request = $.ajax({
                    type: "POST",
                    url: 'change_stateCity.php',
                    data: {stateId: id, selected: selected, fromMod: fromMod},
                    success: function (dataHtml)
                    {
                        $("#vCityc").html(dataHtml);
                    }
                });
            }



            $('#frmsignupo').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block error',
                onkeypress: true,
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.newrow').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.newrow').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.newrow input').addClass('has-shadow-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.prev('input').removeClass('has-shadow-error');
                    e.closest('.newrow').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vEmailo: {required: true, email: true,
                        remote: {
                            url: 'ajax_validate_email_new.php',
                            type: "post",
                            data: {
                                iOrganizationId: '',
                                usertype: 'organization',
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
                        }
                    },
                    vPassword: {required: true, noSpace: true, minlength: 6, maxlength: 16},
                    vPhone: {required: true, minlength: 3, digits: true,
                        remote: {
                            url: 'ajax_driver_mobile_new.php',
                            type: "post",
                            data: {
                                iOrganizationId: '',
                                usertype: function (e) {
                                    return $('input[name=user_type]').val();
                                },
                                vCountry: function (e) {
                                    return $('#vCountryo option:selected').val();
                                },
                            },
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "<?= addslashes($langage_lbl['LBL_PHONE_CHECK_DELETE_ACCOUNT']); ?>";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "<?= addslashes($langage_lbl['LBL_PHONE_EXIST_MSG']); ?>";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                        }
                    },

                    vCompany: {required: true, minlength: 2, maxlength: 30},
                    iUserProfileMasterId: {required: true},

                    vCaddress: {required: true, minlength: 2},
                    vZip: {required: true},
                    'g-recaptcha-response': {required: function (e) {
                            if (grecaptcha.getResponse() == '') {
                                $('#recaptcha-msg').css('display', 'block');
                                return true;
                            } else {
                                $('#recaptcha-msg').css('display', 'none');
                                return false;
                            }
                        }},
                    'remember-me': {required: true},
                },
                messages: {
                    vPassword: {
                        //maxlength: 'Please enter less than 16 characters.'
                    },
                    vEmailo: {remote: function () {
                            return errormessage;
                        }},
                    'remember-me': {required: '<?= addslashes($langage_lbl['LBL_AGREE_TERMS_MSG']); ?>'},
                    vPhone: {
                        //minlength: 'Please enter at least three Number.', 
                        //digits: 'Please enter proper mobile number.', 
                        remote: function () {
                            return errormessage;
                        }},
                    vCompany: {
                        //required: 'This field is required.',
                        //minlength: 'Organization Name at least 2 characters long.',
                        //maxlength: 'Please enter less than 30 characters.'
                    },
                    iUserProfileMasterId: {
                    //required: 'This field is required.'
                }
                }
            });

            $('#verificationo').bind('keydown', function (e) {
                if (e.which == 13) {
                    check_verification('verify');
                    return false;
                }
            });


            function changeCodeo(id)
            {
                var request = $.ajax({
                    type: "POST",
                    url: 'change_code.php',
                    data: 'id=' + id,
                    success: function (data)
                    {
                        document.getElementById("codeo").value = data;
                        //window.location = 'profile.php';
                    }
                });
            }
            /*ajax for unique username*/


            function refreshCaptchao()
            {
                var img = document.images['captchaimgo'];
                img.src = img.src.substring(0, img.src.lastIndexOf("?")) + "?rand=" + Math.random() * 1000;
            }

            function setStateo(id, selected)
            {
                changeCodeo(id);

                var fromMod = 'driver';
                var request = $.ajax({
                    type: "POST",
                    url: 'change_stateCity.php',
                    data: {countryId: id, selected: selected, fromMod: fromMod},
                    success: function (dataHtml)
                    {
                        $("#vStateo").html(dataHtml);
                        if (selected == '')
                            setCity('', selected);
                    }
                });
            }

            function setCityo(id, selected)
            {
                var fromMod = 'driver';
                var request = $.ajax({
                    type: "POST",
                    url: 'change_stateCity.php',
                    data: {stateId: id, selected: selected, fromMod: fromMod},
                    success: function (dataHtml)
                    {
                        $("#vCityo").html(dataHtml);
                    }
                });
            }
            
            var site_type = '<?= $site_type ?>';
            var alert_title = '<?= $langage_lbl['LBL_ATTENTION'] ?>';
            var alert_content = '<?= $langage_lbl['LBL_SIGNUP_DEMO_CONTENT'] ?>';
            var okbtn = '<?= $langage_lbl['LBL_OK'] ?>';
             $("form[name='frmsignup']").submit(function() {
                if(site_type==1) {
                    show_alert(alert_title,alert_content,okbtn,'','',function (btn_id) {
                        $("#custom-alert").removeClass("active");
                        return false;
                    },false);
                    return false;
                }
            });
        </script>
        
        <script type="text/javascript" src="assets/js/modal_alert.js" ></script>
        <!-- End: Footer Script -->
    </body>
</html>
