<?
include_once("common.php");
$meta_arr = $generalobj->getsettingSeo(2);
$sql = "SELECT * from language_master where eStatus = 'Active'";
$db_lang = $obj->MySQLSelect($sql);
$sql = "SELECT * from country where eStatus = 'Active'";
$db_code = $obj->MySQLSelect($sql);
//echo "<pre>";print_r($db_lang);
$script = "Contact Us";
if (isset($_POST['SUBMIT']) && $_POST['SUBMIT'] != "") {
    if(isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])){ 
        $valiedRecaptch = $generalobj->checkRecaptchValied($GOOGLE_CAPTCHA_SECRET_KEY,$_POST['g-recaptcha-response']);
        if($valiedRecaptch){
            //ini_set('display_errors', 1);
            //error_reporting(E_ALL);
            //echo "<pre>";print_r($_POST);die;
            $Data['vFirstName'] = stripcslashes($_POST['vName']);
            $Data['vLastName'] = stripcslashes($_POST['vLastName']);
            $Data['eSubject'] = stripcslashes($_POST['vSubject']);
            $Data['tSubject'] = nl2br(stripcslashes($_POST['vDetail']));
            $Data['vEmail'] = $_POST['vEmail'];
            $Data['cellno'] = $_POST['vPhone'];
            $return = $generalobj->send_email_user("CONTACTUS", $Data);
            if ($return) {
                $success = 1;
                $var_msg = $langage_lbl['LBL_SENT_CONTACT_QUERY_SUCCESS_TXT'];
            } else {
                $error = 1;
                $var_msg = $langage_lbl['LBL_ERROR_OCCURED'];
            }
        }else{
            $error = 1;
            $var_msg = 'Recaptch verification failed, please try again.'; 
        }
    }else{
        $error = 1;
        $var_msg = 'Please check reCAPTCHA box.';
    }
    header("Location:contact-us?msg=" . $var_msg . "&success=".$success."&error=" . $error);
    exit;
}
if (isset($_REQUEST['msg']) && $_REQUEST['msg'] != "") {
    $success = $_REQUEST['success'];
    $error = $_REQUEST['error'];
    $var_msg = $_REQUEST['msg'];
}
//echo $var_msg;die;
$userid = $_SESSION['sess_iUserId'];
$rider_query = "SELECT * from register_user where iUserId = $userid";

$rider_data = $obj->MySQLSelect($rider_query);
$driver_query = "SELECT * from register_driver where iDriverId = $userid";
$driver_data = $obj->MySQLSelect($driver_query);
//print_r($_SESSION);exit;

?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
        <!--<title><?= $SITE_NAME ?></title>-->
        <title><?php echo $meta_arr['meta_title']; ?></title>
        <meta name="keywords" value="<?= $meta_arr['meta_keyword']; ?>"/>
        <meta name="description" value="<?= $meta_arr['meta_desc']; ?>"/>
        <!-- Default Top Script and css -->
        <?php include_once("top/top_script.php"); ?>
        <?php include_once("top/validation.php"); ?>
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
            <div class="about-main">
                <div class="heading-area">
                    <div class="heading-area-inner">
                        <h1><?= $langage_lbl['LBL_CONTACT_US_HEADER_TXT']; ?></h1>
                    </div>
                </div>
                <div class="main-page-wrap">
                    <section class="contact-section">
                        <div class="contact-inner">
                            <div class="contact-left">
                                <h1><?= $langage_lbl['LBL_GET_IN_TOUCH_TXT']; ?></h1>
                                <p><?= $langage_lbl['LBL_CONTACT_US_DESC_TXT']; ?></p>
                                <p><strong><?= $langage_lbl['LBL_WELCOME_TO']; ?> <?= $SITE_NAME ?>, <?= $langage_lbl['LBL_CONTACT_US_SECOND_TXT']; ?></strong></p>
                                <div style="clear:both;"></div>
                                <?php if ($success == 1) { ?>
                                <div class="alert alert-success alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?= $var_msg ?>
                                </div>
                                    <!--<div class="form-err contactmsg">
                                        <span class="msg_close">&#10005;</span>
                                        <p style="background-color: #14b368;" class="btn-block btn btn-rect btn-success error-login-v" id="successf" ><?= $var_msg ?></p>
                                    </div>-->
                                    <?php
                                } else if ($error == 1) {
                                    ?>
                                    <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?= isset($_REQUEST['msg']) ? $_REQUEST['msg'] : ' '; ?>
                                    </div>
                                <?php } ?>
                                <div style="clear:both;"></div>
                                <form class="contact-form" name="frmsignup" id="frmsignup" method="post" action="">
                                    <?php
                                    if ($_SESSION['sess_user'] == 'rider') {
                                        foreach ($rider_data as $rider_datas) {
                                            ?>
                                            <div class="partation">
                                                <div class="form-group half"><label><?= $langage_lbl['LBL_CONTECT_US_FIRST_NAME_HEADER_TXT']; ?></label><input type="text" name="vName" value="<?php echo (isset($rider_datas['vName']) ? $rider_datas['vName'] : '') ?>"></div>
                                                <div class="form-group half"><label><?= $langage_lbl['LBL_CONTECT_US_LAST_NAME_HEADER_TXT']; ?></label><input type="text" name="vLastName" value="<?php echo (isset($rider_datas['vLastName']) ? $rider_datas['vLastName'] : '') ?>"></div>
                                                <div class="form-group half"><label><?= $langage_lbl['LBL_CONTECT_US_EMAIL_LBL_TXT']; ?></label><input type="email" name="vEmail" autocomplete="off" value="<?php echo (isset($rider_datas['vEmail']) ? $rider_datas['vEmail'] : '') ?>"></div>
                                                <div class="form-group half"><label><?= $langage_lbl['LBL_CONTECT_US_777-777-7777'] ?></label><input type="tel" name="vPhone" value="<?php echo (isset($rider_datas['vPhone']) ? $rider_datas['vPhone'] : '') ?>"></div>
                                            </div>
                                            <?php
                                        }
                                    } else if ($_SESSION['sess_user'] == 'driver') {
                                        foreach ($driver_data as $driver_datas) {
                                            ?>
                                            <div class="partation">
                                                <div class="form-group half"><label><?= $langage_lbl['LBL_CONTECT_US_FIRST_NAME_HEADER_TXT']; ?></label><input type="text" name="vName" value="<?php echo (isset($driver_datas['vName']) ? $driver_datas['vName'] : '') ?>"></div>
                                                <div class="form-group half"><label><?= $langage_lbl['LBL_CONTECT_US_LAST_NAME_HEADER_TXT']; ?></label><input type="text" name="vLastName" value="<?php echo (isset($driver_datas['vLastName']) ? $driver_datas['vLastName'] : '') ?>"></div>
                                                <div class="form-group half"><label><?= $langage_lbl['LBL_CONTECT_US_EMAIL_LBL_TXT']; ?></label><input type="email" name="vEmail" autocomplete="off" value="<?php echo (isset($driver_datas['vEmail']) ? $driver_datas['vEmail'] : '') ?>"></div>
                                                <div class="form-group half"><label><?= $langage_lbl['LBL_CONTECT_US_777-777-7777'] ?></label><input type="tel" name="vPhone" value="<?php echo (isset($driver_datas['vPhone']) ? $driver_datas['vPhone'] : '') ?>"></div>
                                            </div>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <div class="partation">
                                            <div class="form-group half newrow"><label><?= $langage_lbl['LBL_CONTECT_US_FIRST_NAME_HEADER_TXT']; ?></label><input type="text" name="vName"></div>
                                            <div class="form-group half newrow"><label><?= $langage_lbl['LBL_CONTECT_US_LAST_NAME_HEADER_TXT']; ?></label><input type="text" name="vLastName"></div>
                                            <div class="form-group half newrow"><label><?= $langage_lbl['LBL_CONTECT_US_EMAIL_LBL_TXT']; ?></label><input type="email" name="vEmail" autocomplete="off"></div>
                                            <div class="form-group half newrow"><label><?= $langage_lbl['LBL_CONTECT_US_777-777-7777'] ?></label><input type="tel" name="vPhone"></div>
                                        </div>
                                    <?php } ?>
                                    <div class="form-group newrow"><label><?= $langage_lbl['LBL_ADD_SUBJECT_HINT_CONTACT_TXT']; ?></label><input type="text" name="vSubject"></div>
                                    <div class="form-group textarea newrow">
                                        <label><?= $langage_lbl['LBL_ENTER_DETAILS_TXT']; ?></label>
                                        <textarea name="vDetail" cols="61" rows="5"></textarea>
                                    </div>
                                    <div class="form-group  captcha-column newrow">
                                        <?php include_once("recaptcha.php"); ?>
                                </div>
                                    <!--<div class="captcha-column">-->
                                    <!--    <div class="form-group newrow">-->
                                    <!--        <label>Captcha</label>-->
                                    <!--        <input type="text" size="5" maxlength="5" id="POST_CAPTCHA" name="POST_CAPTCHA">-->
                                    <!--    </div>-->
                                    <!--    <span>-->
                                    <!--        <img src="captcha_code_file.php?rand=<?php echo rand(); ?>" id='captchaimg' alt=""> <?= $langage_lbl['LBL_CAPTCHA_CANT_READ_SIGNUP']; ?> <a href="javascript: refreshCaptcha();"><?= $langage_lbl['LBL_CLICKHERE_SIGNUP']; ?></a>-->
                                    <!--    </span>-->
                                    <!--</div>-->
                                    <div class="button-block">
                                        <div class="btn-hold">
                                            <input type="submit" value="<?= $langage_lbl['LBL_BTN_CONTECT_US_SUBMIT_TXT']; ?>" name="SUBMIT" />
                                            <img src="assets/img/apptype/<?php echo $template; ?>/arrow.svg" alt="">
                                        </div>
                                    </div>

                                </form>
                            </div>
                            <div class="contact-right">
                                <div class="cont-det-block">
                                    <strong><?= $langage_lbl['LBL_ADDRESS_SIGNUP']; ?></strong>
                                    <address>
                                        <i class="fa fa-map-marker"></i><?= $COMPANY_ADDRESS ?>
                                    </address>
                                </div>
                                <div class="cont-det-block">
                                    <strong><?= $langage_lbl['LBL_PHONE_FRONT_FOOTER']; ?> & <?= $langage_lbl['LBL_CONTECT_US_EMAIL_LBL_TXT']; ?></strong>
                                    <ul>
                                        <li><i class="fa fa-phone"></i><a href="tel:+<?= $SUPPORT_PHONE; ?>"><?= $SUPPORT_PHONE; ?></a></li>
                                        <!--<li><a href="tel:18001234567">1-800-1234-567</a></li>-->
                                        <li><i class="fa fa-envelope"></i><a href="mailto:<?= $SUPPORT_MAIL; ?>"><?= $SUPPORT_MAIL; ?></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </section>
                    <!-- home page end-->
                    <!-- footer part -->
                    
                </div>
            </div>
                <?php include_once('footer/footer_home.php'); ?>
                    <div style="clear:both;"></div>
                    <?php if ($template != 'taxishark') { ?>
                        </div>
                    <?php } ?>
                <!-- footer part end -->
                <!-- Footer Script -->
                <?php include_once('top/footer_script.php'); ?>
                <!-- End: Footer Script -->
                <?php
                $lang = get_langcode($_SESSION['sess_lang']);
                ?>
	<!--<script type="text/javascript" src="assets/js/validation/jquery.validate.min.js" ></script>-->
                <?php if ($lang != 'en') { ?>
                   <!--  <script type="text/javascript" src="assets/js/validation/localization/messages_<?= $lang; ?>.js" ></script> -->
                   <? //include_once('otherlang_validation.php');?>
                <?php } ?>
                <script type="text/javascript" src="assets/js/validation/additional-methods.js" ></script>
                <script type="text/javascript">
                    $('#frmsignup').validate({
                        ignore: 'input[type=hidden]',
                        errorClass: 'help-block error',
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
                            //e.prev('input').removeClass('has-shadow-error');
                            e.parent().find('input').removeClass('has-shadow-error');
                            e.closest('.newrow').removeClass('has-success has-error');
                            e.closest('.help-block').remove();
                            e.closest('.help-inline').remove();
                        },
                        rules: {
                            vName: {required: true},
                            vLastName: {required: true},
                            vSubject: {required: true},
                            vDetail: {required: true},
                            vEmail: {required: true, email: true},
                            vPassword: {required: true, minlength: 6},
                            vPhone: {required: true, phonevalidate: true},
                            /*POST_CAPTCHA: {required: true, remote: {
                                    url: 'ajax_captcha_new.php',
                                    type: "post",
                                    data: {iDriverId: ''},
                                }},*/
                                 'g-recaptcha-response': {required: function (e) {
                                                if (grecaptcha.getResponse() == '') {
                                                    $('#recaptcha-msg').css('display', 'block');
                                                    return true;
                                                } else {
                                                    $('#recaptcha-msg').css('display', 'none');
                                                    return false;
                                                }
                                            }},
                        },
                        messages: {
                            vPhone: {phonevalidate: '<?= addslashes($langage_lbl['LBL_PHONE_VALID_MSG']); ?>'},
                            //POST_CAPTCHA: {remote: '<?= addslashes($langage_lbl['LBL_CAPTCHA_MATCH_MSG']); ?>'}
                        }
                    });
                </script>
    <script>
            function submit_form()
            {
                if (validatrix()) {
                    //alert("Submit Form");
                    document.frmsignup.submit();
                } else {
                    return false;
                }
                return false; //Prevent form submition
            }
                        </script>
    <script type="text/javascript">
            function validate_email(id)
            {
                var eml = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                result = eml.test(id);
                if (result == true)
                {
                    $('#emailCheck').html('<i class="icon icon-ok alert-success alert"> Valid</i>');
                    $('input[type="submit"]').removeAttr('disabled');
                } else
                {
                    $('#emailCheck').html('<i class="icon icon-remove alert-danger alert"> Enter Proper Email</i>');
                    $('input[type="submit"]').attr('disabled', 'disabled');
                    return false;
                }
            }
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
            function validate_mobile(mobile)
            {
                var eml = /^[0-9]+$/;
                result = eml.test(mobile);
                if (result == true)
                {
                    $('#mobileCheck').html('<i class="icon icon-ok alert-success alert"> Valid</i>');
                    $('input[type="submit"]').removeAttr('disabled');
                } else
                {
                    $('#mobileCheck').html('<i class="icon icon-remove alert-danger alert"> Enter Proper Mobile No</i>');
                    $('input[type="submit"]').attr('disabled', 'disabled');
                    return false;
                }
            }
            function refreshCaptcha()
            {
                var img = document.images['captchaimg'];
                img.src = img.src.substring(0, img.src.lastIndexOf("?")) + "?rand=" + Math.random() * 1000;
                $('#POST_CAPTCHA').val('');
            }
            $(document).ready(function () {
                if ($('.alert').html() != '') {
                    setTimeout(function () {
                        $('.alert').fadeOut();
                    }, 4000);
                }
            });
                        </script>
    <!-- End: Footer Script -->
	<!-- Powered by V3Cube.com -->
</body>
</html>
