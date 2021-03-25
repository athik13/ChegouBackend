<?php
include_once("common.php");

//added by SP for cubex changes on 07-11-2019
if($generalobj->checkXThemOn() == 'Yes') {
	$_REQUEST['type'] = 'organization';	
	include_once("cx-sign-up.php");
	exit;
}

$generalobj->go_to_home();
$script = "sign-up-organization";

$sql = "select * from  currency where eStatus='Active' && eDefault='Yes' ORDER BY  iDispOrder ASC";
$db_currency = $obj->MySQLSelect($sql);
$defaultCurrency = $db_currency[0]['vName'];

$sql = "SELECT * FROM country WHERE eStatus = 'Active'";
$db_code = $obj->MySQLSelect($sql);

$meta_arr = $generalobj->getsettingSeo(5);
$Mobile = $MOBILE_VERIFICATION_ENABLE;
$error = isset($_REQUEST['error']) ? $_REQUEST['error'] : '';
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : '';

$session_lang = $_SESSION['sess_lang'];
$profileName = "vProfileName_" . $session_lang;

$sql = "SELECT * FROM country WHERE eStatus = 'Active' ORDER BY  vCountry ASC";
$db_country = $obj->MySQLSelect($sql);

$sqlUserProfileMaster = "SELECT iUserProfileMasterId,vProfileName FROM user_profile_master WHERE eStatus = 'Active' order by vProfileName  asc";
$dbUserProfileMaster = $obj->MySQLSelect($sqlUserProfileMaster);

if (empty($SHOW_CITY_FIELD)) {
    $SHOW_CITY_FIELD = $generalobj->getConfigurations("configurations", "SHOW_CITY_FIELD");
}

if (isset($_SESSION['postDetail'])) {

    $_REQUEST = $_SESSION['postDetail'];
    $user_type = isset($_REQUEST['user_type']) ? $_REQUEST['user_type'] : '';
    $vEmail = isset($_REQUEST['vEmail']) ? $_REQUEST['vEmail'] : '';
    $vCountry = isset($_REQUEST['vCountry']) ? $_REQUEST['vCountry'] : '';
    $vCode = isset($_REQUEST['vCode']) ? $_REQUEST['vCode'] : '';
    $vPhone = isset($_REQUEST['vPhone']) ? $_REQUEST['vPhone'] : '';

    $vCompany = isset($_REQUEST['vCompany']) ? $_REQUEST['vCompany'] : '';
    $vCaddress = isset($_REQUEST['vCaddress']) ? $_REQUEST['vCaddress'] : '';
    $vState = isset($_REQUEST['vState']) ? $_REQUEST['vState'] : '';
    $vCity = isset($_REQUEST['vCity']) ? $_REQUEST['vCity'] : '';
    $vZip = isset($_REQUEST['vZip']) ? $_REQUEST['vZip'] : '';
    $vVat = isset($_REQUEST['vVat']) ? $_REQUEST['vVat'] : '';
    $vCurrencyDriver = isset($_REQUEST['vCurrencyDriver']) ? $_REQUEST['vCurrencyDriver'] : '';

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
                    <h2 class="header-page-sinu trip-detail"><?php echo $langage_lbl['LBL_SIGNUP_ORGANIZATION_WEB']; ?></h2>
                    <!--  <p><?= $langage_lbl['LBL_SIGN_UP_TELL_US_A_BIT_ABOUT_YOURSELF']; ?></p> -->
                    <!-- trips detail page -->
                    <form name="frmsignup" id="frmsignup" method="post" action="signup_action_organization.php" class="clearfix">

                        <div class="driver-signup-page">
                            <?php if ($error != "") { ?>
                                <div class="row">
                                    <div class="col-sm-12 alert alert-danger">
                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                        <?= $var_msg; ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="create-account">

                                <h3><?= $langage_lbl['LBL_SIGN_UP_CREATE_ACCOUNT']; ?></h3>
                                <input type="hidden" placeholder="" name="user_type" id="user_type" class="create-account-input" value="organization" />

                                <input type="hidden" placeholder="" name="vCurrency" id="vCurrency" class="create-account-input" value="<?php echo $defaultCurrency; ?>" />

                                <span class="newrow">
                                    <strong id="emailCheck"><label><?= $langage_lbl['LBL_EMAIL_TEXT_SIGNUP']; ?> <span class="red">*</span> </label>

                                        <input type="text" Required placeholder="<?= $langage_lbl['LBL_EMAIL_TEXT_SIGNUP']; ?>" name="vEmail" class="create-account-input " id="vEmail_verify" value="<?php echo $vEmail; ?>" />
                                    </strong>

                                    <strong><label><?= $langage_lbl['LBL_PASSWORD']; ?> <span class="red">*</span> </label>
                                        <input id="pass" type="password" name="vPassword" placeholder="<?= $langage_lbl['LBL_PASSWORD']; ?>" class="create-account-input create-account-input1 " required value="" /></strong>
                                </span> 


                            </div>
                            <div class="create-account">

                                <h3 class="company"><?= $langage_lbl['LBL_ORGANIZATION_INFORMATION_WEB']; ?></h3>


                                <span class="company newrow">

                                    <strong><label><?= $langage_lbl['LBL_ORGANIZATION_NAME_WEB']; ?> <span class="red">*</span></label>
                                        <input type="text" id="company_name" placeholder="Organization Name" class="create-account-input" name="vCompany" value="<?php echo $vCompany; ?>"  /></strong>

                                    <strong>
                                        <label><?= $langage_lbl['LBL_ORGANIZATION_TYPE_WEB']; ?> <span class="red">*</span></label>

                                        <select class="custom-select-new" name ='iUserProfileMasterId' id="iUserProfileMasterId" >
                                            <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] ?></option>

                                            <?php
                                            if (!empty($dbUserProfileMaster)) {
                                                foreach ($dbUserProfileMaster as $getUserProfle) {
                                                    $iUserProfileMasterId = $getUserProfle['iUserProfileMasterId'];
                                                    $decodevProfileName = json_decode($getUserProfle['vProfileName']);
                                                    ?> 

                                                    <option value = "<?= $iUserProfileMasterId ?>"><?= $decodevProfileName->$profileName; ?></option>

                                                <?php }
                                            } ?> 

                                        </select>
                                    </strong>     


                                </span>

                                <span class="newrow">
                                    <strong><label><?= $langage_lbl['LBL_COUNTRY_TXT'] ?> <span class="red">*</span>  </label>
                                        <select  class="custom-select-new" required name='vCountry' id="vCountry" onChange="setState1(this.value, '');" >
                                            <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] ?></option>
                                            <? for ($i = 0; $i < count($db_country); $i++) { ?>
                                                <option value = "<?= $db_country[$i]['vCountryCode'] ?>" <? if ($DEFAULT_COUNTRY_CODE_WEB == $db_country[$i]['vCountryCode']) { ?>selected<? } ?>><?= $db_country[$i]['vCountry'] ?></option>
<? } ?>
                                        </select>
                                    </strong>	
                                    <strong><label><?= $langage_lbl['LBL_STATE_TXT'] ?></label>
                                        <select class="custom-select-new" name = 'vState' id="vState" onChange="setCity(this.value, '');" >
                                            <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] ?></option>
                                        </select>
                                    </strong>
                                </span>
                                
                                <span class="newrow">
                                    <?php if($SHOW_CITY_FIELD=='Yes') { ?> 
                                    <strong><label><?= $langage_lbl['LBL_CITY_TXT'] ?></label>
                                        <select class="custom-select-new" name = 'vCity' id="vCity">
                                            <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] ?></option>
                                        </select>
                                    </strong>
                                    <?php } ?>
                                    <strong><label><?= $langage_lbl['LBL_ADDRESS_SIGNUP']; ?><span class="red">*</span> </label>
                                        <input name="vCaddress" type="text" class="create-account-input" placeholder="<?= $langage_lbl['LBL_ADDRESS_SIGNUP']; ?>" value="<?php echo $vCaddress; ?>" /></strong>
                                </span>

                                <span class="newrow">
                                    <strong><label><?= $langage_lbl['LBL_ZIP_CODE_SIGNUP']; ?><span class="red">*</span></label>
                                        <input name="vZip" type="text" class="create-account-input create-account-input1" placeholder="<?= $langage_lbl['LBL_ZIP_CODE_SIGNUP']; ?>" value="<?php echo $vZip; ?>" /></strong>

                                    <strong><label><?= $langage_lbl['LBL_SIGNUP_777-777-7777']; ?><span class="red">*</span></label>
                                        <strong class="ph_no newrow" id="mobileCheck"><input required type="text"  id="vPhone" value="<?php echo $vPhone; ?>" placeholder="<?= $langage_lbl['LBL_SIGNUP_777-777-7777']; ?>" class="create-account-input create-account-input1 vPhone_verify" name="vPhone"/></strong>

                                        <strong class="c_code"><input type="text"  name="vCode" readonly  class="create-account-input " value="<?php echo $vCode; ?>" id="code" /></strong></strong>                            
                                </span> 

                                <span class="newrow">
                                </span>

                                <span class="newrow">
                                    <!-- recaptcha code start -->
                                    <!--<script src='https://www.google.com/recaptcha/api.js'></script>
                                    <div class="g-recaptcha" data-sitekey="<?=$GOOGLE_CAPTCHA_SITE_KEY; ?>" data-callback="recaptchaCallback"></div>-->
                                    <?php include_once("recaptcha.php"); ?>
                                    <span id="recaptcha-msg" style="display: none;" class="error">This field is required.</span>
                                     <!-- recaptcha code end -->
                                    <!--<input type="hidden" class="hiddenRecaptcha required" name="hiddenRecaptcha" id="hiddenRecaptcha">-->
                                     <!-- old recaptcha code -->
                                    <!--<strong class="captcha-signup"><label><?= $langage_lbl['LBL_CAPTCHA_SIGNUP']; ?><span class="red">*</span></label>
                                        <input id="POST_CAPTCHA" class="create-account-input" size="5" maxlength="5" name="POST_CAPTCHA" type="text">
                                        <em class="captcha-abx"><img src="captcha_code_file.php?rand=<?php echo rand(); ?>" id='captchaimg' alt="" class="chapcha-img" />&nbsp;<?= $langage_lbl['LBL_CAPTCHA_CANT_READ_SIGNUP']; ?> <a href='javascript: refreshCaptcha();'><?= $langage_lbl['LBL_CLICKHERE_SIGNUP']; ?></a></em>
                                    </strong>-->
                                     <!-- old recaptcha code -->
                                </span>

                                <span class="newrow">
                                    <strong class="captcha-signup1">
                                        <abbr><?= $langage_lbl['LBL_SIGNUP_Agree_to']; ?> <a href="terms_condition.php" target="_blank"><?= $langage_lbl['LBL_SIGN_UP_TERMS_AND_CONDITION']; ?></a>
                                            <div class="checkbox-n">
                                                <input id="c1" name="remember-me" type="checkbox" class="required" value="remember">
                                                <label for="c1"></label>
                                            </div>
                                        </abbr>
                                    </strong>
                                </span>

                                <p><button type="submit" class="submit" name="SUBMIT"><?= $langage_lbl['LBL_BTN_SIGN_UP_SUBMIT_TXT']; ?></button></p>
                            </div>
                        </div>
                    </form>
                    <div class="col-lg-12">
                        <div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h4 class="modal-title" id="H2"><?= $langage_lbl['LBL_SIGNUP_PHONE_VERI']; ?></h4>
                                    </div>
                                    <div class="modal-body">
                                        <form role="form" name="verification" id="verification">
                                            <p class="help-block"><?= $langage_lbl['LBL_SIGNUP_PHONE_VERI_TEXT']; ?></p>
                                            <div class="form-group">
                                                <label><?= $langage_lbl['LBL_SIGNUP_ENTER_CODE']; ?></label>
                                                <input class="form-control" type="text" id="vCode1"/>
                                            </div>
                                            <p class="help-block" id="verification_error"></p>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-primary" onClick="check_verification('verify')"><?= $langage_lbl['LBL_SIGNUP_VERIFY']; ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- -->
                </div>
            </div>
            <!-- footer part -->
<?php include_once('footer/footer_home.php'); ?>
            <!-- footer part end -->
            <!-- -->
            <div style="clear:both;"></div>
        </div>
        <!-- home page end-->
        <!-- Footer Script -->
        <?php include_once('top/footer_script.php');
        $lang = get_langcode($_SESSION['sess_lang']);
        ?>
        <script type="text/javascript" src="assets/js/validation/jquery.validate.min.js" ></script>
        <?php if ($lang != 'en') { ?>
           <!--  <script type="text/javascript" src="assets/js/validation/localization/messages_<?= $lang; ?>.js" ></script> -->
           <? include_once('otherlang_validation.php');?>
<?php } ?>
        <script type="text/javascript" src="assets/js/validation/additional-methods.js" ></script>

        <script type="text/javascript">
			function setState1(args,argw) {
                alert("ff");
            }
            function recaptchaCallback(){
                $('#hiddenRecaptcha').valid();
            }
                                            var errormessage;
                                            $('#frmsignup').validate({
                                                ignore: 'input[type=hidden]',
                                                errorClass: 'help-block error',
                                                onkeypress: true,
                                                errorElement: 'span',
                                                errorPlacement: function (error, e) {
                                                    e.parents('.newrow > strong').append(error);
                                                },
                                                highlight: function (e) {
                                                    $(e).closest('.newrow').removeClass('has-success has-error').addClass('has-error');
                                                    $(e).closest('.newrow strong input').addClass('has-shadow-error');
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
                                                        /*remote: {
                                                            url: 'ajax_validate_email_new.php',
                                                            type: "post",
                                                            data: {
                                                                iOrganizationId: '',
                                                                usertype: function (e) {
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
                                                        }*/
                                                    },
                                                    vPassword: {required: true, noSpace: true, minlength: 6, maxlength: 16},
                                                    vPhone: {required: true, minlength: 3, digits: true,
                                                        /*remote: {
                                                            url: 'ajax_driver_mobile_new.php',
                                                            type: "post",
                                                            data: {
                                                                iOrganizationId: '',
                                                                usertype: function (e) {
                                                                    return $('input[name=user_type]').val();
                                                                }
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
                                                        }*/
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
                                                      /* old recaptcha code */
                                                    /*POST_CAPTCHA: {required: true, remote: {
                                                            url: 'ajax_captcha_new.php',
                                                            type: "post",
                                                            data: {iDriverId: ''},
                                                        }},*/
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
                                                        //required: 'Organization Name is required.',
                                                        //minlength: 'Organization Name at least 2 characters long.',
                                                        //maxlength: 'Please enter less than 30 characters.'
                                                    },
                                                    iUserProfileMasterId: {
                                                        //required: 'Organization Type is required.'
                                                    },
                                                    POST_CAPTCHA: {remote: '<?= addslashes($langage_lbl['LBL_CAPTCHA_MATCH_MSG']); ?>'}
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
                                                        //window.location = 'profile.php';
                                                    }
                                                });
                                            }
                                            /*ajax for unique username*/


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
        <!-- End: Footer Script -->
    </body>
</html>
