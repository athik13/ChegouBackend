<?php

ob_start();
session_start();
// added in v4.0.0

//include_once "linkedin.php";
include_once "linkedin_outh2.php"; //changed by me

include_once('../common.php');
include_once('../assets/libraries/class.general.php');
$generalobj = new General();

$appId = $LINKEDIN_APP_ID;
$appsecretkey = $LINKEDIN_APP_SECRET_KEY;


$config['base_url'] = $tconfig['tsite_url'] . 'linkedin-login/auth-rider.php';
$config['callback_url'] = $tconfig['tsite_url'] . 'linkedin-login/linkedinconfig-rider.php';
$config['linkedin_access'] = $appId;
$config['linkedin_secret'] = $appsecretkey;

$userType = (isset($_REQUEST['userType'])) ? $_REQUEST['userType'] : '';

$_SESSION['linkedin_user'] = 'rider';

$oauth_problem = isset($_GET['oauth_problem']) ? $_GET['oauth_problem'] : '';

$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
if ($oauth_problem == "user_refused") {
    $link = $tconfig['tsite_url'] . 'sign-in.php';
    header("Location:" . $link);
    exit;
}
# First step is to initialize with your consumer key and secret. We'll use an out-of-band oauth_callback
$linkedin = new LinkedIn($config['linkedin_access'], $config['linkedin_secret'], $config['callback_url']);
if(!empty($_GET['code'])) { //changed by me start
    $data = $linkedin->linkedin_auth_get();  
} else {
    $data = $linkedin->getAuthorizationCode();
} //changed by me end

if (isset($_REQUEST['oauth_verifier'])) {
    $_SESSION['oauth_verifier'] = $_REQUEST['oauth_verifier'];

    $linkedin->request_token = unserialize($_SESSION['requestToken']);
    $linkedin->oauth_verifier = $_SESSION['oauth_verifier'];
    $linkedin->getAccessToken($_REQUEST['oauth_verifier']);

    $_SESSION['oauth_access_token'] = serialize($linkedin->access_token);
    header("Location: " . $config['callback_url']);
    exit;
} else {
    $linkedin->request_token = unserialize($_SESSION['requestToken']);
    $linkedin->oauth_verifier = $_SESSION['oauth_verifier'];
    $linkedin->access_token = unserialize($_SESSION['oauth_access_token']);
}

//changed by me start
//$xml_response = $linkedin->getProfile("~:(id,first-name,last-name,email-address,headline,picture-url,picture-urls::(original))?format=json");

//$data = json_decode($xml_response, TRUE);

/*foreach ($data['pictureUrls'] as $key => $value) {
    foreach ($value as $keys => $values) {
        $values;
    }
}*/
//changed by me end

$fbid = $data['id'];

//$fbfirstname = $data['firstName'];
//$fblastname = $data['lastName'];
//changed by me
$fbfirstname =$data['localizedFirstName'];
$fblastname =$data['localizedLastName'];

$headline =$data['headline'];

$femail =$data['email'];
$picture =$data['profile_pic'];

//$femail = $data['emailAddress'];
//$picture = $values;

$status = $data['status'];


if (empty($status)) {
    include_once($tconfig["tsite_libraries_v"]."/Imagecrop.class.php");
    $thumb = new thumbnail();
    $temp_gallery = $tconfig["tsite_temp_gallery"];

    include_once($tconfig["tsite_libraries_v"]."/SimpleImage.class.php");
    $img = new SimpleImage();
    try {
        $db_user = array();
        if ($femail != '') {
            $sqll001 = " vEmail='" . $femail . "'";
        } else {
            $sqll001 = " vFbId = '" . $fbid . "' AND eSignUpType = 'LinkedIn'";
        } {

            if ($femail != '' || $fbid != '') {
                $sql = "SELECT iUserId,vImgName,eGender,vPhone,eStatus FROM register_user WHERE $sqll001";
                $db_user = $obj->MySQLSelect($sql);
            }

            if (count($db_user) > 0) {

                if ($db_user[0]['eStatus'] == "Deleted" || $db_user[0]['eStatus'] == "Inactive") {
                    if ($db_user[0]['eStatus'] == "Deleted") {
                        $_SESSION['sess_error_social'] = addslashes($langage_lbl['LBL_ACC_DELETE_TXT']);
                    } else {
                        $_SESSION['sess_error_social'] = addslashes($langage_lbl['LBL_ACCOUNT_NOT_ACTIVE_ERROR_MSG']);
                    }


                    if ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') {
                        $link = $tconfig["tsite_url"] . "user-login";
                    } else {
                        $link = $tconfig["tsite_url"] . "rider-login";
                    }
                    header("Location:" . $link);
                    exit;
                }

                $Photo_Gallery_folder = $tconfig["tsite_upload_images_passenger_path"] . "/" . $db_user[0]['iUserId'] . "/";

                unlink($Photo_Gallery_folder . $db_user[0]['vImgName']);
                unlink($Photo_Gallery_folder . "1_" . $db_user[0]['vImgName']);
                unlink($Photo_Gallery_folder . "2_" . $db_user[0]['vImgName']);
                unlink($Photo_Gallery_folder . "3_" . $db_user[0]['vImgName']);
                unlink($Photo_Gallery_folder . "4_" . $db_user[0]['vImgName']);

                if (!is_dir($Photo_Gallery_folder)) {
                    mkdir($Photo_Gallery_folder, 0777);
                }

                $baseurl = $picture;
                $url = strtolower($fbid) . ".jpg"; //changed by me
                $image_name = $generalobj->copyRemoteFile($baseurl, $Photo_Gallery_folder . $url);

                if (is_file($Photo_Gallery_folder . $url)) {

                    list($width, $height, $type, $attr) = getimagesize($Photo_Gallery_folder . $url);
                    if ($width < $height) {
                        $final_width = $width;
                    } else {
                        $final_width = $height;
                    }
                    
                    $img->load($Photo_Gallery_folder . $url)->crop(0, 0, $final_width, $final_width)->save($Photo_Gallery_folder . $url);
                    $imgname = $generalobj->img_data_upload($Photo_Gallery_folder, $url, $Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"], "");
                }

                $sql = "UPDATE register_user set vFbId='" . $fbid . "', vImgName='" . $imgname . "',eGender='" . $db_user[0]['eGender'] . "',eSignUpType = 'LinkedIn' WHERE iUserId='" . $db_user[0]['iUserId'] . "'";

                if (SITE_TYPE == 'Demo') {
                    $login_sql = "insert into member_log (iMemberId, eMemberType, eMemberLoginType,vIP) VALUES ('" . $db_user[0]['iUserId'] . "', 'Passenger', 'WebLogin','" . $_SERVER['REMOTE_ADDR'] . "')";
                    $obj->sql_query($login_sql);
                }

                return $generalobj->Checkverification_mobile($db_user[0]['iUserId'], 'rider');
            } else {

                if(SITE_TYPE=='Demo'){
					$_SESSION['sess_error_social'] = addslashes($langage_lbl['LBL_SIGNUP_DEMO_CONTENT']);
					if($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX'){
						$link = $tconfig["tsite_url"]."user-login";
					} else {
						$link = $tconfig["tsite_url"]."rider-login";
					}

					header("Location:".$link);exit;
				}
                $sql = "select * from currency where eDefault = 'Yes'";
                $db_curr = $obj->MySQLSelect($sql);

                $curr = $db_curr[0]['vName'];
                $sql = "select * from language_master where eDefault = 'Yes'";

                $db_lang = $obj->MySQLSelect($sql);
                $lang = $db_lang[0]['vCode'];
                $eReftype = "Rider";
                $refercode = $generalobj->ganaraterefercode($eReftype);
                $dRefDate = Date('Y-m-d H:i:s');
                $tRegistrationDate = Date('Y-m-d H:i:s');


                if ($femail != "") {

                    $sql = "insert into register_user (vFbId ,vName, vLastName, vEmail, eStatus,vImgName,eGender,vLang,vCurrencyPassenger,vRefCode,dRefDate,tRegistrationDate) VALUES ('" . $fbid . "', '" . $fbfirstname . "', '" . $fblastname . "', '" . $femail . "', 'Active','" . $image_name . "','" . $fgender . "','" . $lang . "','" . $curr . "','" . $refercode . "','" . $dRefDate . "','" . $tRegistrationDate . "')";
                    $iUserId = $obj->MySQLInsert($sql);
                } else {

                    $sql = "insert into register_user (vFbId, vImgName, vName, vLastName, vEmail,eStatus,eGender,vLang,vCurrencyPassenger,vRefCode,dRefDate,tRegistrationDate) VALUES ('" . $fbid . "','" . $image_name . "', '" . $fbfirstname . "', '" . $fblastname . "', '" . $femail . "','Active','" . $fgender . "','" . $lang . "','" . $curr . "','" . $refercode . "','" . $dRefDate . "','" . $tRegistrationDate . "')";
                    $iUserId = $obj->MySQLInsert($sql);
                }

                $db_sql = "select * from register_user WHERE iUserId='" . $iUserId . "'";
                $db_user = $obj->MySQLSelect($db_sql);
                $type = base64_encode(base64_encode('rider'));
                $id = $generalobj->encrypt($iUserId);
                $newToken = $generalobj->RandomString(32);
                $url = $tconfig["tsite_url"] . 'reset_password.php?type=' . $type . '&id=' . $id . '&_token=' . $newToken;

                $maildata['EMAIL'] = $femail;
                $maildata['NAME'] = $fbfirstname . " " . $fblastname;
                $maildata['PASSWORD'] = '';
                $maildata['SOCIALNOTES'] = $langage_lbl['LBL_SOCIAL_MEDIA_NOTES1_TXT'] . '<br>' . $url . '<br>' . $langage_lbl['LBL_SOCIAL_MEDIA_NOTES2_TXT'];
                $generalobj->send_email_user("MEMBER_REGISTRATION_USER", $maildata);



                $Photo_Gallery_folder = $tconfig["tsite_upload_images_passenger_path"] . "/" . $iUserId . "/";

                if (!is_dir($Photo_Gallery_folder)) {
                    mkdir($Photo_Gallery_folder, 0777);
                }

                $baseurl = $picture;
                $url = strtolower($fbid) . ".jpg"; //changed by me 
                $image_name = $generalobj->copyRemoteFile($baseurl, $Photo_Gallery_folder . $url);



                if (is_file($Photo_Gallery_folder . $url)) {

                    list($width, $height, $type, $attr) = getimagesize($Photo_Gallery_folder . $url);
                    if ($width < $height) {
                        $final_width = $width;
                    } else {
                        $final_width = $height;
                    }
                    $img->load($Photo_Gallery_folder . $url)->crop(0, 0, $final_width, $final_width)->save($Photo_Gallery_folder . $url);


                    $imgname = $generalobj->img_data_upload($Photo_Gallery_folder, $url, $Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"], "");
                }

                $sql = "UPDATE register_user set vFbId='" . $fbid . "', vImgName='" . $imgname . "',eGender='" . $db_user[0]['eGender'] . "',eSignUpType = 'LinkedIn' WHERE iUserId='" . $iUserId . "'";
                $obj->sql_query($sql);

                return $generalobj->Checkverification_mobile($db_user[0]['iUserId'], 'rider');
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        echo $error;
        exit;
    }
} else {
    $msg1 = "Invalid Token";
    $link = $tconfig['tsite_url'] . 'sign-in.php';
    header("Location:" . $link);
    exit;
}
?>