<?php
include_once 'common.php';
$script = "Profile";

$user = isset($_SESSION["sess_user"]) ? $_SESSION["sess_user"] : '';
$success = isset($_REQUEST["success"]) ? $_REQUEST["success"] : '';
$var_msg = isset($_REQUEST["var_msg"]) ? $_REQUEST["var_msg"] : '';
$new = '';

$db_doc = array();
if (isset($_SESSION['sess_new'])) {
    $new = $_SESSION['sess_new'];
    unset($_SESSION['sess_new']);
}
$sessionUserType = "";
if(isset($_SESSION['sess_user'])){
    $sessionUserType = $_SESSION['sess_user'];    
}
$sessionUserId = 0;
if(isset($_SESSION['sess_iUserId'])){
    $sessionUserId = $_SESSION['sess_iUserId'];    
}
if (empty($SHOW_CITY_FIELD)) {
    $SHOW_CITY_FIELD = $generalobj->getConfigurations("configurations", "SHOW_CITY_FIELD");
}

$generalobj->check_member_login();
$sql = "select * from country where eStatus = 'Active' ORDER BY vCountry ASC ";
$db_country = $obj->MySQLSelect($sql);

$sql = "select * from currency where eStatus = 'Active' ORDER BY vName ASC ";
$db_currency = $obj->MySQLSelect($sql);

$access = 'company,driver';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$generalobj->setRole($access, $url);

/* if($APP_TYPE == 'Ride-Delivery'){
  $eTypeQuery = " AND (eType='Ride' OR eType='Delivery')";
  } else if($APP_TYPE == 'Ride-Delivery-UberX'){
  $eTypeQuery = " AND (eType='Ride' OR eType='Delivery' OR eType='UberX')";
  } else {
  $eTypeQuery = " AND eType='".$APP_TYPE."'";
  } */
//Added By HJ On 16-05-2020 For Checked Ubex Doc Data Start
$ufxEnable = 'No';
$ufxDocCondition = " AND dm.eDocServiceType!='ServiceSpecific' AND dm.eType!='UberX'";
if ($generalobj->CheckUfxServiceAvailable() == 'Yes') {
    $ufxEnable = 'Yes';
    $ufxDocCondition="";
}
//Added By HJ On 16-05-2020 For Checked Ubex Doc Data End
if ($sessionUserType == 'company') {
    $sql = "select * from company where iCompanyId = '" . $sessionUserId . "'";
    $db_user = $obj->MySQLSelect($sql);
    if (count($db_user) > 0) {
        //echo "<pre>";print_r($db_user);die;
    }
    $sql = "SELECT vNoc,vCerti from company where iCompanyId = '" . $sessionUserId . "'";
    $db_doc = $obj->MySQLSelect($sql);
    $sql = "SELECT dm.doc_masterid masterid, dm.doc_usertype ,dm.doc_name_" . $_SESSION['sess_lang'] . "  as d_name , dm.doc_name ,dm.ex_status,dm.status, dl.doc_masterid masterid_list ,dl.ex_date,dl.doc_file , dl.status, dm.eType FROM document_master dm left join (SELECT * FROM `document_list` where doc_userid='" . $sessionUserId . "' ) dl on dl.doc_masterid=dm.doc_masterid where dm.doc_usertype='company' and dm.status='Active' $ufxDocCondition and (dm.country ='" . $db_user[0]['vCountry'] . "' OR dm.country ='All')";
    $db_userdoc = $obj->MySQLSelect($sql);
    $count_all_doc = count($db_userdoc);
}

if ($sessionUserType == 'driver') {
    $sql = "select * from register_driver where iDriverId = '" . $sessionUserId . "'";
    $db_user = $obj->MySQLSelect($sql);
    if (count($db_user) > 0) {
        $driverCompany = $db_user[0]['iCompanyId'];
        //echo "<pre>fg";print_r($db_user[0]['iCompanyId']);die;
    }
    $sql = "SELECT dm.doc_masterid masterid, dm.doc_usertype ,dm.doc_name_" . $_SESSION['sess_lang'] . "  as d_name , dm.doc_name ,dm.ex_status,dm.status, dl.doc_masterid masterid_list ,dl.ex_date,dl.doc_file , dl.status, dm.eType FROM document_master dm left join (SELECT * FROM `document_list` where doc_userid='" . $sessionUserId . "' ) dl on dl.doc_masterid=dm.doc_masterid where dm.doc_usertype='driver' and dm.status='Active' $ufxDocCondition and (dm.country ='" . $db_user[0]['vCountry'] . "' OR dm.country ='All')";

    $db_userdoc = $obj->MySQLSelect($sql);
    $count_all_doc = count($db_userdoc);

    if ($SITE_VERSION == "v5") {
        $data_driver_pref = $generalobj->Get_User_Preferences($sessionUserId);
    }
}
//echo "<pre>";print_r($db_userdoc);die;
$noc = $certi = $licence = '';
if (count($db_doc) > 0) {
    $noc = $db_doc[0]['vNoc'];
    $certi = $db_doc[0]['vCerti'];
    if ($sessionUserType == 'driver') {
        $licence = $db_doc[0]['vLicence'];
    }
}
$sql = "select * from language_master where eStatus = 'Active' ORDER BY vTitle ASC ";
$db_lang = $obj->MySQLSelect($sql);
$lang = "";
for ($i = 0; $i < count($db_lang); $i++) {
    if ($db_user[0]['vLang'] == $db_lang[$i]['vCode']) {
        $lang_user = $db_lang[$i]['vTitle'];
    }
}

for ($i = 0; $i < count($db_country); $i++) {
    if ($db_user[0]['vCountry'] == $db_country[$i]['vCountryCode']) {
        $country = $db_country[$i]['vCountry'];
    }
}
//echo "<pre>";print_r($action);die;
if ($action = 'document' && isset($_POST['doc_type'])) {
    $expDate = $_POST['dLicenceExp'];
    if (SITE_TYPE == 'Demo') {
        header("location:profile?success=2&id=" . $_REQUEST['id'] . "&var_msg=" . $var_msg);
        exit;
    }
    $user = $_POST['user'];
    $masterid = $_REQUEST['master'];

    if (isset($_POST['doc_path'])) {
        $doc_path = $_POST['doc_path'];
    }
    $temp_gallery = $doc_path . '/';
    $image_object = $_FILES['driver_doc']['tmp_name'];
    $image_name = $_FILES['driver_doc']['name'];

    if (empty($image_name)) {
        $image_name = $_POST['driver_doc_hidden'];
    }
    if ($image_name == "") {
        if ($expDate != "") {
            if ($user == 'company') {
                $sql = "select ex_date from document_list where doc_userid='" . $_REQUEST['id'] . "' and doc_usertype='company' and doc_masterid='" . $masterid . "'";
            } else {
                $sql = "select ex_date from document_list where doc_userid='" . $_REQUEST['id'] . "' and doc_usertype='driver' and doc_masterid='" . $masterid . "'";
            }
            $query = $obj->MySQLSelect($sql);
            $fetch = $query[0];

            if ($fetch['ex_date'] == $expDate) {
                if ($user == 'company') {
                    $sql = "UPDATE `document_list` SET  ex_date='" . $expDate . "' WHERE doc_userid='" . $_REQUEST['id'] . "' and doc_usertype='company' and doc_masterid='" . $masterid . "'";
                } else {
                    $sql = "UPDATE `document_list` SET  ex_date='" . $expDate . "' WHERE doc_userid='" . $_REQUEST['id'] . "' and doc_usertype='driver' and doc_masterid='" . $masterid . "'";
                }
            } else {
                if ($user == 'company') {

                    $sql = "INSERT INTO `document_list` ( `doc_masterid`, `doc_usertype`, `doc_userid`, `ex_date`, `doc_file`, `status`, `edate`) VALUES ( '" . $_REQUEST['doc_type'] . "', 'company', '" . $_REQUEST['id'] . "', '" . $expDate . "', '', 'Inactive', CURRENT_TIMESTAMP)";
                } else {
                    $sql = " INSERT INTO `document_list` ( `doc_masterid`, `doc_usertype`, `doc_userid`, `ex_date`, `doc_file`, `status`, `edate`) VALUES ( '" . $_REQUEST['doc_type'] . "', 'driver', '" . $_REQUEST['id'] . "', '" . $expDate . "', '', 'Inactive', CURRENT_TIMESTAMP)";
                }
            }
            $query = $obj->sql_query($sql);
        }

        $var_msg = $langage_lbl['LBL_UPLOAD_IMG_ERROR'];
        header("location:profile?success=0&id=" . $_REQUEST['id'] . "&var_msg=" . $var_msg);
    }

    if ($_FILES['driver_doc']['name'] != "") {
        /* $check_file_query = "select iDriverId,vNoc from company where iDriverId=" . $_REQUEST['id'];
          $check_file = $obj->sql_query($check_file_query);
          $check_file['vNoc'] = $doc_path . '/' . $_REQUEST['id'] . '/' . $check_file[0]['vNoc']; */

        $filecheck = basename($_FILES['driver_doc']['name']);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[count($fileextarr) - 1]);
        $flag_error = 0;
        if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp" && $ext != "pdf" && $ext != "doc" && $ext != "docx") {
            $flag_error = 1;
            $var_msg = $langage_lbl['LBL_WRONG_FILE_SELECTED_TXT'];
        }

        if ($flag_error == 1) {
            header("location:profile?success=0&id=" . $_REQUEST['id'] . "&var_msg=" . $var_msg);
            //$generalobj->getPostForm($_POST, $var_msg, "driver_document_action.php?success=1&id=" . $_REQUEST['id'] . "&var_msg=" . $var_msg);
            exit;
        } else {
            $Photo_Gallery_folder = $doc_path . '/' . $_REQUEST['id'] . '/';
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
            }
            $vFile = $generalobj->fileupload($Photo_Gallery_folder, $image_object, $image_name, $prefix = '', $vaildExt = "pdf,doc,docx,jpg,jpeg,gif,png");
            $vImage = $vFile[0];
            $var_msg = $langage_lbl['LBL_UPLOAD_MSG'];
            $tbl = 'document_list';
            if ($user == 'company') {
                $sql = "select doc_id from  " . $tbl . "  where doc_userid='" . $_REQUEST[id] . "' and doc_usertype='company'  and doc_masterid=" . $_REQUEST['doc_type'];
            } else {
                $sql = "select doc_id from  " . $tbl . "  where doc_userid='" . $_REQUEST[id] . "' and doc_usertype='driver'  and doc_masterid=" . $_REQUEST['doc_type'];
            }
            $db_data = $obj->MySQLSelect($sql);
            $q = "INSERT INTO ";
            $where = '';

            if (count($db_data) > 0) {

                if ($user == 'company') {

                    $query = "UPDATE `" . $tbl . "` SET `doc_file`='" . $vImage . "' , `ex_date`='" . $expDate . "' WHERE doc_userid='" . $_REQUEST[id] . "' and doc_usertype='company'  and doc_masterid=" . $_REQUEST['doc_type'];

                    $q = "UPDATE ";
                    $where = " WHERE `iDriverId` = '" . $_REQUEST['id'] . "'";
                } else {

                    $query = "UPDATE `" . $tbl . "` SET `doc_file`='" . $vImage . "' , `ex_date`='" . $expDate . "' WHERE doc_userid='" . $_REQUEST[id] . "' and doc_usertype='driver'  and doc_masterid=" . $_REQUEST['doc_type'];
                    $q = "UPDATE ";
                    $where = " WHERE `iDriverId` = '" . $_REQUEST['id'] . "'";
                }
            } else {

                if ($user == 'company') {

                    $query = " INSERT INTO `" . $tbl . "` ( `doc_masterid`, `doc_usertype`, `doc_userid`, `ex_date`, `doc_file`, `status`, `edate`) "
                            . "VALUES " . "( '" . $_REQUEST['doc_type'] . "', 'company', '" . $_REQUEST['id'] . "', '" . $expDate . "', '" . $vImage . "', 'Inactive', CURRENT_TIMESTAMP)";
                } else {

                    $query = " INSERT INTO `" . $tbl . "` ( `doc_masterid`, `doc_usertype`, `doc_userid`, `ex_date`, `doc_file`, `status`, `edate`) "
                            . "VALUES " . "( '" . $_REQUEST['doc_type'] . "', 'driver', '" . $_REQUEST['id'] . "', '" . $expDate . "', '" . $vImage . "', 'Inactive',     CURRENT_TIMESTAMP)";
                }
            }

            $obj->sql_query($query);

            ###### Email #######
            if ($user == 'company') {
                $maildata['NAME'] = $db_user[0]['vCompany'] . " (" . $langage_lbl['LBL_DOCUMNET_UPLOAD_BY_COMPANY'] . ")";
            } else {
                $maildata['NAME'] = $db_user[0]['vName'] . " " . $db_user[0]['vLastName'] . " (" . $langage_lbl['LBL_DOCUMNET_UPLOAD_BY_DRIVER'] . ")";
            }

            $maildata['EMAIL'] = $db_user[0]['vEmail'];
            $docname_SQL = "SELECT doc_name_" . $default_lang . " as docname FROM document_master WHERE doc_masterid = '" . $_REQUEST['doc_type'] . "'";
            $docname_data = $obj->MySQLSelect($docname_SQL);
            $maildata['DOCUMENTTYPE'] = $docname_data[0]['docname'];

            if ($user == 'company') {
                $maildata['DOCUMENTFOR'] = $langage_lbl['LBL_DOCUMNET_UPLOAD_BY_COMPANY'];
            } else {
                $maildata['DOCUMENTFOR'] = $langage_lbl['LBL_DOCUMNET_UPLOAD_BY_DRIVER'];
            }
            $generalobj->send_email_user("DOCUMENT_UPLOAD_WEB", $maildata);

            if ($user == 'driver') {
                $sqlquery = "SELECT vEmail,vCompany FROM company WHERE iCompanyId = '" . $_SESSION['sess_iCompanyId'] . "'";
                $Companydata = $obj->MySQLSelect($sqlquery);
                $maildata['COMPANYEMAIL'] = $Companydata[0]['vEmail'];
                $maildata['COMPANYNAME'] = $Companydata[0]['vCompany'];
                $generalobj->send_email_user("DOCUMENT_UPLOAD_WEB_COMPANY", $maildata);
            }
            #######Email ##########
            //Start :: Log Data Save
            $vNocPath = $vImage;
            $generalobj->save_log_data($sessionUserId, $_REQUEST['id'], 'company', 'noc', $vNocPath);
            //End :: Log Data Save
            // Start :: Status in edit a Document upload time
            // $set_value = "`eStatus` ='inactive'";
            //$generalobj->estatus_change('register_driver','iDriverId',$_REQUEST['id'],$set_value);
            // End :: Status in edit a Document upload time
            header("location:profile?success=1&id=" . $_REQUEST['id'] . "&var_msg=" . $var_msg);
        }
    } else {
        $vImage = $_POST['driver_doc_hidden'];
        $var_msg = $langage_lbl['LBL_UPLOAD_DOC_SUCCESS_UPLOAD_DOC'];
        $tbl = 'document_list';
        if ($user == 'company') {
            $sql = "select doc_id from  " . $tbl . "  where doc_userid='" . $_REQUEST[id] . "' and doc_usertype='company'  and doc_masterid=" . $_REQUEST['doc_type'];
        } else {
            $sql = "select doc_id from  " . $tbl . "  where doc_userid='" . $_REQUEST[id] . "' and doc_usertype='driver'  and doc_masterid=" . $_REQUEST['doc_type'];
        }
        $db_data = $obj->MySQLSelect($sql);
        $q = "INSERT INTO ";
        $where = '';
        if (count($db_data) > 0) {
            if ($user == 'company') {
                $query = "UPDATE `" . $tbl . "` SET `doc_file`='" . $vImage . "' , `ex_date`='" . $expDate . "' WHERE doc_userid='" . $_REQUEST[id] . "' and doc_usertype='company'  and doc_masterid=" . $_REQUEST['doc_type'];
                $q = "UPDATE ";
                $where = " WHERE `iDriverId` = '" . $_REQUEST['id'] . "'";
            } else {
                $query = "UPDATE `" . $tbl . "` SET `doc_file`='" . $vImage . "' , `ex_date`='" . $expDate . "' WHERE doc_userid='" . $_REQUEST[id] . "' and doc_usertype='driver'  and doc_masterid=" . $_REQUEST['doc_type'];
                $q = "UPDATE ";
                $where = " WHERE `iDriverId` = '" . $_REQUEST['id'] . "'";
            }
        } else {
            if ($user == 'company') {
                $query = " INSERT INTO `" . $tbl . "` ( `doc_masterid`, `doc_usertype`, `doc_userid`, `ex_date`, `doc_file`, `status`, `edate`) " . "VALUES " . "( '" . $_REQUEST['doc_type'] . "', 'company', '" . $_REQUEST['id'] . "', '" . $expDate . "', '" . $vImage . "', 'Inactive', CURRENT_TIMESTAMP)";
            } else {
                $query = " INSERT INTO `" . $tbl . "` ( `doc_masterid`, `doc_usertype`, `doc_userid`, `ex_date`, `doc_file`, `status`, `edate`) " . "VALUES " . "( '" . $_REQUEST['doc_type'] . "', 'driver', '" . $_REQUEST['id'] . "', '" . $expDate . "', '" . $vImage . "', 'Inactive',     CURRENT_TIMESTAMP)";
            }
        }
        $obj->sql_query($query);
        $vNocPath = $vImage;
        $generalobj->save_log_data($sessionUserId, $_REQUEST['id'], 'company', 'noc', $vNocPath);
        header("location:profile?success=1&id=" . $_REQUEST['id'] . "&var_msg=" . $var_msg);
    }
}
if (count($db_userdoc) > 0) {
    for ($i = 0; $i < count($db_userdoc); $i++) {
        if ($db_userdoc[$i]['doc_file'] != "") {
            $test[] = '1';
        }
    }
}

if (count($test) == count($db_userdoc)) {
    $UploadDocuments = 'Yes';
} else {
    $UploadDocuments = 'No';
}

$isStoreDriverOption = isStoreDriverAvailable();

$vCompanyname = '';
//echo $db_user[0]['iCompanyId'];die;
$showServiceDesc = 1;
if (isset($db_user[0]['iCompanyId']) && $db_user[0]['iCompanyId'] > 0) {
    $companydata = "select vCompany,eSystem from company WHERE iCompanyId = " . $db_user[0]['iCompanyId'] . "";
    $db_companydata = $obj->MySQLSelect($companydata);
    if (count($db_companydata) > 0) {
        //echo "<pre>";print_r($db_companydata);die;
        if(isset($db_companydata[0]['eSystem']) && strtoupper($db_companydata[0]['eSystem']) == "DELIVERALL"){
            $showServiceDesc = 0;
        }
        if ($isStoreDriverOption > 0) {
            $vCompanyname = $db_companydata[0]['vCompany'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_HEADER_PROFILE_TXT']; ?> </title>
        <!-- Default Top Script and css -->
        <?php include_once "top/top_script.php"; ?>
        <link rel="stylesheet" href="assets/css/bootstrap-fileupload.min.css" >
        <?php if ($sessionUserType == 'driver' && $APP_TYPE == 'UberX' && $ufxEnable == 'Yes') { ?>
            <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&language=en&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>"></script>
        <?php } ?>
        <!-- End: Default Top Script and css-->

        <style type="text/css">

        </style>

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
            <section class="profile-section">
                <div class="profile-section-inner">
                    <?php
                    //echo $_SESSION['sess_user'];die;
                    if ($sessionUserType == 'company') {
                        if (SITE_TYPE == 'Demo') {
                            ?><div class="demo-warning" style="width: 100%; margin-bottom:30px;">
                                <p><?= $langage_lbl['LBL_WE_SEE_YOU_HAVE_REGISTERED_AS_A_COMPANY']; ?></p>
                                <p><?= $langage_lbl['LBL_SINCE_IT_IS_DEMO_VERSION']; ?></p>

                                <p><?= $langage_lbl['LBL_STEP1']; ?></p>
                                <!--    <p><?= $langage_lbl['LBL_STEP2']; ?></p>-->
                                <p><?= $langage_lbl['LBL_STEP3']; ?></p>

                                <p><?= $langage_lbl['LBL_HOWEVER_IN_REAL_SYSTEM']; ?></p>
                            </div>
                        <? } else { ?>
                            <div class="demo-warning" style="width: 100%; margin-bottom:30px;">
                                <p>
                                    <?= $langage_lbl['LBL_WE_SEE_YOU_HAVE_REGISTERED_AS_A_COMPANY']; ?>
                                    <? if ($UploadDocuments == 'No') { ?>
                                        <?= $langage_lbl['LBL_KINDLY_PROVIDE_BELOW']; ?>
                                    <? } ?></p>
                                <p><?= $langage_lbl['LBL_ALSO_ADD_DRIVERS']; ?></p>
                                <p><?= $langage_lbl['LBL_EITHER_YOU_AS_A_COMPANY_DRIVER']; ?></p>
                            </div>
                            <?php
                        }
                    } else {
                        ?>
                        <? if (SITE_TYPE == 'Demo') { ?>
                            <div class="demo-warning" style="width: 100%; margin-bottom:30px;">
                                <p><?= $langage_lbl['LBL_PROFILE_WE_SEE_YOU_HAVE_REGISTERED_AS_A_DRIVER']; ?></p>
                                <p><?= $langage_lbl['LBL_SINCE_IT_IS_DEMO_VERSION_ADDVEHICLE']; ?></p>

                                <p><?= $langage_lbl['LBL_HOWEVER_IN_REAL_SYSTEM_DRIVER']; ?></p>
                            </div>
                            <?
                        } else {
                            if ($UploadDocuments == 'No') {
                                ?>
                                <div class="demo-warning" style="width: 100%; margin-bottom:30px;">
                                    <p>
                                        <?php
                                        if (isset($_REQUEST['first']) && $_REQUEST['first'] == 'yes') {
                                            echo $langage_lbl['LBL_PROFILE_WE_SEE_YOU_HAVE_REGISTERED_AS_A_DRIVER'];
                                        }
                                        ?>
                                    </p>
                                    <p>
                                        <?php
                                        if ($db_user[0]['eStatus'] == 'inactive') {
                                            echo $langage_lbl['LBL_INACTIVE_DRIVER_MESSAGE'];
                                        }
                                        ?>
                                    </p>
                                    <p>
                                        <?= $langage_lbl['LBL_KINDLY_PROVIDE_BELOW_VISIBLE']; ?>
                                    </p>
                                </div>
                            <?php } else if ($db_user[0]['eStatus'] == 'inactive') { ?>
                                <div class="demo-warning" style="width: 100%; margin-bottom:30px;">
                                    <p>
                                        <?= $langage_lbl['LBL_INACTIVE_DRIVER_MESSAGE']; ?>
                                    </p>
                                </div>
                                <?php
                            }
                        }
                    }
                    ?>




                    <div class="profile-caption">
                        <div class="page-heading">
                            <h1><?= $langage_lbl['LBL_PROFILE_TITLE_TXT']; ?></h1>
                        </div>

                        <div style="width: 100%;">
                            <?php if ($success == 1) { ?>
                                <div class="alert alert-success" style="width: 100%;margin: 0px 0 30px 0;">
                                    <span><?= $langage_lbl['LBL_PROFILE_UPDATED']; ?></span>
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>

                                </div>
                                <?php
                            } else if ($success == 2) {
                                ?>
                                <div class="alert alert-danger">
                                    <span><?php echo $langage_lbl['LBL_EDIT_DELETE_RECORD']; ?></span>
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>

                                </div>
                                <?php
                            } else if ($success == 0 && $var_msg != "") {
                                ?>
                                <div class="alert alert-danger msgs_hide">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $var_msg; ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="profile-image">
                            <?php
                            if ($sessionUserType == 'company') {
                                $img_path = $tconfig["tsite_upload_images_compnay"];
                                $profileImgpath = $tconfig["tsite_upload_images_compnay_path"];
                            } else if ($sessionUserType == 'driver') {
                                $img_path = $tconfig["tsite_upload_images_driver"];
                                $profileImgpath = $tconfig["tsite_upload_images_driver_path"];
                            }
                            if (($db_user[0]['vImage'] == 'NONE' || $db_user[0]['vImage'] == '') || !file_exists($profileImgpath . '/' . $sessionUserId . '/2_' . $db_user[0]['vImage'])) {
                                ?>
                                <img src="assets/img/profile-user-img.png" alt="">
                            <? } else { ?>
                                <img src = "<?= $img_path . '/' . $sessionUserId . '/2_' . $db_user[0]['vImage'] ?>" style="height:150px;"/>
                            <?php } ?>

                    <!-- <a data-toggle="modal" data-target="#uiModal_4"><i class="fa fa-pencil" aria-hidden="true"></i></a> -->

                            <?php if (($db_user[0]['vImage'] == 'NONE' || $db_user[0]['vImage'] == '') && ($db_user[0]['eStatus'] == 'inactive')) { ?>
                                <a  href="javascript:void(0);"><i class="fa fa-pencil" aria-hidden="true" onClick ="checkProviderRestrictProfile();" ></i></a>

                            <?php } else if (($db_user[0]['vImage'] == 'NONE' || $db_user[0]['vImage'] == '') && ($db_user[0]['eStatus'] == 'active')) { ?>
                                <a href="javascript:void(0)"  onclick="alert('<?php echo $langage_lbl['LBL_PROFILE_IMAGE_BLOCK']; ?>');" ><i class="fa fa-pencil" aria-hidden="true" onClick ="checkProviderRestrictProfile();" ></i></a>
                            <?php } else { ?>
                                <a href="javascript:void(0)"><i class="fa fa-pencil" aria-hidden="true" onClick ="checkProviderRestrictProfile();" ></i></a>
                            <?php } ?>   

                        </div>
                        <div class="profile-block">         	
                            <div class="profile-caption-header">
                                <label><?= $langage_lbl['LBL_HELLO'] . ", " . $generalobj->cleanall(htmlspecialchars($db_user[0]['vName'])) . ' ' . $generalobj->cleanall(htmlspecialchars($db_user[0]['vLastName'])); ?></label>

                                <button class="profile_edit_btn" onclick="<?php if ($generalobj->getEditDriverProfileStatus($db_user[0]['eStatus']) == "No" && $sessionUserType == 'driver') { ?> alert('<?php echo $langage_lbl['LBL_PROFILE_EDIT_BLOCK_TXT']; ?>') <?php } ?>" ><?= $langage_lbl['LBL_EDIT_PROFILE_TXT']; ?></button>
                            </div>
                            <div class="profile-detail">
                                <div class="profile-column">
                                    <i class="fa fa-envelope-o" aria-hidden="true"></i>
                                    <div class="data_info">
                                        <strong><?= $langage_lbl['LBL_EMAIL_LBL_TXT']; ?></strong>
                                        <span><?= $db_user[0]['vEmail']; ?></span>
                                    </div>
                                </div>
                                <div class="profile-column">
                                    <i class="icon-call" aria-hidden="true"></i>
                                    <div class="data_info">

                                        <strong><?= $langage_lbl['LBL_PHONE']; ?></strong>
                                        <span dir="ltr"><? if (!empty($db_user[0]['vPhone'])) { ?>(+<?= $db_user[0]['vCode'] ?>) <?= $db_user[0]['vPhone'] ?><?php } ?></span>
                                    </div>
                                </div>
                                <div class="profile-column">
                                    <i class="icon-location" aria-hidden="true"></i>
                                    <div class="data_info">
                                        <strong><?= $langage_lbl['LBL_COUNTRY_TXT']; ?></strong>
                                        <span><?php if ($db_user[0]['vCountry'] != "") { ?><?= $country ?><?php } ?></span>
                                    </div>
                                </div>
                                <?php if ($REFERRAL_SCHEME_ENABLE == 'Yes') { ?>
                                    <div class="profile-column">
                                        <i class="icon-refer" aria-hidden="true"></i>
                                        <div class="data_info">
                                            <strong><?php echo $langage_lbl['LBL_REFERAL_CODE_SIGNUP']; ?></strong>
                                            <span><?= $db_user[0]['vRefCode'] ?></span>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <?php if ($success == 1) { ?>
                        <!-- <div class="demo-success msgs_hide">
                        <button class="demo-close" type="button">×</button>
                        <?= $var_msg ?>
                    </div> -->
                        <?php
                    } else if ($success == 2) {
                        ?>
                        <!-- <div class="alert alert-danger msgs_hide">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                        <?= $langage_lbl['LBL_EDIT_DELETE_RECORD']; ?>
                        </div> -->
                        <?php
                    } else if ($success == 0 && $var_msg != "") {
                        ?>
                        <!-- <div class="alert alert-danger msgs_hide">
                            <button class="demo-close" type="button">×</button>
                        <?= $var_msg; ?>
                        </div> -->
                    <?php } ?>


                    <form id="frm1" method="post" action="javascript:void(0);" class="general-form profile_edit profile-caption addVehicleCX ">
                        <input  type="hidden" class="edit" name="action" value="allInOne">
                        <div class="partation">
                            <h1><?= $langage_lbl['LBL_PERSONAL_INFO_TXT']; ?></h1>
                            <div class="form-group half newrow">
                                <label><?= $langage_lbl['LBL_PROFILE_YOUR_EMAIL_ID']; ?> <span class="red">*</span></label>
                                <input type="hidden" name="uid" id="u_id1" value="<?= $sessionUserId; ?>">
                                <input type="hidden" name="user_type" id="user_type" value="<?= $sessionUserType; ?>">
                                <input type="email" id="in_email" class="edit-profile-detail-form-input" value = "<?= $db_user[0]['vEmail'] ?>" name="email" <?= isset($db_user[0]['vEmail']) ? '' : ''; ?>  required title="Please enter valid email address">
                                <div class="required-label" id="emailCheck"></div>	                                	
                            </div>
                            <?php
                            if ($sessionUserType == 'driver') {
                                if ($generalobj->getEditDriverProfileStatus($db_user[0]['eStatus']) == "No")
                                    $readOnly = ($db_user[0]['eStatus'] == "active") ? 'readonly' : '';
                                ?>
                                <div class="form-group half newrow">
                                    <label><?= $langage_lbl['LBL_SIGN_UP_FIRST_NAME_HEADER_TXT']; ?><span class="red">*</span></label>
                                    <input type="text" class="edit-profile-detail-form-input" value = "<?= $generalobj->cleanall(htmlspecialchars($db_user[0]['vName'])); ?>" name="name" required <?= $readOnly ?> >
                                </div>
                                <div class="form-group half newrow">
                                    <label><?= $langage_lbl['LBL_YOUR_LAST_NAME']; ?><span class="red">*</span></label>
                                    <input type="text" class="edit-profile-detail-form-input" value = "<?= $generalobj->cleanall(htmlspecialchars($db_user[0]['vLastName'])); ?>" name="lname" required <?= $readOnly ?> >
                                </div>
                                <?
                            } else if ($sessionUserType == 'company') {
                                ?>
                                <div class="form-group half newrow">
                                    <label><?= $langage_lbl['LBL_COMPANY_SIGNUP']; ?><span class="red">*</span></label>
                                    <input type="text" class="edit-profile-detail-form-input" value = "<?= $generalobj->cleanall(htmlspecialchars($db_user[0]['vCompany'])); ?>" name="vCompany" required >
                                </div>
                            <? } ?>	  

                            <div class="form-group half newrow">
                               <!-- <label><?= $langage_lbl['LBL_SELECT_CONTRY']; ?><span class="red">*</span></label> -->
                               <?php 
                                        if(count($db_country) > 1){ 
                                              $style = "";
                                             }else{
                                            $style = " disabled=disabled";
                                        } ?>
                                <select  <?= $style ?> class="custom-select-new vCountry" name = 'vCountry' id="country" onChange="changeCode(this.value);" required>
                                    <?php 
                                        if(count($db_country) > 1){ ?>
                                    <option value=""><?= $langage_lbl['LBL_SELECT_CONTRY']; ?></option>
                                    <?php } ?>
                                    <? for ($i = 0; $i < count($db_country); $i++) { ?>
                                        <option value = "<?= $db_country[$i]['vCountryCode'] ?>" <? if ($db_user[0]['vCountry'] == $db_country[$i]['vCountryCode']) { ?>selected<? } ?>><?= $db_country[$i]['vCountry'] ?></option>
                                    <? } ?>
                                </select>
                                <div class="required-label" id="vCountryCheck"></div>	                                    	
                            </div>   

                            <div class="form-group half newrow phone-column">
                                <label><?= $langage_lbl['LBL_Phone_Number']; ?><span class="red">*</span></label>
                                <input type="text" class="input-phNumber1 phonecode" id="code" name="vCode" value="<?= $db_user[0]['vCode'] ?>" readonly  >
                                <input name="phone" id="phone" type="text" value="<?= $db_user[0]['vPhone'] ?>" class="edit-profile-detail-form-input input-phNumber2"  title="Please enter proper phone number." onKeyUp="return isNumberKey(event);" onkeypress="return isNumberKey(event);" onblur="return isNumberKey(event);" required/>	                                    	
                            </div>

                            <?php if ($isStoreDriverOption > 0 && $vCompanyname != '' && $db_user[0]['iCompanyId'] > 1) { ?>

                                <div class="form-group half newrow">
                                    <select class="custom-select-new store" name = 'store' id="store" onChange="changeCode(this.value);" disabled>
                                        <option value=""><?= $langage_lbl['LBL_STORE']; ?></option>
                                        <? for ($i = 0; $i < count($db_country); $i++) { ?>
                                            <option value = "<?= $db_country[$i]['vCountryCode'] ?>" <? if ($db_user[0]['vCountry'] == $db_country[$i]['vCountryCode']) { ?>selected<? } ?>><?= $vCompanyname; ?></option>
                                        <? } ?>
                                    </select>
                                </div>

                            <?php } ?>

                            <?php if ($sessionUserType == 'company') { ?>
                                <div class="form-group half newrow">
                                    <label><?= $langage_lbl['LBL_VAT_NUMBER_SIGNUP']; ?></label>
                                    <input type="text" class="form-control" name="vVatNum"  id="vVatNum" value="<?= $db_user[0]['vVat']; ?>">

                                </div>    
                            <?php } ?>

                        </div>

                        <div class="partation">
                            <h1><?= $langage_lbl['LBL_PROFILE_ADDRESS']; ?></h1>


                            <div class="form-group half newrow">
                                <label><?= $langage_lbl['LBL_PROFILE_ADDRESS']; ?></label>
                                <input type="text" class="profile-address-input" value="<?= $generalobj->cleanall(htmlspecialchars($db_user[0]['vCaddress'])); ?>" name="address1"  <?php if ($sessionUserType == 'company') { ?>required<? } ?> >
                            </div>


                            <!-- <div class="form-group half newrow">

                                 <select class="form-control" name = 'vCountry' id="vCountry" onChange="setState(this.value, '');" required disabled="true">
                                     <option value="">Select</option>
                            <? for ($i = 0; $i < count($db_country); $i++) { ?>
                                                 <option  <? if ($db_user[0]['vCountry'] == $db_country[$i]['vCountryCode']) { ?>selected<? } ?> value = "<?= $db_country[$i]['vCountryCode'] ?>"><?= $db_country[$i]['vCountry'] ?></option>
                            <? } ?>
                                 </select>
                                 <div class="required-label" id="vCountryCheck"></div>
                             </div>-->

                            <div class="form-group half newrow">
                                <select class="form-control" name = 'vState' id="vState" onChange="setCity(this.value, '');" >
                                    <option value="">Select</option>
                                </select>
                            </div>

                            <?php if ($SHOW_CITY_FIELD == 'Yes') { ?>
                                <div class="form-group half newrow">
                                    <select class="form-control" name = 'vCity' id="vCity"  >
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            <?php } ?>     

                            <div class="form-group half newrow">
                                <label>Zip  <?php if ($sessionUserType == 'company') { ?><span class="red">*</span><? } ?></label>
                                <input type="text" class="profile-address-input" value="<?= $db_user[0]['vZip'] ?>" name="vZipcode" <?php if ($sessionUserType == 'company') { ?> required <? } ?>>
                            </div>
                        </div>


                        <div class="partation">
                            <h1><?= $langage_lbl['LBL_PROFILE_PASSWORD_LBL_TXT']; ?></h1>


                            <?php if ($db_user[0]['vFbId'] >= 0 && $db_user[0]['vPassword'] != "") { ?>

                                <div class="form-group half newrow">
                                    <label><?= $langage_lbl['LBL_CURR_PASS_HEADER']; ?></label>
                                    <input type="password" class="input-box" name="cpass" id="cpass" onkeyup="nospaces(this)" >
                                </div>

                            <?php } ?>

                            <div class="form-group half newrow">
                                <label><?= $langage_lbl['LBL_NEW_PASSWORD_TXT']; ?></label>
                                <input type="password" class="input-box" name="npass" id="npass" onkeyup="nospaces(this)"  >
                            </div>


                            <div class="form-group half newrow">
                                <label><?= $langage_lbl['LBL_Confirm_New_Password']; ?></label>
                                <input type="password" class="input-box" name="ncpass" id="ncpass" onkeyup="nospaces(this)" onblur="<?= ($db_user[0]['vFbId'] >= 0 && $db_user[0]['vPassword'] != "" ) ? 'validate_password()' : 'validate_password_fb()'; ?>">
                            </div>
                        </div>
                        <!-- bank detail -->
                        <?php if ($sessionUserType == 'driver') { ?>
                            <div class="partation">
                                <h1><?= $langage_lbl['LBL_BANK_DETAILS_TXT']; ?></h1>
                                <!-- <input  type="hidden" class="edit" name="action" value="bankdetail"> -->
                                <div class="form-group half newrow">
                                  <label><?= $langage_lbl['LBL_PAYMENT_EMAIL_TXT']; ?><!--<span class="red">*</span>--></label>
                                    <input type="email" class="profile-address-input" value="<?= $db_user[0]['vPaymentEmail'] ?>" name="vPaymentEmail">
                                </div>
                                <div class="form-group half newrow">
                                    <label><?= $langage_lbl['LBL_PROFILE_BANK_HOLDER_TXT']; ?>  </label>
                                    <input type="text" class="profile-address-input" value="<?= $db_user[0]['vBankAccountHolderName'] ?>" name="vBankAccountHolderName" >
                                </div>
                                <div class="form-group half newrow">
                                    <label><?= $langage_lbl['LBL_ACCOUNT_NUMBER']; ?></label>
                                    <input type="text" class="profile-address-input" value="<?= $db_user[0]['vAccountNumber'] ?>"
                                           name="vAccountNumber" >
                                </div>
                                <div class="form-group half newrow">
                                    <label><?= $langage_lbl['LBL_BANK_NAME']; ?> </label>
                                    <input type="text" class="profile-address-input" value="<?= $db_user[0]['vBankName'] ?>" name="vBankName" ></div>
                                <div class="form-group half newrow">
                                    <label><?= $langage_lbl['LBL_BANK_LOCATION']; ?></label>
                                    <input type="text" class="profile-address-input" value="<?= $db_user[0]['vBankLocation'] ?>" name="vBankLocation" >
                                </div>
                                <div class="form-group half newrow">
                                    <label><?= $langage_lbl['LBL_BIC_SWIFT_CODE']; ?></label>
                                    <input type="text" class="profile-address-input" value="<?= $db_user[0]['vBIC_SWIFT_Code'] ?>"
                                           name="vBIC_SWIFT_Code" >
                                </div>
                            </div>	                           				

                        <?php } ?>

                        <div class="partation">
                            <h1><?= $langage_lbl['LBL_OTHER_INFO_TXT']; ?></h1>
                            <?php if (count($db_lang) <= 1) { ?>
                            <input name="lang1" type="hidden" class="create-account-input" value="<?php echo $db_lang[0]['vCode']; ?>"/>	
                            <?php } else { ?>
                            <div class="form-group half newrow">
                                <select name="lang1" class="custom-select-new profile-language-input">
                                    <?php
                                    for ($i = 0; $i < count($db_lang); $i++) {
                                        ?>
                                        <option value="<?= $db_lang[$i]['vCode'] ?>" <? if ($db_user[0]['vLang'] == $db_lang[$i]['vCode']) { ?> selected <? } ?> ><? echo $db_lang[$i]['vTitle']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <?php } if ($sessionUserType == 'driver') { ?>
                            <?php if(count($db_currency) <= 1) { ?>
                            <input name="vCurrencyDriver" type="hidden" class="create-account-input" value="<?php echo $db_currency[0]['vName']; ?>"/>
                            <? } else { ?>
                                <div class="form-group half newrow">
                                    <!-- <label><?= $langage_lbl['LBL_SELECT_CURRENCY']; ?><span class="red">*</span></label> -->
                                    <select class="custom-select-new vCurrencyDriver" name = 'vCurrencyDriver' required>
                                        <option value="">--select--</option>
                                        <? for ($i = 0; $i < count($db_currency); $i++) { ?>
                                            <option value = "<?= $db_currency[$i]['vName'] ?>" <? if ($db_user[0]['vCurrencyDriver'] == $db_currency[$i]['vName']) { ?>selected<? } ?>><?= $db_currency[$i]['vName'] ?></option>
                                        <? } ?>
                                    </select>
                                    <div class="required-label" id="vCurrencyDriverCheck"></div>
                                </div>
                            <?php } } ?>	 

                            <?php if ($sessionUserType == 'driver' && ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') && ONLYDELIVERALL != 'Yes' && $ufxEnable == 'Yes' && $showServiceDesc > 0) { ?>
                                <div class="form-group full has-textarea">
                                    <label><?= $langage_lbl['LBL_PROFILE_DESCRIPTION']; ?></label>
                                    <textarea name="tProfileDescription" rows="8" cols="40" class="form-control" id="tProfileDescription"><?= $db_user[0]['tProfileDescription'] ?></textarea>
                                </div>
                            <?php } ?>	   			                                    

                        </div>	                           			

                        <div class="button-block">
                            <div class="btn-hold">
                                <input name="save" id="validate_submit" type="submit" value="<?= $langage_lbl['LBL_Save']; ?>"> 
                            </div>
                            <div class="btn-hold">
                                <input name="" id="hide-edit-profile-div" type="button" value="<?= $langage_lbl['LBL_BTN_PROFILE_CANCEL_TRIP_TXT']; ?>" class="gen-btn cancel_btn">
                            </div>
                        </div>

                    </form>        
                </div>
            </section>   


            <section class="profile-earning">
                <div class="profile-earning-inner">
                    <ul>

                        <!-- Document Start -->

                        <?php if ($count_all_doc != 0) { ?>

                               <!--  <h3><?= $langage_lbl['LBL_REQUIRED_DOCS']; ?></h3> -->


                            <?php
                            for ($i = 0; $i < $count_all_doc; $i++) {
                                if ($db_userdoc[$i]['eType'] == 'UberX' && $ufxEnable == 'Yes') {
                                    $etypeName = 'Service';
                                } else {
                                    $etypeName = $db_userdoc[$i]['eType'];
                                }
                                ?>
                                <li> 
                                    <div class="upload-block">
                                        <input  type="hidden" id="ex_status" value="<?php echo $db_userdoc[$i]['ex_status']; ?>">
                                        <strong>
                                            <?php echo $db_userdoc[$i]['d_name']; ?>
                                            <?php if (($APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') && $sessionUserType == 'driver') { ?>
                                            <?php } ?>
                                        </strong>
                                        <input type="hidden" id="doc_id" value="<?php $db_userdoc[$i]['doc_file']; ?>">
                                        <div class="doc-image-block">

                                            <?php if ($db_userdoc[$i]['doc_file'] != '') { ?>
                                                <?php
                                                $file_ext = $generalobj->file_ext($db_userdoc[$i]['doc_file']);
                                                if ($file_ext == 'is_image') {
                                                    if ($sessionUserType == 'company') {
                                                        $path = $tconfig["tsite_upload_compnay_doc"];
                                                    } else {
                                                        $path = $tconfig["tsite_upload_driver_doc"];
                                                    }
                                                    ?>
                                                    <a href="<?= $path . '/' . $sessionUserId . '/' . $db_userdoc[$i]['doc_file'] ?>" target="_blank"><img src = "<?= $path . '/' . $sessionUserId . '/' . $db_userdoc[$i]['doc_file'] ?>" style="cursor:pointer;" alt ="<?= $db_userdoc[$i]['d_name']; ?> Image" /></a>

                                                    <?php
                                                } else {
                                                    if ($sessionUserType == 'company') {
                                                        $tconfig_new = $tconfig["tsite_upload_compnay_doc"];
                                                    } else {
                                                        $tconfig_new = $tconfig["tsite_upload_driver_doc"];
                                                    }
                                                    ?>
                                                    <p><a href="<?= $tconfig_new . '/' . $sessionUserId . '/' . $db_userdoc[$i]['doc_file'] ?>" target="_blank"><?php echo $db_userdoc[$i]['d_name']; ?></a></p>

                                                <?php } ?>
                                                <?php
                                            } else {

                                                echo '<p>' . $db_userdoc[$i]['d_name'] . " " . $langage_lbl['LBL_NOT_FOUND'] . '</p>';
                                            }
                                            ?>

                                            <br/>
                                            <b></b>
                                        </div>
                                        <div class="button-block">
                                            <button class="btn gen-btn" data-toggle="modal" data-target="#uiModal" id="custId"  onClick="setModel001('<?php echo $db_userdoc[$i]['masterid']; ?>')" >
                                                <?php
                                                if ($db_userdoc[$i]['doc_file'] != '') {
                                                    echo $db_userdoc[$i]['d_name'];
                                                } else {
                                                    echo $db_userdoc[$i]['d_name'];
                                                }
                                                ?>
                                            </button>
                                        </div>
                                    </div>
                                </li>
                            <?php } ?>
                        <?php } ?>
                        <div class="col-lg-12">
                            <div class="custom-modal-main in  fade" id="uiModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                <div class="custom-modal">
                                    <div class="modal-content image-upload-1">
                                        <div class="fetched-data"></div>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </ul>
                </div>
            </section>     
            <div class="page-contant">
                <div class="page-contant-inner">
                    <!-- profile page -->
                    <div class="driver-profile-page">
                        <!-- bank detail -->
                        <?php if ($sessionUserType == 'driver') { ?>
                            <?php }
                        if ($APP_TYPE == 'UberX' && $ufxEnable == 'Yes') {
                            $class_name = 'driver-profile-bottom-part required-documents-bottom-part two-part-document';
                        } else {
                            $class_name = 'driver-profile-bottom-part required-documents-bottom-part';
                        }
                        ?>

                        <? if ($sessionUserType == "driver" && $SITE_VERSION == "v5") { ?>
                            <div class="<?php echo $class_name; ?>">
                                <h3><?= $langage_lbl['LBL_PREFERENCES_TEXT'] ?> </h3>
                                <p>
                                <div class="driver-profile-info-aa col-md-5"> <? foreach ($data_driver_pref as $val) { ?>
                                        <img data-toggle="tooltip" class="borderClass-aa border_class-bb" title="<?= $val['pref_Title'] ?>" src="<?= $tconfig["tsite_upload_preference_image_panel"] . $val['pref_Image'] ?>">
                                    <? } ?>
                                </div>

                                <span class="col-md-5">
                                    <a href="preferences.php" id="show-edit-language-div" class="hide-language">
                                        <i class="fa fa-pencil" aria-hidden="true"></i>
                                        <?= $langage_lbl['LBL_PROFILE_EDIT'] ?></a>
                                </span>
                                </p>
                            </div>
                        <? } ?>
                    </div>
                    <div style="clear:both;"></div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="custom-modal-main in " id="uiModal_4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="custom-modal">
                        <div class="model-header"><h4><?= $langage_lbl['LBL_PROFILE_PICTURE']; ?></h4></div>
                        <div class="upload-content">
                            <form class="form-horizontal frm9" id="frm9" method="post" enctype="multipart/form-data" action="upload_doc.php" name="frm9">
                                <input type="hidden" name="action" value ="photo"/>
                                <input type="hidden" name="img_path" value ="
                                <?php
                                if ($sessionUserType == 'company') {
                                    echo $tconfig["tsite_upload_images_compnay_path"];
                                } else if ($sessionUserType == 'driver') {
                                    echo $tconfig["tsite_upload_images_driver_path"];
                                }
                                ?>"/>
                                <input type="hidden" name="currPage" value="driver-profile">
                                <div class="form-group">
                                    <div class="col-lg-12">
                                        <div class="model-body fileupload fileupload-new" data-provides="fileupload">
                                            <div class="fileupload-preview thumbnail" id="fileupload-preview">
                                                <?php
                                                if ($sessionUserType == 'company') {
                                                    $img_path = $tconfig["tsite_upload_images_compnay"];
                                                    $profileImgpath = $tconfig["tsite_upload_images_compnay_path"];
                                                } else if ($sessionUserType == 'driver') {
                                                    $img_path = $tconfig["tsite_upload_images_driver"];
                                                    $profileImgpath = $tconfig["tsite_upload_images_driver_path"];
                                                }
                                                if (($db_user[0]['vImage'] == 'NONE' || $db_user[0]['vImage'] == '') || !file_exists($profileImgpath . '/' . $sessionUserId . '/2_' . $db_user[0]['vImage'])) {
                                                    ?>
                                                    <img class="imagename" src="assets/img/profile-user-img.png" alt="">
                                                <? } else { ?>
                                                    <img class="imagename" src = "<?= $img_path . '/' . $sessionUserId . '/2_' . $db_user[0]['vImage'] ?>" style="height:150px;"/>
                                                <?php } ?>
                                            </div>
                                            <div>
                                                <span class="btn btn-file btn-success gen-btn"><span class="fileupload-new"><?= $langage_lbl['LBL_UPLOAD_PHOTO']; ?></span>
                                                    <span class="fileupload-exists"><?= $langage_lbl['LBL_CHANGE']; ?></span>
                                                    <input type="file" class="gen-btn" name="photo"/></span>
                                                <input type="hidden" name="photo_hidden"  id="photo" value="<?php echo ($db_data[0]['vImage'] != "") ? $db_data[0]['vImage'] : ''; ?>" />
                                                <a href="#" class="gen-btn fileupload-exists" data-dismiss="fileupload" onclick="change_img('<?= $img_path . '/' . $sessionUserId . '/2_' . $db_data[0]['vImage']; ?>')">Close</a>
                                            </div>
                                            <div class="upload-error"><span class="file_error"></span></div>
                                        </div>

                                        <div class="model-footer">
                                            <div class="button-block">
                                                <input type="submit" class="gen-btn" name="save" value="<?= $langage_lbl['LBL_Save']; ?>">
                                                <input type="button" class="gen-btn" data-dismiss="modal" name="cancel" value="<?= $langage_lbl['LBL_BTN_PROFILE_CANCEL_TRIP_TXT']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div style="clear:both;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="custom-modal-main in" id="formModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="custom-modal">
                        <div class="modal-content modal-content-profile">

                            <div class="model-header">
                                <h4><?= $langage_lbl['LBL_NOTE_FOR_DEMO']; ?></h4>
                                <i class="icon-close" data-dismiss="modal"></i>
                            </div>

                            <div class="model-body">
                                <form role="form" name="verification" id="verification">
                                    <? if ($sessionUserType == 'driver') { ?>
                                        <p><?= $langage_lbl['LBL_PROFILE_WE_SEE_YOU_HAVE_REGISTERED_AS_A_DRIVER']; ?></p>
                                        <p><?= $langage_lbl['LBL_SINCE_IT_IS_DEMO_VERSION_ADDVEHICLE']; ?></p>

                                        <p><?= $langage_lbl['LBL_HOWEVER_IN_REAL_SYSTEM_DRIVER']; ?></p>
                                    <? } else { ?>
                                        <p><?= $langage_lbl['LBL_WE_SEE_YOU_HAVE_REGISTERED_AS_A_COMPANY']; ?></p>
                                        <p><?= $langage_lbl['LBL_SINCE_IT_IS_DEMO_VERSION']; ?></p>

                                        <p><?= $langage_lbl['LBL_STEP1']; ?></p>
                                        <!--p><? //= $langage_lbl['LBL_STEP2'];      ?></p-->
                                        <p><?= $langage_lbl['LBL_STEP3']; ?></p>

                                        <p><?= $langage_lbl['LBL_HOWEVER_IN_REAL_SYSTEM']; ?></p>
                                    <? } ?>
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

<!-- <link rel="stylesheet" type="text/css" media="screen" href="<?php echo $tconfig["tsite_url_main_admin"] ?>css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css"> -->
        <script type="text/javascript" src="<?php echo $tconfig["tsite_url_main_admin"] ?>js/moment.min.js"></script>
        <script type="text/javascript" src="<?php echo $tconfig["tsite_url_main_admin"] ?>js/bootstrap-datetimepicker.min.js"></script>
        <script type="text/javascript" src="<?php echo $tconfig["tsite_url_main_admin"] ?>js/validation/jquery.validate.min.js" ></script>


        <script src="assets/js/jquery-ui.min.js"></script>

        <?php if ($lang != 'en') { ?>
                    <!-- <script type="text/javascript" src="assets/js/validation/localization/messages_<?= $lang; ?>.js" ></script> -->
            <? include_once('otherlang_validation.php'); ?>
        <?php } ?>
        <script type="text/javascript" src="assets/js/validation/additional-methods.js" ></script>
        <!-- End: Footer Script -->
        <script type="text/javascript">

                                                    function checkProviderRestrictProfile()
                                                    {
                                                        $.ajax({
                                                            type: 'post',
                                                            url: 'ajax_restric_provider_profile.php',
                                                            data: '',
                                                            success: function (data) {
                                                                if (data == '0') {
                                                                    alert('<?= addslashes($langage_lbl['LBL_EDIT_PROFILE_DISABLED']); ?>');
                                                                    //window.location = "profile.php";
                                                                    return false;
                                                                } else {
                                                                    $("#uiModal_4").addClass('active');
                                                                    //$('#uiModal_4').modal('show');
                                                                    //$('#modal').modal();
                                                                    /* return true;*/
                                                                }
                                                            }
                                                        });
                                                    }

                                                    function nospaces(t) {
                                                        if (t.value.match(/\s/g)) {
                                                            alert('Password should not contain whitespace.');
                                                            //t.value=t.value.replace(/\s/g,'');
                                                            t.value = '';
                                                        }
                                                    }
                                                    function isNumberKey(evt) {
                                                        var charCode = (evt.which) ? evt.which : evt.keyCode
                                                        if (charCode > 31 && (charCode < 35 || charCode > 57)) {
                                                            return false;
                                                        } else {
                                                            return true;
                                                        }
                                                    }
                                                    function change_img(action)
                                                    {
                                                        $('#fileupload-preview').html('<img src="' + action + '" />');
                                                        $(".imagename").fadeIn();
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
                                                            var selectedOption1 = $('.custom-select-new.vCurrencyDriver').find(":selected").text();
                                                            if (selectedOption != "" || selectedOption1 != "") {
                                                                $('.custom-select-new.vCountry').next(".holder").text(selectedOption);
                                                                $('.custom-select-new.vCurrencyDriver').next(".holder").text(selectedOption1);
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
                                                        var id = '<?php echo $sessionUserId; ?>';
                                                        var user = '<?= $sessionUserType; ?>';

                                                        $.ajax({
                                                            type: 'post',
                                                            url: 'cx-driver_profile_document_fetch1.php', //Here you will fetch records
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
                                                            err += "<?php echo addslashes($langage_lbl['LBL_NEW_PASS_MSG']) ?><br/>";
                                                        }
                                                        if (npass.length < 6) {
                                                            err += "<?php echo addslashes($langage_lbl['LBL_PASS_LENGTH_MSG']) ?><br/>";
                                                        }
                                                        if (ncpass == '') {
                                                            err += "<?php echo addslashes($langage_lbl['LBL_REPASS_MSG']) ?><br/>";
                                                        }

                                                        if (err == "") {

                                                            if (npass != ncpass)
                                                                err += "<?php echo addslashes($langage_lbl['LBL_PASS_NOT_MATCH']) ?><br/>";
                                                        }
                                                        if (err == "")
                                                        {
                                                            //editProfile('pass');
                                                            //return false;
                                                        } else {
                                                            if ($('#npass').val() != "") {
                                                                bootbox.dialog({
                                                                    title: "&nbsp;",
                                                                    message: "<h3>" + err + "</h3>",
                                                                    buttons: {
                                                                        danger: {
                                                                            label: "Ok",
                                                                            className: "btn-danger",
                                                                        },
                                                                    }
                                                                });
                                                            }
                                                            $('#npass,#ncpass').val('');
                                                            /*bootbox.dialog({
                                                             message: "<h3>"+err+"</h3>",
                                                             buttons: {
                                                             danger: {
                                                             label: "Ok",
                                                             className: "btn-danger",
                                                             },
                                                             }
                                                             });*/
                                                            //document.getElementById("err_password").innerHTML = '<div class="alert alert-danger">' + err + '</div>';
                                                            return false;
                                                        }
                                                    }


                                                    function validate_password() {
                                                        var cpass = document.getElementById('cpass').value;
                                                        var npass = document.getElementById('npass').value;
                                                        var ncpass = document.getElementById('ncpass').value;
                                                        var err = '';

                                                        /*if(ncpass == '' && npass == ''){
                                                         
                                                         editProfile('allInOne');
                                                         return true;
                                                         
                                                         }else{
                                                         
                                                         if (cpass == '') {
                                                         err += "<?= addslashes($langage_lbl['LBL_CURRENT_PASS_MSG']); ?><br />";
                                                         }
                                                         if (npass == '') {
                                                         err += "<?= addslashes($langage_lbl['LBL_NEW_PASS_MSG']); ?><br />";
                                                         }*/
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
                                                            /*$.ajax({
                                                             type: "POST",
                                                             url: 'ajax_check_password_a.php',
                                                             data: {cpass: cpass},
                                                             success: function (dataHtml)
                                                             {
                                                             if (dataHtml.trim() == 1) {
                                                             editProfile('allInOne');
                                                             return false;
                                                             } else {
                                                             err += "<?= addslashes($langage_lbl['LBL_INCCORECT_CURRENT_PASS_ERROR_MSG']); ?><BR>";
                                                             $('#cpass').val('');
                                                             $('#npass').val('');
                                                             $('#ncpass').val('');
                                                             bootbox.dialog({
                                                             title: "&nbsp;",
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
                                                             }); */
                                                        } else {
                                                            if ($('#npass').val() != "") {
                                                                bootbox.dialog({
                                                                    title: "&nbsp;",
                                                                    message: "<h3>" + err + "</h3>",
                                                                    buttons: {
                                                                        danger: {
                                                                            label: "Ok",
                                                                            className: "btn-danger",
                                                                        },
                                                                    }
                                                                });
                                                            }
                                                            $('#cpass,#npass,#ncpass').val('');
                                                            return false;
                                                        }
                                                        // }

                                                    }
                                                    //function editPro(action)
                                                    //{	
                                                    //    editProfile(action);
                                                    //    return false;
                                                    //}

                                                    function editProfile(action)
                                                    {


                                                        var chk = '<?php echo SITE_TYPE; ?>';

                                                        if (action == 'allInOne')
                                                        {

                                                            data = $("#frm1").serialize();
                                                        }

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
                                                            url: 'profile_action.php',
                                                            data: data,
                                                            success: function (data)
                                                            {

                                                                if (data == '0' || data == 0) {
                                                                    err = "<?php echo addslashes($langage_lbl['LBL_INCCORECT_CURRENT_PASS_ERROR_MSG']) ?>";
                                                                    bootbox.dialog({
                                                                        message: "<h3>" + err + "</h3>",
                                                                        buttons: {
                                                                            danger: {
                                                                                label: "Ok",
                                                                                className: "btn-danger",
                                                                            },
                                                                        }
                                                                    });
                                                                    $('#npass').val('');
                                                                    $('#ncpass').val('');
                                                                    $('#cpass').val('');
                                                                    return false;
                                                                } else if (data == '2' || data == '3' || data == 2 || data == 3) {
                                                                    alert("profile?success=2&var_msg=" + data);
                                                                    return false;
                                                                    window.location = "profile?success=2&var_msg=" + data;
                                                                    return false;
                                                                } else {
                                                                    window.location = 'profile?success=1&var_msg=' + data;
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
                                                                document.getElementById("code").value = data;
                                                                document.getElementById("vCountry").value = id;

                                                                setState(id, '<?php echo $db_user[0]['vState']; ?>');
                                                                setCity('<?php echo $db_user[0]['vState']; ?>', '<?php echo $db_user[0]['vCity']; ?>');

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
            var dataa = {};
            if (user == 'company') {
                dataa.iCompanyId = "<?= $sessionUserId; ?>";
                dataa.usertype = user;
                dataa.vCountry = $('#country option:selected').val();
            } else {
                dataa.iDriverId = "<?= $sessionUserId; ?>";
                dataa.usertype = user;
                dataa.vCountry = $('#country option:selected').val();
            }
            var errormessage;
            $('#frm1').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block error',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    /*if (element.attr("name") == "vCurrencyDriver")
                     error.appendTo('#vCurrencyDriverCheck');
                     else if (element.attr("name") == "vCountry")
                     error.appendTo('#vCountryCheck');
                     else
                     error.insertAfter(element);*/
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
                    phone: {required: true, minlength: 3, digits: true,
                        remote: {
                            url: 'ajax_driver_mobile_new.php',
                            type: "post",
                            data: dataa,
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
                            async: false
                        }
                    },
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
                    phone: {
//minlength: 'Please enter at least three Number.', 
//digits: 'Please enter proper mobile number.', 
                        remote: function () {
                            return errormessage;
                        }}
                },
                submitHandler: function () {
                    // $("#vCountry").prop('disabled',false);
                    $("#country").prop('disabled',false);
                    if ($("#frm1").valid()) {
                        editProfile('allInOne');
                    }
                }
            });


//var from = document.getElementById('vWorkLocation');
//autocomplete_from1 = new google.maps.places.Autocomplete(from);
            /*		google.maps.event.addListener(autocomplete_from1, 'place_changed', function() {
             var placeaddress = autocomplete_from1.getPlace();
             
             $('#vWorkLocationLatitude').val(placeaddress.geometry.location.lat());
             $('#vWorkLocationLongitude').val(placeaddress.geometry.location.lng());
             
             });  */


            $(document).on('click', '.profile_edit_btn', function () {
                $('.profile_edit').addClass('active');
//general_label(); // Commented By HJ On 10-01-2020 As Per Discuss With SP Bcoz Function Not Found
            })
            $(document).on('click', '.cancel_btn', function () {
                $('.profile_edit').removeClass('active');
            })

        </script>
    </body>
</html>
