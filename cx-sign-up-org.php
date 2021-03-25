<?php if ($error != "" && $_REQUEST['type'] == 'organization') { ?>
    <div class="row">
        <div class="col-sm-12 alert alert-danger">
            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
            <?= $var_msg; ?>
        </div>
    </div>
<?php } ?>
<form name="frmsignup" id="frmsignupo" method="post" action="signup_action_organization.php" class="clearfix">
    <input type="hidden" placeholder="" name="user_type" id="user_type" class="create-account-input" value="organization" />

    <input type="hidden" placeholder="" name="vCurrency" id="vCurrencyo" class="create-account-input" value="<?php echo $defaultCurrency; ?>" />
    <div class="partation">
        <h1>Account Information</h1>
        <div class="form-group half newrow">
            <label><?= $langage_lbl['LBL_EMAIL_TEXT_SIGNUP']; ?> <span class="red">*</span></label>
            <input type="text" Required placeholder="" name="vEmailo" class="create-account-input " id="vEmail_verifyo" value="<?php echo $vEmail; ?>" />
        </div>
        
            <div class="form-group half newrow">
                <div class="relative_ele">
                <label><?= $langage_lbl['LBL_PASSWORD']; ?> <span class="red">*</span></label>
                <input id="passo" type="password" name="vPassword" placeholder="" class="create-account-input create-account-input1 " required value="" />
                <!--<button type="button" onclick="showHidePassword('passo')" id="eye"><img src="assets/img/eye.png" alt="eye"/></button>-->
            </div>
        </div>
    </div>
    <div class="partation">
        <h1>Basic Information</h1>
        <div class="form-group onethird newrow">
            <label><?= $langage_lbl['LBL_ORGANIZATION_NAME_WEB']; ?> <span class="red">*</span></label>
            <!--<input type="text" onkeypress="return IsAlphaNumeric(event, this.id);" id="company_nameo" class="create-account-input" name="vCompany" value="<?php echo $vCompany; ?>"  />-->
            <!--<span id="company_nameo_spaveerror" style="color: Red; display: none;font-size: 11px;">* White space not allowed</span>-->
            <input type="text" id="company_nameo" class="create-account-input" name="vCompany" value="<?php echo $vCompany; ?>"  />
        </div>
        <div class="form-group onethird newrow">
            <select name ='iUserProfileMasterId' id="iUserProfileMasterIdo" >
                <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] . " " . $langage_lbl['LBL_ORGANIZATION_TYPE_WEB'] ?></option>

                <?php
                if (!empty($dbUserProfileMaster)) {
                    foreach ($dbUserProfileMaster as $getUserProfle) {

                        $iUserProfileMasterId = $getUserProfle['iUserProfileMasterId'];
                        $decodevProfileName = json_decode($getUserProfle['vProfileName']);
                        ?> 
                        <option value = "<?= $iUserProfileMasterId ?>"><?= $decodevProfileName->$profileName; ?></option>

                    <?php }
                }
                ?> 

            </select>
        </div>
        <div class="form-group onethird newrow">
            <select required name='vCountry' id="vCountryo" onChange="setStateo(this.value, '');" >
                <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] . " " . $langage_lbl['LBL_COUNTRY_TXT'] ?></option>
                <? for ($i = 0; $i < count($db_country); $i++) { ?>
                    <option value = "<?= $db_country[$i]['vCountryCode'] ?>" <? if ($DEFAULT_COUNTRY_CODE_WEB == $db_country[$i]['vCountryCode']) { ?>selected<? } ?>><?= $db_country[$i]['vCountry'] ?></option>
<? } ?>
            </select>
        </div>
        <div class="form-group onethird newrow">
            <select name = 'vState' id="vStateo" onChange="setCityo(this.value, '');" >
                <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] . " " . $langage_lbl['LBL_STATE_TXT'] ?></option>
            </select>
        </div>
<?php if ($SHOW_CITY_FIELD == 'Yes') { ?>
            <div class="form-group onethird newrow">
                <select name = 'vCity' id="vCityo">
                    <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] . " " . $langage_lbl['LBL_CITY_TXT'] ?></option>
                </select>
            </div>
<?php } ?>
        <div class="form-group onethird newrow">
            <label><?= $langage_lbl['LBL_ADDRESS_SIGNUP']; ?> <span class="red">*</span></label>
            <input name="vCaddress" type="text" class="create-account-input" value="<?php echo $vCaddress; ?>" />
        </div>
        <div class="form-group onethird newrow">
            <label><?= $langage_lbl['LBL_ZIP_CODE_SIGNUP']; ?> <span class="red">*</span></label>
            <input name="vZip" type="text" class="create-account-input create-account-input1" value="<?php echo $vZip; ?>" />
        </div>
        <div class="form-group onethird phone-column newrow">
            <label><?= $langage_lbl['LBL_SIGNUP_777-777-7777']; ?> <span class="red">*</span></label>
            <!--<select>
                <option value="">+91</option>
            </select>-->
            <input type="text"  name="vCode" readonly  class="phonecode" value="<?php echo $vCode; ?>" id="codeo" class="phonecode" />
            <input required type="text"  id="vPhoneo" value="<?php echo $vPhone; ?>" class="create-account-input create-account-input1 vPhone_verify" name="vPhone"/>
        </div>
        <div class="form-group  captcha-column newrow captchauser">
<?php //include_once("recaptcha.php");  ?>
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
<?= $langage_lbl['LBL_ALREADY_HAVE_ACC']; ?> <a href="sign-in?type=organization" tabindex="5"><?= $langage_lbl['LBL_SIGN_IN']; ?></a>
    </div>
    <input type = 'reset' class = 'resetform' value = 'reset' style="display:none"/>
</form>