<div class="modal fade" id="newsletter" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h4><?= $langage_lbl['LBL_HEAD_SUBSCRIBE_NEWSLATTER_TXT']; ?></h4></div>
            <div class="modal-body">

                <div class="form-box-content export-popup">
                    <form  name="newsletter" id="frmnewsletter" method="post" action="" class="clearfix" enctype="multipart/form-data">
                        <div class="row">  
                            <div class="col-lg-12">
                                <label><?= $langage_lbl['LBL_USER_NAME_HEADER_SLIDE_TXT']; ?><span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-8 rideo-work">
                                <input type="text" autocomplete="off" class="form-control" name="vNamenewsletter"  id="vNamenewsletter" value="<?= $vNamenewsletter; ?>" placeholder="<?= $langage_lbl['LBL_USER_NAME_HEADER_SLIDE_TXT']; ?>" >
                            </div>
                        </div>
                        <div class="row" style="margin-top:10px;">
                            <div class="col-lg-12">
                                <label><?= $langage_lbl['LBL_EMAIL_LBL_TXT']; ?><span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-8 rideo-work">
                                <input type="text" autocomplete="off" class="form-control" name="vEmailnewsletter"  id="vEmailnewsletter" value="<?= $vEmailnewsletter; ?>" placeholder="<?= $langage_lbl['LBL_EMAIL_LBL_TXT']; ?>" > 
                            </div> 
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-lg-8 rideo-work">
                                <span class="news_subs"><label><input type="radio" checked="" name="eStatus" value="Subscribe"></label><?php echo $langage_lbl['LBL_SUBSCRIBE']; ?></span>
                                <span class="news_subs"><label><input type="radio" name="eStatus" value="Unsubscribe"></label><?php echo $langage_lbl['LBL_UNSUBSCRIBE']; ?></span>
                            </div>
                        </div>
                        <br>
                        <!-- Captcha Syntax -->
                        <span class="newrow">
                            <strong class="captcha-newsletter"> <label ><?= $langage_lbl['LBL_CAPTCHA_SIGNUP']; ?><span class="red">*</span></label>
                                <input id="POST_CAPTCHA_NEWSLETTER" class="create-account-input" size="5" maxlength="5" name="POST_CAPTCHA_NEWSLETTER" type="text" autocomplete="off" style="border-bottom: 1px solid black;" placeholder=""  >

                                <em class="captcha-dd">
                                    <img src="captcha_code_news_file.php?rand=<?php echo rand(); ?>" id='newslettercaptchaimg' alt="" class="chapcha-img" />&nbsp;<?= $langage_lbl['LBL_CAPTCHA_CANT_READ_SIGNUP']; ?>
                                    <a href='javascript:void(0)' onclick="refreshCaptchanewsletter();"><?= $langage_lbl['LBL_CLICKHERE_SIGNUP']; ?></a>
                                </em>

                            </strong>
                        </span>
                        <!-- Close Captcha -->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><?= $langage_lbl['LBL_BTN_CANCEL_TRIP_TXT']; ?></button>
                            <input type="submit" class="btn btn-success"  name="submitss" id="submitss" value="<?php echo $langage_lbl_admin['LBL_BTN_SUBMIT_TXT']; ?>" >
                        </div>
                    </form>
                </div>
            </div>

        </div>
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
        rules: {
            vNamenewsletter: {required: true, minlength: 2, maxlength: 40},
            vEmailnewsletter: {required: true, email: true},
            POST_CAPTCHA_NEWSLETTER: {required: true, remote: {
                    url: 'ajax_captcha_newsletter_new.php',
                    type: "post"
                }}
        },  
        messages: {vNamenewsletter: {
                required: 'This field is required.',
                minlength: 'Name at least 2 characters long.',
                maxlength: 'Please enter less than 40 characters.'
            },
            vEmailnewsletter: {remote: function () {
                    return errormessage;
                }},
            POST_CAPTCHA_NEWSLETTER: {remote: '<?= addslashes($langage_lbl['LBL_CAPTCHA_MATCH_MSG']); ?>'}
        },
        onkeypress: true
    });
});
</script>
