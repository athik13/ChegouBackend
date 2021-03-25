<?php

include_once("common.php");

include_once ('include_generalFunctions_dl.php');

check_type_wise_mr('customer_info');



$fromOrder = "guest";

if (isset($_REQUEST['order']) && $_REQUEST['order'] != "") {

    $fromOrder = $_REQUEST['order'];

}

$_SESSION['MANUAL_ORDER_USER'] = $fromOrder;

//print_R($_SESSION); exit;

//if(checkSystemStoreSelection()) {

//    $service_categories = array();

//    if (isset($serviceCategoriesTmp) && !empty($serviceCategoriesTmp)) {

//        $service_categories = $serviceCategoriesTmp;

//    }

//    

//    //$cnt_sc = count($service_categories);

//    //if($cnt_sc==1) {

//        session_start();

//        $store_data = $generalobj->getStoreDataForSystemStoreSelection($service_categories[0]['iServiceId']);

//        //$iCompanyId = $store_data[0]['iCompanyId'];

//        $iCompanyId = $store_data['iCompanyId'];

//        $_SESSION[$orderLongitudeSession] = $store_data['vRestuarantLocationLat'];

//        $_SESSION[$orderLatitudeSession] = $store_data['vRestuarantLocationLong'];

//        $_SESSION[$orderServiceSession] = $store_data['iServiceId'];

//        $_SESSION[$orderAddressSession] = $store_data['vCaddress'];

//        $_SESSION[$orderServiceNameSession] = $service_categories[($store_data['iServiceId'] - 1)]['vServiceName'];

//        header("location: store-items?id=" . $iCompanyId . "&order=" . $fromOrder);

//        exit;

//    //}

//}

//if(checkSystemStoreSelection()) {

//    $store_data = $generalobj->getStoreDataForSystemStoreSelection();

//    $iCompanyId = $store_data[0]['iCompanyId'];

//    header("location: store-items?id=".$iCompanyId."&order=".$fromOrder);

//    exit;

//}



$db_code = $obj->MySQLSelect("SELECT vCountryCode,vCountry,vPhoneCode,vTimeZone from country where eStatus = 'Active'");

$customer_info_vPhoneCode = "1";

$vDistance = 0;

$vCountryCode = "US";

$address = $vRideCountry = "USA";

$vPhoneCode = $vDuration = 1;

$eStatus = "Active";

$vTimeZone = "Asia/Kolkata";

for ($g = 0; $g < count($db_code); $g++) {

    if ($db_code[$g]['vCountryCode'] == $DEFAULT_COUNTRY_CODE_WEB) {

        $vCountryCode = $db_code[$g]['vCountryCode'];

        $customer_info_vPhoneCode = $db_code[$g]['vPhoneCode'];

        $address = $db_code[$g]['vCountry'];

        $vTimeZone = $db_code[$g]['vTimeZone'];

    }

}

if($fromOrder == "store" && $_SESSION['sess_user'] == "company" && !empty($_SESSION['sess_iCompanyId'])){

    $db_company = $obj->MySQLSelect("SELECT vCountry,vCode from company where iCompanyId = '".$_SESSION['sess_iCompanyId']."'");

    $vCountryCode = $db_company[0]['vCountry'];

    $db_countrydata = $obj->MySQLSelect("SELECT vCountryCode,vCountry,vPhoneCode,vTimeZone from country where vCountryCode = '".$vCountryCode."'");

    $customer_info_vPhoneCode = $db_countrydata[0]['vPhoneCode'];

    $address = $db_countrydata[0]['vCountry'];

    $vTimeZone = $db_countrydata[0]['vTimeZone'];

}



//$customer_info_vPhoneCode = $generalobjAdmin->clearPhone($customer_info_vPhoneCode);

$customer_info_vPhone = $customer_info_vName = $customer_info_vLastName = $customer_info_vEmail = "";

$prepAddr = str_replace(' ', '+', $address);

$script = "Customer info";

$meta_arr = $generalobj->getStaticPage(1, $_SESSION['sess_lang']);

$meta_keyword = $meta_desc = "Manual Store Order Screen";

if (isset($meta_arr['meta_keyword'])) {

    $meta_keyword = $meta_arr['meta_keyword'];

}

if (isset($meta_arr['meta_desc'])) {

    $meta_desc = $meta_arr['meta_desc'];

}



$sqlcont="select vCountry,tLatitude,tLongitude from country where vCountryCode='".$vCountryCode."'";

$db_contry = $obj->MySQLSelect($sqlcont);



 $latitude = $db_contry[0]['tLatitude'];

 $longitude = $db_contry[0]['tLongitude'];

// $geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?&key=' . $GOOGLE_SEVER_API_KEY_WEB . '&address=' . $prepAddr . '&sensor=false');

// $output = json_decode($geocode);

//print_r($geocode);die;

// $latitude = $output->results[0]->geometry->location->lat;

//echo $latitude;die;

// $longitude = $output->results[0]->geometry->location->lng;

// $from_lat_long = '(' . $latitude . ', ' . $longitude . ')';

$_REQUEST["vLang"] = $_SESSION['sess_lang'];

//include('assets/libraries/configuration.php');

if (isset($serviceCategoriesTmp) && !empty($serviceCategoriesTmp)) {

    $service_categories = $serviceCategoriesTmp;

} else {

    $service_categories = array();

}

$checkUser = check_user_mr();

$emailExistsMsg = $langage_lbl['LBL_EMAIL_EXISTS_MSG'];

$pageHead = $SITE_NAME . " | " . $langage_lbl['LBL_MANUAL_STORE_ORDER_TXT'];



?>

<!DOCTYPE html>

<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr';?>">

    <head>

        <meta charset="UTF-8">

        <meta name="viewport" content="width=device-width,initial-scale=1">

        <title><?=$pageHead;?></title>

        <meta name="keywords" value="<?=$meta_keyword;?>"/>

        <meta name="description" value="<?=$meta_desc;?>"/>

        <!-- Default Top Script and css -->

        <?php include_once("top/top_script.php"); ?>

        <?php include_once("top/validation.php"); ?>

        <!-- End: Default Top Script and css-->

        <script src="//maps.google.com/maps/api/js?sensor=true&key=<?=$GOOGLE_SEVER_API_KEY_WEB?>&libraries=places,address" type="text/javascript"></script>

        <script type='text/javascript' src='assets/map/gmaps.js'></script>

        <script type='text/javascript' src='assets/js/jquery-ui.min.js'></script>

        <script type='text/javascript' src='assets/js/bootbox.min.js'></script>

        <link href="assets/css/radio.css" rel="stylesheet" type="text/css" />

        <?php include_once("store_css_include.php"); ?>

    </head>

    <body>

        <div id="main-uber-page">

            <?php include_once("top/left_menu.php"); ?>

            <?php include_once("top/header_topbar.php"); ?>

	    <?php if ($generalobj->checkXThemOn() == 'Yes') {?>

	    <div class="profile-section">

                <div class="profile-section-inner">

                    <div class="profile-caption">

                    <div class="page-heading">

                        <h1>

	    <?php } else {?>

            <div class="page-contant">

                <div class="page-contant-inner clearfix">

                    <h2 class="header-page trip-detail"><?php }?><?php echo $langage_lbl['LBL_MANUAL_STORE_ADD_USER_INFO']; ?>

		    <?php if($generalobj->checkXThemOn() == 'Yes') { ?></h1></div></div><?php } else { ?>

		    </h2>

		    <?php }?>

                    <div class="static-page static-page-new">



                        <?php

if (isset($_REQUEST['error']) && $_REQUEST['error'] != "") {

    ?>

                            <div class="row Syserror">

                                <div class="col-sm-12 alert alert-danger">

                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>

                                    <?=$_REQUEST['var_msg'];?>

                                </div>

                            </div>

                            <?php

}



?>

                        <div class="row msgforcustomer" style="display:none">

                            <div class="col-sm-12 alert alert-danger msgforcustomerdisplay">

                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>

                                <?=$_REQUEST['var_msg'];?>

                            </div>

                        </div>

                        <form action="customer_info_action.php" class="general-form-new" name="CustomerForm" id="CustomerForm" method="post" class="clearfix">

                            <input type="hidden" name="previousLink" id="previousLink" value=""/>

                            <input type="hidden" name="backlink" id="backlink" value="user-order-information"/>

                            <input type="hidden" name="distance" id="distance" value="<?=$vDistance;?>">

                            <input type="hidden" name="duration" id="duration" value="<?=$vDuration;?>">

                            <input type="hidden" name="from_lat_long" id="from_lat_long" value="<?=$from_lat_long;?>" >

                            <input type="hidden" name="from_lat" id="from_lat" value="<?=$latitude;?>" >

                            <input type="hidden" name="from_long" id="from_long" value="<?=$longitude;?>" >

                            <input type="hidden" value="1" id="location_found" name="location_found">

                            <input type="hidden" value="" id="user_type" name="user_type" >

                            <input type="hidden" value="0" id="areatext" name="areatext"  onChange="changearea()">

                            <input type="hidden" value="<?=$eStatus;?>" id="eStatus" name="eStatus" >

                            <input type="hidden" value="" id="serviceAdd" name="serviceAdd" >

                            <input type="hidden" value="<?=$vTimeZone;?>" id="vTimeZone" name="vTimeZone" >

                            <input type="hidden" value="<?=$vRideCountry;?>" id="vRideCountry" name="vRideCountry" >

                            <input type="hidden" value="<?=$GOOGLE_SEVER_API_KEY_WEB;?>" id="google_server_key" name="google_server_key" >

                            <input type="hidden" value="" id="getradius" name="getradius" >

                            <input type="hidden" value="KMs" id="eUnit" name="eUnit" >

                            <input type="hidden" name="fromOrder" id="fromOrder" value="<?=$fromOrder;?>" >

                            <?php if ($APP_TYPE != 'Ride-Delivery' && $APP_TYPE != 'Ride-Delivery-UberX' || ($APP_TYPE == 'Ride-Delivery' && $APP_DELIVERY_MODE == "Multi")) {?>

                                <input type="hidden" value="<?=$etype?>" id="eType" name="eType" />

                            <?php }?>

                            <div class="form-column newrow newrow-new">

                                <!-- <select name="customer_info_vCountry" id="customer_info_vCountry" onChange="changeCode(this.value);  getGeoCounty(this.value);

" required> --> 
                            <?php 
                                if(count($db_code) > 1){ 
                                      $style = "";
                                     }else{
                                    $style = " disabled=disabled ";
                                } ?>

                                <select <?= $style ?> name="customer_info_vCountry" id="customer_info_vCountry" onChange="changeCode(this.value);" required>

                                    <?php for ($i = 0; $i < count($db_code); $i++) { ?>
                                        <option value="<?=$db_code[$i]['vCountry']?>"
                                        <?php
                                            if ($db_code[$i]['vCountryCode'] == $vCountryCode) {
                                                echo "selected";
                                            }
                                            ?> >

                                                    <?=$db_code[$i]['vCountry'];?>

                                        </option>

                                    <? } ?>

                                </select>

                            </div>

                            <div class="form-column newrow country-code">

                                <input type="text"  name="customer_info_vPhoneCode"  id="customer_info_vPhoneCode" value="<?=$customer_info_vPhoneCode;?>"  readonly />

                                <input type="text"   pattern="[0-9]{1,}"  name="customer_info_vPhone" clear id="customer_info_vPhone" value="<?=$customer_info_vPhone;?>" autocomplete="off" placeholder="<?=$langage_lbl['LBL_777-777-7777'];?>" onblur="showPhoneDetail();"  />



                            </div>

                            <div class="form-column newrow">

                                <input type="email" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$" name="customer_info_vEmail" id="customer_info_vEmail" value="<?=$customer_info_vEmail;?>" placeholder="<?=$langage_lbl['LBL_ENTER_EMAIL_HINT'];?>" />

                            </div>

                            <div class="form-column half newrow">

                                <input type="text" name="customer_info_vName" id="customer_info_vName" value="<?=$customer_info_vName;?>"  placeholder="<?=$langage_lbl['LBL_FIRST_NAME_HEADER_TXT'];?>" required />

                            </div>

                            <div class="form-column half newrow">

                                <input type="text" name="customer_info_vLastName" id="customer_info_vLastName"  value="<?=$customer_info_vLastName;?>" placeholder="<?=$langage_lbl['LBL_LAST_NAME_HEADER_TXT'];?>" required />

                            </div>

                            <?php if (strtolower($checkUser) != 'store') {?>

                                <div class="form-column half" >

                                    <?php if (count($service_categories) == 1) {?>

                                        <input type="hidden" name="serviceid" id="serviceid" value="<?=$service_categories[0]['iServiceId'];?>" >

                                    <?php } else {?>

                                        <select name="serviceid">

                                            <?php

if (count($service_categories) > 0) {

    for ($i = 0; $i < count($service_categories); $i++) {

        $iServiceId = $service_categories[$i]['iServiceId'];

        if ($service_categories[$i]['vImage'] == "") {

            $service_categories[$i]['vImage'] = $tconfig['tsite_url'] . '/assets/img/burger.jpg';

        }

        ?>

                                                    <option value="<?php echo $iServiceId; ?>"><?php echo ucfirst($service_categories[$i]['vServiceName']); ?> </option>

                                                    <?php

}

}

    ?>

                                        </select>

                                    <?php }?>

                                </div>

                            <?php }?>

                            <div style="clear:both; width:100%;"></div>

                            <div class="form-column half newrow" style="margin-bottom:20px;" id="DeliveryAddress">

                                <input type="text" name="vServiceAddress" id="vServiceAddress" autocomplete="off" placeholder="<?=$langage_lbl['LBL_DELIVERY_ADDRESS'];?>"  />

                            </div>

                            <div class="form-column half newrow" style="margin-bottom:20px;" id="BuildingNo">

                                <input type="text" name="vBuildingNo" id="vBuildingNo" autocomplete="off" placeholder="<?php echo $langage_lbl['LBL_JOB_LOCATION_HINT_INFO']; ?>"  />

                            </div>

                            <div class="form-column half newrow" style="margin-bottom:20px;"  id="Landmark">

                                <input type="text" name="vLandmark" id="vLandmark" autocomplete="off" placeholder="<?php echo $langage_lbl['LBL_LANDMARK_HINT_INFO']; ?>"/>

                            </div>

                            <div class="form-column half" style="margin-bottom:20px;" id="AddressType">

                                <input type="text" name="vAddressType" id="vAddressType" autocomplete="off" placeholder="<?php echo $langage_lbl['LBL_ADDRESSTYPE_HINT_INFO']; ?>"/>

                            </div>

                            <div class="form-column-full" id="riderAddresses">



                            </div>



                            <div align="left" id="new_oneadd" class="form-button-block" style="display:none;" >

                                <input  type="button"  class="btn-submit" id="new_one"  value="<?php echo $langage_lbl['LBL_ADD_NEW_ADDRESS_TXT']; ?>" onclick="chacknew()">

                            </div>

                            <div align="left" id="old_oneadd" class="form-button-block" style="display:none;" >

                                <input  type="button"  class="btn-submit" id="old_one"  value="<?php echo $langage_lbl['LBL_OTHER_TXT'] . " " . $langage_lbl['LBL_PROFILE_ADDRESS']; ?>" onclick="displayOtherAddress()">

                            </div>

                            <div class="form-column-full"><div class="map-page">

                                    <div class="panel-heading location-map" style="background:none;">

                                        <div class="google-map-wrap">

                                            <div id="map-canvas" class="google-map" style="width:100%; height:400px;"></div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                            <div class="form-button-block"  style="margin-top:10px;" >

                                <button type="submit" name="SUBMIT" ><?=$langage_lbl['LBL_BTN_SUBMIT_TXT'];?></button>

                                <input type="button" name="reset" id="reset" class="cancel-btn" style="background-color:#000000;" value="<?=$langage_lbl['LBL_RESET'];?>"/>

                            </div>

                        </form>



                    </div>

                </div>

            </div>

            <?php if (empty($_SESSION['sess_iAdminUserId']) && strpos($_SERVER['SCRIPT_FILENAME'], 'customer_info.php') !== false) {

                include_once('footer/footer_home.php'); 

}?>

            <div style="clear:both;"></div>

        </div>

        <?php include_once('top/footer_script.php'); ?>

        <script src="assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>

        <script type="text/javascript" src="admin/js/moment.min.js"></script>

        <script type="text/javascript" src="admin/js/plugins/select2.min.js"></script>

        <script type="text/javascript" src="assets/js/validation/jquery.validate.min.js" ></script>

        <!-- End: Footer Script -->

        <script>

                            $(document).ready(function () {

                                $('#vServiceAddress').keyup(function (e) {

                                        buildAutoComplete("vServiceAddress",e, "<?=$MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE;?>","<?=$_SESSION['sess_lang'];?>", function(latitude, longitude, address){

                                            show_locations();

                                        }); // (orignal function)



                                    });



                                });

                                    $("#reset").on("click", function () {

                                        $("#customer_info_vPhone,#customer_info_vName,#customer_info_vLastName,#customer_info_vEmail,#BuildingNo,#Landmark,#DeliveryAddress,#AddressType").val('');

                                        showPhoneDetail();

                                    });

                                    function chkvalidation() {

                                        var from_lat = $('#from_lat').val();

                                        var from_long = $('#from_long').val();

                                        var iUserAddressId = $('#iUserAddressId:checked').val();

                                        var vBuildingNo = $('#vBuildingNo').val();

                                        var Landmark = $('#vLandmark').val();

                                        if ($.trim(from_lat) != "" && $.trim(from_long) != "" && $.trim(Landmark) != "" && $.trim(vBuildingNo) != "") {

                                            $.ajax({

                                                type: "POST",

                                                url: 'ajax_check_address_store.php',

                                                dataType: 'json',

                                                data: {from_lat: from_lat, from_long: from_long, iUserAddressId: iUserAddressId},

                                                success: function (dataHTML)

                                                {

                                                    if (dataHTML.Action == 0)

                                                    {

                                                        $('.msgforcustomerdisplay').html('<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>' + dataHTML.message);

                                                        $('.msgforcustomer').show();

                                                        if ($('.Syserror').length > 0) {

                                                            $('.Syserror').hide();

                                                            $('#vServiceAddress').focus();

                                                        }

                                                        return false;

                                                    } else {

                                                        $('.msgforcustomer').hide();

                                                        $('.msgforcustomerdisplay').html('');



                                                        if ($('.Syserror').length > 0) {

                                                            $('.Syserror').show();

                                                        }

                                                        $('#CustomerForm').submit();

                                                        return true;

                                                    }

                                                }

                                            });

                                            return false;

                                        } else {

                                            $('.msgforcustomer').hide();

                                            $('.msgforcustomerdisplay').html('');

                                            if ($('.Syserror').length > 0) {

                                                $('.Syserror').show();

                                            }

                                            return true;

                                        }

                                        //return false;

                                    }

                                    var errormessage;

                                    $('#CustomerForm').validate({

                                        ignore: 'input[type=hidden]',

                                        errorClass: 'help-block error',

                                        errorElement: 'span',

                                        errorPlacement: function (error, e) {

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

                                        rules: {

                                            customer_info_vCountry: {required: true},

                                            customer_info_vPhone: {required: true, minlength: 3, digits: true,

                                                remote: {

                                                    url: 'ajax_rider_mobile_new.php',

                                                    type: "post",

                                                    data: {iUserId: ''},

                                                    dataFilter: function (response) {

                                                        //response = $.parseJSON(response);

                                                        if (response == 'deleted') {

                                                            errormessage = "<?=addslashes($langage_lbl['LBL_PHONE_CHECK_DELETE_ACCOUNT']);?>";

                                                            return false;

                                                        } else if (response == 'false') {

                                                            errormessage = "<?=addslashes($langage_lbl['LBL_PHONE_EXIST_MSG']);?>";

                                                            return false;

                                                        } else {

                                                            return true;

                                                        }

                                                    },

                                                }

                                            },

                                            customer_info_vName: {required: true, minlength: 2, maxlength: 30},

                                            customer_info_vLastName: {required: true, minlength: 2, maxlength: 30},

                                            customer_info_vEmail: {required: true, email: true},

                                        },

                                        messages: {

                                            customer_info_vPhone: {required: '<?php echo addslashes($langage_lbl['LBL_MANUAL_STORE_REQUIRED_FIELD']) . '.'; ?>', minlength: '<?php echo addslashes($langage_lbl['LBL_MANUAL_STORE_PLEASE_ENTER_AT_LEAST']) . ' 3 ' . addslashes($langage_lbl['LBL_MANUAL_STORE_NUMBER']) . '.'; ?>', digits: '<?php echo addslashes($langage_lbl['LBL_PHONE_VALID_MSG']); ?>', remote: function () {

                                                    return errormessage;

                                                }},

                                            customer_info_vName: {required: '<?php echo addslashes($langage_lbl['LBL_MANUAL_STORE_REQUIRED_FIELD']) . '.'; ?>', minlength: '<?php echo addslashes($langage_lbl['LBL_MANUAL_STORE_PLEASE_ENTER_AT_LEAST']) . ' 2 ' . addslashes($langage_lbl['LBL_MANUAL_STORE_CHARACTERS']) . '.'; ?>', maxlength: '<?php echo addslashes($langage_lbl['LBL_MANUAL_STORE_PLEASE_ENTER_NO_MORE_THAN'] . ' 30 ' . $langage_lbl['LBL_MANUAL_STORE_CHARACTERS']) . '.'; ?>'},

                                            customer_info_vLastName: {required: '<?php echo addslashes($langage_lbl['LBL_MANUAL_STORE_REQUIRED_FIELD']) . '.'; ?>', minlength: '<?php echo addslashes($langage_lbl['LBL_MANUAL_STORE_PLEASE_ENTER_AT_LEAST'] . ' 2 ' . $langage_lbl['LBL_MANUAL_STORE_CHARACTERS']) . '.'; ?>', maxlength: '<?php echo addslashes($langage_lbl['LBL_MANUAL_STORE_PLEASE_ENTER_NO_MORE_THAN'] . ' 30 ' . $langage_lbl['LBL_MANUAL_STORE_CHARACTERS']) . '.'; ?>'},

                                            customer_info_vEmail: {required: '<?php echo addslashes($langage_lbl['LBL_MANUAL_STORE_REQUIRED_FIELD']) . '.'; ?>', email: '<?php echo addslashes($langage_lbl['LBL_FEILD_EMAIL_ERROR_TXT_IPHONE']) . '.'; ?>.'},

                                            vServiceAddress: {required: '<?php echo addslashes($langage_lbl['LBL_MANUAL_STORE_REQUIRED_FIELD']) . '.'; ?>'},

                                            vBuildingNo: {required: '<?php echo addslashes($langage_lbl['LBL_MANUAL_STORE_REQUIRED_FIELD']) . '.'; ?>'},

                                            vLandmark: {required: '<?php echo addslashes($langage_lbl['LBL_MANUAL_STORE_REQUIRED_FIELD']) . '.'; ?>'}

                                        },

                                        submitHandler: function (form) {
                                            $("#customer_info_vCountry").prop('disabled',false);
                                            return true;

                                        }

                                    });

                                    function chacknew() {

                                        $("#old_oneadd").show();

                                        $('input[name=iUserAddressId]').attr('checked', false);

                                        $('#new_oneadd,#riderAddresses').hide();

                                        $('#DeliveryAddress,#BuildingNo,#Landmark,#AddressType').show();

                                        $('#areatext').val(0);

                                        document.getElementById("vBuildingNo").required = true;

                                        document.getElementById("vLandmark").required = true;

                                        document.getElementById("vServiceAddress").required = true;

                                        return false;

                                    }

                                    function displayOtherAddress() {

                                        showPhoneDetail();

                                        $("#old_oneadd").hide();

                                    }

                                    function isNumberKey(evt) {

                                        var charCode = (evt.which) ? evt.which : evt.keyCode

                                        if (charCode > 31 && (charCode < 35 || charCode > 57)) {

                                            return false;

                                        } else {

                                            return true;

                                        }

                                    }

                                    function changeCode(id) {

                                        $.ajax({

                                            type: "POST",

                                            url: 'change_check_code.php',

                                            dataType: 'json',

                                            data: {id: id, eUnit: 'yes'},

                                            success: function (dataHTML)

                                            {

                                                document.getElementById("customer_info_vPhoneCode").value = dataHTML.vPhoneCode;

                                                document.getElementById("eUnit").value = dataHTML.eUnit;

                                                document.getElementById("vRideCountry").value = dataHTML.vCountryCode;

                                                document.getElementById("vTimeZone").value = dataHTML.vTimeZone;

                                                showPhoneDetail();

                                            }

                                        });

                                    }

                                    function checkCustomerEmail() {

                                        var custEmail = $("#customer_info_vEmail").val();

                                        var phone = $('#customer_info_vPhone').val();

                                        var phoneCode = $('#customer_info_vPhoneCode').val();

                                        $.ajax({

                                            type: "POST",

                                            url: 'ajax_rider_mobile_new.php',

                                            dataType: 'json',

                                            data: {type: "email", custEmail: custEmail, phone: phone, phoneCode: phoneCode},

                                            success: function (dataHTML)

                                            {

                                                if (dataHTML > 0) {

                                                    alert("<?=$emailExistsMsg;?>");

                                                    $("#customer_info_vEmail").val("");

                                                }

                                            }

                                        });

                                    }

                                    function showPhoneDetail() {

                                        var phone = $('#customer_info_vPhone').val();

                                        var phoneCode = $('#customer_info_vPhoneCode').val();

                                        $("#vServiceAddress").val("");

                                        if (phone != "" && phoneCode != "") {

                                            $.ajax({

                                                type: "POST",

                                                url: 'ajax_find_rider_by_number.php',

                                                data: {phone: phone, phoneCode: phoneCode},

                                                dataType: "json",

                                                success: function (dataHtml)

                                                {

                                                    if (dataHtml.cont != "" && dataHtml.count > 0) {

                                                        $('#riderAddresses,#new_oneadd').show();

                                                        var result = dataHtml.cont.split(':');

                                                        $('#customer_info_vName').val(result[0]);

                                                        $('#customer_info_vLastName').val(result[1]);

                                                        $('#customer_info_vEmail').val(result[2]);

                                                        $('#iUserId').val(result[3]);

                                                        $('#eStatus').val(result[4]);

                                                        $('#areatext').val(1);

                                                        $('#DeliveryAddress,#BuildingNo,#Landmark,#AddressType').hide();

                                                        if (dataHtml.workAddress != "" && dataHtml.vLatitude != "" && dataHtml.vLongitude != "") {

                                                            var results = dataHtml.workAddress;

                                                            document.getElementById("vBuildingNo").required = false;

                                                            document.getElementById("vLandmark").required = false;

                                                            document.getElementById("vServiceAddress").required = false;

                                                            $('#riderAddresses').html(results);

                                                            var vUserAddressLatitude = dataHtml.vLatitude;

                                                            var vUserAddressLongitude = dataHtml.vLongitude;

                                                            var myLatlongs = new google.maps.LatLng(vUserAddressLatitude, vUserAddressLongitude);

                                                            var showsurgemodal = "No";

                                                            $("#from_lat").val(vUserAddressLatitude);

                                                            $("#from_long").val(vUserAddressLongitude);

                                                            $("#from_lat_long").val(myLatlongs);

                                                            getAddress(vUserAddressLatitude, vUserAddressLongitude, 'serviceAdd','from');

                                                            show_locationsper();

                                                        } else {

                                                            var result = dataHtml.cont.split(':');

                                                            var workAddressNew = dataHtml.workAddress;

                                                            $('#customer_info_vName').val(result[0]);

                                                            $('#customer_info_vLastName').val(result[1]);

                                                            $('#customer_info_vEmail').val(result[2]);

                                                            $('#iUserId').val(result[3]);

                                                            $('#eStatus').val(result[4]);

                                                            $('#areatext').val(1);

                                                            $('#riderAddresses').html(workAddressNew);

                                                            document.getElementById("vBuildingNo").required = true;

                                                            document.getElementById("vLandmark").required = true;

                                                            document.getElementById("vServiceAddress").required = true;

                                                            $('#DeliveryAddress,#BuildingNo,#Landmark,#AddressType').show();

                                                            $('#new_oneadd,#riderAddresses').hide();

                                                        }

                                                    } else {

                                                        $('#customer_info_vName,#customer_info_vLastName,#customer_info_vEmail,#iUserId,#eStatus').val('');

                                                        $('#areatext').val(0);

                                                        document.getElementById("vBuildingNo").required = true;

                                                        document.getElementById("vLandmark").required = true;

                                                        document.getElementById("vServiceAddress").required = true;

                                                        $('#DeliveryAddress,#BuildingNo,#Landmark,#AddressType').show();

                                                        $('#new_oneadd,#riderAddresses').hide();

                                                    }

                                                    //checkCustomerEmail();

                                                }



                                            });

                                        } else {

                                            document.getElementById("vBuildingNo").required = true;

                                            document.getElementById("vLandmark").required = true;

                                            document.getElementById("vServiceAddress").required = true;

                                            $('#areatext').val(0);

                                            $('#DeliveryAddress,#BuildingNo,#Landmark,#AddressType').show();

                                            $('#new_oneadd,#riderAddresses').hide();

                                            $("#user_type,#customer_info_vName,#customer_info_vLastName,#customer_info_vEmail,#iUserId").val('');

                                            var serviceAdd = $("#serviceAdd").val();

                                            if (serviceAdd != "") {

                                                $("#vServiceAddress").val(serviceAdd);

                                                if ($("#vServiceAddress").val() != "") {

                                                    DeleteMarkers('from_loc');

                                                    var latlng = new google.maps.LatLng($("#from_lat").val(), $("#from_long").val());

                                                    setMarker(latlng, 'from_loc');

                                                }

                                            }

                                        }

                                    }

                                    function changelocation(idss, vLatitude, vLongitude) {

                                       // console.log(idss + "====" + vLatitude + "====" + vLongitude);

                                        var vUserAddressLatitude = vLatitude;

                                        var vUserAddressLongitude = vLongitude;

                                        var myLatlongs = new google.maps.LatLng(vUserAddressLatitude, vUserAddressLongitude);

                                        var showsurgemodal = "No";

                                        var from_latold = $("#from_lat").val();

                                            var from_longold = $("#from_long").val();

                                            var from_lat_longold = $("#from_lat_long").val();

                                            var from_addressold = $("#vServiceAddress").val();

                                        $("#from_lat").val(vUserAddressLatitude);

                                        $("#from_long").val(vUserAddressLongitude);

                                        $("#from_lat_long").val(myLatlongs);

                                        getAddress(vUserAddressLatitude, vUserAddressLongitude, 'serviceAdd','from',from_latold,from_longold,from_lat_longold,from_addressold);

                                        show_locationsper();

                                    }

                                    var eFlatTrip = 'No';

                                    var eTypeQ11 = 'yes';

                                    var map;

                                    // var geocoder;

                                    var circle;

                                    var markers = [];

                                    var driverMarkers = [];

                                    var bounds = [];

                                    var newLocations = "";

                                    var autocomplete_from;

                                    var autocomplete_to;

                                    // var geocoder = new google.maps.Geocoder();

                                    var directionsService = new google.maps.DirectionsService(); // For Route Services on map

                                    var directionsOptions = {// For Polyline Route line options on map

                                        polylineOptions: {

                                            strokeColor: '#FF7E00',

                                            strokeWeight: 5

                                        }

                                    };

                                    var directionsDisplay = new google.maps.DirectionsRenderer(directionsOptions);

                                    var showsurgemodal = "Yes";

                                    var status;

                                    var eType = "";

                                    var APP_DELIVERY_MODE = '<?=$APP_DELIVERY_MODE?>';

                                    var ENABLE_TOLL_COST = "<?=$ENABLE_TOLL_COST?>";

                                    // alert(APP_DELIVERY_MODE);

                                    switch ("<?php echo $APP_TYPE; ?>") {

                                        case "Ride-Delivery":

                                            if (APP_DELIVERY_MODE == "Multi") {

                                                eType = 'Ride';

                                            }

                                            break;

                                        case "Ride-Delivery-UberX":

                                            if (APP_DELIVERY_MODE == "Multi") {

                                                eType = 'Ride';

                                            }

                                            break;

                                        case "Delivery":

                                            eType = 'Deliver';

                                            break;

                                        case "UberX":

                                            eType = 'UberX';

                                            break;

                                        default:

                                            eType = 'Ride';

                                    }

                                    function initialize() {

                                        var bounds = new google.maps.LatLngBounds();

                                        var from_lat = $('#from_lat').val();

                                        var from_lng = $('#from_long').val();

                                        var thePoint = new google.maps.LatLng(from_lat, from_lng);

                                        bounds.extend(thePoint);

                                        var mapOptions = {

                                            zoom: 4,

                                            center: thePoint

                                        };

                                        map = new google.maps.Map(document.getElementById('map-canvas'),

                                                mapOptions);

                                        map.fitBounds(bounds);

                                        zoomChangeBoundsListener = google.maps.event.addListenerOnce(map, 'bounds_changed', function (event) {

                                            if (this.getZoom()) {

                                                this.setZoom(12);

                                            }

                                        });

                                        if (eType == "Deliver") {

                                            show_type(eType);

                                        }

                                        callEditFundtion()

                                    }

                                    $(document).ready(function () {

                                        google.maps.event.addDomListener(window, 'load', initialize);

                                        setDriversMarkers('test');

                                        $("#eType").val(eType);

                                        // var lat = event.latLng.lat();

                                        //     var lng = event.latLng.lng();

                                        //     var myLatlongs = new google.maps.LatLng(lat, lng);

                                            showsurgemodal = "No";

                                            var from_latold = $("#from_lat").val();

                                            var from_longold = $("#from_long").val();

                                            var from_lat_longold = $("#from_lat_long").val();

                                            var from_addressold = $("#vServiceAddress").val();

                                        getAddress($("#from_lat").val(), $("#from_long").val(), 'vServiceAddress','from',from_latold,from_longold,from_lat_longold,from_addressold);

                                    });

                                    function setDriversMarkers(flag) {

                                        newType = $("#newType").val();

                                    }

                                    function getAddress(mDlatitude, mDlongitude, addId,setLatLongField,oldlat,oldlong,oldlatlong,oldAddress) {

                                        var mylatlang = new google.maps.LatLng(mDlatitude, mDlongitude);

                                        getReverseGeoCode(addId,setLatLongField,sess_lang,mDlatitude, mDlongitude, oldlat, oldlong, oldlatlong, oldAddress, function(latitude, longitude, address){

                                             show_locationsper();

                                        // geocoder.geocode({'latLng': mylatlang},

                                        //         function (results, status) {

                                        //             console.log(results);

                                        //             if (status == google.maps.GeocoderStatus.OK) {

                                        //                 if (results[0]) {



                                        //                     $('#' + addId).val(results[0].formatted_address);



                                        //                 } else {

                                        //                     document.getElementById('#' + addId).value = "No results";

                                        //                 }

                                        //             } else {

                                        //                 document.getElementById('#' + addId).value = status;

                                        //             }

                                        //         });

                                                });

                                    }

                                    function show_locationsper() {

                                        DeleteMarkers('from_loc');



                                        var latlng = new google.maps.LatLng($("#from_lat").val(), $("#from_long").val());

                                        setMarker(latlng, 'from_loc');

                                    }

                                    function setMarkers(postitions, valIcon) {

                                        var newIcon;

                                        if (valIcon == 'from_loc') {

                                            newIcon = '<?php echo $tconfig["tsite_url"] ?>webimages/upload/mapmarker/source_marker.png';

                                        }

                                        var marker = new google.maps.Marker({

                                            map: map,

                                            animation: google.maps.Animation.DROP,

                                            position: postitions,

                                            icon: newIcon

                                        });

                                        marker.id = valIcon;

                                        markers.push(marker);

                                        map.setCenter(marker.getPosition());

                                        map.setZoom(15);



                                    }

                                    function setMarker(postitions, valIcon) {

                                        var newIcon;

                                        if (valIcon == 'from_loc') {

                                            newIcon = '<?php echo $tconfig["tsite_url"] ?>webimages/upload/mapmarker/source_marker.png';

                                        }

                                        var marker = new google.maps.Marker({

                                            map: map,

                                            draggable: true,

                                            animation: google.maps.Animation.DROP,

                                            position: postitions,

                                            icon: newIcon

                                        });

                                        marker.id = valIcon;

                                        markers.push(marker);

                                        map.setCenter(marker.getPosition());

                                        map.setZoom(15);

                                        if (valIcon == "from_loc") {

                                            marker.addListener('dragend', function (event) {

                                                    // var lat = event.latLng.lat();

                                                    // var lng = event.latLng.lng();

                                                    // var myLatlongs = new google.maps.LatLng(lat, lng);

                                                    // showsurgemodal = "No";

                                                    // $("#from_lat").val(lat);

                                                    // $("#from_long").val(lng);

                                                    // $("#from_lat_long").val(myLatlongs);

                                                    // getAddress(lat, lng, 'vServiceAddress');

                                                    var lat = event.latLng.lat();

                                                    var lng = event.latLng.lng();

                                                    var myLatlongs = new google.maps.LatLng(lat, lng);

                                                    showsurgemodal = "No";

                                                    var from_latold = $("#from_lat").val();

                                                    var from_longold = $("#from_long").val();

                                                    var from_lat_longold = $("#from_lat_long").val();

                                                    var from_addressold = $("#vServiceAddress").val();

                                                    $("#from_lat").val(lat);

                                                    $("#from_long").val(lng);

                                                    $("#from_lat_long").val(myLatlongs);

                                                    getAddress(lat, lng, 'vServiceAddress','from',from_latold,from_longold,from_lat_longold,from_addressold);

                                                    // console.log($("#from_lat").val() +"=="+ $("#from_long").val());

                                                });

                                        }

                                    }

                                    function show_locations() {

                                        if ($("#vServiceAddress").val() != "") {

                                            DeleteMarkers('from_loc');

                                            var latlng = new google.maps.LatLng($("#from_lat").val(), $("#from_long").val());

                                            setMarker(latlng, 'from_loc');

                                        }

                                    }

                                    function DeleteMarkers(newId) {

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

                                    function callEditFundtion() {

                                        var from_lat = $('#from_lat').val();

                                        var from_lng = $('#from_long').val();

                                        var from = new google.maps.LatLng(from_lat, from_lng);

                                        if (from != '') {

                                            setMarker(from, 'from_loc');

                                        }

                                    }



                                    // $(function () {

                                    //     var from = document.getElementById('vServiceAddress');

                                    //     autocomplete_from = new google.maps.places.Autocomplete(from);

                                    //     google.maps.event.addListener(autocomplete_from, 'place_changed', function () {

                                    //         var place = autocomplete_from.getPlace();

                                    //         $("#from_lat_long").val(place.geometry.location);

                                    //         $("#from_lat").val(place.geometry.location.lat());

                                    //         $("#from_long").val(place.geometry.location.lng());

                                    //         show_locations();

                                    //     });

                                    // });









                                    // function from_to(from) {

                                    //     DeleteMarkers('from_loc');

                                    //     if (from == '')

                                    //         var from = document.getElementById('vServiceAddress');

                                    //     $("#from_lat_long,#from_lat,#from_long").val('');

                                    //     if (from != '') {

                                    //         geocoder.geocode({'address': from}, function (results, status) {

                                    //             if (status == google.maps.GeocoderStatus.OK) {

                                    //                 if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {

                                    //                     // console.log(results[0].geometry.location);

                                    //                     $("#from_lat_long").val((results[0].geometry.location));

                                    //                     $("#from_lat").val(results[0].geometry.location.lat());

                                    //                     $("#from_long").val(results[0].geometry.location.lng());



                                    //                     setMarker(results[0].geometry.location, 'from_loc');

                                    //                 } else {

                                    //                     alert("No results found");

                                    //                 }

                                    //             } else {

                                    //                 var place19 = autocomplete_from.getPlace();

                                    //                 $("#from_lat_long").val(place19.geometry.location);

                                    //             }

                                    //         });

                                    //     }

                                    // }



                                    // function getGeoCounty(Countryname) {

                                    //     var geocoder = new google.maps.Geocoder();

                                    //     var address = Countryname;

                                    //     var tlat, tlong;

                                    //     geocoder.geocode({'address': address}, function (results, status) {

                                    //         if (status == google.maps.GeocoderStatus.OK)

                                    //         {

                                    //             var from_latold = $("#from_lat").val();

                                    //                 var from_longold = $("#from_long").val();

                                    //                 var from_lat_longold = $("#from_lat_long").val();

                                    //                 var from_addressold = $("#vServiceAddress").val();

                                    //             tlat = results[0].geometry.location.lat();

                                    //             $('#from_lat').val(tlat);

                                    //             tlong = results[0].geometry.location.lng();

                                    //             $('#from_long').val(tlong);

                                    //             getAddress(tlat, tlong, 'vServiceAddress','from',from_longold,from_lat_longold,from_addressold);

                                    //             if (tlat != '' && tlong != '') {

                                    //                 play();

                                    //             }

                                    //         }

                                    //     });

                                    // }

                                    $country = $('#customer_info_vCountry').val()

                                    /*$('#from_lat').val('37.09024');

                                    $('#from_long').val('-95.712891');*/

                                    // getGeoCounty($country);

									var tlat = $("#from_lat").val();

									var tlong = $("#from_long").val();

									var from_longold = '';

									var from_lat_longold = '';

									var from_addressold = '';

                                    getAddress(tlat, tlong, 'vServiceAddress','from',from_longold,from_lat_longold,from_addressold);

									

                                    function play(tlat, tlong) {

                                        var bounds = new google.maps.LatLngBounds();

                                        var pt = new google.maps.LatLng($("#from_lat").val(), $("#from_long").val());

                                        bounds.extend(pt);

                                        map.setCenter(pt);

                                        map.fitBounds(bounds);

                                        zoomChangeBoundsListener = google.maps.event.addListenerOnce(map, 'bounds_changed', function (event) {

                                            if (this.getZoom()) {

                                                this.setZoom(12);

                                            }

                                        });

                                        DeleteMarkers('from_loc');

                                        callEditFundtion();

                                    }

									$(document).ready(function () {

                                        google.maps.event.addDomListener(window, 'load', initialize);

                                        setDriversMarkers('test');

                                        $("#eType").val(eType);

                                        // var lat = event.latLng.lat();

                                        //     var lng = event.latLng.lng();

                                        //     var myLatlongs = new google.maps.LatLng(lat, lng);

                                            showsurgemodal = "No";

                                            var from_latold = $("#from_lat").val();

                                            var from_longold = $("#from_long").val();

                                            var from_lat_longold = $("#from_lat_long").val();

                                            var from_addressold = $("#vServiceAddress").val();

                                        getAddress($("#from_lat").val(), $("#from_long").val(), 'vServiceAddress','from',from_latold,from_longold,from_lat_longold,from_addressold);

                                    });

                                     function change_lang(lang) {
                                         document.location = 'common.php?lang=' + lang;
                                     }
        </script>

    </body>

</html>

