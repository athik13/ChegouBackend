<?php
include_once('../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();


$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$script = 'Delivery Charges';

$tbl_name = 'delivery_charges';
$tbl_name1 = 'location_master';

$iLocationId = isset($_POST['iLocationId']) ? $_POST['iLocationId'] : '';
$fOrderPriceValue = isset($_POST['fOrderPriceValue']) ? $_POST['fOrderPriceValue'] : '';
$fDeliveryChargeAbove = isset($_POST['fDeliveryChargeAbove']) ? $_POST['fDeliveryChargeAbove'] : '';
$fDeliveryChargeBelow = isset($_POST['fDeliveryChargeBelow']) ? $_POST['fDeliveryChargeBelow'] : '';
$fFreeOrderPriceSubtotal = isset($_POST['fFreeOrderPriceSubtotal']) ? $_POST['fFreeOrderPriceSubtotal'] : '';
$iFreeDeliveryRadius = isset($_POST['iFreeDeliveryRadius']) ? $_POST['iFreeDeliveryRadius'] : '';

$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

if (isset($_POST['submitbtn'])) {
    if ($action == "Add" && !$userObj->hasPermission('create-delivery-charges')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create Delivery Charges.';
        header("Location:delivery_charges.php");
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission('edit-delivery-charges')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update Delivery Charges.';
        header("Location:delivery_charges.php");
        exit;
    }
    //Start :: Upload Image Script
    if (!empty($id)) {
        if (SITE_TYPE == 'Demo') {
            $_SESSION['success'] = 2;
            header("Location:delivery_charges.php.php?id=" . $id);
            exit;
        }
    }

    $q = "INSERT INTO ";
    $where = '';

    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `iDeliveyChargeId` = '" . $id . "'";
    }

    $query = $q . " `" . $tbl_name . "` SET
      `iLocationId` = '" . $iLocationId . "',
      `fOrderPriceValue` = '" . $fOrderPriceValue . "',              
      `fDeliveryChargeAbove` = '" . $fDeliveryChargeAbove . "',                         
      `fDeliveryChargeBelow` = '" . $fDeliveryChargeBelow . "',
      `fFreeOrderPriceSubtotal` = '" . $fFreeOrderPriceSubtotal . "',          
      `iFreeDeliveryRadius` = '" . $iFreeDeliveryRadius . "'" . $where;

    $obj->sql_query($query);

    $id = ($id != '') ? $id : $obj->GetInsertId();

    if ($action == "Add") {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    } else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    }

    header("Location:" . $backlink);
    exit;
}
// for Edit
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iDeliveyChargeId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);

    if (count($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $iLocationId = $value['iLocationId'];
            $fOrderPriceValue = $value['fOrderPriceValue'];
            $fDeliveryChargeAbove = $value['fDeliveryChargeAbove'];
            $fDeliveryChargeBelow = $value['fDeliveryChargeBelow'];
            $fFreeOrderPriceSubtotal = $value['fFreeOrderPriceSubtotal'];
            $iFreeDeliveryRadius = $value['iFreeDeliveryRadius'];
            
            //added by SP 27-06-2019 for remove validation to leave blank
            if($fFreeOrderPriceSubtotal==0) $fFreeOrderPriceSubtotal = '';
            if($iFreeDeliveryRadius==0) $iFreeDeliveryRadius = '';
        }
    }
}

$query = "SELECT vLocationName,iLocationId FROM " . $tbl_name1 . " WHERE eFor = 'UserDeliveryCharge' AND eStatus = 'Active'";

$db_location = $obj->MySQLSelect($query);
$sql = "select vName,vSymbol from currency where eDefault = 'Yes'";
$db_currency = $obj->MySQLSelect($sql);
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title>Admin | Delivery Charges  <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="css/bootstrap-select.css" rel="stylesheet" />
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
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
            <? include_once('header.php'); ?>
            <? include_once('left_menu.php'); ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2><?= $action . " Delivery Charges"; ?> </h2>
                            <a class="back_link" href="delivery_charges.php">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />
                    <? if ($success == 2) { ?>
                        <div class="alert alert-danger alert-dismissable msgs_hide">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                            <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                        </div><br/>
                    <? } ?>
                    <div class="body-div">
                        <div class="form-group location-wise-box">
                            <? if ($success == 1) { ?>
                                <div class="alert alert-success alert-dismissable msgs_hide">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                    <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                                </div><br/>
                            <? } ?>
                            <form method="post" action="" enctype="multipart/form-data" id="deliveryChargeForm">
                                <input type="hidden" name="id" value="<?= $id; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="delivery_charges.php"/>

                                <div class="row">                   
                                    <div class="col-lg-12">
                                        <label>Select Location <span class="red"> *</span><i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="You can define the delivery charges for specific location, so this charges would be applicable to the <?php echo strtolower($langage_lbl_admin['LBL_RIDERS_ADMIN']); ?> based on their location."></i></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select name="iLocationId" class="form-control" required="required" onchange="checkdeliveryareaexist(this.value)">
                                            <option value="">Select Location</option>
                                                      <option value="0" <?php if($iLocationId == '0') {echo "selected";} ?>>All Location</option>
                                            <?php foreach ($db_location as $key => $value) { ?>
                                                <option value="<?php echo $value['iLocationId'] ?>" <?php if ($value['iLocationId'] == $iLocationId) {   echo "selected"; }?>>
                                                    <?php echo $value['vLocationName'] ?></option>
                                                <?php } ?>
                                        </select>
                                    </div>
                                    <?php if ($userObj->hasPermission('view-geo-fence-locations')) { ?>
                                        <div class="col-lg-6">
                                            <a class="btn btn-primary" href="location.php" target="_blank">Enter New Location</a>
                                        </div>
                                    <?php } ?>
                                    <div class="clear"></div>
                                    <div class="col-lg-12 deliverycharge_area">
                                        <div class="exist_area error"></div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                                    <label> Order Total(Price In <?=$db_currency[0]['vName']?>)<span class="red">*</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='The value you define here will be treated as a base value based on which the delivery charges would be applicable. This price must be in USD.
For E.g.: If you define $5 as a value under "Order Total" field and if you wanted to apply the delivery charges if the value of order is greater or lesser than the value defined in order total.'></i></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="fOrderPriceValue"  id="fOrderPriceValue" value="<?= $fOrderPriceValue; ?>" required="required">

                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                                    <label> Delivery charge applicable on order amount greater than order total (Price In <?=$db_currency[0]['vName']?>)<span class="red">*</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='This charges would be applicable if the order value is greater than the order total
For E.g.: If you define $5 as a value under "Order Total" field and if the value of order is greater than $5, then delivery fees defined in this field would be applicable to the <?php echo strtolower($langage_lbl_admin['LBL_RIDER']); ?>.'></i></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="fDeliveryChargeAbove"  id="fDeliveryChargeAbove" value="<?= $fDeliveryChargeAbove; ?>" required="required">

                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                                    <label> Delivery charge applicable on order amount lesser than order total (Price In <?=$db_currency[0]['vName']?>)<span class="red">*</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='This charges would be applicable if the order value is lesser than the order total
For E.g.: If you define $5 as a value under "Order Total" field and if the value of order is lesser than $5, then delivery fees defined in this field would be applicable to the <?php echo strtolower($langage_lbl_admin['LBL_RIDER']); ?>.'></i></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="fDeliveryChargeBelow"  id="fDeliveryChargeBelow" value="<?= $fDeliveryChargeBelow; ?>" required="required">

                                    </div>
                                </div> 
                                <div class="row">
                                    <div class="col-lg-12">
                                                    <label>  Free delivery for order amount above (Price In <?=$db_currency[0]['vName']?>) <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Ex. Free delivery on all orders above $50'></i></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="fFreeOrderPriceSubtotal"  id="fFreeOrderPriceSubtotal" value="<?= $fFreeOrderPriceSubtotal; ?>"><?php //added by SP 27-06-2019 for remove validation to leave blank ?>

                                    </div>
                                </div>                                                                    
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>  Free delivery radius <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='No delivery charges would be applicable if the delivery radius is within the specified range.
For E.g.: If you define 5km as a value under "Free delivery radius" field and if the delivery radius is within the 5km then in that <?php echo strtolower($langage_lbl_admin['LBL_RIDER']); ?> is eligible for the free delivery.'></i></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="iFreeDeliveryRadius" id="iFreeDeliveryRadius" value="<?= $iFreeDeliveryRadius; ?>"><?php //added by SP 27-06-2019 for remove validation to leave blank ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <?php if (($action == 'Edit' && $userObj->hasPermission('edit-delivery-charges')) || ($action == 'Add' && $userObj->hasPermission('create-delivery-charges'))) { ?>
                                            <input type="submit" class="save btn-info" name="submitbtn" id="submitbtn" value="<?php  if($action=='Add'){?><?= $action; ?> Delivery Charges<?php } else{ ?>Update<?php } ?>">
<?php } ?>
                                        <a href="delivery_charges.php" class="btn btn-default back_link">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- <div class="admin-notes">
                            <h4>Notes:</h4>
                            <ul>
                             <li>
                                The commission for Flat Fare is same which is set for the selected vehicle type here.
                              </li>
                            </ul>
                      </div> -->
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
<? include_once('footer.php'); ?>
        <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
        <script src="js/bootstrap-select.js"></script>
        <script>

            $(document).ready(function () {
                $(window).keydown(function (event) {
                    if (event.keyCode == 13) {
                        event.preventDefault();
                        return false;
                    }
                });
            });

            var successMSG1 = '<?php echo $success; ?>';

            if (successMSG1 != '') {
                setTimeout(function () {
                    $(".msgs_hide").hide(1000)
                }, 5000);
            }

            $(document).ready(function () {
                var referrer;
                if ($("#previousLink").val() == "") {
                    referrer = document.referrer;
                    //alert(referrer);
                } else {
                    referrer = $("#previousLink").val();
                }
                if (referrer == "") {
                    referrer = "delivery_charges.php";
                } else {
                    $("#backlink").val(referrer);
                }
                $(".back_link").attr('href', referrer);

                // jquery validation
                $('#deliveryChargeForm').validate({
                    rules: {
                        iLocationId: {
                            required: true
                        },
                        fOrderPriceValue: {
                            required: true,
                            number: true
                        },
                        fDeliveryChargeAbove: {
                            required: true,
                            number: true
                        },
                        fDeliveryChargeBelow: {
                            required: true,
                            number: true
                        },
                        //added by SP 27-06-2019 for remove validation to leave blank
//                        fFreeOrderPriceSubtotal: {
//                            required: true,
//                            number: true
//                        },
//                        iFreeDeliveryRadius: {
//                            required: true,
//                            number: true
//                        }
                    },
                    messages: {
                        iLocationId: {
                            required: 'Please Select From Location.'
                        },
                        fOrderPriceValue: {
                            required: 'Please Add Order Amount.'
                        }
                    }
                });

            });
        function checkdeliveryareaexist(iLocationId) {
            var deliverycharge_id = "";
            <?php if(!empty($id)) { ?>
            deliverycharge_id = <?php echo $id ?>;
            <?php } ?>
            var request = $.ajax({
                type: "POST",
                url: 'ajax_check_deliverycharge_area.php',
                data: 'iLocationId=' + iLocationId + '&deliverycharge_id='+ deliverycharge_id,
                success: function (data)
                {
                    if(data > 0) {
                        $('.deliverycharge_area').css('padding-top','15px');
                        $( "div.exist_area" ).html("Please Check, This delivery charges Area Already Selected.");
                        $('input[type="submit"]').attr('disabled','disabled');
                    } else {
                        $('.deliverycharge_area').css('padding-top','0px');
                        $( "div.exist_area" ).html("");
                        $('input[type="submit"]').removeAttr('disabled');
                    }
                }
            });
        }
        </script>
    </body>
    <!-- END BODY-->
</html>

