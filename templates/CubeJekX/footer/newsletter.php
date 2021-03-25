<div class="custom-modal-main" id="newsletter">
    <div class="custom-modal">
        <div class="model-header">
            <h4><?= $langage_lbl['LBL_HEAD_SUBSCRIBE_NEWSLATTER_TXT']; ?></h4>
            <i class="icon-close" data-dismiss="modal"></i>
        </div>
        <form class="general-form" name="newsletter" id="frmnewsletter" method="post" action="" enctype="multipart/form-data"> 
        <div class="model-body">
            
            <div>
                <div class="form-group newrow">
                    <label><?= $langage_lbl['LBL_USER_NAME_HEADER_SLIDE_TXT']; ?></label>
                    <input type="text" autocomplete="off" name="vNamenewsletter"  id="vNamenewsletter" value="<?= $vNamenewsletter; ?>">
                </div>
                <div class="form-group newrow">
                    <label><?= $langage_lbl['LBL_EMAIL_LBL_TXT']; ?></label>
                    <input type="email" autocomplete="off" name="vEmailnewsletter"  id="vEmailnewsletter" value="<?= $vEmailnewsletter; ?>">
                </div>
            </div>
            <div class="data-row">
                <div class="radio-combo">
                    <div class="radio-main">
                        <span class="radio-hold">
                            <input id="subscribe" name="eStatus" type="radio" value="Subscribe"  checked="" >
                            <span class="radio-button"></span>
                        </span>
                    </div><label for="subscribe"><?php echo $langage_lbl['LBL_SUBSCRIBE']; ?></label>
                </div>
                <div class="radio-combo">
                    <div class="radio-main">
                        <span class="radio-hold">
                            <input id="unsubscribe" name="eStatus" type="radio" value="Unsubscribe" >
                            <span class="radio-button"></span>
                        </span>
                    </div><label for="unsubscribe"><?php echo $langage_lbl['LBL_UNSUBSCRIBE']; ?></label>
                </div>
            </div>
            <div class="form-group  captcha-column newrow">
                    <?php include_once("recaptcha.php"); ?>
            </div>
            <!--<div class="captcha-column">
                <div class="form-group newrow">
                    <label>Captcha</label>
                    <input id="POST_CAPTCHA_NEWSLETTER" class="create-account-input" size="5" maxlength="5" name="POST_CAPTCHA_NEWSLETTER" type="text" autocomplete="off" style="border-bottom: 1px solid black;" placeholder=""  >
                    <?php //include_once("newsletterrecaptch.php"); ?>
                </div>
                <span>
                    <img src="captcha_code_news_file.php?rand=<?php echo rand(); ?>" id="newslettercaptchaimg" alt=""> Can't read the image? 
                    <a href='javascript:void(0)' onclick="refreshCaptchanewsletter();">Click here.</a> 
                </span>
            </div>--> 
            
        </div>
        <div class="model-footer">
            <div class="button-block">
                <input type="submit"  name="submitss" id="submitss" class="gen-btn" value="<?php echo $langage_lbl['LBL_BTN_SUBMIT_TXT']; ?>" >
                <button type="button" class="gen-btn" data-dismiss="modal"><?= $langage_lbl['LBL_BTN_CANCEL_TRIP_TXT']; ?></button>
            </div>
        </div>
        </form>
    </div>
</div> 

<script type="text/javascript">
function refreshCaptchanewsletter() {
    document.getElementById('POST_CAPTCHA_NEWSLETTER').value = '';
    var img = document.images['newslettercaptchaimg'];
    var codee = Math.random() * 1000;
    img.src = img.src.substring(0, img.src.lastIndexOf("?")) + "?rand=" + codee;
}
$(document).ready(function () {
    var errormessage;
   
    $('#frmnewsletter').validate({ 
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
            vNamenewsletter: {required: true, minlength: 2, maxlength: 40},
            vEmailnewsletter: {required: true, email: true}, 
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
            vNamenewsletter: {
                //required: 'This field is required.',
                //minlength: 'Name at least 2 characters long.',
                //maxlength: 'Please enter less than 40 characters.'
            },
            vEmailnewsletter: {remote: function () {
                return errormessage;
            }}, 
            POST_CAPTCHA_NEWSLETTER: {remote: '<?= addslashes($langage_lbl['LBL_CAPTCHA_MATCH_MSG']); ?>'}
        },
        submitHandler: function (form) { 
            // if (grecaptcha.getResponse() == '') {
            //    $('#recaptcha-msg-newerror').css('display', 'block');
            //    return false;
            // } 
            document.getElementById("frmnewsletter").submit();  
        }, 
        onkeypress: true
    });
});
</script> 
