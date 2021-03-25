<?php if ($error != "" && ($_REQUEST['type'] == 'company' || $_REQUEST['type'] == 'restaurant')) { ?>
    <div class="row">
        <div class="col-sm-12 alert alert-danger">
            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
            <?= $var_msg; ?>
        </div>
    </div>
<?php } ?>
<form name="frmsignup" id="frmsignupc" method="post" action="signup_a.php" class="clearfix">
    <div class="partation">
        <h1><?= $langage_lbl['LBL_ACC_INFO']; ?></h1>
        <!--<div class="radio-holding-row">
            <strong><?= $langage_lbl['LBL_ARE_YOU_AN_INDIVIDUAL']; ?></strong>
            <div class="flexible-row">
                <label><?= $langage_lbl['LBL_Member_Type:']; ?>:</label>
                <div class="label-data-hold">
                    <span class="radio-holder">
                        <input type="radio" id="11" name="user_type" value="driver" onChange="show_companyc(this.value);" >
                        <span class="radio-box"></span>
                    </span><label for="1"><?= $langage_lbl['LBL_SIGNUP_INDIVIDUAL_DRIVER']; ?></label>
                </div>
                <div class="label-data-hold">
                    <span class="radio-holder">
                        <input type="radio" id="22" name="user_type" value="company" onChange="show_companyc(this.value);" class="" checked="checked">
                        <span class="radio-box"></span>
                    </span><label for="2"><?= $langage_lbl['LBL_COMPANY_SIGNIN']; ?></label>
                </div>
            </div>
        </div>-->
        <input type="hidden" id="22" name="user_type" value="company">

        <div class="radio-holding-row" style="display:none">
            <div class="flexible-row">
                <label><?= $langage_lbl['LBL_Member_Type:']; ?>:</label>
                <div class="label-data-hold">
                    <span class="radio-holder">
                        <input type="radio" id="company_store" name="company_store" value="company" onChange="show_company_store(this.value);" class="" checked="checked">
                        <span class="radio-box"></span>
                    </span><label for="2"><?= $langage_lbl['LBL_COMPANY_SIGNIN']; ?></label>
                </div>
                <div class="label-data-hold">
                    <span class="radio-holder">
                        <input type="radio" id="company_store1" name="company_store" value="store" onChange="show_company_store(this.value);" >
                        <span class="radio-box"></span>
                    </span><label for="1"><?= $langage_lbl['LBL_RESTAURANT_TXT_ADMIN']; ?></label>
                </div> 
            </div>
        </div>
        <div class="form-group onethird newrow">
            <label><?= $langage_lbl['LBL_EMAIL_TEXT_SIGNUP']; ?> <span class="red">*</span></label>
            <input type="email" Required name="vEmailc" class="create-account-input " id="vEmail_verifyc" value="<?php echo $vEmail; ?>" />
        </div>
        
            <div class="form-group onethird newrow">
                <div class="relative_ele">
                <label><?= $langage_lbl['LBL_PASSWORD']; ?> <span class="red">*</span></label>
                <input id="passc" type="password" name="vPassword" class="create-account-input create-account-input1 " required value="" />
                <!--<button type="button" onclick="showHidePassword('passc')" id="eye"><img src="assets/img/eye.png" alt="eye"/></button>-->
            </div>
        </div>

        <?php if (count($service_cat_list) <= 1) { ?>
            <input name="iServiceId" type="hidden" class="create-account-input" value="<?php echo $db_service_category[0]['iServiceId']; ?>"/>
        <?php } else { ?>
            <div class="form-group onethird newrow storedata" id="AvilableServiceCategory" style="display:none">
                <select name="iServiceId">
                    <option value=""><?= $langage_lbl['LBL_SELECT_SERVICE_TYPE'] ?></option>
                    <?php foreach ($db_service_category as $service_category) { ?>
                        <option name="<?= $service_category['iServiceId'] ?>" value="<?= $service_category['iServiceId'] ?>" <?php echo (isset($iServiceId) && in_array($service_category['iServiceId'], $iServiceId)) ? 'selected="selected"' : ""; ?>><?= $service_category['servicename'] ?></option>
                    <?php } ?>    
                </select>
            </div>
        <?php } ?>

    </div> 
    <div class="partation">
        <h1><?= $langage_lbl['LBL_BASIC_INFO']; ?></h1>
        <div class="form-group onethird newrow ">
            <label class="comdata"><?= $langage_lbl['LBL_COMPANY_SIGNUP']; ?> <span class="red">*</span></label>
            <label class="storedata" style="display:none"><?= $langage_lbl['LBL_SIGNUP_STORE_NAME']; ?> <span class="red">*</span> </label>
            <!--<input type="text" onkeypress="return IsAlphaNumeric(event, this.id);" id="company_name" name="vCompany" value="<?php echo $vCompany; ?>"  />-->
            <!--<span id="company_name_spaveerror" style="color: Red; display: none;font-size: 11px;">* White space not allowed</span>-->
            <input type="text" id="company_name" name="vCompany" value="<?php echo $vCompany; ?>"  />
        </div>
        <div class="form-group onethird newrow comdata">
            <label><?= $langage_lbl['LBL_VAT_NUMBER_SIGNUP']; ?></label>
            <input name="vVat" type="text" class="create-account-input" value="<?php echo $vVat; ?>" />
        </div>
        <!--<div class="form-group onethird newrow storedata" style="display:none">
        <label><?= $langage_lbl['LBL_SIGNUP_STORE_NAME']; ?></label>
        <input type="text" id="company_name" class="create-account-input" name="vCompany" value="<?php echo $vCompany; ?>"  />
        </div>-->
        <div class="form-group onethird newrow storedata" style="display:none">
            <label><?= $langage_lbl['LBL_RES_CONTACT_PERSON_NAME']; ?></label>
            <input name="vContactName" type="text" class="create-account-input" value="<?php echo $vContactName; ?>" />
        </div>
        <div class="form-group onethird newrow">
            <select required name='vCountry' id="vCountryc" onChange="setStatec(this.value, '');" >
                <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] . " " . $langage_lbl['LBL_COUNTRY_TXT'] ?></option>
                <? for ($i = 0; $i < count($db_country); $i++) { ?>
                    <option value = "<?= $db_country[$i]['vCountryCode'] ?>" <? if ($DEFAULT_COUNTRY_CODE_WEB == $db_country[$i]['vCountryCode']) { ?>selected<? } ?>><?= $db_country[$i]['vCountry'] ?></option>
                <? } ?>
            </select>
        </div>
        <div class="form-group onethird newrow">
            <select name = 'vState' id="vStatec" onChange="setCityc(this.value, '');" >
                <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] . " " . $langage_lbl['LBL_STATE_TXT'] ?></option>
            </select>
        </div>

        <?php if ($SHOW_CITY_FIELD == 'Yes') { ?>
            <div class="form-group onethird newrow">
                <select name = 'vCity' id="vCityc">
                    <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] . " " . $langage_lbl['LBL_CITY_TXT'] ?></option>
                </select>
            </div>
        <?php } ?>
        <div class="form-group onethird newrow">
            <label><?= $langage_lbl['LBL_ADDRESS_SIGNUP']; ?></label>
            <input name="vCaddress" type="text" class="create-account-input" value="<?php echo $vCaddress; ?>" />
        </div>
        <div class="form-group onethird newrow">
            <label><?= $langage_lbl['LBL_ZIP_CODE_SIGNUP']; ?></label>
            <input name="vZip" type="text" class="create-account-input create-account-input1" value="<?php echo $vZip; ?>" />
        </div>
        <div class="form-group onethird phone-column newrow">
            <label><?= $langage_lbl['LBL_SIGNUP_777-777-7777']; ?> <span class="red">*</span></label>
            <!--<select name="vCode">
                <option value="91">+91</option>
            </select>-->
            <input type="text"  name="vCode" readonly  class="phonecode" value="<?php echo $vCode; ?>" id="codec" class="phonecode"/>
            <input required type="text"  id="vPhonec" value="<?php echo $vPhone; ?>" placeholder="" class="create-account-input create-account-input1 vPhone_verify" name="vPhone"/>
        </div>
        <div class="form-group  captcha-column newrow captchauser">
            <?php //include_once("recaptcha.php"); ?>
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
                <input type="submit" name="SUBMIT" class="submit" value="<?= $langage_lbl['LBL_REGISTER_SMALL']; ?>"/>
                <img src="assets/img/apptype/<?php echo $template; ?>/arrow.svg" alt="">
            </div>
        </div>
    </div>
    <div class="member-txt">
        <?= $langage_lbl['LBL_ALREADY_HAVE_ACC']; ?> <a href="sign-in?type=company" tabindex="5" id="signinlink"><?= $langage_lbl['LBL_SIGN_IN']; ?></a>
    </div>
    <input type = 'reset' class = 'resetform' value = 'reset' style="display:none"/>
</form>