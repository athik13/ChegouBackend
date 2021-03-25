<?php
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();

$eSystem = "DeliverAll";

$sql = "SELECT iCountryId,vCountry,vCountryCode FROM country WHERE eStatus = 'Active'";
$db_country = $obj->MySQLSelect($sql);

$sqllocation = "SELECT * FROM location_master WHERE eStatus = 'Active' AND eFor = 'VehicleType' ORDER BY  vLocationName ASC ";
$db_location = $obj->MySQLSelect($sqllocation);

/* to fetch max iDisplayOrder from table for insert */
$select_order   = $obj->MySQLSelect("SELECT count(iDisplayOrder) AS iDisplayOrder FROM vehicle_type where eType ='".$eSystem."'");
$iDisplayOrder  = isset($select_order[0]['iDisplayOrder'])?$select_order[0]['iDisplayOrder']:0;
$iDisplayOrder_max  = $iDisplayOrder + 1; // Maximum order number

$message_print_id = $id;
$tbl_name = 'vehicle_type';
$script = 'StoreVehicleType';

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$vVehicleType = isset($_POST['vVehicleType']) ? $_POST['vVehicleType'] : '';
$iLocationId = isset($_POST['iLocationId']) ? $_POST['iLocationId'] : '';
$fDeliveryCharge = isset($_POST['fDeliveryCharge']) ? $_POST['fDeliveryCharge'] : '';
$fDeliveryChargeCancelOrder = isset($_POST['fDeliveryChargeCancelOrder']) ? $_POST['fDeliveryChargeCancelOrder'] : '';
$fRadius= isset($_POST['fRadius']) ? $_POST['fRadius'] : '';

$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
//  for ordering
$iDisplayOrder  = isset($_POST['iDisplayOrder'])?$_POST['iDisplayOrder']:$iDisplayOrder;
$temp_order     = isset($_POST['temp_order'])? $_POST['temp_order'] : "";


$vTitle_store = array();
$sql = "SELECT * FROM `language_master` where eStatus='Active' ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = count($db_master);
if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
        $vValue = 'vVehicleType_' . $db_master[$i]['vCode'];
        array_push($vTitle_store, $vValue);
        $$vValue = isset($_POST[$vValue]) ? $_POST[$vValue] : '';
    }
}

if (isset($_POST['btnsubmit'])) {

    if($action == "Add" && !$userObj->hasPermission('create-vehicle-type')){
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create '.strtolower($langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']);
        header("Location:store_vehicle_type.php");
        exit;
    }

    if($action == "Edit" && !$userObj->hasPermission('edit-vehicle-type')){
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update '.strtolower($langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']);
        header("Location:store_vehicle_type.php");
        exit;
    }


    if (SITE_TYPE == 'Demo') {
        header("Location:store_vehicle_type_action.php?id=" . $id . "&success=2");
        exit;
    }

    if($temp_order == "1" && $action == "Add"){
        $temp_order = $iDisplayOrder_max;
    }
    if($temp_order > $iDisplayOrder) { 
        for($i = $temp_order-1; $i >= $iDisplayOrder; $i--) { 
           $sql="UPDATE ".$tbl_name." SET iDisplayOrder = '".($i+1)."' WHERE iDisplayOrder = '".$i."' AND eType ='".$eSystem."'";
            $obj->sql_query($sql);
        }
    } else if($temp_order < $iDisplayOrder) {
        for($i = $temp_order+1; $i <= $iDisplayOrder; $i++) {
            $sql="UPDATE ".$tbl_name." SET iDisplayOrder = '".($i-1)."' WHERE iDisplayOrder = '".$i."' AND eType ='".$eSystem."'";
            $obj->sql_query($sql);
        }
    }

    $q = "INSERT INTO ";
    $where = '';
    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `iVehicleTypeid` = '" . $id . "'";
    }
    $sql_str = '';
    if(count($vTitle_store) > 0){
        for ($i = 0; $i < count($vTitle_store); $i++) {
            $vValue = 'vVehicleType_' . $db_master[$i]['vCode'];
            $sql_str .= $vValue." = '".$_POST[$vTitle_store[$i]]."',";
        }
    }
    
    $query = $q . " `" . $tbl_name . "` SET
			`vVehicleType` = '" . $vVehicleType . "',
            `iLocationid` = '" . $iLocationId . "',
            `fDeliveryCharge` = '" . $fDeliveryCharge . "',
            `fDeliveryChargeCancelOrder` = '" . $fDeliveryChargeCancelOrder . "',
			`fRadius` = '" . $fRadius . "',
            `eType` = '" . $eSystem . "',
			" . $sql_str."
            `iDisplayOrder` = '".$iDisplayOrder."'"
            . $where;
    $obj->sql_query($query);
    $id = ($id != '') ? $id : $obj->GetInsertId();


    if ($action == "Add") {
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
        $_SESSION['success'] = "1";
        header("Location:store_vehicle_type.php");
        exit;
    } else {
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        $_SESSION['success'] = "1";
        header("Location:store_vehicle_type.php");
        exit;
    }
}

// for Edit
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iVehicleTypeid = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);

    $vLabel = $id;
    if (count($db_data) > 0) {
        for ($i = 0; $i < count($db_master); $i++) {

            foreach ($db_data as $key => $value) {
                $vValue = 'vVehicleType_' . $db_master[$i]['vCode'];
                $$vValue = $value[$vValue];
                $vVehicleType = $value['vVehicleType'];
                $fDeliveryCharge = $value['fDeliveryCharge'];
                $fDeliveryChargeCancelOrder = $value['fDeliveryChargeCancelOrder'];
                $fRadius = $value['fRadius'];
                $iLocationId = $value['iLocationid'];
                $iDisplayOrder_db = $value['iDisplayOrder'];
            }
        }
    }
}
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
    <title>Admin | <?php echo $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?> <?= $action; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
    <? include_once('global_files.php');   ?>
</head>
<!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <? include_once('header.php');
            include_once('left_menu.php');
            ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2> <?php echo $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?> </h2>
                             <a href="javascript:void(0);" class="back_link">
                                <input type="button" value="Back to Listing" class="add-btn">
                           </a>
                        </div>
                    </div>
                    <hr />
                    <div class="body-div">
                        <div class="form-group">
                            <? if ($success == 1) {?>
                            <div class="alert alert-success alert-dismissable msgs_hide">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                <?= $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                            </div><br/>
                            <? } elseif ($success == 2) { ?>
                            <div class="alert alert-danger alert-dismissable ">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                            </div><br/>
                            <? } else if ($success == 3) { ?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                <?php echo $_REQUEST['varmsg']; ?> 
                            </div><br/>	
                            <? } ?>
                            <? if($_REQUEST['var_msg'] !=Null ) { ?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button> Record  Not Updated .</div><br/>
                            <? } ?>                   
                            <form id="_vehicleType_form" name="_vehicleType_form" method="post" action="" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?= $id; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="store_vehicle_type.php"/>
								<div class="row"> 
                                    <div class="col-lg-12" id="errorMessage"></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label><?php echo $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?><span class="red"> *</span> 
                                            <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Please add if your vehicle type is "Hatchback" , "Sedan" , "SUV" , "Van" , Luxurious Car" etc'></i>
                                        </label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vVehicleType"  id="vVehicleType"  value="<?= $vVehicleType; ?>" required>
                                    </div>
                                </div>
                                <? if($count_all > 0) {
                                    for($i=0;$i<$count_all;$i++) {
                                        $vCode = $db_master[$i]['vCode'];
                                        $vTitle = $db_master[$i]['vTitle'];
                                        $eDefault = $db_master[$i]['eDefault'];

                                        $vValue = 'vVehicleType_'.$vCode;
                                        $required = ($eDefault == 'Yes')?'required':'';
                                        $required_msg = ($eDefault == 'Yes')?'<span class="red"> *</span>':''; ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?php echo $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?> (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" value="<?= $$vValue; ?>" placeholder="<?= $vTitle; ?>Value" <?= $required; ?>>
                                            </div>
            								    <? if($vCode == $default_lang  && count($db_master) > 1){ ?>
            										<div class="col-lg-6">
            											<button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode();">Convert To All Language</button>
            										</div>
            								    <?php } ?>
                                        </div>
                                    <? }
                                } ?>
                                <div class="row">
                                     <div class="col-lg-12">
                                          <label>Select Location <span class="red"> *</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Select the location in which you would like to appear this vehicle type. For example "Luxurious" vehicle type to appear for any specific city or state or may be for whole country. You can define these locations from "Manage Locations >> Geo Fence Location" section'></i></label>
                                     </div>
                                     <div class="col-lg-6">

                                        <select class="form-control" name = 'iLocationId' id="iLocationId" required="" onchange="changeCode_distance(this.value);">
                                            <option value="">Select Location</option>
                                            <option value="-1" <?if($iLocationId== "-1"){?> selected <? } ?>>All</option>
                                            <?php 
                                            foreach ($db_location as $i => $row) {
                                                if(count($userObj->locations) > 0 && !in_array($row['iLocationId'], $userObj->locations)){
                                                    continue;
                                                }
                                                ?>
                                                <option value = "<?= $row['iLocationId'] ?>" <?if($iLocationId == $row['iLocationId']){?>selected<? } ?>><?= $row['vLocationName'] ?></option>
                                            <?php } ?>
                                        </select>
                                     </div>

                                    <?php if($userObj->hasPermission('create-geo-fence-locations')){ ?>
                                         <div class="col-lg-6">
                                           <a class="btn btn-primary" href="location.php" target="_blank">Enter New Location</a>
                                        </div>
                                    <?php } ?>
                                </div>

                                <div class="row" id="hide-km">
                                    <div class="col-lg-12">
                                        <label><?php echo $langage_lbl_admin['LBL_DELIVERY_CHARGES_PER_ORDER_FOR_COMPLETED_ORDERS']; ?> (In <?=$db_currency[0]['vName']?>)<span class="red"> *</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='"Set the delivery charge for completed orders, as per type and location. E.q. $10 if Delivery is done by a car for location California."'></i></label>
                                    </div>
                                    
                                     <div class="col-lg-6">
                                        <input type="text" class="form-control" name="fDeliveryCharge"  id="fDeliveryCharge" value="<?= $fDeliveryCharge; ?>" >
                                    </div>

                                </div>

                                <div class="row" id="hide-km">
                                    <div class="col-lg-12">
                                        <label><?php echo $langage_lbl_admin['LBL_DELIVERY_CHARGES_PER_ORDER_FOR_CANCELLED_ORDERS']; ?> (In <?=$db_currency[0]['vName']?>)<span class="red"> *</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='"Set the minimum delivery charge for canceled orders, as per type and location. E.q. $5 if a <?php echo strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']); ?> was on-route by a car for location California."'></i></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="fDeliveryChargeCancelOrder"  id="fDeliveryChargeCancelOrder" value="<?= $fDeliveryChargeCancelOrder; ?>" >
                                    </div>

                                </div>

                                <div class="row" id="hide-price">
                                    <div class="col-lg-12">
                                        <label><?php echo $langage_lbl_admin['LBL_DELIVERY_RADIUS']; ?><span class="red"> *</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Driver will get the order request for this vehicle type for the specified range. E.g. if the type is Cycle then get request within 2 KM'></i></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="fRadius"  id="fRadius" value="<?= $fRadius; ?>" >
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Display Order</label>
                                    </div>
                                    <div class="col-lg-6">
                                        
                                        <input type="hidden" name="temp_order" id="temp_order" value="<?=($action == 'Edit') ? $iDisplayOrder_db : '1';?>">
                                        <?
                                            $display_numbers = ($action=="Add") ? $iDisplayOrder_max : $iDisplayOrder;
                                        ?>
                                        <select name="iDisplayOrder" class="form-control">
                                            <? for($i=1; $i <= $display_numbers; $i++){ ?>
                                                <option value="<?=$i?>" <?if($i == $iDisplayOrder_db){echo "selected";}?>> -- <?=$i?> --</option>
                                            <? } ?>
                                        </select>
                                        
                                    </div>
                                </div>
            					<div class="col-lg-12">
                                    <?php if(($action == 'Edit' && $userObj->hasPermission('edit-vehicle-type')) || ($action == 'Add' &&  $userObj->hasPermission('create-vehicle-type'))){ ?>
                                        <input type="submit" class="btn btn-default" name="btnsubmit" id="btnsubmit" value="<?= $action; ?> Vehicle Type" >
                                        <input type="reset" value="Reset" class="btn btn-default">
                                    <?php } ?>
                                    <a href="store_vehicle_type.php" class="btn btn-default back_link">Cancel</a>
                                </div>          
                            </form>
                        </div>      
                    </div>
                </div>
                <div style="clear:both;"></div>
            </div>
        </div>
        <!--END PAGE CONTENT -->
        <!--END MAIN WRAPPER -->
        <div class="row loding-action" id="imageIcon" style="display:none;">
            <div align="center">                                                                       
                <img src="default.gif">                                                              
                <span>Language Translation is in Process. Please Wait...</span>                       
            </div>                                                                                 
        </div>
		<? include_once('footer_vehicleType.php');  ?>
        <script type="text/javascript" src="js/validation/jquery.validate.min.js" ></script>
        <script type="text/javascript" src="js/validation/additional-methods.min.js" ></script>
        <script>
        // just for the demos, avoids form submit
        if (_system_script == 'VehicleType') {
        if ($('#_vehicleType_form').length !== 0) {
            $( "#_vehicleType_form" ).validate({
              rules: {
                fDeliveryCharge: {
                    required:true,
                    number: true,
                    min: 0
                },
                fDeliveryChargeCancelOrder: {
                    required:true,
                    number: true,
                    min: 0
                },
                fRadius: {
                    required:true,
                    number: true,
                    min: 0
                }
              }
            });
        }
    }
        jQuery.extend(jQuery.validator.messages, {
            number: "Please enter a valid number.",
            min: jQuery.validator.format("Please enter a value greater than 0.")
        });
        </script>		
		<script>
			$('[data-toggle="tooltip"]').tooltip();
			var successMSG1 = '<?php echo $success; ?>';
			if (successMSG1 != '') {
				setTimeout(function () {
					$(".msgs_hide").hide(1000)
				}, 5000);
			}
        </script>
        <!--For Faretype End--> 
        <script>
			function changeCode_distance(id) {
				$.ajax({
					type: "POST",
					url: 'ajax_get_unit.php',
					data: {id: id},
					success: function (dataHTML2)
					{
						if(dataHTML2 != null)
							$("#change_eUnit").text(dataHTML2);
					}
				});
			}
			changeCode_distance('<?=$iLocationId?>');
        </script>
        <script type="text/javascript" language="javascript">
        function getAllLanguageCode(){
              var def_lang = '<?=$default_lang?>';
        	  var def_lang_name = '<?=$def_lang_name?>';
              var getEnglishText = $('#vVehicleType_'+def_lang).val();
              var error = false;
              var msg = '';
              
              if(getEnglishText==''){
                  msg += '<div class="alert alert-danger fade in"><a href="#" class="close" data-dismiss="alert"><icon class="fa fa-close"></icon></a><strong>Please Enter '+def_lang_name+' Value</strong></div> <br>';
                  error = true;
              }
              
              if(error==true){
                      $('#errorMessage').html(msg);
                      return false;
              }else{
                $('#imageIcon').show();
                $.ajax({
                        url: "ajax_get_all_language_translate.php",
                        type: "post",
                        data: {'englishText':getEnglishText},
                        dataType:'json',
                        success:function(response){
        					// $("#vVehicleType_EN").val(getEnglishText);
                             $.each(response,function(name, Value){
                                var key = name.split('_');
                                $('#vVehicleType_'+key[1]).val(Value);
                             });
                             $('#imageIcon').hide();
                        }
                });
              }
        }
       
        $(document).ready(function() {
            var referrer;
            if($("#previousLink").val() == "" ){
                referrer =  document.referrer;
            }else { 
                referrer = $("#previousLink").val();
            }
            if(referrer == "") {
                referrer = "store_vehicle_type.php";
            }else {
                $("#backlink").val(referrer);
            }
            $(".back_link").attr('href',referrer);
        });
        </script>
</body>
<!-- END BODY-->
</html>
