<?php
include_once('../common.php');
require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();
$script = "Coupon";
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
include_once ('../app_common_functions.php'); //added by SP on 23-11-2019 for fly changes
$flyEnable = "No";
if(checkFlyStationsModule()) {
	$flyEnable = "Yes";	
}

////$generalobjAdmin->check_member_login();
$id = $_GET['id'];
$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$db_currency = $obj->MySQLSelect("SELECT vName,vSymbol FROM currency WHERE eDefault = 'Yes'");
$defaultCurrency = "USD";
if (count($db_currency) > 0) {
    $defaultCurrency = $db_currency[0]['vName'];
}
$iCouponId = isset($_REQUEST['iCouponId']) ? $_REQUEST['iCouponId'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$error = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : '';
$action = ($iCouponId != '') ? 'Edit' : 'Add';
$tbl_name = 'coupon';
// set all variables with either post (when submit) either blank (when insert)
$iCouponId = isset($_REQUEST['iCouponId']) ? $_REQUEST['iCouponId'] : '';
$existsCoupon = isset($_REQUEST['existscoupon']) ? $_REQUEST['existscoupon'] : '';
$vCouponCode = isset($_REQUEST['vCouponCode']) ? $_REQUEST['vCouponCode'] : '';
$fDiscount = isset($_REQUEST['fDiscount']) ? $_REQUEST['fDiscount'] : '';
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : '';
$eValidityType = isset($_REQUEST['eValidityType']) ? $_REQUEST['eValidityType'] : '';
$dActiveDate = isset($_REQUEST['dActiveDate']) ? $_REQUEST['dActiveDate'] : '';
$dExpiryDate = isset($_REQUEST['dExpiryDate']) ? $_REQUEST['dExpiryDate'] : '';
$iUsageLimit = isset($_REQUEST['iUsageLimit']) ? $_REQUEST['iUsageLimit'] : '';
$iUsed = isset($_REQUEST['iUsed']) ? $_REQUEST['iUsed'] : '';
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : 'Active';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$eSystemType = isset($_REQUEST['eSystemType']) ? $_REQUEST['eSystemType'] : 'General';
$couponsystem = isset($_REQUEST['couponsystem']) ? $_REQUEST['couponsystem'] : 'General';
$vPromocodeType = isset($_REQUEST['vPromocodeType']) ? $_REQUEST['vPromocodeType'] : 'Public';

$eFly = 0;
if($eSystemType=='Fly' || $couponsystem == 'Fly') {
	$eFly = 1;
	$eSystemType = 'Ride';
}
//Added BY HJ On 09-01-2020 For Set Option Name As Per Service Start
$serviceIds = $generalobj->getCurrentActiveServiceCategoriesIds();
$optionName = "DeliverAll";
if ($serviceIds == 1) {
    $optionName = "Food";
}
//Added BY HJ On 09-01-2020 For Set Option Name As Per Service End
if (isset($_POST['submit'])) {
    //echo "<pre>";print_r($_POST);die;
    if ($action == "Add" && !$userObj->hasPermission('create-promocode')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create Promo Code.';
        header("Location:coupon.php");
        exit;
    }

    if ($action == "Edit" && !$userObj->hasPermission('edit-promocode')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update Promo Code.';
        header("Location:coupon.php");
        exit;
    }

    if (!empty($iCouponId)) {
        if (SITE_TYPE == 'Demo') {
            header("Location:coupon_action.php?iCouponId=" . $iCouponId . '&success=2');
            exit;
        }
    }
    require_once("library/validation.class.php");
    $validobj = new validation();
    $validobj->add_fields($_POST['vCouponCode'], 'req', 'Coupon Code is required');
    $validobj->add_fields($_POST['tDescription_' . $default_lang], 'req', 'Description is required');
    $validobj->add_fields($_POST['fDiscount'], 'req', 'Discount is required');
    if ($_POST['eValidityType'] == "Defined") {
        $validobj->add_fields($_POST['dActiveDate'], 'req', 'Activation Date is required');
        $validobj->add_fields($_POST['dExpiryDate'], 'req', 'Expiry Date is required');
    }
    $validobj->add_fields($_POST['iUsageLimit'], 'req', 'Usage Limit is required');
    $validobj->add_fields($_POST['eStatus'], 'req', 'Status is required');
    $validobj->add_fields($_POST['vPromocodeType'], 'req', 'Promocode Type is required');
    $error = $validobj->validate();

    if ($error) {
        $success = 3;
        $newError = $error;
    } else {
        //Added By HJ On 06-03-2019 For Check Coupon Code with It's System Type As Per Discuss With KS Sir Start
        if ($action == 'Add') {
            $couponsystem = $eSystemType;
        }
        $whereSystemType = " AND (eSystemType='" . $couponsystem . "' OR eSystemType='General')";
        if ($couponsystem == "General") {
            $whereSystemType = " AND (eSystemType='General' OR eSystemType!='General')";
        }
        if ($action == 'Edit') {
            $whereSystemType .= " AND iCouponId != '" . $iCouponId . "'";
        }
        //echo $whereSystemType;die;
        $checkPromocode = $obj->MySQLSelect("SELECT * FROM " . $tbl_name . " WHERE vCouponCode='" . $vCouponCode . "' AND eStatus != 'Deleted'" . $whereSystemType);
        //echo "<pre>";print_R($checkPromocode);die;
        //Added By HJ On 06-03-2019 For Check Coupon Code with It's System Type As Per Discuss With KS Sir End
        if (count($checkPromocode) > 0) {
            $existsType = $checkPromocode[0]['eSystemType'];
            //$_SESSION['success'] = '3';
            //$_SESSION['var_msg'] = 'Promo Code already exists.';
            header("Location:coupon_action.php?success=3&var_msg=Promo code already exists in <b>" . $existsType . "</b> system.&existscoupon=" . $vCouponCode);
            exit;
        } else {
            $descArr = array();
            for ($b = 0; $b < count($db_master); $b++) {
                $tDescription = "";
                if (isset($_POST['tDescription_' . $db_master[$b]['vCode']])) {
                    $tDescription = $_POST['tDescription_' . $db_master[$b]['vCode']];
                }
                $descArr["tDescription_" . $db_master[$b]['vCode']] = $tDescription;
            }
            $jsonDesc =  $generalobj->getJsonFromAnArr($descArr);
            $q = "INSERT INTO ";
            $where = '';
            if ($action == 'Edit') {
                $str = "";
            } else {
                $str = " , eSystemType = '" . $eSystemType . "' ";
            }
            if ($eValidityType == 'Permanent') {
                $dActiveDate = $dExpiryDate = '';
            }
            if ($iCouponId != '') {
                $q = "UPDATE ";
                $where = " WHERE `iCouponId` = '" . $iCouponId . "'";
            }
            //echo $jsonDesc;die;  
            $query = $q . " `" . $tbl_name . "` SET
		`vCouponCode` = '" . $vCouponCode . "',
		`fDiscount` = '" . $fDiscount . "',
		`eType` = '" . $eType . "',
		`eValidityType` = '" . $eValidityType . "',
		`dActiveDate` = '" . $dActiveDate . "',
		`dExpiryDate` = '" . $dExpiryDate . "',
		`iUsageLimit` = '" . $iUsageLimit . "',		
		`tDescription` = '" . $jsonDesc . "' $str,
        `eFly` = '".$eFly."',
        `vPromocodeType` = '".$vPromocodeType."',
		`eStatus` = '" . $eStatus . "'" . $where;
	$obj->sql_query($query);

            if ($action == "Add") {
                $_SESSION['success'] = '1';
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
            } else {
                $_SESSION['success'] = '1';
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
            }
        }
        header("Location:" . $backlink);
        exit;
    }
}
// for Edit
$eSystemType = "General";
$userEditDataArr = array();
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iCouponId = '" . $iCouponId . "'";
    $db_data = $obj->MySQLSelect($sql);
    $vPass = $generalobj->decrypt($db_data[0]['vPassword']);
    $vLabel = $id;
    if (count($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $vCouponCode = $value['vCouponCode'];
            $fDiscount = $value['fDiscount'];
            $eType = $value['eType'];
            $eValidityType = $value['eValidityType'];
            $dActiveDate = $value['dActiveDate'];
            $dExpiryDate = $value['dExpiryDate'];
            $iUsageLimit = $value['iUsageLimit'];
            $iUsed = $value['iUsed'];
            $eStatus = $value['eStatus'];
            $eSystemType = $value['eSystemType'];
            $eFly = $value['eFly'];
            $tDescription = (array) json_decode($value['tDescription']);
            foreach ($tDescription as $key4 => $value4) {
                $userEditDataArr[$key4] = $value4;
            }
            $vCurrencyDriver = $value['vCurrencyDriver'];
            $vPromocodeType = $value['vPromocodeType'];
        }
    }
}
//$ufxEnable = $generalobj->CheckUfxServiceAvailable(); // Added By HJ On 28-11-2019 For Check UberX Service Status
$ufxEnable = isUberXModuleAvailable() ? "Yes" : "No"; //add function to modules availibility
$rideEnable = isRideModuleAvailable() ? "Yes" : "No";
$deliveryEnable = isDeliveryModuleAvailable() ? "Yes" : "No";
$deliverallEnable = isDeliverAllModuleAvailable() ? "Yes" : "No";
$onlyDeliverallModule = strtoupper(ONLYDELIVERALL);
if($cubeDeliverallOnly > 0){
    $onlyDeliverallModule = "YES";
}
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title>Admin | PromoCode <?= $action; ?> </title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />

        <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <? include_once('global_files.php'); ?>
        <!-- On OFF switch -->
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />

    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53">
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?
            include_once('header.php');
            include_once('left_menu.php');
            ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2>
                                <?= $action; ?>
                                Promo Code
                                <?= $vName; ?>
                            </h2>
                            <a href="coupon.php">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a> </div>
                    </div>
                    <hr />
                    <? if ($success == 3) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                            <?php print_r($error); ?>
                        </div>
                        <br/>
                    <? } ?>
                    <div class="body-div coupon-action-part">
                        <div class="form-group"> 
                            <span style="color:red; font-size:small;" id="coupon_status"></span>

                            <form name="_coupon_form" id="_coupon_form" method="post" action="" enctype="multipart/form-data" class="">
                                <input type="hidden" name="iCouponId" value="<?php
                                if (isset($db_data[0]['iCouponId'])) {
                                    echo $db_data[0]['iCouponId'];
                                }
                                ?>">
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="coupon.php"/>
                                <input type="hidden" name="vCouponCodeval" id="vCouponCodeval" value="<?= $vCouponCode; ?>"/>

                                <div class="row coupon-action-n1">
                                    <div class="col-lg-12">
                                        <label>Coupon Code :<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vCouponCode" <?php
                                        if ($action == 'Edit') {
                                            echo "readonly";
                                        } else {
                                            ?>  <? } ?> id="vCouponCode" value="<?= $vCouponCode; ?>" placeholder="Coupon Code">
                                               <?php
                                               if ($action == 'Edit') {
                                                   
                                               } else {
                                                   ?>
                                            <a style="margin: 0 !important;" class="btn btn-sm btn-info" onClick="randomStringToInput(this)">Generate Coupon Code</a>
                                        <?php } ?>
                                    </div>
                                </div>
                                <?php
                                if (count($db_master) > 0) {
                                    for ($d = 0; $d < count($db_master); $d++) {
                                        $vCode = $db_master[$d]['vCode'];
                                        $vTitle = $db_master[$d]['vTitle'];
                                        $eDefault = $db_master[$d]['eDefault'];
                                        $descVal = 'tDescription_' . $vCode;
                                        $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                        ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Description (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <textarea name="<?= $descVal; ?>" rows="5" cols="40" class="form-control" id="<?= $descVal; ?>" placeholder="<?= $vTitle; ?> Value" ><?= $userEditDataArr[$descVal]; ?></textarea>
                                            </div>
                                            <? if ($vCode == $default_lang && count($db_master) > 1) { ?>
                                                <div class="col-lg-6">
                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tDescription');">Convert To All Language</button>
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>

                                <div class="row coupon-action-n2">
                                    <div class="col-lg-12">
                                        <label>Discount :<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" onkeypress="return isNumberKey(event)" class="form-control" name="fDiscount" id="fDiscount" value="<?= $fDiscount; ?>" placeholder="Discount">
                                        <select id="eType" name="eType" class="form-control">
                                            <option value="percentage" <?php if ($db_data[0]['eType'] == "percentage") { ?> selected <?php } ?> >%</option>
                                            <option value="cash" <?php if ($db_data[0]['eType'] == "cash") { ?>selected <?php } ?> >Flat Amount (In <?= $defaultCurrency; ?>) </option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Validity :<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="radio" name="eValidityType" onClick="showhidedate(this.value)" value="Permanent"
                                               <?php if ($db_data[0]['eValidityType'] == "Permanent") { ?> checked <?php } ?> >
                                        Permanent
                                        <input class="coup-act1" type="radio" name="eValidityType" onClick="showhidedate(this.value)" value="Defined" <?php if ($db_data[0]['eValidityType'] == "Defined") { ?> checked <?php } ?> >
                                        Custom</div>
                                </div>

                                <div class="row">
                                          <div class="col-lg-12">
                                              <label><?= $langage_lbl_admin['LBL_PROMOCODE_TYPE']; ?> <span class="red">*</span> <i data-html="true" class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title=" Public : If the Admin User selects PromoCode Type as â€œPublicâ€? then all the User in entire system would be able to see the respective PromoCode in the apps while trying to apply the PromoCode.<br/> Private : If the Admin User selects PromoCode Type as â€œPrivateâ€? the respective PromoCode would not be visible to the in the apps while trying to apply the PromoCode. However, if the admin shares the private PromoCode with any of the user by any mode that promocode would be applied if itâ€™s a valid. "></i></label>
                                          </div>
                                          <div class="col-lg-6" >
                                                <input type="radio"  name="vPromocodeType"  id="vPromocodeType"  value="Public"  <?php if ($vPromocodeType == "Public") echo 'checked="checked"'; ?> >  Public 
                                                <input class="coup-act1" type="radio"  name="vPromocodeType"   id="vPromocodeType" value="Private"  <?php if ($vPromocodeType == "Private") echo 'checked="checked"'; ?> > Private
                                          </div>
                                </div>


                                <div class="row" id="date1" style="display:none;">
                                    <div class="col-lg-12" >
                                        <label>Activation Date :<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" style="float: left;margin-right: 10px; width:45%; cursor: pointer;background: #fff;" class="form-control" name="dActiveDate"  id="dActiveDate" value="<?= $dActiveDate ?>" placeholder="Activation Date" readonly>
                                    </div>
                                </div>
                                <div class="row" id="date2" style="display:none;">	
                                    <div class="col-lg-12">
                                        <label>Expiry Date:<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" style="float: left;margin-right: 10px; width:45%;cursor: pointer;background: #fff;" class="form-control" name="dExpiryDate" value="<?= $dExpiryDate ?>"  id="dExpiryDate" placeholder="Expiry Date" readonly>
                                    </div>
                                </div>
                                <div class="row coupon-action-n3">
                                    <div class="col-lg-12">
                                        <label>Usage Limit <span class="red" > *</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Promo code can be used one time only for each user. So if you set Usage limit to 100 then 100 unique user can use this promo code."></i></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="number" id="iUsageLimit" value="<?= $iUsageLimit ?>"  name="iUsageLimit"  placeholder="Usage Limit" class="form-control" onKeyup="checkuserlimit(this.value);" />
                                        <div id="iUsageLimitmsg"></div>
                                    </div>             

                                </div>
                                <div class="row coupon-action-n3">
                                    <div class="col-lg-12">
                                        <label>Status<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select id="eStatus" name="eStatus" class="form-control ">
                                            <option value="Active" <?php if ($db_data[0]['eStatus'] == "Active") { ?>selected <?php } ?> >Active</option>
                                            <option value="Inactive" <?php if ($db_data[0]['eStatus'] == "Inactive") { ?>selected <?php } ?> >Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <? if (($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery') && $onlyDeliverallModule == "NO") { ?>
                                    <div class="row coupon-action-n3">
                                        <div class="col-lg-12">
                                            <label>System Type<span class="red"> *</span></label>
                                        </div>
                                        <?php if($eFly=='1') { ?>
                                            <input type="hidden" name="couponsystem" value="Fly">
                                        <?php } else { ?>
                                            <input type="hidden" name="couponsystem" value="<?= $eSystemType; ?>">
                                        <?php } ?>
                                        <div class="col-lg-6">
                                            <select <? if ($action == 'Edit') { ?> disabled=""<? } ?> id="eSystemType" name="eSystemType" class="form-control ">
                                                <option value="General" <?php if ($eSystemType == "General") { ?>selected <?php } ?> >General</option>
                                                <? if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery') {
                                                    if($rideEnable == "Yes") { ?>
                                                    <option value="Ride" <?php if ($eSystemType == "Ride" && $eFly=='0') { ?>selected <?php } ?> >Ride</option>
                                                    <? } if($deliveryEnable == "Yes") { ?>
                                                    <option value="Delivery" <?php if ($eSystemType == "Delivery") { ?>selected <?php } ?> >Delivery</option>
                                                <? } } ?>
                                                <? if ($APP_TYPE == 'Ride-Delivery-UberX' && $ufxEnable == "Yes") { ?>
                                                    <option value="UberX" <?php if ($eSystemType == "UberX") { ?>selected <?php } ?> >UberX</option>
                                                <? } ?>
                                                <? if (DELIVERALL == "Yes" && $deliverallEnable == "Yes") { ?>
                                                    <option value="DeliverAll" <?php if ($eSystemType == "DeliverAll") { ?>selected <?php } ?> >DeliverAll</option>
                                                <? } ?>
				                                    <? if ($flyEnable == "Yes") { ?>
                                                    <option value="Fly" <?php if ($eSystemType == "Ride" && $eFly=='1') { ?>selected <?php } ?> >Fly</option>
                                                <? } ?>
                                            </select>
                                        </div>
                                    </div>
                                <? } else if ($onlyDeliverallModule == "YES") { ?>
                                    <input type="hidden" name="eSystemType" class="form-control" id="eSystemType" value="DeliverAll">
                                <? } else { ?>
                                    <input type="hidden" name="eSystemType" class="form-control" id="eSystemType" value="<?= $APP_TYPE; ?>">
                                <? } ?>

                                <div class="row coupon-action-n4">
                                    <div class="col-lg-12">
                                        <?php if (($action == 'Edit' && $userObj->hasPermission('edit-promocode')) || ($action == 'Add' && $userObj->hasPermission('create-promocode'))) { ?>
                                            <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?php if ($action == 'Add') { ?><?= $action; ?> PromoCode<?php } else { ?>Update<?php } ?>">
                                            <input type="reset" value="Reset" class="btn btn-default">
                                        <?php } ?>
<!-- 				<a href="javascript:void(0);" <?php if ($action == 'Edit') { ?> onClick="reset_form('_coupon_form'),reset_CouponCode();" <?php } else { ?> onClick="reset_form('_coupon_form');"  <? } ?>  class="btn btn-default">Reset</a> -->
                                        <a href="coupon.php" class="btn btn-default back_link">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
        <div class="row loding-action" id="imageIcon" style="display:none;">
            <div align="center">                                                                       
                <img src="default.gif">                                                              
                <span>Language Translation is in Process. Please Wait...</span>                       
            </div>                                                                                 
        </div>
        <? include_once('footer.php'); ?>
        <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
        <script>
                                            $('[data-toggle="tooltip"]').tooltip();
                                            function validate_coupon(username)
                                            {
                                                var request = $.ajax({
                                                    type: "POST",
                                                    url: 'ajax_validate_coupon.php',
                                                    data: 'vCouponCode=' + username,
                                                    success: function (data)
                                                    {
                                                        if (data == 0)
                                                        {
                                                            $('#coupon_status').html('<i class="icon icon-remove alert-danger alert"> 	Coupon Code Already Exist</i>');
                                                            $('input[type="submit"]').attr('disabled', 'disabled');
                                                            return false;
                                                        } else if (data == 1)
                                                        {
                                                            $('#coupon_status').html('<i class="icon icon-ok alert-success alert"> Valid</i>');
                                                            $('vCouponCode[type="submit"]').removeAttr('disabled');
                                                        } else if (data == 2)
                                                        {
                                                            $('#coupon_status').html('<i class="icon icon-remove alert-danger alert"> Please Enter Coupon Code</i>');
                                                            $('vCouponCode[type="submit"]').removeAttr('disabled');
                                                        }
                                                    }
                                                });
                                            }
        </script>

        <?php if ($action == 'Edit') { ?>
            <script>
                window.onload = function () {
                    showhidedate('<?php echo $eValidityType; ?>');
                };
            </script>
        <? } else { ?>
            <script>
                window.onload = function () {
                    $('input:radio[name=eValidityType][value=Permanent]').attr('checked', true);
                };
            </script>
        <?php } ?>
        <script type='text/javascript' src='../assets/js/jquery-ui.min.js'></script>

        <script type="text/javascript">
            var adt = $("#dActiveDate").val();
            if (adt == '0000-00-00')
            {
                $("#dActiveDate").datepicker({
                    minDate: 0, //for avoid previous dates
                    numberOfMonths: 1,
                    dateFormat: "yy-mm-dd",
                    onSelect: function (selected) {
                        var dt = new Date(selected);
                        //dt.setDate(dt.getDate() + 1);
                        dt.setDate(dt.getDate());
                        $("#dExpiryDate").datepicker("option", "minDate", dt);
                    }
                }).val('');

                $("#dExpiryDate").datepicker({
                    minDate: 0,
                    numberOfMonths: 1,
                    dateFormat: "yy-mm-dd",
                    onSelect: function (selected) {
                        var dt = new Date(selected);
                        //dt.setDate(dt.getDate() - 1);
                        dt.setDate(dt.getDate());
                        $("#dActiveDate").datepicker("option", "maxDate", dt);
                    }
                }).val('');
            } else
            {
                $("#dActiveDate").datepicker({
                    minDate: 0, //for avoid previous dates
                    numberOfMonths: 1,
                    dateFormat: "yy-mm-dd",
                    onSelect: function (selected) {
                        var dt = new Date(selected);
                        // dt.setDate(dt.getDate() + 1);
                        dt.setDate(dt.getDate());
                        $("#dExpiryDate").datepicker("option", "minDate", dt);
                    }
                });

                $("#dExpiryDate").datepicker({
                    minDate: 0,
                    numberOfMonths: 1,
                    dateFormat: "yy-mm-dd",
                    onSelect: function (selected) {
                        var dt = new Date(selected);
                        //dt.setDate(dt.getDate() - 1);
                        dt.setDate(dt.getDate());
                        $("#dActiveDate").datepicker("option", "maxDate", dt);
                    }
                });
            }
            function showhidedate(val) {
                if (val == "Defined") {
                    document.getElementById("date1").style.display = '';
                    document.getElementById("date2").style.display = '';
                    document.getElementById("dActiveDate").lang = '*';
                    document.getElementById("dExpiryDate").lang = '*';
                } else {
                    document.getElementById("date1").style.display = 'none';
                    document.getElementById("date2").style.display = 'none';
                    document.getElementById("dActiveDate").required = false;
                    document.getElementById("dExpiryDate").required = false;

                    document.getElementById("dActiveDate").lang = '';
                    document.getElementById("dExpiryDate").lang = '';
                }
            }

            function randomStringToInput(clicked_element)
            {
                var self = $(clicked_element);
                var random_string = generateRandomString(6);
                $('input[name=vCouponCode]').val(random_string);

            }
            function generateRandomString(string_length)
            {
                var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                var string = '';
                for (var i = 0; i <= string_length; i++)
                {
                    var rand = Math.round(Math.random() * (characters.length - 1));
                    var character = characters.substr(rand, 1);
                    string = string + character;
                }
                return string;
            }

            $(document).ready(function () {
                var referrer;
                if ($("#previousLink").val() == "") {
                    referrer = document.referrer;
                } else {
                    referrer = $("#previousLink").val();
                }
                if (referrer == "") {
                    referrer = "coupon.php";
                } else {
                    $("#backlink").val(referrer);
                }
                $(".back_link").attr('href', referrer);
            });

            function checkuserlimit(userlimit)
            {
                if (userlimit != "") {
                    if (userlimit == 0)
                    {
                        $('#iUsageLimitmsg').html('<i class="icon icon-remove alert-danger alert">You Can Not Enter Zero Number</i>');
                        $('input[type="submit"]').attr('disabled', 'disabled');
                    } else if (userlimit <= 0) {
                        $('#iUsageLimitmsg').html('<i class="icon icon-remove alert-danger alert">You Can Not Enter Negative Number</i>');
                        $('input[type="submit"]').attr('disabled', 'disabled');
                    } else {
                        $('#iUsageLimitmsg').html('');
                        $('input[type="submit"]').removeAttr('disabled');
                    }
                } else {
                    $('#iUsageLimitmsg').html('');
                }

            }

            function reset_CouponCode() {
                var vCouponCodeval = $('#vCouponCodeval').val();
                $('#vCouponCode').val(vCouponCodeval);
            }
            function getAllLanguageCode(textBoxId) {
                var def_lang = '<?= $default_lang ?>';
                var def_lang_name = '<?= $def_lang_name ?>';
                var getEnglishText = $('#' + textBoxId + '_' + def_lang).val();
                var error = false;
                var msg = '';

                if (getEnglishText == '') {
                    msg += '<div class="alert alert-danger fade in"><a href="#" class="close" data-dismiss="alert"><icon class="fa fa-close"></icon></a><strong>Please Enter ' + def_lang_name + ' Value</strong></div> <br>';
                    error = true;
                }

                if (error == true) {
                    $('#errorMessage').html(msg);
                    return false;
                } else {
                    $('#imageIcon').show();
                    $.ajax({
                        url: "ajax_get_all_language_translate.php",
                        type: "post",
                        data: {'englishText': getEnglishText},
                        dataType: 'json',
                        success: function (response) {
                            $.each(response, function (name, Value) {
                                var key = name.split('_');
                                $('#' + textBoxId + '_' + key[1]).val(Value);
                            });
                            $('#imageIcon').hide();
                        }
                    });
                }
            }
            function isNumberKey(evt)
            {
                var charCode = (evt.which) ? evt.which : event.keyCode
                if (charCode > 47 && charCode < 58 || charCode == 46 || charCode == 127 || charCode == 8)
                    return true;
                return false;
            }
        </script>
        <?php if ($action != 'Edit') { ?>
            <script>
                //randomStringToInput(document.getElementById("vCouponCode"));
            </script>
        <?php } ?>
    </body>
</html>