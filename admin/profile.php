<?php
include_once('../common.php');
require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();
$tsiteUrl = $tconfig['tsite_url'];
$sql = "SELECT * FROM country WHERE eStatus='Active' ORDER BY  vCountry ASC ";
$db_country = $obj->MySQLSelect($sql);

if (empty($SHOW_CITY_FIELD)) {
    $SHOW_CITY_FIELD = $generalobj->getConfigurations("configurations", "SHOW_CITY_FIELD");
} 
    
$tbl_name = 'administrators';
$script = 'profile';

$id = isset($_SESSION['sess_iAdminUserId']) ? $_SESSION['sess_iAdminUserId'] : '';
$iGroupId = isset($_SESSION['sess_iGroupId']) ? $_SESSION['sess_iGroupId'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$ksuccess = isset($_REQUEST['ksuccess']) ? $_REQUEST['ksuccess'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';

// set all variables with either post (when submit) either blank (when insert)
$vFirstName = isset($_POST['vFirstName']) ? $_POST['vFirstName'] : '';
$vLastName = isset($_POST['vLastName']) ? $_POST['vLastName'] : '';
$vEmail = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
$vPassword = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
$vCode = isset($_POST['vCode']) ? $_POST['vCode'] : '';
$vContactNo = isset($_POST['vContactNo']) ? $_POST['vContactNo'] : '';
$vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : '';
$vState = isset($_POST['vState']) ? $_POST['vState'] : '';
$vCity = isset($_POST['vCity']) ? $_POST['vCity'] : '';
$vAddress = isset($_POST['vAddress']) ? $_POST['vAddress'] : '';
$vAddressLat = isset($_POST['vAddressLat']) ? $_POST['vAddressLat'] : '';
$vAddressLong = isset($_POST['vAddressLong']) ? $_POST['vAddressLong'] : '';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$vPass = ($vPassword != "") ? $generalobj->encrypt_bycrypt($vPassword) : '';

$vPaymentEmail = isset($_POST['vPaymentEmail']) ? $_POST['vPaymentEmail'] : '';
$vBankAccountHolderName = isset($_POST['vBankAccountHolderName']) ? $_POST['vBankAccountHolderName'] : '';
$vAccountNumber = isset($_POST['vAccountNumber']) ? $_POST['vAccountNumber'] : '';
$vBankName = isset($_POST['vBankName']) ? $_POST['vBankName'] : '';
$vBankLocation = isset($_POST['vBankLocation']) ? $_POST['vBankLocation'] : '';
$vBIC_SWIFT_Code = isset($_POST['vBIC_SWIFT_Code']) ? $_POST['vBIC_SWIFT_Code'] : '';

$vPickupFrom = isset($_POST['vPickupFrom']) ? $_POST['vPickupFrom'] : '';
if (isset($_POST['submit'])) {
    if ($id != "" && SITE_TYPE == 'Demo') {
        $_SESSION['success'] = '2';
        header("location:" . $backlink);
        exit;
    }
    //Add Custom validation
    require_once("library/validation.class.php");
    $validobj = new validation();
    if ($_SESSION["SessionUserType"] != 'hotel') {
        $validobj->add_fields($_POST['vFirstName'], 'req', 'First Name is required');
    } else {
        $validobj->add_fields($_POST['vFirstName'], 'req', 'Hotel Name is required');
    }
    if ($_SESSION["SessionUserType"] != 'hotel') {
        $validobj->add_fields($_POST['vLastName'], 'req', 'Last Name is required');
    }
    $validobj->add_fields($_POST['vEmail'], 'req', 'Email Address is required.');
    $validobj->add_fields($_POST['vContactNo'], 'req', 'Contact No is required');
    $validobj->add_fields($_POST['vAddress'], 'req', 'Address is required');
    if ($action == "Add") {
        $validobj->add_fields($_POST['vPassword'], 'req', 'Password is required.');
    }
    $error = $validobj->validate();
    //Other Validations
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
    //echo "<pre>";print_r($_POST);die;
    //$checPhoneExist = $generalobj->checkMemberDataInfo($vPhone, "", 'COMPANY', $vCountry, $id, $eSystem);
    if ($error) {
        $success = 3;
        $newError = $error;
    } else {
        $passPara = '';
        if ($vPass != "") {
            $passPara = "`vPassword` = '" . $vPass . "',";
        }
        $q = "INSERT INTO ";
        $where = '';
        if ($id != '') {
            $q = "UPDATE ";
            $where = " WHERE `iAdminId` = '" . $id . "' and `iGroupId` = '" . $iGroupId . "' ";
        }
        $query = $q . " `" . $tbl_name . "` SET
            `vFirstName` = '" . $vFirstName . "',
            `vLastName` = '" . $vLastName . "',
			`vEmail` = '" . $vEmail . "',
            `vCode` = '" . $vCode . "',
            `vContactNo` = '" . $vContactNo . "',
			`vCountry` = '" . $vCountry . "',
			`vState` = '" . $vState . "',
			`vCity` = '" . $vCity . "',
			`vAddress` = '" . $vAddress . "',
			`vAddressLat` = '" . $vAddressLat . "',
            `vPaymentEmail`= '" . $vPaymentEmail . "',
            `vBankAccountHolderName`= '" . $vBankAccountHolderName . "',
            `vAccountNumber`= '" . $vAccountNumber . "',
            `vBankName`= '" . $vBankName . "',
            `vBankLocation`= '" . $vBankLocation . "',
            `vBIC_SWIFT_Code`= '" . $vBIC_SWIFT_Code . "',
			$passPara
			`vAddressLong` = '" . $vAddressLong . "'
             " . $where;
        $obj->sql_query($query);
        $id = ($id != '') ? $id : $obj->GetInsertId();
        if ($id != '') {
            $q = "UPDATE ";
            $where1 = " WHERE `iAdminId` = '" . $id . "'";
        }
        $subquery = $q . " `hotel` 
        SET `iAdminId` = '" . $id . "',
        vPickupFrom = '" . $vPickupFrom . "'
         " . $where1;
        $obj->sql_query($subquery);

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

            $vVehicleTypeImg = $img1;
            $sql1 = "UPDATE hotel SET `vVehicleTypeImg` = '" . $vVehicleTypeImg . "' WHERE `iAdminId` = '" . $id . "'";
            $obj->sql_query($sql1);
        }

        if ($action == "Add") {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
        } else {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        }

        header("location:profile.php");
        exit();
    }
}

// for Edit
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iAdminId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);

    $sql1 = "SELECT * FROM hotel WHERE iAdminId = '" . $id . "'";
    $db_hoteldata = $obj->MySQLSelect($sql1);

    $vLabel = $id;
    if (count($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $vFirstName = isset($value['vFirstName']) ? $value['vFirstName'] : '';
            $vLastName = $generalobjAdmin->clearName(" " . $value['vLastName']);
            $vEmail = (isset($_SESSION['SessionUserType']) && $_SESSION['SessionUserType'] == "hotel") ? $value['vEmail'] : $generalobjAdmin->clearEmail($value['vEmail']);
            $vCode = $value['vCode'];
            $vContactNo = isset($value['vContactNo']) ? $value['vContactNo'] : '';
            $vCountry = isset($value['vCountry']) ? $value['vCountry'] : '';
            $vState = isset($value['vState']) ? $value['vState'] : '';
            $vCity = isset($value['vCity']) ? $value['vCity'] : '';
            $vAddress = isset($value['vAddress']) ? $value['vAddress'] : '';
            $vAddressLat = isset($value['vAddressLat']) ? $value['vAddressLat'] : '';
            $vAddressLong = isset($value['vAddressLong']) ? $value['vAddressLong'] : '';
            $vPassword = isset($value['vPassword']) ? $value['vPassword'] : '';
            $iGroupId = isset($iGroupId) ? $iGroupId : '';
            $HOTEL_BOOKING_SERVICE_CHARGE = $value['fHotelServiceCharge'];
            $vPaymentEmail = $value['vPaymentEmail'];
            $vBankAccountHolderName = $value['vBankAccountHolderName'];
            $vAccountNumber = $value['vAccountNumber'];
            $vBankName = $value['vBankName'];
            $vBankLocation = $value['vBankLocation'];
            $vBIC_SWIFT_Code = $value['vBIC_SWIFT_Code'];

            $vImgName = $db_hoteldata[0]['vImgName'];
            $vVehicleTypeImg = $db_hoteldata[0]['vVehicleTypeImg'];
            $vPickupFrom = $db_hoteldata[0]['vPickupFrom'];
            $hotelid = $db_hoteldata[0]['iHotelId'];
        }
    }
}
?>

<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | Hotel Profile <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php include_once('global_files.php'); ?>
        <!-- On OFF switch -->
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
        <script src="//maps.google.com/maps/api/js?sensor=true&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>&libraries=places" type="text/javascript"></script>
        <script type='text/javascript' src='../assets/map/gmaps.js'></script>
        <link rel="stylesheet" href="css/select2/select2.min.css" type="text/css" >
        <script type="text/javascript" src="js/plugins/select2.min.js"></script>
        <script type='text/javascript' src='../assets/js/bootbox.min.js'></script>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?php include_once('header.php');
            include_once('left_menu.php'); ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2><?= $action; ?> Hotel Profile</h2>
                        </div>
                    </div>
                    <hr/>

                    <div class="body-div">
                        <div class="form-group">
                            <? if ($success == 2) {?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
<?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                            </div><br/>
                            <?} ?>
                            <? if ($success == 3) {?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
<?php print_r($error); ?>
                            </div><br/>
                            <?} ?>
<?php include 'valid_msg.php'; ?>
                            <form name="_admin_form" id="_admin_form" method="post" action="" enctype="multipart/form-data">
                                <input type="hidden" name="actionOf" id="actionOf" value="<?php echo $action; ?>"/>
                                <input type="hidden" name="id" id="iAdminId" value="<?php echo $id; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="profile.php"/>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label><?php if ($_SESSION["SessionUserType"] != 'hotel') { ?>First Name<?php } else { ?>Hotel Name
<?php } ?><span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vFirstName"  id="vName" value="<?= $vFirstName; ?>" placeholder="First Name">
                                    </div>
                                </div><?php if ($_SESSION["SessionUserType"] != 'hotel') { ?>
                                    <div class="row">
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
                                        <label>Password<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="password" class="form-control" name="vPassword"  id="vPassword" value="" placeholder="Password">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12"> 
                                        <label><?= $langage_lbl['LBL_COUNTRY_TXT']; ?><span class="red">*</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                         <?php 
                                            if(count($db_country) > 1){ 
                                                  $style = "";
                                                 }else{
                                                $style = " disabled=disabled ";
                                            } ?>
                                        <select <?= $style ?> class="form-control valid"  id='vCountry' name='vCountry' onChange="changeCode(this.value);setState(this.value, '<?= $vState ?>');" required>
                                            <?php 
                                            if(count($db_country) > 1){ ?>
                                            <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] ?></option>
                                            <? } ?>
                                            <?php for ($i = 0; $i < count($db_country); $i++) { ?>
                                                <option value = "<?= $db_country[$i]['vCountryCode'] ?>" <?php if ($DEFAULT_COUNTRY_CODE_WEB == $db_country[$i]['vCountryCode'] && $action == 'Add') { ?> selected <?php } else if ($vCountry == $db_country[$i]['vCountryCode']) { ?>selected<?php } ?>><?= $db_country[$i]['vCountry'] ?></option>
                                                    <?php } ?>
                                                </select>
                                                <div id="vCountry_validate"></div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12"> 
                                                <label><?= $langage_lbl['LBL_STATE_TXT']; ?></label>
                                            </div>
                                            <div class="col-lg-6"> 
                                                <select class="form-control" name = 'vState' id="vState" onChange="setCity(this.value, '<?= $vCity ?>');">
                                                    <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] ?></option>
                                                    <?php  for($i=0;$i<count($db_state);$i++){ ?>
                                                    <option value = "<?= $db_state[$i]['iStateId'] ?>" <?php if($vState == $db_state[$i]['iStateId']) { ?> selected <?php } ?>><?= $db_state[$i]['vState'] ?></option>
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
                                                <?php  for($i=0;$i<count($db_city);$i++){ ?>
                                                <option value = "<?= $db_city[$i]['iCityId'] ?>" <?php if($vCity == $db_city[$i]['iCityId']) { ?> selected <?php } ?>><?= $db_city[$i]['vcity'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                 <?php } ?>   
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>ContactNo<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-select-2" id="code" name="vCode" value="<?= $vCode ?>"  readonly style="width: 10%;height: 36px;text-align: center;"/ >
                                               <input type="text" class="form-control" style="margin-top: 5px; width:90%;" name="vContactNo" id="vContactNo" value="<?= $vContactNo; ?>" placeholder="Contact No">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Address<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" id="vAddress" class="form-control" name="vAddress"  id="vAddress" value="<?= $vAddress; ?>" placeholder=" Location" required>
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
                                        <label>Hotel Booking Service Charge (In %)</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="hotelbookingservicecharge" value="<?= $HOTEL_BOOKING_SERVICE_CHARGE; ?>" disabled="disabled" />
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
                                <?php if(ENABLEKIOSKPANEL == 'Yes') { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Logo</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <?php if (isset($vImgName) && $vImgName != '') { ?>                                               
                                        <img src="<?= $tconfig['tsite_upload_images_hotel_passenger'] . "/" . $hotelid . "/" . $vImgName; ?>" style="width:100px;height:100px;">

                                        <?php } ?>
                                        <input type="file" class="form-control" name="vImgName"  id="vImgName" placeholder="Name Label" accept='image/*'>
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
                                <div class="row">
                                    <div class="col-lg-12">
                                        <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?= $action; ?>&nbsp;<?php if ($_SESSION["SessionUserType"] != 'hotel') { ?>Admin<?php } else { ?>Hotel<?php } ?>" >
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
    <!-- END BODY-->
</html>
<script>
 var markers = [];
    $(document).ready(function () {
		$('#vAddress').keyup(function (e) {
			 buildAutoComplete("vAddress",e, "<?=$MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE;?>","<?=$_SESSION['sess_lang'];?>", function(latitude, longitude, address){
				 DeleteMarkers('from_loc');
				var newIcon = '<?= $tsiteUrl; ?>/webimages/upload/mapmarker/PinTo.png';
				$("#vAddress").val(address);
				$("#vAddressLat").val(latitude);
				$("#vAddressLong").val(longitude);
				var latlng = new google.maps.LatLng(latitude, longitude);
				setMarker(latlng, 'from_loc');
			});
		});
		
        var referrer;
        if ($("#previousLink").val() == "") {
            referrer = document.referrer;
        } else {
            referrer = $("#previousLink").val();
        }
        if (referrer == "") {
            referrer = "profile.php";
        } else {
            $("#backlink").val(referrer);
        }
        $(".back_link").attr('href', referrer);
    });

    function changeCode(id) {
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
    function setState(id, selected) {
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
		var marker = new google.maps.Marker({
            map: map,
            anchorPoint: new google.maps.Point(0, -29)
        });
		
        // var input = document.getElementById('vAddress');
        // var autocomplete = new google.maps.places.Autocomplete(input);
        // autocomplete.bindTo('bounds', map);
        // var marker = new google.maps.Marker({
            // map: map,
            // anchorPoint: new google.maps.Point(0, -29)
        // });
        // autocomplete.addListener('place_changed', function () {
            // marker.setVisible(false);
            // var place = autocomplete.getPlace();
            // if (!place.geometry) {
                // window.alert("Autocomplete's returned place contains no geometry");
                // return;
            // }
            // // If the place has a geometry, then present it on a map.
            // if (place.geometry.viewport) {
                // map.fitBounds(place.geometry.viewport);
            // } else {
                // map.setCenter(place.geometry.location);
                // map.setZoom(17);
            // }
            // marker.setPosition(place.geometry.location);
            // marker.setVisible(true);
            // var address = '';
            // if (place.address_components) {
                // address = [
                    // (place.address_components[0] && place.address_components[0].short_name || ''),
                    // (place.address_components[1] && place.address_components[1].short_name || ''),
                    // (place.address_components[2] && place.address_components[2].short_name || '')
                // ].join(' ');
            // }
            // $("#vAddress").val(place.formatted_address);
            // $("#vAddressLat").val(place.geometry.location.lat());
            // $("#vAddressLong").val(place.geometry.location.lng());
        // });

        if ($("#vAddress").val() != "") {
            var myLatLng = new google.maps.LatLng($("#vAddressLat").val(), $("#vAddressLong").val());
            // marker.setPosition(myLatLng);
			setMarker(myLatLng, 'from_loc');
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
	function setMarker(postitions, valIcon) {
                               
                                var marker = new google.maps.Marker({
                                    map: map,
                                    animation: google.maps.Animation.DROP,
                                    position: postitions,
                                });
                                marker.id = valIcon;
                                markers.push(marker);
                                map.setCenter(marker.getPosition());
                                map.setZoom(15);
 }
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
</script>
