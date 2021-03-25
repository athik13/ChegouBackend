<?php
include_once '../common.php';
include_once '../app_common_functions.php';
global $userObj;
require_once TPATH_CLASS . "/Imagecrop.class.php";
$thumb = new thumbnail();
if (!mapAPIreplacementAvailable()) {
    header("Location:" . $tconfig["tsite_url_main_admin"]);
}
$sql = "SELECT vCountryCode,vCountry from country where eStatus = 'Active'";
$db_code = $obj->MySQLSelect($sql);
$sql = "select cn.vCountryCode,cn.vCountry,cn.vPhoneCode,cn.vTimeZone from country cn inner join configurations c on c.vValue=cn.vCountryCode where c.vName='DEFAULT_COUNTRY_CODE_WEB'";
$db_con = $obj->MySQLSelect($sql);
$vRideCountry = isset($_REQUEST['vRideCountry']) ? $_REQUEST['vRideCountry'] : $db_con[0]['vCountryCode'];
$vTimeZone = isset($_REQUEST['vTimeZone']) ? $_REQUEST['vTimeZone'] : $db_con[0]['vTimeZone'];
$vCountry = $db_con[0]['vCountryCode'];
$search_address = $db_con[0]['vCountry']; // Google HQexit;
if (!isset($generalobjAdmin)) {
    require_once TPATH_CLASS . "class.general_admin.php";
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$userType = isset($_REQUEST['userType']) ? $_REQUEST['userType'] : ''; // Added By HJ On 12-08-2019 For Edit eEnableDemoLocDispatch Value If QA User as Per Disucss WIth KS
$action = ($id != '') ? 'Edit' : 'Add';
$tbl_name = 'register_driver';
$script = 'map_api_setting';
// set all variables with either post (when submit) either blank (when insert)
$vSid = isset($_REQUEST['sid']) ? $_REQUEST['sid'] : '';
$DbName = TSITE_DB;
$TableName = "auth_master_accounts_places";
// if ($vSid != '') {$searchQuery['vServiceId'] = intVal($vSid);
    // $usage_order = array();
    // $data_by_serviceID = $obj->fetchAllRecordsFromMongoDBWithDBName($DbName, $TableName, $searchQuery);
    // $serviceIDIntheRecord = $data_by_serviceID[0]['vServiceId'];
    // foreach ($data_by_serviceID as $values_by_serviceID_value) {
        // $usage_order[] = $values_by_serviceID_value['vUsageOrder'];
    // }
    // $max_usage_order = max($usage_order) + 1;
// }
if ($id != '') {
    $searchQuery['_id'] = new MongoDB\BSON\ObjectID($id);
    $data_by_serviceID = $obj->fetchAllRecordsFromMongoDBWithDBName($DbName, $TableName, $searchQuery);
    $sid_val = $data_by_serviceID[0]['vServiceId'];
    $vUsageOrderVal = $data_by_serviceID[0]['vUsageOrder'];
    // $usage_order = array();
    // $searchQuerySid['vServiceId'] = intVal($_REQUEST['sid']);
    $data_by_serviceIDnew = $obj->fetchAllCollectionFromMongoDB($DbName, $TableName);
	
    $totalActiveAccount = [];
    $totalAccounts = 0;
    foreach ($data_by_serviceIDnew as $key => $values_by_serviceID_value) {
        $usage_order[] = $values_by_serviceID_value['vUsageOrder'];
        $totalActiveAccount[$key] = $values_by_serviceID_value['eStatus'];
        $totalAccounts = $totalAccounts + 1;
    }
    $max_usage_order = max($usage_order) + 1;
}
unset($totalActiveAccount[$vUsageOrderVal - 1]);
$requiredServicesAry = array("Active");
$result = array_diff($requiredServicesAry, $totalActiveAccount);
$vTitle = isset($_POST['vTitle']) ? $_POST['vTitle'] : '';
$vServiceId = isset($_POST['vServiceId']) ? $_POST['vServiceId'] : '';
$vAuthKey = isset($_POST['vAuthKey']) ? $_POST['vAuthKey'] : '';
$vUsageOrder = isset($_POST['vUsageOrder']) ? $_POST['vUsageOrder'] : '';
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : '';
$alowinact = $_REQUEST['alowinact'];
$alowme = $_REQUEST['alowme'];
if (isset($_POST['submit'])) {
    if ($action == 'Add' && !$userObj->hasPermission('create-providers')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create ' . $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"];
        header("Location:map_api_setting.php");
        exit;
    }
    if ($action == 'Edit' && !$userObj->hasPermission('edit-providers')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update ' . $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"];
        header("Location:map_api_setting.php");
        exit;
    }
    if (!empty($id) && SITE_TYPE == 'Demo') {
        $_SESSION['success'] = 2;
        header("Location:map_api_setting.php");
        exit;
    }
    require_once "library/validation.class.php";
    $validobj = new validation();
    // $validobj->add_fields($_POST['vTitle'], 'req', ' Title is required');
    // $validobj->add_fields($_POST['vServiceId'], 'req', 'Service id is required');
    // $validobj->add_fields(strtolower($_POST['vAuthKey']), 'req', 'Email Address is required.');
    $validobj->add_fields(strtolower($_POST['vUsageOrder']), 'req', 'Please enter usage order.');
    $validobj->add_fields(strtolower($_POST['eStatus']), 'req', 'Please select status.');
    $error = $validobj->validate();
    /* Added by SP for phone unique validation end */
    /* 06-09-219 check email,phone validation using member function added by Rs start(check phone number using country) */
    $eSystem = "";
    /* 06-09-219 check phone validation end */
    //Other Validations
    if ($error) {
        $success = 3;
        $newError = $error;
        //exit;
    } else {
        $vRefCodePara = '';
        if ($action == 'Edit') {
            if ($eStatus == "Inactive") {
                // if (count($result) > 0) {
                    $DbName = TSITE_DB;
                    $TableNameAuthMaster = "auth_master_accounts_places";
                    $requiredServicesAry = array("Geocoding", "AutoComplete", "Direction");
                    $activeRecordsResult = $obj->fetchAllCollectionFromMongoDB($DbName, $TableNameAuthMaster);
                    foreach ($activeRecordsResult as $key => $activeRecordsResult) {
                        if ($activeRecordsResult['eStatus'] == "Active") {
                            $AllactiveServices[$key + 1] = $activeRecordsResult['vActiveServices'];
                        }
                    }
                    unset($AllactiveServices[$vSid]);
                    $AllactiveServices = array_values($AllactiveServices);
                    for ($i = 0; $i <= count($AllactiveServices); $i++) {
                        $explodeData = explode(",", $AllactiveServices[$i]);
                        foreach ($explodeData as $Row) {
                            if ($Row != '') {
                                $RowAry[] = $Row;
                            }
                        }
                    }
                        $resultNew = array_diff($requiredServicesAry, $RowAry);
						if(count($resultNew) > 0){
							$redirect = $tconfig["tsite_url_main_admin"] . "map_api_setting.php";
							$redirectForthis = $tconfig["tsite_url_main_admin"] . "map_api_mongo_auth_places_action.php?alowme=Y&id=$id&eStatus=Inactive&action=Edit";
							$redirectForInactiveService = $tconfig["tsite_url_main_admin"] . "map_api_mongo_auth_places_action.php?alowinact=Y&id=$id&eStatus=Inactive&action=Edit";
							echo "<script language='JavaScript' type='text/javascript' >
						function goback(){
							window.location.href ='$redirect';
						}
						var countResult = " . count($resultNew) . ";
							if (confirm('Your service will be inactive. Do you like to inactive service?')) {
								if(countResult > 0){
									alert('Keep atleast one service active.');
									window.location.href ='$redirect';
								}else{
									window.location.href ='$redirect';
								}
								
						}else{
							window.location.href ='$redirect';
						}
						</script>";
							exit;
                    }
                // }
            }
            // if ($_REQUEST['alowme'] == 'Y') {	
                $DbName = TSITE_DB;
                $TableNameMaster = "auth_master_accounts_places";
                $uniqueFieldNameMaster = 'vServiceId';
                $uniqueFieldValueMaster = intval($vSid);
                $tempDataMaster = [];
                $tempDataMaster["eStatus"] = $eStatus;
				$tempDataMaster["vUsageOrder"] = $vUsageOrder;
                $asdasd = $obj->updateRecordsToMongoDBWithDBName($DbName, $TableNameMaster, $uniqueFieldNameMaster, $uniqueFieldValueMaster, $tempDataMaster);
            // }
			
        }
       
        if ($action == "Add") {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
        } else {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        }
        if ($action == "Edit") {
            // header("Location:map_api_mongo_auth_places_action.php?id=" . $id);
            header("Location:map_api_setting.php");
            exit;
        } else {
            // header("Location:map_api_mongo_auth_places_action.php?id=" . $vSid);
            header("Location:map_api_setting.php");
            exit;
        }
    }
}
if ($action == 'Edit') {
    $DbName = TSITE_DB;
    $TableName = "auth_master_accounts_places";
    $searchQuery = [];
    if ($id != '') {
        $searchQuery['_id'] = new MongoDB\BSON\ObjectID($id);
    }
    $data_drv = $obj->fetchAllRecordsFromMongoDBWithDBName($DbName, $TableName, $searchQuery);
// echo "<pre>";
// print_r($data_drv);exit;
    if (count($data_drv) > 0) {
        foreach ($data_drv as $key => $value) {
            $vTitle = $value['vServiceName'];
            $vServiceId = intval($value['vServiceId']);
            $vAuthKey = $value['auth_key'];
            $vUsageOrder = intval($value['vUsageOrder']);
            $eStatus = $value['eStatus'];
        }
    }
    if ($SITE_VERSION == "v5") {
        $sql = "select * from preferences where eStatus ='Active'";
        $data_preference = $obj->MySQLSelect($sql);
        $data_driver_pref = $generalobj->Get_User_Preferences($id);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title><?=$SITE_NAME?> | <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>  <?=$action;?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?
        include_once('global_files.php');
        ?>
        <!-- On OFF switch -->
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >
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
                            <h2><?=$action;?> <?php echo ($action == 'Edit') ? " Account" : $langage_lbl_admin['LBL_MAP_API_AUTH_MASTER_ACCOUNT_PLACES']; ?>  <?=$vTitle;?></h2>
                            <a href="map_api_setting.php"  class="back_link">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />
                    <div class="body-div">
                        <div class="form-group">
                            <?php if ($success == 2) {?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                </div><br/>
                            <?php }?>
                            <?php if ($success == 3) {?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php print_r($error);?>
                                </div><br/>
                            <?php }?>
                            <form id="_map_api_setting_action_form" name="_map_api_setting_action_form" method="post" action="" enctype="multipart/form-data">
                                <input type="hidden" name="actionOf" id="actionOf" value="<?=$action;?>"/>
                                <input type="hidden" name="id" id="id" value="<?=$id;?>"/>
                                <input type="hidden" name="oldImage" value="<?=$oldImage;?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?=$previousLink;?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="map_api_setting.php"/>
                                <input type="hidden" name="sid" id="sid" value="<?=($vSid != "") ? $vSid : $vServiceId;?>" >
								<input type="hidden" name="vServiceAccountId" id="vServiceAccountId" value="<?=($vSid != "") ? $vSid : $vServiceId;?>" >
                                <?php if ($id) {?>
                                    <div class= "row col-md-12" id="hide-profile-div">
                                        <?php $class = ($SITE_VERSION == "v5") ? "col-lg-3" : "col-lg-4";?>
                                        <?php if ($SITE_VERSION == "v5") {?>
                                            <div class="col-lg-4">
                                                <fieldset class="col-md-12 field">
                                                    <legend class="lable"><h4 class="headind1"> Preferences: </h4></legend>
                                                    <p>
                                                    <div class=""> <?php foreach ($data_driver_pref as $val) {?>
                                                            <img data-toggle="tooltip" class="borderClass-aa1 border_class-bb1" title="<?=$val['pref_Title']?>" src="<?=$tconfig["tsite_upload_preference_image_panel"] . $val['pref_Image']?>">
                                                        <?php }?>
                                                    </div>
                                                    <span class="col-md-12"><a href="" data-toggle="modal" data-target="#myModal" id="show-edit-language-div" class="hide-language1">
                                                            <i class="fa fa-pencil" aria-hidden="true"></i>
                                                            Manage Preferences</a>
                                                    </span>
                                                    </p>
                                                </fieldset>
                                            </div>
                                        <?php }?>
                                    </div>
                                <?php }?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" readonly class="form-control" value="<?=$vTitle;?>" placeholder="Title" >
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Usage Order<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                    <select class="form-control" name = 'vUsageOrder' id="vUsageOrder" >
                                    <?php
$html = '';
for ($i = 1; $i <= $max_usage_order; $i++) {
    if ($action == "Add") {
        if ($i == $max_usage_order) {
            $selected = " selected";
        } else {
            $selected = " ";
        }
    } else {
        if ($vUsageOrder == $i) {
            $selected = " selected";
        } else {
            $selected = " ";
        }
    }
    $html .= '<option value = "' . $i . '" ' . $selected . '>' . $i . '</option>';
}
$html .= '</select>';
echo $html;
?>
                                        <!-- <input type="text" class="form-control" onkeypress="onlynumbers(event)" name="vUsageOrder" value="<?=$vUsageOrder;?>"  id="vUsageOrder" placeholder="Usage Order" > -->
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Status <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
<?php if($vTitle != "Google"){ ?>
                                        <select class="form-control" name = 'eStatus' id="eStatus" >
                                            <?php if ($action == "Add") {
    $ActiveStatus = " Selected";
    $InActiveStatus = " ";
} else {
    if ($eStatus == "Active") {
        $ActiveStatus = " Selected";
        $InActiveStatus = " ";
    } else if ($eStatus == "Inactive") {
        $ActiveStatus = " ";
        $InActiveStatus = " Selected";
    } else {
        $ActiveStatus = " ";
        $InActiveStatus = " ";
    }
}?>
                                            <option value="">Select</option>
                                                <option value = "Active" <?=($ActiveStatus)?> >Active</option>
                                                <option value = "Inactive" <?=($InActiveStatus)?> >Inactive</option>
                                        </select>
                                    <?php }else{ ?>
                                        <input type="hidden" name="eStatus" id="eStatus"  value="Active" />
                                    <?php } ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <?php if (($action == 'Add' && $userObj->hasPermission('create-providers')) || ($action == 'Edit' && $userObj->hasPermission('edit-providers'))) {?>
                                            <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?php if ($action == 'Add') {?><?=$action;?> <?php echo $langage_lbl_admin['LBL_MANUAL_STORE_ACCOUNT']; ?><?php } else {?>Update<?php }?>">
                                            <input type="reset" value="Reset" class="btn btn-default">
                                        <?php }?>
                                        <!-- <a href="javascript:void(0);" onClick="reset_form('_driver_form');" class="btn btn-default">Reset</a> -->
                                        <a href="map_api_setting.php" class="btn btn-default back_link">Cancel</a>
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
        <?php include_once 'footer.php';?>
        <script type='text/javascript' src='../assets/js/jquery-ui.min.js'></script>
        <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
        <script>
		$( document ).ready(function() {
			$("#vAuthKey").keypress(function(){
			  $("#vAuthKey-error").text('');
			});
			$("#vAuthKey").blur(function(){
			  $("#vAuthKey-error").text('');
			});
		});
        function onlynumbers(evt) {
                    var theEvent = evt || window.event;
                    // Handle paste
                    if (theEvent.type === 'paste') {
                        key = event.clipboardData.getData('text/plain');
                    } else {
                    // Handle key press
                        var key = theEvent.keyCode || theEvent.which;
                        key = String.fromCharCode(key);
                    }
                    var regex = /[0-9]|\./;
                    if( !regex.test(key) ) {
                        theEvent.returnValue = false;
                        if(theEvent.preventDefault) theEvent.preventDefault();
                    }
                    }
                                            $('#_driver_form').validate({
                                            rules: {
                                            vName: {
                                                required: true, minlength: 2, maxlength: 30
                                            },
                                            vLastName: {
                                                required: true, minlength: 2, maxlength: 30
                                            },
                                            vEmail: {
                                                required: true
                                            },
                                            vCountry: {
                                                required: true
                                            },
                                            }
                                            });
        </script>
    </body>
    <!-- END BODY-->
</html>
