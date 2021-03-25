<?php
include_once("common.php");

if(SITE_TYPE=='Demo') {
    header("Location:" . $tconfig["tsite_url"] . "sign-up_rider.php?error=1");
    exit;
}
if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
    $valiedRecaptch = $generalobj->checkRecaptchValied($GOOGLE_CAPTCHA_SECRET_KEY, $_POST['g-recaptcha-response']);
    //echo $valiedRecaptch;exit;
    if ($valiedRecaptch) {
        if ($_POST) {
            /* if($_POST['vPassword'] != $_POST['vRPassword'])
              {
              $generalobj->getPostForm($_POST,"Password doesn't match","SignUp");
              exit;
              } */
            $msg = $generalobj->checkDuplicateFront('vEmail', "register_user", Array('vEmail'), $tconfig["tsite_url"] . "sign-up_rider.php?error=1&var_msg=Email already Exists", "Email already Exists", "", "");
            $eSystem = "";
            $checPhoneExist = $generalobj->checkMemberDataInfo($_POST['vPhone'],"",'Rider',$_POST['vCountry'],"",$eSystem);
            //echo "<pre>";
            //print_r($checPhoneExist);exit;
            if($checPhoneExist['status'] == 0){
                $_SESSION['postDetail'] = $_REQUEST;
                header("Location:" . $tconfig["tsite_url"] . "sign-up_rider.php?error=1&var_msg=". $langage_lbl['LBL_PHONE_EXIST_MSG']);
                exit;
            }else if($checPhoneExist['status'] == 2){
                header("Location:" . $tconfig["tsite_url"] . "sign-up_rider.php?error=1&var_msg=".$langage_lbl['LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT']);
                exit;
            }
            
            $eReftype = "Rider";
            $Data['vRefCode'] = $generalobj->ganaraterefercode($eReftype);
            $Data['iRefUserId'] = $_POST['iRefUserId'];
            $Data['eRefType'] = $_POST['eRefType'];
            $Data['vName'] = $_POST['vName'];
            $Data['vLang'] = $_POST['vLang'];
	  $_SESSION["sess_lang"]=$_POST['vLang'];
            $Data['vLastName'] = $_POST['vLastName'];

            $Data['vPassword'] = $generalobj->encrypt_bycrypt($_REQUEST['vPassword']);
            $Data['vEmail'] = $_POST['vEmail'];
            $Data['vPhone'] = $_POST['vPhone'];
            $Data['vCountry'] = $_POST['vCountry'];
            $Data['vPhoneCode'] = $_POST['vPhoneCode'];
            $Data['vZip'] = $_POST['vZip'];

            $Data['vInviteCode'] = $_POST['vInviteCode'];
            $Data['vCurrencyPassenger'] = $_POST['vCurrencyPassenger'];
            //$Data['eGender'] = $_POST['eGender'];
            $Data['dRefDate'] = Date('Y-m-d H:i:s');
            $Data['tRegistrationDate'] = Date('Y-m-d H:i:s');

            $csql = "SELECT eZeroAllowed,vCountryCode FROM `country` WHERE vPhoneCode = '" . $_POST['vPhoneCode'] . "'";
            $CountryData = $obj->MySQLSelect($csql);
            $eZeroAllowed = $CountryData[0]['eZeroAllowed'];

            if ($eZeroAllowed == 'Yes') {
                $Data['vPhone'] = $Data['vPhone'];
            } else {
                $first = substr($Data['vPhone'], 0, 1);

                if ($first == "0") {
                    $Data['vPhone'] = substr($Data['vPhone'], 1);
                }
            }

            if (SITE_TYPE == 'Demo') {
                $Data['eStatus'] = 'Active';
                //Added By HJ On 17-07-2019 For Auto Verify Email and Phone Driver When Register Start
                $Data['eEmailVerified'] = $Data['ePhoneVerified'] = 'Yes';
                //Added By HJ On 17-07-2019 For Auto Verify Email and Phone Driver When Register End
            }
            $eSystem = "";
            $checkValid = $generalobj->checkMemberDataInfo($_POST['vEmail'], "", $eReftype,$_POST['vCountry'],"",$eSystem);
            
            if ($checkValid['status'] == 1) {
                $id = $obj->MySQLQueryPerform("register_user", $Data, 'insert');
                if ($id != "") {
                    if (SITE_TYPE == 'Demo') {
                        insertCorporateUserProfile($id, $Data['vEmail']); // Added By HJ On 31-07-2018 As Per Discuss With BM QA and CD Sir
                    }
                    $_SESSION['sess_iUserId'] = $id;
                    $_SESSION["sess_vName"] = $Data['vName'] . ' ' . $Data['vLastName'];
                    $_SESSION["sess_company"] = " ";
                    $_SESSION["sess_vEmail"] = $Data['vEmail'];
                    $_SESSION["sess_user"] = "rider";
                    $_SESSION["sess_vCurrency"] = $Data['vCurrencyPassenger'];
                    $maildata['EMAIL'] = $_SESSION["sess_vEmail"];
                    $maildata['NAME'] = $_SESSION["sess_vName"];
                    //$maildata['PASSWORD'] = $langage_lbl["LBL_PASSWORD"] . ": " . $_REQUEST['vPassword']; //Commented By HJ On 11-01-2019 For Hide Password As Per Discuss With QA BM
                    $maildata['SOCIALNOTES'] = '';
                    $generalobj->send_email_user("MEMBER_REGISTRATION_USER", $maildata);
                    //User login log added by Rs
                    $generalobj->createUserLog('Passenger', 'Yes', $id, 'Web');
                    
                    if (!empty($_SESSION['sess_currentpage_url_mr']) && isset($_SESSION['sess_currentpage_url_mr'])) {
                        $redirect = $_SESSION['sess_currentpage_url_mr'];
                        unset($_SESSION['sess_currentpage_url_mr']);
                        header("Location:" . $redirect);

                        exit;
                    }
                    if ($_REQUEST['depart'] != "" && $_REQUEST['depart'] == 'mobi') {
                        header("Location:mobi");
                        exit;
                    }
                    header("Location:profile_rider.php");
                    exit;
                }
            } else if ($checkValid['status'] == 2) {
                $_SESSION['postDetail'] = $_REQUEST;
                header("Location:" . $tconfig["tsite_url"] . "sign-up_rider.php?error=1&var_msg=".$langage_lbl['LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT']);
                exit;
            }
        }
    } else {
        $_SESSION['postDetail'] = $_REQUEST;
        header("Location:" . $tconfig["tsite_url"] . "sign-up_rider.php?error=1&var_msg=".$langage_lbl['LBL_CAPTCHA_MATCH_MSG']);
        exit;
    }
} else {
    $_SESSION['postDetail'] = $_REQUEST;
    header("Location:" . $tconfig["tsite_url"] . "sign-up_rider.php?error=1&var_msg=Please check reCAPTCHA box.");
    exit;
}

//Added By HJ On 31-07-2019 For Insert Default Corporate User When Add New User/Rider In Demo Copy Start
function insertCorporateUserProfile($iUserId, $email) {
    global $obj;
    $insert_user = array();
    $insert_user['iUserId'] = $iUserId;
    $insert_user['iUserProfileMasterId'] = 1;
    $insert_user['iOrganizationId'] = 1;
    $insert_user['vProfileEmail'] = $email;
    $insert_user['eStatus'] = "Active";
    $id = $obj->MySQLQueryPerform("user_profile", $insert_user, 'insert');
    return $id;
}

//Added By HJ On 31-07-2019 For Insert Default Corporate User When Add New User/Rider In Demo Copy End
?>
