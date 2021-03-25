<?php
include_once('../common.php');
require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$ksuccess = isset($_REQUEST['ksuccess']) ? $_REQUEST['ksuccess'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';

$tbl_name = 'administrators';
$script = 'Admin';

$sql = "SELECT vCountryCode,vCountry FROM country WHERE eStatus='Active' ORDER BY vCountry ASC";
$db_country = $obj->MySQLSelect($sql);

if (empty($SHOW_CITY_FIELD)) {                                                                                                         
    $SHOW_CITY_FIELD = $generalobj->getConfigurations("configurations", "SHOW_CITY_FIELD");
}
$hotelPanel = isHotelPanelEnable(); 
$kioskPanel = isKioskPanelEnable();
if ($hotelPanel > 0 || $kioskPanel > 0) {
    $sql1 = "SELECT iGroupId,vGroup FROM admin_groups WHERE eStatus = 'Active'";
} else {
    $sql1 = "SELECT iGroupId,vGroup FROM admin_groups WHERE eStatus = 'Active' AND iGroupId != '4'";
}
$db_group = $obj->MySQLSelect($sql1);

// set all variables with either post (when submit) either blank (when insert)
$vFirstName = isset($_POST['vFirstName']) ? $_POST['vFirstName'] : '';
$vLastName = isset($_POST['vLastName']) ? $_POST['vLastName'] : '';
$vEmail = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
$vPassword = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : '';
$iGroupId = isset($_POST['iGroupId']) ? $_POST['iGroupId'] : '';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$vPass = ($vPassword != "") ? $generalobj->encrypt_bycrypt($vPassword) : '';

$fHotelServiceCharge = isset($_POST['fHotelServiceCharge']) ? $_POST['fHotelServiceCharge'] : '';
$vPaymentEmail = isset($_POST['vPaymentEmail']) ? $_POST['vPaymentEmail'] : '';
$vBankAccountHolderName = isset($_POST['vBankAccountHolderName']) ? $_POST['vBankAccountHolderName'] : '';
$vAccountNumber = isset($_POST['vAccountNumber']) ? $_POST['vAccountNumber'] : '';
$vBankName = isset($_POST['vBankName']) ? $_POST['vBankName'] : '';
$vBankLocation = isset($_POST['vBankLocation']) ? $_POST['vBankLocation'] : '';
$vBIC_SWIFT_Code = isset($_POST['vBIC_SWIFT_Code']) ? $_POST['vBIC_SWIFT_Code'] : '';

$vCode = isset($_POST['vCode']) ? $_POST['vCode'] : '';
$vContactNo = isset($_POST['vContactNo']) ? $_POST['vContactNo'] : '';
$vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : $DEFAULT_COUNTRY_CODE_WEB;
$vState = isset($_POST['vState']) ? $_POST['vState'] : '';
$vCity = isset($_POST['vCity']) ? $_POST['vCity'] : '';
$vAddress = isset($_POST['vAddress']) ? $_POST['vAddress'] : '';
$vAddressLat = isset($_POST['vAddressLat']) ? $_POST['vAddressLat'] : '';
$vAddressLong = isset($_POST['vAddressLong']) ? $_POST['vAddressLong'] : '';

$vPickupFrom = isset($_POST['vPickupFrom']) ? $_POST['vPickupFrom'] : '';

if (isset($_POST['submit'])) {
    //print_r($_POST);die;
    if (!$userObj->hasRole(1) && ($action == "Add" && !$userObj->hasPermission('create-admin'))) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create admin.';
        header("Location:admin.php");
        exit;
    }
    if (!$userObj->hasRole(1) && ($action == "Edit" && !$userObj->hasPermission('edit-admin'))) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update admin.';
        header("Location:admin.php");
        exit;
    }
    if ($id != "" && SITE_TYPE == 'Demo') {
// header("Location:admin_action.php?id=" . $id . '&success=2');
        $_SESSION['success'] = '2';
        header("location:" . $backlink);
        exit;
    }
//Add Custom validation
    require_once("library/validation.class.php");
    $validobj = new validation();
    $validobj->add_fields($_POST['vFirstName'], 'req', 'First Name is required');
    if ($iGroupId != 4) {
        $validobj->add_fields($_POST['vLastName'], 'req', 'Last Name is required');
    }
    $validobj->add_fields($_POST['vEmail'], 'req', 'Email Address is required.');
    $validobj->add_fields($_POST['vEmail'], 'email', 'Please enter valid Email Address.');
    if ($action == "Add") {
        $validobj->add_fields($_POST['vPassword'], 'req', 'Password is required.');
    }
//$validobj->add_fields($_POST['vPhone'], 'req', 'Phone Number is required.');
    if ($_SESSION['sess_iGroupId'] == 1) {
        $validobj->add_fields($_POST['iGroupId'], 'req', 'Group is required.');
    }
    $error = $validobj->validate();
//Other Validations
    if (isset($_POST['iGroupId']) && $_POST['iGroupId'] == 4) {
        //echo $vEmail."==".$vCountry."===".$id;die;
        $eSystem = "";
        $checEmailExist = $generalobj->checkMemberDataInfo($vEmail, "", 'ADMIN', $vCountry, $id,$eSystem);
        //print_r($checEmailExist);die;
        if ($checEmailExist['status'] == 0) {
            $error .= '* Email Address is already exists.<br>';
        } else if ($checEmailExist['status'] == 2) {
            $error .= $langage_lbl['LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT'];
        }
    } else {
        if ($vEmail != "") {
            if ($id != "") {
                $msg1 = $generalobj->checkDuplicateAdminNew('iAdminId', 'administrators', Array('vEmail'), $id, "");
            } else {
                $msg1 = $generalobj->checkDuplicateAdminNew('vEmail', 'administrators', Array('vEmail'), "", "");
            }

            if ($msg1 == 1) {
                $error .= '* Email Address is already exists.<br>';
            }
        }
    }
    if ($error) {
        $success = 3;
        $newError = $error;
//exit;
    } else {
        $passPara = '';
        if ($vPass != "") {
            $passPara = "`vPassword` = '" . $vPass . "',";
        }
        $groupSave = "";
        if ($_SESSION['sess_iGroupId'] == 1) {
            $groupSave = "`iGroupId` = '" . $iGroupId . "'";
        } else {
            $groupSave = "`iGroupId` = '" . $userObj->role_id . "'";
        }

        $q = "INSERT INTO ";
        $where = '';
        if ($action == 'Edit') {
            $str = ", eStatus = 'Inactive' ";
        } else {
            $str = '';
        }
        if ($id != '') {
            $q = "UPDATE ";
            $where = " WHERE `iAdminId` = '" . $id . "'";
        }

        $query = $q . " `" . $tbl_name . "` SET
			`vFirstName` = '" . $vFirstName . "',
			`vLastName` = '" . $vLastName . "',
			`vEmail` = '" . $vEmail . "',
            `fHotelServiceCharge`= '" . $fHotelServiceCharge . "',
            `vCode` = '" . $vCode . "',
            `vContactNo` = '" . $vContactNo . "',
            `vCountry` = '" . $vCountry . "',
            `vState` = '" . $vState . "',
            `vCity` = '" . $vCity . "',
            `vAddress` = '" . $vAddress . "',
            `vAddressLat` = '" . $vAddressLat . "',
            `vAddressLong` = '" . $vAddressLong . "',
            `vPaymentEmail`= '" . $vPaymentEmail . "',
            `vBankAccountHolderName`= '" . $vBankAccountHolderName . "',
            `vAccountNumber`= '" . $vAccountNumber . "',
            `vBankName`= '" . $vBankName . "',
            `vBankLocation`= '" . $vBankLocation . "',
            `vBIC_SWIFT_Code`= '" . $vBIC_SWIFT_Code . "',
			$passPara
			$groupSave
			 " . $where;
        $obj->sql_query($query);
        $id = ($id != '') ? $id : $obj->GetInsertId();

// new add
        if ($iGroupId == 4) {
            $hsql = "SELECT * FROM hotel WHERE iAdminId = '" . $id . "'";
            $htotalData = $obj->MySQLSelect($hsql);

            if (count($htotalData) == 0) {
                $q = "INSERT INTO ";
                $where = '';
            }

            $subquery = $q . " `hotel` 
            SET `iAdminId` = '" . $id . "',
            vLang ='" . $_SESSION['sess_lang'] . "',
            vPickupFrom = '" . $vPickupFrom . "',
            vCurrencyPassenger ='" . $_SESSION['sess_currency'] . "'
             " . $where;
            $obj->sql_query($subquery);
        }


        $sql1 = "SELECT iHotelId FROM  `hotel` WHERE `iAdminId` = '" . $id . "'";
        $db_hoteldata = $obj->MySQLSelect($sql1);
        $hotelid = $db_hoteldata[0]['iHotelId'];
        if ($_FILES['vImgName']['name'] != '') {

            $img_path = $tconfig["tsite_upload_images_hotel_passenger_path"];
            $temp_gallery = $img_path . '/';
            $image_object = $_FILES['vImgName']['tmp_name'];
            $image_name = $_FILES['vImgName']['name'];
            $check_file = $img_path . '/' . $hotelid . '/' . $oldImage;
            if ($oldImage != '' && file_exists($check_file)) {
                @unlink($img_path . '/' . $hotelid . '/' . $oldImage);
                @unlink($img_path . '/' . $hotelid . '/1_' . $oldImage);
                @unlink($img_path . '/' . $hotelid . '/2_' . $oldImage);
                @unlink($img_path . '/' . $hotelid . '/3_' . $oldImage);
                @unlink($img_path . '/' . $hotelid . '/4_' . $oldImage);
            }

            $Photo_Gallery_folder = $img_path . '/' . $hotelid . '/';
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
            }
            $img1 = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, $tconfig["tsite_upload_images_hotel_passenger_size1"], $tconfig["tsite_upload_images_hotel_passenger_size2"], $tconfig["tsite_upload_images_hotel_passenger_size3"], $tconfig["tsite_upload_images_hotel_passenger_size4"], '', '', 'Y', '', $Photo_Gallery_folder);

            $vImgName = $img1;
            $sql1 = "UPDATE hotel SET `vImgName` = '" . $vImgName . "' WHERE `iAdminId` = '" . $id . "'";
            $obj->sql_query($sql1);
        }

        if ($_FILES['vVehicleTypeImg']['name'] != '') {
            $img_path = $tconfig["tsite_upload_images_hotel_passenger_path"];
            $temp_gallery = $img_path . '/';
            $image_object = $_FILES['vVehicleTypeImg']['tmp_name'];
            $image_name = $_FILES['vVehicleTypeImg']['name'];

            $filecheck = basename($_FILES['vVehicleTypeImg']['name']);
            $fileextarr = explode(".", $filecheck);
            $ext = strtolower($fileextarr[count($fileextarr) - 1]);
            $flag_error = 0;
            if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
                $flag_error = 1;
                $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png";
            }

            $dataimg = getimagesize($_FILES['vVehicleTypeImg']['tmp_name']);
            $imgwidth = $dataimg[0];
            $imgheight = $dataimg[1];
            if ($imgwidth < 1024) {
                echo"<script>alert('Your Image upload size is less than recommended. Image will look stretched.');</script>";
            }

            /* if ($flag_error == 1) {
              if ($action == "Add") {
              header("Location:vehicle_category_action.php?id=" . $id . "&var_msg=" . $var_msg . "&goback=1");
              exit;
              } else {
              header("Location:vehicle_category_action.php?id=" . $id . "&var_msg=" . $var_msg . "&goback=1");
              exit;
              }
              } */

            $check_file = $img_path . '/' . $hotelid . '/' . $oldImage;
            if ($oldImage != '' && file_exists($check_file)) {
                @unlink($img_path . '/' . $hotelid . '/' . $oldImage);
                @unlink($img_path . '/' . $hotelid . '/1_' . $oldImage);
                @unlink($img_path . '/' . $hotelid . '/2_' . $oldImage);
                @unlink($img_path . '/' . $hotelid . '/3_' . $oldImage);
                @unlink($img_path . '/' . $hotelid . '/4_' . $oldImage);
            }



            if ($flag_error == 1) {
                $_SESSION['success'] = '3';
                $_SESSION['var_msg'] = $var_msg;
                header("Location:admin.php");
            } else {

                $Photo_Gallery_folder = $img_path . '/' . $hotelid . '/';
                if (!is_dir($Photo_Gallery_folder)) {
                    mkdir($Photo_Gallery_folder, 0777);
                }
                $img1 = $generalobj->fileupload($Photo_Gallery_folder, $image_object, $image_name, '', 'jpg,png,gif,jpeg');
                $vVehicleTypeImg = $img1[0];

                $sql1 = "UPDATE hotel SET `vVehicleTypeImg` = '" . $vVehicleTypeImg . "' WHERE `iAdminId` = '" . $id . "'";
                $obj->sql_query($sql1);
            }
        }

        $locations_ids = isset($_POST['locations_ids']) ? $_POST['locations_ids'] : [];
        $user = Models\Administrator::find($id);
        if ($user) {
            $user->locations()->sync($locations_ids);
        }

        if ($action == "Add") {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
        } else {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        }
        header("location:" . $backlink);
        exit;
    }
}
// for Edit

if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iAdminId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);

    $sql1 = "SELECT * FROM hotel WHERE iAdminId = '" . $id . "'";
    $db_hoteldata = $obj->MySQLSelect($sql1);

// $vPass = $generalobj->decrypt($db_data[0]['vPassword']);
    $vLabel = $id;
    if (count($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $vFirstName = $value['vFirstName'];
            $vLastName = $generalobjAdmin->clearName(" " . $value['vLastName']);
            $vEmail = $generalobjAdmin->clearEmail($value['vEmail']);
// $vUserName = $value['vUserName'];
            $vPassword = $value['vPassword'];
            $iGroupId = $value['iGroupId'];
            $hotel_booking_service_charge = $value['fHotelServiceCharge'];
            $vPaymentEmail = $value['vPaymentEmail'];
            $vBankAccountHolderName = $value['vBankAccountHolderName'];
            $vAccountNumber = $value['vAccountNumber'];
            $vBankName = $value['vBankName'];
            $vBankLocation = $value['vBankLocation'];
            $vBIC_SWIFT_Code = $value['vBIC_SWIFT_Code'];

            $vCode = $value['vCode'];
            $vContactNo = isset($value['vContactNo']) ? $value['vContactNo'] : '';
            $vCountry = isset($value['vCountry']) ? $value['vCountry'] : '';
            $vState = isset($value['vState']) ? $value['vState'] : '';
            $vCity = isset($value['vCity']) ? $value['vCity'] : '';
            $vAddress = isset($value['vAddress']) ? $value['vAddress'] : '';
            $vAddressLat = isset($value['vAddressLat']) ? $value['vAddressLat'] : '';
            $vAddressLong = isset($value['vAddressLong']) ? $value['vAddressLong'] : '';

            $vImgName = $db_hoteldata[0]['vImgName'];
            $vVehicleTypeImg = $db_hoteldata[0]['vVehicleTypeImg'];
            $vPickupFrom = $db_hoteldata[0]['vPickupFrom'];
            $hotelid = $db_hoteldata[0]['iHotelId'];
        }
    }
}


$locations = Models\LocationMaster::adminLocations()->get()->pluck('vLocationName', 'iLocationId')->toArray();
$selected_locations = Models\Administrator::find($id)->locations;


if ($selected_locations && $selected_locations->count() > 0) {
    $selected_location_ids = $selected_locations->pluck(['iLocationId'])->toArray();
}
?>

<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | Admin <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <?php include_once('global_files.php'); ?>
        <script src="//maps.google.com/maps/api/js?sensor=true&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>&libraries=places" type="text/javascript"></script>
        <script type='text/javascript' src='../assets/map/gmaps.js'></script>
        <!-- On OFF switch -->
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
        <link rel="stylesheet" href="css/select2/select2.min.css"></link>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53">

        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?php
            include_once('header.php');
            include_once('left_menu.php');
            ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2><?= $action; ?> Admin <?= $vFirstName; ?></h2>
                            <a class="back_link" href="company.php">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />

                    <div class="body-div">
                        <div class="form-group">
                            <?php if ($success == 2) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                    <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                </div><br/>
                            <?php } ?>
                            <?php if ($success == 3) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                    <?php print_r($error); ?>
                                </div><br/>
                            <?php } ?>
                            <form name="_admin_form" id="_admin_form" method="post" action="" enctype="multipart/form-data">
                                <input type="hidden" name="actionOf" id="actionOf" value="<?php echo $action; ?>"/>
                                <input type="hidden" name="id" id="iAdminId" value="<?php echo $id; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="admin.php"/>
                                <?php if ($_SESSION['sess_iGroupId'] != 1) { ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Group<span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-control disabled"><?php
                                                for ($i = 0; $i < count($db_group); $i++) {
                                                    if ($userObj->hasRole($db_group[$i]['iGroupId'])) {
                                                        echo $db_group[$i]['vGroup'];
                                                        break;
                                                    }
                                                }
                                                ?></div>
                                        </div>
                                    </div>
                                <?php } ?>
                                <?php if ($_SESSION['sess_iGroupId'] == 1) { ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Group<span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <select  class="form-control" name="iGroupId" id="iGroupId">
                                                <option value="">--select--</option>
                                                <?php for ($i = 0; $i < count($db_group); $i++) { ?>
                                                    <option value = "<?= $db_group[$i]['iGroupId'] ?>" <?= ($db_group[$i]['iGroupId'] == $iGroupId) ? 'selected' : ''; ?>><?= $db_group[$i]['vGroup'] ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                <?php } ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label class="admin">First Name<span class="red"> *</span></label>
                                        <label class="hoteladmin" style="display: none;">Hotel Name<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vFirstName"  id="vName" value="<?= $vFirstName; ?>" placeholder="First Name">
                                    </div>
                                </div>
                                <?php if ($iGroupId != 4) { ?>
                                    <div class="row vLastName">
                                        <div class="col-lg-12">
                                            <label>Last Name<span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="text" class="form-control" name="vLastName"  id="vLastName" value="<?= $vLastName; ?>" placeholder="Last Name">
                                        </div>
                                    </div>
                                <?php } ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Email<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vEmail" id="vEmail" value="<?= $vEmail; ?>" placeholder="Email">
                                    </div><div id="emailCheck"></div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Password<span class="red"> *</span>
                                            <?php if ($action == 'Edit') { ?>
                                                <span>&nbsp;[Leave blank to retain assigned password.]</span>
                                            <?php } ?>
                                        </label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="password" class="form-control" name="vPassword"  id="vPassword" value="" placeholder="Password">
                                    </div>
                                </div>

                                <!--<div class="row">
                                    <div class="col-lg-12">
                                        <label>Locations</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select class="select2 form-control" name="locations_ids[]" multiple>
                                            <?php foreach ($locations as $location_id => $location_name) { ?>
                                                <option <?php echo in_array($location_id, $selected_location_ids) ? "selected" : "" ?> value="<?php echo $location_id ?>"><?php echo $location_name ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>-->
                                <?php if ($_SESSION['sess_iGroupId'] == 1) { ?>
                                    <div id="hotel_bookingField" style="display: none;">
                                        <div class="row">
                                            <div class="col-lg-12"> 
                                                <label><?= $langage_lbl['LBL_COUNTRY_TXT']; ?><span class="red">*</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <?php 
                                                    if(count($db_country) > 1){ 
                                                          $style = "";
                                                         }else{
                                                        $style = " disabled=disabled";
                                                    } ?>
                                                <select <?= $style ?> class="form-control valid" name='vCountry' id='vCountry' onChange="changeCode(this.value);setState(this.value, '<?= $vState ?>');">
                                                    <?php 
                                                    if(count($db_country) > 1){ ?>
                                                        <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] ?></option>
                                                     <?php } ?>
                                                    <?php for ($i = 0; $i < count($db_country); $i++) { ?>
                                                        <option value = "<?= $db_country[$i]['vCountryCode'] ?>" <?php if ($DEFAULT_COUNTRY_CODE_WEB == $db_country[$i]['vCountryCode'] && $action == 'Add') { ?> selected <?php } else if ($vCountry == $db_country[$i]['vCountryCode']) { ?>selected<?php } ?>><?= $db_country[$i]['vCountry'] ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12"> 
                                                <label><?= $langage_lbl['LBL_STATE_TXT']; ?></label>
                                            </div>
                                            <div class="col-lg-6"> 
                                                <select class="form-control" name = 'vState' id="vState" onChange="setCity(this.value, '<?= $vCity ?>');">
                                                    <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] ?></option>
                                                    <?php for ($i = 0; $i < count($db_state); $i++) { ?>
                                                        <option value = "<?= $db_state[$i]['iStateId'] ?>" <?php if ($vState == $db_state[$i]['iStateId']) { ?> selected <?php } ?>><?= $db_state[$i]['vState'] ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <?php if($SHOW_CITY_FIELD=='Yes') { ?>
                                        <div class="row">
                                            <div class="col-lg-12"> 
                                                <label><?= $langage_lbl['LBL_CITY_TXT']; ?></label>
                                            </div>
                                            <div class="col-lg-6"> 
                                                <select class="form-control" name = 'vCity' id="vCity" >
                                                    <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] ?></option>
                                                    <?php for ($i = 0; $i < count($db_city); $i++) { ?>
                                                        <option value = "<?= $db_city[$i]['iCityId'] ?>" <?php if ($vCity == $db_city[$i]['iCityId']) { ?> selected <?php } ?>><?= $db_city[$i]['vcity'] ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <?php } ?>
                                        <div class="row" id="hasherror">
                                            <div class="col-lg-12">
                                                <label>Contact No <span class="red">*</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-select-2" id="code" name="vCode" value="<?= $vCode ?>"  readonly style="width: 10%;height: 36px;text-align: center;"/ >
                                                       <input type="text" class="form-control" style="margin-top: 5px; width:90%;" name="vContactNo" id="vContactNo" value="<?= $vContactNo; ?>" placeholder="Contact No">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Address<span class="red">*</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" id="vAddress" class="form-control" name="vAddress"  id="vAddress" value="<?= $vAddress; ?>" placeholder=" Location" >
                                            </div>
                                            <input type="hidden" name="vAddressLat" id="vAddressLat" value="<?= $vAddressLat ?>">
                                            <input type="hidden" name="vAddressLong" id="vAddressLong" value="<?= $vAddressLong ?>">
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div id="map" style="width:100%;height:200px;"></div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Hotel Booking Service Charge (In %)<span class="red">*</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="fHotelServiceCharge" id='fHotelServiceCharge' value="<?= $hotel_booking_service_charge; ?>" />
                                                [Note : Booking service charge will apply on Base Fare.]
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Payment Email</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="vPaymentEmail" value="<?= $vPaymentEmail; ?>" placeholder="Payment Email" />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Account Holder name</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="vBankAccountHolderName" value="<?= $vBankAccountHolderName; ?>" placeholder="Account Holder name" />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Account Number</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="vAccountNumber" value="<?= $vAccountNumber; ?>"  placeholder="Account Number" />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Name of Bank</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="vBankName" value="<?= $vBankName; ?>" placeholder="Name of Bank" />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Bank Location</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="vBankLocation" value="<?= $vBankLocation; ?>" placeholder="Bank Location" />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>BIC/SWIFT Code</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="vBIC_SWIFT_Code" value="<?= $vBIC_SWIFT_Code; ?>" placeholder="BIC/SWIFT Code" />
                                            </div>
                                        </div>
                                        <?php if (ENABLEKIOSKPANEL == 'Yes') { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Logo</label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <?php if (isset($vImgName) && $vImgName != '') { ?>                                               
                                                        <img src="<?= $tconfig['tsite_upload_images_hotel_passenger'] . "/" . $hotelid . "/" . $vImgName; ?>" style="width:100px;height:100px;">

                                                    <?php } ?>
                                                    <input type="file" class="form-control" name="vImgName"  id="vImgName" placeholder="Name Label" accept='image/*'>
                                                    [Note: Please Upload image size of 280px*280px.]
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>VehicleType Screen bg Image (Kiosk)</label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <?php if (isset($vVehicleTypeImg) && $vVehicleTypeImg != '') { ?>                                               
                                                        <img src="<?= $tconfig['tsite_upload_images_hotel_passenger'] . "/" . $hotelid . "/" . $vVehicleTypeImg; ?>" style="width:100px;height:100px;">

                                                    <?php } ?>
                                                    <input type="file" class="form-control" name="vVehicleTypeImg"  id="vVehicleTypeImg" placeholder="Name Label" accept='image/*'>
                                                    [Note: Please Upload image size of 1024px*680px for better resolution.]
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Pickup From</label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <input type="text" class="form-control" name="vPickupFrom" value="<?= $vPickupFrom; ?>" placeholder="Pickup From" />
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <?php
                                        if ($userObj->hasRole(1) || ($action == "Edit" && $userObj->hasPermission('edit-admin')) || ($action == "Add" && $userObj->hasPermission('create-admin'))) {
                                            if ($action == "Edit") {
                                                $actionTxt = 'Update';
                                            } else {
                                                $actionTxt = 'Add Admin';
                                            }
                                            ?>
                                            <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?= $actionTxt; ?>" >
                                            <input type="reset" value="Reset" class="btn btn-default">
                                        <?php } ?>
                                        <a href="admin.php" class="btn btn-default back_link">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
        <?php include_once('footer.php'); ?>
    </body>

    <script type="text/javascript" src="js/plugins/select2.min.js"></script>
    <script>
	var markers = [];
                                                $(document).ready(function () {
                                                    $('.select2').select2({
                                                        allowClear: true,
                                                    });

                                                    var referrer;
                                                    if ($("#previousLink").val() == "") {
                                                        referrer = document.referrer;
                                                        //alert(referrer);
                                                    } else {
                                                        referrer = $("#previousLink").val();
                                                    }
                                                    if (referrer == "") {
                                                        referrer = "admin.php";
                                                    } else {
                                                        $("#backlink").val(referrer);
                                                    }
                                                    $(".back_link").attr('href', referrer);
                                                });
                                                $(document).ready(function () {
                                                    var iGroupId = $('#iGroupId').val();
                                                    if (iGroupId == '4') {
                                                        $("#hotel_bookingField").css("display", "block");
                                                        $('#vCountry').attr('required', 'required');
                                                        $('#vAddress').attr('required', 'required');
                                                        $('#vContactNo').attr('required', 'required');
                                                        $('#fHotelServiceCharge').attr('required', 'required');
                                                        $('.vLastName').hide();
                                                        $('#vLastName').removeAttr('required');
                                                        $('.hoteladmin').show();
                                                        $('.admin').hide();
                                                    } else {
                                                        $("#hotel_bookingField").css("display", "none");
                                                        $('#vCountry').removeAttr('required');
                                                        $('#vAddress').removeAttr('required');
                                                        $('#vContactNo').removeAttr('required');
                                                        $('#fHotelServiceCharge').removeAttr('required');
                                                        $('.vLastName').show();
                                                        $('#vLastName').attr('required', 'required');
                                                        $('.hoteladmin').hide();
                                                        $('.admin').show();
                                                    }
                                                });

                                                $('#iGroupId').on('change', function () {
                                                    if (this.value == '4') {
                                                        $("#hotel_bookingField").css("display", "block");
                                                        $('#vCountry').attr('required', 'required');
                                                        $('#vAddress').attr('required', 'required');
                                                        $('#vContactNo').attr('required', 'required');
                                                        $('#fHotelServiceCharge').attr('required', 'required');
                                                        $('.vLastName').hide();
                                                        $('#vLastName').removeAttr('required');
                                                        $('.hoteladmin').show();
                                                        $('.admin').hide();
                                                    } else {
                                                        $("#hotel_bookingField").css("display", "none");
                                                        $('#vCountry').removeAttr('required');
                                                        $('#vAddress').removeAttr('required');
                                                        $('#vContactNo').removeAttr('required');
                                                        $('#fHotelServiceCharge').removeAttr('required');
                                                        $('.vLastName').show();
                                                        $('#vLastName').attr('required', 'required');
                                                        $('.hoteladmin').hide();
                                                        $('.admin').show();
                                                    }
                                                });

                                                function changeCode(id) {
                                                    var request = $.ajax({
                                                        type: "POST",
                                                        url: 'change_code.php',
                                                        data: 'id=' + id,
                                                        success: function (data)
                                                        {
                                                            document.getElementById("code").value = data;
                                                            $("#vContactNo-error").hide();
            }
        });
    }
    function setState(id,selected) {

        $("#vState + em").html('<?= addslashes($langage_lbl['LBL_SELECT_TXT']) ?>');
        $("#vCity + em").html('<?= addslashes($langage_lbl['LBL_SELECT_TXT']) ?>');

                                                    var fromMod = 'profile';
                                                    var request = $.ajax({
                                                        type: "POST",
                                                        url: 'change_stateCity.php',
                                                        data: {countryId: id, selected: selected, fromMod: fromMod},
                                                        success: function (dataHtml)
                                                        {
                                                            $("#vCity").html('<option value=""><?= addslashes($langage_lbl['LBL_SELECT_TXT']) ?></option>');
                                                            $("#vState").html(dataHtml);
                                                            if (selected == '')
                                                                setCity('', selected);
                                                        }
                                                    });
                                                }

                                                function setCity(id, selected) {
                                                    var fromMod = 'profile';
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

                                                setState('<?php echo $vCountry; ?>', '<?php echo $vState; ?>');
                                                changeCode('<?php echo $vCountry; ?>');
                                                setCity('<?php echo $vState; ?>', '<?php echo $vCity; ?>');
                                                var map;
                                                function initialize() {
                                                    map = new google.maps.Map(document.getElementById('map'), {
                                                        center: {lat: -33.8688, lng: 151.2195},
                                                        zoom: 13
                                                    });
                                                    $('#vAddress').keyup(function (e) {
                                                buildAutoComplete("vAddress",e, "<?=$MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE;?>","<?=$_SESSION['sess_lang'];?>", function(latitude, longitude, address){
                                                    // show_locations();
                                                    var marker = new google.maps.Marker({
                                                        map: map,
                                                        anchorPoint: new google.maps.Point(0, -29)
                                                    });
                                                    var location = new google.maps.LatLng(latitude, longitude);
                                                            map.setCenter(location);
                                                            map.setZoom(17);
                                                            marker.setVisible(false);
                                                            marker.setPosition(location);
                                                            marker.setVisible(true);
                                                            $("#vAddressLat").val(latitude);
                                                            $("#vAddressLong").val(longitude);
															}); // (orignal function)
												});
                                                    // var input = document.getElementById('vAddress');
                                                    // // map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

                                                    // var autocomplete = new google.maps.places.Autocomplete(input);
                                                    // autocomplete.bindTo('bounds', map);

                                                    var marker = new google.maps.Marker({
                                                        map: map,
                                                        anchorPoint: new google.maps.Point(0, -29)
                                                    });

                                                    // autocomplete.addListener('place_changed', function () {

                                                    //     marker.setVisible(false);
                                                    //     var place = autocomplete.getPlace();
                                                    //     if (!place.geometry) {
                                                    //         window.alert("Autocomplete's returned place contains no geometry");
                                                    //         return;
                                                    //     }

                                                    //     // If the place has a geometry, then present it on a map.
                                                    //     if (place.geometry.viewport) {
                                                    //         map.fitBounds(place.geometry.viewport);
                                                    //     } else {
                                                    //         map.setCenter(place.geometry.location);
                                                    //         map.setZoom(17);
                                                    //     }
                                                    //     /*        marker.setIcon(({
                                                    //      size: new google.maps.Size(71, 71),
                                                    //      origin: new google.maps.Point(0, 0),
                                                    //      anchor: new google.maps.Point(17, 34),
                                                    //      scaledSize: new google.maps.Size(35, 35)
                                                    //      }));*/
                                                    //      console.log(place.geometry.location+"test ");
                                                    //     marker.setPosition(place.geometry.location);
                                                    //     marker.setVisible(true);

                                                    //     var address = '';
                                                    //     if (place.address_components) {
                                                    //         address = [
                                                    //             (place.address_components[0] && place.address_components[0].short_name || ''),
                                                    //             (place.address_components[1] && place.address_components[1].short_name || ''),
                                                    //             (place.address_components[2] && place.address_components[2].short_name || '')
                                                    //         ].join(' ');
                                                    //     }
                                                    //     console.log(place.geometry.location+"=== Address : "+place);
                                                    //     //$("#vAddress").val(place.formatted_address);
                                                    //     $("#vAddressLat").val(place.geometry.location.lat());
                                                    //     $("#vAddressLong").val(place.geometry.location.lng());
                                                    // });

                                                    if ($("#vAddress").val() != "") {
                                                        var myLatLng = new google.maps.LatLng($("#vAddressLat").val(), $("#vAddressLong").val());
                                                        marker.setPosition(myLatLng);
                                                        map.setCenter(myLatLng);
                                                        map.setZoom(17);
                                                        marker.setVisible(true);
                                                    }
                                                }

                                                google.maps.event.addDomListener(window, 'load', initialize);
                                                var input = document.getElementById('vAddress');
                                                google.maps.event.addDomListener(input, 'keydown', function (event) {
                                                    if (event.keyCode === 13) {
                                                        event.preventDefault();
                                                    }
                                                });
							   function DeleteMarkers(newId) {
									// Loop through all the markers and remove
									for (var i = 0; i < markers.length; i++) {
										if (newId != '') {
											if (markers[i].id == newId) {
												markers[i].setMap(null);
											}
										} else {
											markers[i].setMap(null);
										}
									}
									if (newId == '') {
										markers = [];
									}
								}
								function setMarker(postitions, valIcon) {
									var marker = new google.maps.Marker({
										map: map,
										draggable: true,
										animation: google.maps.Animation.DROP,
										position: postitions,
									});
									marker.id = valIcon;
									markers.push(marker);
									map.setCenter(marker.getPosition());
									map.setZoom(15);
								}
    </script>
    <!-- END BODY-->
</html>
