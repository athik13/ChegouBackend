<?php
include_once('common.php');

//added by SP for cubex changes on 07-11-2019
if($generalobj->checkXThemOn() == 'Yes') {
        include_once("cx-add_services.php");
        exit;
}

$generalobj->check_member_login();

$abc = 'driver,company';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$generalobj->setRole($abc, $url);

$start = @date("Y");
$end = '1970';
$script = "My Availability";
$tbl_name = 'driver_vehicle';
$tbl_name1 = 'service_pro_amount';
$tbl_dsr = 'driver_service_request';

$iDriverId = isset($_REQUEST['iDriverId']) ? base64_decode(base64_decode(trim($_REQUEST['iDriverId']))) : '';

$sql = "select iDriverVehicleId from driver_vehicle where iDriverId = '" . $iDriverId . "' AND eType='UberX'";
$db_drv_veh = $obj->MySQLSelect($sql);

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : $db_drv_veh[0]['iDriverVehicleId'];
$action = ($id != '') ? 'Edit' : 'Add';

$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$vLicencePlate = isset($_POST['vLicencePlate']) ? $_POST['vLicencePlate'] : '';
$iMakeId = isset($_POST['iMakeId']) ? $_POST['iMakeId'] : '3';
$iModelId = isset($_POST['iModelId']) ? $_POST['iModelId'] : '1';
$fAmount = isset($_POST['fAmount']) ? $_POST['fAmount'] : '';
$iYear = isset($_POST['iYear']) ? $_POST['iYear'] : Date('Y');
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$vCarType = isset($_POST['vCarType']) ? $_POST['vCarType'] : '';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : 'UberX';

$sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();

if ($_SESSION['sess_user'] == 'driver') {
    $sql = "select iCompanyId,vCurrencyDriver from `register_driver` where iDriverId = '" . $_SESSION['sess_iUserId'] . "'";
    $db_usr = $obj->MySQLSelect($sql);
    $iCompanyId = $db_usr[0]['iCompanyId'];
    $vCurrencyDriver = $db_usr[0]['vCurrencyDriver'];
    if ($vCurrencyDriver != '') {
        $sql1 = "select Ratio,vSymbol from `currency` where vName = '" . $vCurrencyDriver . "'";
        $db_usrcurrency = $obj->MySQLSelect($sql1);
    } else {
        $sql1 = "select Ratio,vName,vSymbol from currency where eDefault = 'Yes'";
        $db_usrcurrency = $obj->MySQLSelect($sql1);
    }
    $vSymbol = $db_usrcurrency[0]['vSymbol'];
    $ration = $db_usrcurrency[0]['Ratio'];
}
if ($_SESSION['sess_user'] == 'company') {
    $iCompanyId = $_SESSION['sess_iCompanyId'];
    $sql = "select * from register_driver where iCompanyId = '" . $_SESSION['sess_iCompanyId'] . "'";
    $db_drvr = $obj->MySQLSelect($sql);

    $sql = "select iCompanyId,vCurrencyDriver from `register_driver` where iDriverId = '" . $iDriverId . "'";
    $db_usr = $obj->MySQLSelect($sql);
    $vCurrencyDriver = $db_usr[0]['vCurrencyDriver'];
    if ($vCurrencyDriver != '') {
        $sql1 = "select Ratio,vSymbol from `currency` where vName = '" . $vCurrencyDriver . "'";
        $db_usrcurrency = $obj->MySQLSelect($sql1);
    } else {
        $sql1 = "select Ratio,vName,vSymbol from currency where eDefault = 'Yes'";
        $db_usrcurrency = $obj->MySQLSelect($sql1);
    }
    $vSymbol = $db_usrcurrency[0]['vSymbol'];
    $ration = $db_usrcurrency[0]['Ratio'];
}

/* Replace with ePricetype */
$chngamt = "Disabled";
if ($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes") {
    $chngamt = "Enabled";
}

if (isset($_POST['submit1'])) {

    if (SITE_TYPE == 'Demo' && $action == 'Edit') {
        $error_msg = $langage_lbl['LBL_EDIT_DELETE_RECORD'];
        header("Location:add_services.php?iDriverId=" . base64_encode(base64_encode($iDriverId)) . "&error_msg=" . $error_msg . "&success=2");
        exit;
    }

    if (!isset($_REQUEST['vCarType'])) {
        $error_msg = $langage_lbl['LBL_SELECT_CAR_TYPE'];
        header("Location:add_services.php?iDriverId=" . base64_encode(base64_encode($iDriverId)) . "&error_msg=" . $error_msg . "&success=2");
        exit;
    }

    if ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') {
        $vLicencePlate = 'My Services';
    } else {
        $vLicencePlate = $vLicencePlate;
    }

    if (SITE_TYPE == 'Demo') {
        $str = ", eStatus = 'Active' ";
    } else {
        $str = ", eStatus = 'Active' ";
    }

    $cartype = implode(",", $_REQUEST['vCarType']);

    $driverstatusQuery = "SELECT eStatus FROM register_driver WHERE iDriverId = '".$iDriverId."'";
    $iDriverStatus = $obj->MySQLSelect($driverstatusQuery);
    $eStatus = $iDriverStatus[0]['eStatus'];

    /* ------------------------------ */
    /* Request Service for Activation */
    if (($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') && $eType == "UberX" && $ENABLE_DRIVER_SERVICE_REQUEST_MODULE == 'Yes') {

        if($eStatus != 'inactive'){
                
                // $sql = "SELECT vCarType from driver_vehicle where iDriverId = '" . $iDriverId . "' AND vLicencePlate = 'My Services'";
                $sql  = 'SELECT dv.vCarType, rd.vEmail, rd.vName ,rd.vLastName ,rd.vCode ,rd.vPhone FROM driver_vehicle AS dv JOIN register_driver AS rd ON rd.iDriverId = dv.iDriverId WHERE dv.iDriverId = ' .$iDriverId.' AND dv.vLicencePlate = "My Services"' ;
                $existRequestdb = $obj->MySQLSelect($sql);

                $existServices = explode(',',$existRequestdb[0]['vCarType']);

                $remainingCats = array_diff($_REQUEST['vCarType'],$existServices);
                
                foreach ($remainingCats as $key => $catVal) {

                    if(!empty($catVal)){
                        $sql = "SELECT iDriverId from driver_service_request where iDriverId = '" . $iDriverId . "' AND iVehicleCategoryId = '" . $catVal . "'";
                        $existRequest = $obj->MySQLSelect($sql);

                        if (count($existRequest) == 0) {
                            $q = "INSERT INTO ";
                            $wheredrs = '';
                        
                            $query = $q . " `" . $tbl_dsr . "` SET		
                                `iVehicleCategoryId` = '" . $catVal . "',
                                `iDriverId` = '" . $iDriverId . "',
                                `cRequestStatus` = 'Pending'"
                                    . $wheredrs;
                                    
                            $obj->sql_query($query);        
                        }
                    }
                }

                if(!empty($remainingCats)){

                    /* Send Email to Driver */  
                    $getMaildata['name'] = $existRequestdb[0]['vName']." ".$existRequestdb[0]['vLastName'];
                    $getMaildata['email'] = $existRequestdb[0]['vEmail'];
                    $getMaildata['phone'] = "+".$existRequestdb[0]['vCode']." ".$existRequestdb[0]['vPhone'];
                    $mail = $generalobj->send_email_user('SERVICE_REQUEST_FROM_PROVIDER',$getMaildata);
                    
                }

            }
    }
    /* End Request Service for Activation */            
    /* ------------------------------ */

    $q = "INSERT INTO ";
    $where = '';

    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `iDriverId` = '" . $iDriverId . "' AND `iDriverVehicleId` = '" . $id . "' ";
    }

    $query = $q . " `" . $tbl_name . "` SET		
		`vLicencePlate` = '" . $vLicencePlate . "',
		`iYear` = '" . $iYear . "',		
		`iCompanyId` = '" . $iCompanyId . "',
		`iDriverId` = '" . $iDriverId . "',
		`eType` = '" . $eType . "',
		`vCarType` = '" . $cartype . "' $str"
            . $where;

    $obj->sql_query($query);
    $id = ($id != '') ? $id : $obj->GetInsertId();

    /* --------------------------------------- */
    /* This is for Reverse operation for new added services as it should be approve first */
    if (($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') && $eType == "UberX" && $ENABLE_DRIVER_SERVICE_REQUEST_MODULE == 'Yes') {
        if($eStatus != 'inactive'){

            $sql = "SELECT vCarType from driver_vehicle where iDriverId = '" . $iDriverId . "' AND vLicencePlate = 'My Services'";
            $existRequest = $obj->MySQLSelect($sql);
            $existServices = explode(',',$existRequest[0]['vCarType']);
            
            $existServices = implode(',',array_diff($existServices,$remainingCats));
            $sqlu  = 'UPDATE driver_vehicle SET vCarType = "'.$existServices.'" WHERE iDriverId = "' .$iDriverId.'" AND vLicencePlate = "My Services"' ;
            $existingServices = $obj->sql_query($sqlu);
        }
    }
    /* End for Reverse operation for new added services */
    /* --------------------------------------- */

    if (!empty($fAmount)) {
        //$amt_man=$fAmount;
        $amt_man = array();
        foreach ($fAmount as $key => $value) {
            $amt_man[$key] = $value / $ration;
        }
        $sql = "select iServProAmntId,iDriverVehicleId from " . $tbl_name1 . " where iDriverVehicleId = '" . $id . "' ";
        $db_drv_price = $obj->MySQLSelect($sql);
        if (count($db_drv_price) > 0) {
            $sql = "delete from " . $tbl_name1 . " where iDriverVehicleId='" . $db_drv_price[0]['iDriverVehicleId'] . "'";
            $obj->sql_query($sql);
        }

        foreach ($amt_man as $key => $value) {
            if ($value != "") {
                $q = "Insert Into ";
                $query = $q . " `" . $tbl_name1 . "` SET
					`iDriverVehicleId` = '" . $id . "',
					`iVehicleTypeId` = '" . $key . "',
					`fAmount` = '" . $value . "'";
                $db_parti_price = $obj->sql_query($query);
            }
        }
    }

    if ($action == "Add") {
        $sql = "SELECT * FROM company WHERE iCompanyId = '" . $iCompanyId . "'";
        $db_compny = $obj->MySQLSelect($sql);

        $sql = "SELECT * FROM register_driver WHERE iDriverId = '" . $iDriverId . "'";
        $db_status = $obj->MySQLSelect($sql);

        $maildata['EMAIL'] = $db_status[0]['vEmail'];
        $maildata['NAME'] = $db_status[0]['vName'];
        $maildata['DETAIL'] = "Your Services is Added For " . $db_compny[0]['vName'] . " and will process your document and activate your account ";
        $generalobj->send_email_user("VEHICLE_BOOKING", $maildata);
    }

    $var_msg = $langage_lbl['LBL_Record_Updated_successfully'];
    header("Location:add_services.php?success=1&var_msg=" . $var_msg . "&iDriverId=" . base64_encode(base64_encode($iDriverId)));
}

// for Edit

$sql = "SELECT t.*,t1.fAmount,t1.iServProAmntId,t1.iVehicleTypeId AS `VehicleId`,t1.iDriverVehicleId AS `DriverVehilceId` from  $tbl_name as t left join $tbl_name1 t1 on t.iDriverVehicleId=t1.iDriverVehicleId where t.iDriverId = '" . $iDriverId . "' AND t.iDriverVehicleId = '" . $db_drv_veh[0]['iDriverVehicleId'] . "' ";
$db_data = $obj->MySQLSelect($sql);
$vLabel = $id;
$fAmount = array();
if (count($db_data) > 0) {
    foreach ($db_data as $key => $value) {
        $vLicencePlate = $value['vLicencePlate'];
        $iYear = $value['iYear'];
        $eCarX = $value['eCarX'];
        $eType = $value['eType'];
        $eCarGo = $value['eCarGo'];
        $iDriverId = $value['iDriverId'];
        $vCarType = $value['vCarType'];
        $iCompanyId = $value['iCompanyId'];
        $eStatus = $value['eStatus'];
        $iDriverVehicleId = $value['iDriverVehicleId'];
        $amt = $value['fAmount'] * $ration;
        $fAmount[$value['VehicleId']] = $amt;
    }
}

$vCarTyp = explode(",", $vCarType);

if ($APP_TYPE == 'Delivery') {
    $Vehicle_type_name = 'Deliver';
} else if ($APP_TYPE == 'Ride-Delivery-UberX') {
    $Vehicle_type_name = 'UberX';
} else {
    $Vehicle_type_name = $APP_TYPE;
}

if ($Vehicle_type_name == "Ride-Delivery") {
    $vehicle_type_sql = "SELECT * from  vehicle_type where(eType ='Ride' or eType ='Deliver')";
    $vehicle_type_data = $obj->MySQLSelect($vehicle_type_sql);
} else {
    if ($Vehicle_type_name == 'UberX') {
        $userSQL = "SELECT c.iCountryId from register_driver AS rd LEFT JOIN country AS c ON c.vCountryCode=rd.vCountry where rd.iDriverId='" . $iDriverId . "'";
        $drivers = $obj->MySQLSelect($userSQL);
        $iCountryId = $drivers[0]['iCountryId'];
        $whereParentId = "";
        if ($parent_ufx_catid > 0) {
            $whereParentId = " AND vc.iVehicleCategoryId='" . $parent_ufx_catid . "'";
        }
        $getvehiclecat = "SELECT vc.iVehicleCategoryId, vc.vCategory_EN as main_cat FROM ".$sql_vehicle_category_table_name." as vc WHERE vc.eStatus='Active' AND vc.iParentId='0' $whereParentId";
        $vehicle_type_data = $obj->MySQLSelect($getvehiclecat);
        $i = 0;
        foreach ($vehicle_type_data as $key => $val) {
            $vehicle_type_sql = "SELECT vt.vVehicleType,vc.iParentId,vc.vCategory_" . $_SESSION['sess_lang'] . ",vc.iVehicleCategoryId from  vehicle_type as vt  left join ".$sql_vehicle_category_table_name." as vc on vt.iVehicleCategoryId = vc.iVehicleCategoryId where vt.eType='" . $Vehicle_type_name . "' AND vc.iParentId ='" . $val['iVehicleCategoryId'] . "'  AND vc.eStatus='Active' GROUP BY vc.iVehicleCategoryId";
            $vehicle_type_dataOld = $obj->MySQLSelect($vehicle_type_sql);
            $vehicle_type_data[$i]['SubCategory'] = $vehicle_type_dataOld;
            $j = 0;
            foreach ($vehicle_type_dataOld as $subkey => $subvalue) {
                $vehicle_type_sql1 = "SELECT vt.*,vc.*,lm.vLocationName from  vehicle_type as vt  left join ".$sql_vehicle_category_table_name." as vc on vt.iVehicleCategoryId = vc.iVehicleCategoryId left join location_master as lm ON lm.iLocationId = vt.iLocationid where vt.eType='" . $Vehicle_type_name . "' and vc.iVehicleCategoryId = '" . $subvalue['iVehicleCategoryId'] . "' AND (lm.iCountryId='" . $iCountryId . "' || vt.iLocationid='-1') AND vt.eStatus='Active'";
                $vehicle_type_dataNew = $obj->MySQLSelect($vehicle_type_sql1);
                $vehicle_type_data[$i]['SubCategory'][$j]['VehicleType'] = $vehicle_type_dataNew;
                $j++;
            }

            $i++;
        }
    } else {
        $vehicle_type_sql = "SELECT * from  vehicle_type  where eType='" . $Vehicle_type_name . "' ";
        $vehicle_type_data = $obj->MySQLSelect($vehicle_type_sql);
    }
}

$sql  = 'SELECT iVehicleCategoryId FROM driver_service_request WHERE iDriverId = "' .$iDriverId.'" ' ;
$ReqServices = $obj->MySQLSelect($sql);
$requestedServices = [];
foreach ($ReqServices as $key => $ReqService) {
    $requestedServices[] = $ReqService['iVehicleCategoryId'] ;
}

?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_HEADER_MY_SERVICES']; ?></title>
        <!-- Default Top Script and css -->
        <?php include_once("top/top_script.php"); ?>
        <!-- End: Default Top Script and css-->
    </head>
    <body>
        <!-- home page -->
        <div id="main-uber-page">
            <!-- Left Menu -->
            <?php include_once("top/left_menu.php"); ?>
            <!-- End: Left Menu-->
            <!-- Top Menu -->
            <?php include_once("top/header_topbar.php"); ?>
            <link rel="stylesheet" href="assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
            <!-- End: Top Menu-->
            <!-- Add Service page-->
            <div class="page-contant">
                <div class="page-contant-inner page-trip-detail">
                    <h2 class="header-page trip-detail driver-detail1"> <?= $langage_lbl['LBL_HEADER_MY_SERVICES']; ?>
                        <? if (($Vehicle_type_name == 'UberX') && $_SESSION['sess_user'] == "company") { ?>
                            <a href="providerlist">
                                <img src="assets/img/arrow-white.png" alt="">
                                <?= $langage_lbl['LBL_BACK_To_Listing_WEB']; ?>
                            </a>
                        <? } ?>
                    </h2>
                    <!-- Service detail page -->
                    <div class="driver-add-vehicle"> 
                        <? if ($success == 1) { ?>
                            <div class="alert alert-success alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?= $langage_lbl['LBL_Record_Updated_successfully']; ?>
                            </div>
                        <? } else if ($success == 2) { ?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?= isset($_REQUEST['error_msg']) ? $_REQUEST['error_msg'] : ' '; ?>
                            </div>
                        <? } ?>
                        <?php if (!empty($vehicle_type_data)) { ?>
                            <form name="frm1" method="post" action="">
                                <input type="hidden" name="iDriverIdNew"  value="<?= $iDriverIdNew ?>"/>
                                <input type="hidden" name="iCompanyId"  value="<?= $iCompanyId ?>"/>
                                <input type="hidden" name="id" value="<?= $iDriverVehicleId; ?>"/>
                                <input type="hidden" name="vLicencePlate"  value="<?= $vLicencePlate; ?>"/>
                                <input type="hidden" name="eType"  value="<?= $eType; ?>"/>
                                <div class="car-type add-car-services-hatch add-services-hatch add-services-taxi">				          
                                    <ul>
                                        <?php
                                        foreach ($vehicle_type_data as $value1) {
                                            foreach ($value1['SubCategory'] as $Vehicle_Type) {
                                                if (!empty($Vehicle_Type['VehicleType'])) {
                                                    if ($Vehicle_type_name == 'UberX') {
                                                        $vName = 'vCategory_' . $_SESSION['sess_lang'];
                                                        $vehicleName = $Vehicle_Type[$vName];
                                                    } else {
                                                        $vehicle_typeName = $Vehicle_Type['vVehicleType'];
                                                    }
                                                    $iParentcatId = $Vehicle_Type['iParentId'];
                                                    $sql_query = "SELECT ePriceType FROM ".$sql_vehicle_category_table_name." WHERE iVehicleCategoryId = '" . $iParentcatId . "' ";
                                                    $ePricetype_data = $obj->MySQLSelect($sql_query);
                                                    $ePricetype = $ePricetype_data[0]['ePriceType'];
                                                    ?>

                                                    <fieldset>
                                                        <legend><strong><?= $value1['main_cat'] . " - " . $vehicleName; ?></strong></legend>
                                                        <?php
                                                        foreach ($Vehicle_Type['VehicleType'] as $val) {
                                                            $VehicleName1 = 'vVehicleType_' . $_SESSION['sess_lang'];
                                                            if ($val['eFareType'] == 'Fixed') {
                                                                $eFareType = 'Fixed';
                                                                $amt_old = $val['fFixedFare'] * $ration;
                                                                $fAmount_old = $amt_old;
                                                            } else if ($val['eFareType'] == 'Hourly') {
                                                                $eFareType = 'Per hour';
                                                                $amt_old1 = $val['fPricePerHour'] * $ration;
                                                                $fAmount_old = $amt_old1;
                                                            } else {
                                                                $eFareType = '';
                                                                $amt_old2 = $val['fFixedFare'] * $ration;
                                                                $fAmount_old = $amt_old2;
                                                            }
                                                            $vehicle_typeName = $val[$VehicleName1];

                                                            if (!empty($val['vLocationName'])) {
                                                                $localization = '(Location : ' . $val["vLocationName"] . ')';
                                                            } else {
                                                                $localization = '';
                                                            }

                                                            
                                                            $disStat = '';
                                                            if ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') {   
                                                                if(in_array($val['iVehicleTypeId'],$requestedServices)){
                                                                    $disStat = 'disabled';
                                                                }
                                                            }
                                                            ?>
                                                            <li>
                                                                <b><?php echo $vehicle_typeName; ?><br/>
                                                                    <div style="font-size: 12px;"><?php echo $localization; ?></div></b>
                                                                <div class="make-switch" data-on="success" data-off="warning">
                                                        <input type="checkbox" <? if ($ePricetype == "Provider") { ?>onchange="check_box_value(this.value);" <? }else{ ?>onchange="cTrig('vCarType1_<?= $val['iVehicleTypeId'] ?>')"  <?php } ?> id="vCarType1_<?= $val['iVehicleTypeId'] ?>" class="chk" name="vCarType[]" <?php if (in_array($val['iVehicleTypeId'], $vCarTyp)) { ?>checked<?php } ?> value="<?= $val['iVehicleTypeId'] ?>" <?=$disStat?> />
                                                            


                                                                </div>

                                                                
                                                                <?php if(!empty($disStat) && $ENABLE_DRIVER_SERVICE_REQUEST_MODULE == 'Yes') { ?>
                                                                    <br><br><br>
                                                                    <small><?= $langage_lbl['LBL_SERVICE_REQUEST_PENDING']; ?></small><br>
                                                                <?php } ?>    

                                                                <?php
                                                                if ($ePricetype == "Provider") {
                                                                    $p001 = "style='display:none;'";
                                                                    if (in_array($val['iVehicleTypeId'], $vCarTyp)) {
                                                                        $p001 = "style='display:block;'";
                                                                    }

                                                                    $fAmount_new = $fAmount[$val['iVehicleTypeId']];
                                                                    $famount_val = (empty($fAmount_new)) ? round($fAmount_old, 2) : round($fAmount_new, 2);
                                                                    ?>
                                                                    <div class="hatchback-search" id="amt1_<?= $val['iVehicleTypeId'] ?>" <? echo $p001; ?>>
                                                                        <input type="hidden" name="desc" id="desc_<?= $val['iVehicleTypeId'] ?>" value="<?= $val[$VehicleName1] ?>">
                                                                        <?php if ($val['eFareType'] != 'Regular') { ?>
                                                                            <label><? echo $vSymbol; ?></label>
                                                                            <input class="form-control" type="text" name="fAmount[<?= $val['iVehicleTypeId'] ?>]" value="<?= $famount_val; ?>" placeholder="Enter Amount for <?= $val[$VehicleName1] ?>" id="fAmount_<?= $val['iVehicleTypeId'] ?>" maxlength="10"><label><?php echo $eFareType; ?></label>
                                                                        </div>
                                                                        <?
                                                                    }
                                                                }
                                                                ?>
                                                            
                                                            </li>
                                                        <?php } ?>
                                                    </fieldset> 

                                                    <?php
                                                }
                                            }
                                        }
                                        ?>
                                    </ul>
                                    <strong><input type="submit" class="save-vehicle" name="submit1" id="submit1" value="<?= $langage_lbl['LBL_SUBMIT_BUTTON_TXT']; ?>" onclick="return check_empty();"> </strong>

                                </div>

                                <!-- -->
                            </form>
                        <?php } else { ?>
                            <div> <?= $langage_lbl['LBL_NO_SERVICE_AVAIL_WEB']; ?></div>
                        <?php } ?>
                    </div>
                    <div style="clear:both;"></div>
                </div>
            </div>
            <!-- footer part -->
            <?php include_once('footer/footer_home.php'); ?>
            <!-- footer part end -->
            <!-- End:contact page-->
            <div style="clear:both;"></div>
        </div>
        <!-- home page end-->
        <!-- Footer Script -->
        <?php include_once('top/footer_script.php'); ?>
        <script src="assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>

        <script>
        var driverReqModule = '<?php echo $ENABLE_DRIVER_SERVICE_REQUEST_MODULE; ?>';
        
                                    function check_box_value(val1)
                                    {
                                        if ($('#vCarType1_' + val1).is(':checked'))
                                        {
                                            $("#amt1_" + val1).show();
                                            $("#fAmount_" + val1).focus();
                                        } else {
                                            if(driverReqModule == 'Yes')
                                            alert('<?= $langage_lbl['LBL_UNSELECT_CHECKBOX_FOR_SERVICE']; ?>'); 
                                            
                                            $("#amt1_" + val1).hide();
                                        }
                                    }


                                    function cTrig(clickedid) { 
                                            if ($('#' + clickedid).is(':checked')) {
                                                return true;
                                            } else {
                                                if(driverReqModule == 'Yes')
                                                alert('<?= $langage_lbl['LBL_UNSELECT_CHECKBOX_FOR_SERVICE']; ?>'); 
                                            }
                                    }

                                    function check_empty()
                                    {
                                        var err = 0;
                                        $("input[type=checkbox]:checked").each(function () {
                                            var tmp = "fAmount_" + $(this).val();
                                            var tmp1 = "desc_" + $(this).val();
                                            var tmp1_val = $("#" + tmp1).val();

                                            if ($("#" + tmp).val() == "")
                                            {
                                                alert('Please Enter Amount for ' + tmp1_val + '.');
                                                $("#" + tmp).focus();
                                                err = 1;
                                                return false;
                                            }
                                        });
                                        if (err == 1)
                                        {
                                            return false;
                                        } else {
                                            //document.frm1.submit();
                                             jQuery('#frm1').submit();
                                        }
                                    }

        </script>
        <!-- End: Footer Script -->
    </body>
</html>
