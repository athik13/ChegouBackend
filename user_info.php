<?php
include_once "common.php";
//ini_set('display_errors', 1);
//error_reporting(E_ALL);

include_once 'include_generalFunctions_dl.php';
check_type_wise_mr('user_info');
$_REQUEST["vLang"] = $_SESSION['sess_lang'];
include_once 'assets/libraries/configuration.php';

$fromOrder = "guest";
if (isset($_REQUEST['order']) && $_REQUEST['order'] != "") {
    $fromOrder = $_REQUEST['order'];
}

if(($fromOrder == "guest" || $fromOrder == "user") && $_SESSION['sess_user'] != "rider")
{
    header('Location:profile');
}

$orderLatitudeSession = "MANUAL_ORDER_LATITUDE_" . strtoupper($fromOrder);
$orderLongitudeSession = "MANUAL_ORDER_LONGITUDE_" . strtoupper($fromOrder);
$orderServiceSession = "MAUAL_ORDER_SERVICE_" . strtoupper($fromOrder);
$orderAddressSession = "MANUAL_ORDER_ADDRESS_".strtoupper($fromOrder);
$orderServiceNameSession = "MANUAL_ORDER_SERVICE_NAME_".strtoupper($fromOrder);
if(checkSystemStoreSelection()) {
    $service_categories = array();
    if (isset($serviceCategoriesTmp) && !empty($serviceCategoriesTmp)) {
        $service_categories = $serviceCategoriesTmp;
    }
    $cnt_sc = count($service_categories);
    if($cnt_sc==1) {
        session_start();
        $store_data = $generalobj->getStoreDataForSystemStoreSelection($service_categories[0]['iServiceId']);
        //$iCompanyId = $store_data[0]['iCompanyId'];
        $iCompanyId = $store_data['iCompanyId'];
        
        $_SESSION[$orderLongitudeSession] = $store_data['vRestuarantLocationLat'];
        $_SESSION[$orderLatitudeSession] = $store_data['vRestuarantLocationLong'];
        $_SESSION[$orderServiceSession] = $store_data['iServiceId'];
        $_SESSION[$orderAddressSession] = $store_data['vCaddress'];
        $_SESSION[$orderServiceNameSession] = $service_categories[($store_data['iServiceId'] - 1)]['vServiceName'];
        header("location: store-items?id=" . $iCompanyId . "&order=" . $fromOrder);
        exit;
    }
}
$service_categories = array();
if (isset($serviceCategoriesTmp) && !empty($serviceCategoriesTmp)) {
    $service_categories = $serviceCategoriesTmp;
}
$sql1 = "SELECT iServiceId FROM `service_categories` WHERE iServiceId != '1' AND eStatus='Active' ORDER BY iServiceId LIMIT 1";
$servicecatid = $obj->MySQLSelect($sql1);
$langage_lblDataOther = getLanguageLabelsArr($_SESSION['sess_lang'], '1', $servicecatid[0]['iServiceId']);

$siteUrl = $tconfig['tsite_url'];
$foodButtonName = $langage_lbl['LBL_MANUAL_SHOW_RESTAURANTS'];
$otherButtonName = $langage_lbl['LBL_MANUAL_SHOW_STORE'];
$pageHead = $SITE_NAME . " | " . $langage_lbl['LBL_ORDER_ITEMS_MANUAL_TXT'];
//echo $SITE_NAME;die;

$db_orderimage = $generalobj->getStaticPage(53, $_SESSION['sess_lang']);
$vImage = $db_orderimage[0]['vImage'];
$script = "order-items";

foreach($service_categories as $key=> $value) {
    if($value['vService']==strtolower($_SESSION["navigatedPage"])) {
        $selectedServiceIdOrderItems = $value['iServiceId'];
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= $pageHead; ?></title>
        <meta name="keywords" value="<?= $meta_arr['meta_keyword']; ?>"/>
        <meta name="description" value="<?= $meta_arr['meta_desc']; ?>"/>
        <!-- Default Top Script and css -->
        <?php include_once "top/top_script.php"; ?>
        <?php include_once "top/validation.php"; ?>

        <!-- End: Default Top Script and css-->
        <script src="//maps.google.com/maps/api/js?sensor=true&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>&libraries=places,address" type="text/javascript"></script>
        <script type='text/javascript' src='<?= $siteUrl; ?>assets/map/gmaps.js'></script>
        <script type='text/javascript' src='<?= $siteUrl; ?>assets/js/jquery-ui.min.js'></script>
        <script type='text/javascript' src='<?= $siteUrl; ?>assets/js/bootbox.min.js'></script>
        <link href="<?= $siteUrl; ?>assets/css/radio.css" rel="stylesheet" type="text/css" />


        <?php include_once "store_css_include.php"; ?>
        <style>

            .user_info_address{
                position:relative;
            }
            .user_info_address .progress-indeterminate{
                position:absolute;
                width: calc(100% - 366px)!important;
            }


            @media (max-width: 768px){
                .know-more-btn.hidden-md.btn-singin-new {
                    display: block !important;
                }
                .user_info_address .progress-indeterminate {
                    position: absolute;
                    top:51px;
                    width: calc(100% - 366px);
                    width: -o-calc(100% - 366px);
                    width: -ms-calc(100% - 366px);
                    width: -moz-calc(100% - 366px);
                    width: -webkit-calc(100% - 366px);
                    left:0px!important;
                }
            }
            @media (max-width: 767px){
                .know-more-btn.hidden-md.btn-singin-new {
                    display: block !important;
                }
                .user_info_address .progress-indeterminate {
                    position: absolute;
                    width: calc(100% - 31px) !important;
                    width: -o-calc(100% - 31px);
                    width: -ms-calc(100% - 31px);
                    width: -moz-calc(100% - 31px);
                    width: -webkit-calc(100% - 31px);
                    top:47px;
                    left:16px!important;
                }
            }

            // theme 2
            .has-not-categories{
                position:relative;
            }
            .has-not-categories .progress-indeterminate{
                position:absolute;
                width: calc(100% - 268px)!important;
            }


            @media (max-width: 768px){
                .know-more-btn.hidden-md.btn-singin-new {
                    display: block !important;
                }
                .has-not-categories .progress-indeterminate {
                    position: absolute;
                    top:46px;
                    width: calc(100% - 247px);
                    width: -o-calc(100% - 268px);
                    width: -ms-calc(100% - 268px);
                    width: -moz-calc(100% - 268px);
                    width: -webkit-calc(100% - 268px);
                    left:0px!important;
                }
            }
            @media (max-width: 767px){
                .know-more-btn.hidden-md.btn-singin-new {
                    display: block !important;
                }
                .has-not-categories .progress-indeterminate {
                    position: absolute;
                    width: calc(100% - 31px) !important;
                    width: -o-calc(100% - 31px);
                    width: -ms-calc(100% - 31px);
                    width: -moz-calc(100% - 31px);
                    width: -webkit-calc(100% - 31px);
                    top:47px;
                    left:16px!important;
                }
            }


        </style>
    </head>
    <body>
        <div id="main-uber-page">
            <!-- Left Menu -->
            <?php include_once "top/left_menu.php"; ?>
            <!-- End: Left Menu-->
            <!-- home page -->
            <!-- Top Menu -->
            <?php include_once "top/header_topbar.php"; ?>
            <!-- End: Top Menu-->
            <!-- contact page-->

            <div class="page-contant _MB0_ GRAYBG mainof-searchpage">
                <div class="search-banner" style="background-image: url(<?php echo $tconfig["tsite_upload_page_images"] . '' . $vImage; ?>)">
                    <div class="page-contant-inner set-min-height clearfix">
                        <!-- trips detail page -->
                        <div class="search-page-wrap">
                            <?php
                            if (isset($_REQUEST['error']) && $_REQUEST['error'] != "") {
                                ?>
                                <div class="row">
                                    <div class="col-sm-12 alert alert-danger">
                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                        <?= $_REQUEST['var_msg']; ?>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                            <div>
                                <h3 class="search-head" id ="search-head"><?= $langage_lbl['LBL_MANUAL_STORE_USER_MAIN_TEXT']; ?></h3>
                                <p><?= $langage_lbl['LBL_MANUAL_STORE_USER_MAIN_DESCRIPTION']; ?></p>
                                <form action="<?= $siteUrl; ?>user_info_action.php" class="search-main-form" name="CustomerForm" id="CustomerForm" method="post" class="clearfix">
                                    <input type="hidden" name="from_lat_long" id="from_lat_long" value="<?= $from_lat_long; ?>" >
                                    <input type="hidden" name="from_lat" id="from_lat" value="<?= $latitude; ?>"class="from_lat"  >
                                    <input type="hidden" name="from_long" id="from_long" value="<?= $longitude; ?>" >
                                    <input type="hidden" name="fromOrder" id="fromOrder" value="<?= $fromOrder; ?>" >

                                    <?php
                                    $service_categories_classs = "";
                                    if (count($service_categories) > 0) {
                                        if (count($service_categories) == 1) {
                                            $service_categories_classs = 'has-not-categories';
                                        }
                                    }
                                    ?>
                                    <div id="DeliveryAddress" class="deliver-address user_info_address newrow <?php echo $service_categories_classs; ?>">
                                        <div class="user_info_input">
                                            <input class="delivery-input" type="text" name="vServiceAddress" id="vServiceAddress" autocomplete="off" placeholder="<?= $langage_lbl['LBL_MANUAL_SEARCH_BOX_PLACEHOLDER']; ?>" required=""/>
                                        </div>
                                        <!-- <div class="delivery-click-event"></div> -->
                                        <!-- <div class="progress" style="width:100%;height:5px;">
<div class="progress-bar progress-bar-striped progress-bar-animated active bg-light" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%">    
</div>
</div> -->

                                        <span class="col-clear" style="display: none;z-index:9999999;" id="clearbutton">
                                            <img src="<?= $siteUrl; ?>assets/img/cancel.svg" alt="" onClick="clearSearchBox();" class="close_ico">
                                        </span>
                                        <?php
                                        $addCss = "";
                                        if (count($service_categories) > 0) {
                                            if (count($service_categories) == 1) {
                                                $addCss = 'style="width:268px;"';
                                                ?>
                                                <input type="hidden" name="serviceid" id="serviceid" value="<?= $service_categories[0]['iServiceId']; ?>" >
                                                <?php
                                            } else {
                                                ?>
                                                <select name="serviceid" id="serviceid" onchange="renameButtonName(this.value);">
                                                    <?php
                                                    for ($i = 0; $i < count($service_categories); $i++) {
                                                        $iServiceId = $service_categories[$i]['iServiceId'];
                                                        if ($service_categories[$i]['vImage'] == "") {
                                                            $service_categories[$i]['vImage'] = $siteUrl . 'assets/img/burger.jpg';
                                                        }
                                                        ?>
                                                        <option value="<?php echo $iServiceId; ?>" data-servicename="<?php echo ucfirst($service_categories[$i]['vServiceName']); ?>" <? if($iServiceId==$selectedServiceIdOrderItems) echo "selected"; ?>><?php echo ucfirst($service_categories[$i]['vServiceName']); ?> </option>
                                                    <?php }
                                                    ?></select><?php
                                            }
                                        }
                                        ?>
                                        <button <?= $addCss; ?> type="submit" id="submitbtn" name="SUBMIT"><?= $foodButtonName; ?></button>

                                        <ul id="suggestions">

                                        </ul>

                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!--  -->
                <div class="howitwork">
                    <div class="howitwork-inner">
                        <ul>
                            <li>
                                <i><img src="<?= $siteUrl; ?>assets/img/quick-booking.png" alt=""></i>
                                <strong><?= $langage_lbl['LBL_MANUAL_QUICK_BOOKING']; ?></strong>
                                <p><?= $langage_lbl['LBL_MANUAL_QUICK_BOOKING_DESC']; ?></p>
                            </li>
                            <li>
                                <i><img src="<?= $siteUrl; ?>assets/img/unlimited-store.png" alt=""></i>
                                <strong><?= $langage_lbl['LBL_MANUAL_UNLIMITED_STORE']; ?></strong>
                                <p><?= $langage_lbl['LBL_MANUAL_UNLIMITED_STORE_DESC']; ?></p>
                            </li>
                            <li>
                                <i><img src="<?= $siteUrl; ?>assets/img/track-order.png" alt=""></i>
                                <strong><?= $langage_lbl['LBL_MANUAL_TRACK_ORDER']; ?></strong>
                                <p><?= $langage_lbl['LBL_MANUAL_TRACK_ORDER_DESC']; ?></p>
                            </li>
                            <li>
                                <i><img src="<?= $siteUrl; ?>assets/img/fast-delivery.png" alt=""></i>
                                <strong><?= $langage_lbl['LBL_MANUAL_FAST_DELIVERY']; ?></strong>
                                <p><?= $langage_lbl['LBL_MANUAL_FAST_DELIVERY_DESC']; ?></p>
                            </li>
                        </ul>
                    </div>
                </div>
                <!--  -->
                <?php
                $oddEven = 1;
//$allCat = $service_categories;
                $allCat = array();
                for ($b = 0; $b < count($allCat); $b++) {
                    $oddEvenClass = "";
                    if ($oddEven % 2 == 0) {
                        $oddEvenClass = "reverse";
                    }
                    $oddEven++;
                    $categoryImg = $allCat[$b]['vImage'];
                    if ($categoryImg == "") {
                        $categoryImg = $siteUrl . 'assets/img/food-cat.jpg';
                    }
                    ?>
                    <div class="delivery-category <?= $oddEvenClass; ?>">
                        <div class="delivery-category-data" style="background-image:url(<?= $categoryImg; ?>)">
                            <div class="delivery-category-caption">
                                <div class="caption-data">
                                    <strong><?= strtoupper($allCat[$b]['vServiceName']); ?></strong>
                                    <p><?= $allCat[$b]['tDescription']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <!--<div class="delivery-category">
                    <div class="delivery-category-data" style="background-image:url(assets/img/food-cat.jpg)">
                        <div class="delivery-category-caption">
                            <div class="caption-data">
                                <strong>FOOD DELIVERY</strong>
                                <p>Let your customers order food online and get it delivered right at their doorstep instantly!</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="delivery-category reverse">
                    <div class="delivery-category-data" style="background-image:url(assets/img/alchohol-delivery.jpg)">
                        <div class="delivery-category-caption">
                            <div class="caption-data">
                                <strong>Alcohol Delivery</strong>
                                <p>Let your customers order food online and get it delivered right at their doorstep instantly!</p>
                            </div>
                        </div>
                    </div>
                </div>-->
            </div>
            <!-- home page end-->
            <!-- footer part -->

            <?php include_once 'footer/footer_home.php'; ?>

            <!-- End:contact page-->
            <div style="clear:both;"></div>
        </div>
        <?php include_once 'top/footer_script.php'; ?>
        <script src="<?= $siteUrl; ?>assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
        <script type="text/javascript" src="<?= $siteUrl; ?>admin/js/moment.min.js"></script>
        <script type="text/javascript" src="<?= $siteUrl; ?>admin/js/plugins/select2.min.js"></script>
        <script type="text/javascript" src="<?= $siteUrl; ?>assets/js/validation/jquery.validate.min.js" ></script>
        <script>
                                                    $(document).ready(function () {
                                                        var e = document.getElementById("serviceid");
                                                        if (typeof e.options != "undefined") {
                                                            var serviceId = e.options[e.selectedIndex].value;
                                                            renameButtonName(serviceId);
                                                        }
                                                    });
                                                    $("#reset").on("click", function () {
                                                        $('#vServiceAddress,#from_lat,#from_long,#from_lat_long').val('');
                                                    });
                                                    var errormessage;
                                                    $('#CustomerForm').validate({
                                                        ignore: '',
                                                        errorClass: 'help-block error',
                                                        errorElement: 'span',
                                                        errorPlacement: function (error, e) {
                                                            console.log(e.hasClass('from_lat'));
                                                            if (e.hasClass('from_lat') == true) {
                                                                $('#vServiceAddress').after(error);
                                                            } else {
                                                                $('#vServiceAddress').after('');
                                                            }
                                                        },
                                                        highlight: function (e) {
                                                            $(e).closest('.newrow').removeClass('has-success has-error').addClass('has-error');
                                                            $(e).closest('.newrow strong input').addClass('has-shadow-error');
                                                            $(e).closest('.help-block').remove();
                                                        },
                                                        success: function (e) {
                                                            e.prev('input').removeClass('has-shadow-error');
                                                            e.closest('.newrow').removeClass('has-success has-error');
                                                            e.closest('.help-block').remove();
                                                            e.closest('.help-inline').remove();
                                                        },
                                                        rules: {
                                                            from_lat: {required: true},

                                                        },
                                                        messages: {
                                                            from_lat: {required: '<?php echo $langage_lbl["LBL_MANUAL_STORE_REQUIRED_DELIVERY_ADDRESS"] ?>'},
                                                        }

                                                    });
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
                                                    var APP_DELIVERY_MODE = '<?= $APP_DELIVERY_MODE ?>';
                                                    var ENABLE_TOLL_COST = "<?= $ENABLE_TOLL_COST ?>";
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
                                                        var thePoint = new google.maps.LatLng(from_lat, from_long);
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
                                                    }
                                                    // function getAddress(mDlatitude, mDlongitude, addId) {
                                                    //     var mylatlang = new google.maps.LatLng(mDlatitude, mDlongitude);
                                                    //     geocoder.geocode({'latLng': mylatlang},
                                                    //             function (results, status) {
                                                    //                 // console.log(results);
                                                    //                 if (status == google.maps.GeocoderStatus.OK) {
                                                    //                     if (results[0]) {
                                                    //                         $('#' + addId).val(results[0].formatted_address);
                                                    //                     } else {
                                                    //                         document.getElementById('#' + addId).value = "No results";
                                                    //                     }
                                                    //                 } else {
                                                    //                     document.getElementById('#' + addId).value = status;
                                                    //                 }
                                                    //             });
                                                    // }

                                                    function clearSearchBox() {
                                                        // var previousValue;
                                                        $("#vServiceAddress").val('');
                                                        $('#clearbutton').hide();
                                                        $('.progress-indeterminate').hide();
                                                        // $("#suggestions").html('');
                                                        // $(".advance-pac-container").html('');
                                                        // $( ".advance-pac-container" ).remove();
                                                        $(".ui-helper-hidden-accessible").html('');
                                                        $(".ui-autocomplete").hide();
                                                        $(".ui-menu-item").remove();
                                                        sessionStorage.setItem("session_token", '');
                                                        $('#hidden_' + newfieldId).val('');
                                                        previousValue = '';
                                                    }

                                                    $(document).ready(function () {
                                                        $('#vServiceAddress').keyup(function (e) {
                                                            buildAutoComplete("vServiceAddress", e, "<?= $MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE; ?>", "<?= $_SESSION['sess_lang']; ?>"); // (orignal function)
                                                            // buildAutoComplete("<?= $MAPS_API_REPLACEMENT_STRATEGY ?>", "vServiceAddress",e.keyCode, "<?= $MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE; ?>","<?= $_SESSION['sess_lang']; ?>"); // (orignal function)
                                                        });
                                                    });

                                                    function change_lang(lang) {
                                                        document.location = 'common.php?lang=' + lang;
                                                    }
                                                    function renameButtonName(serviceId) {
                                                        var toptxt = '<?= $langage_lblDataOther['LBL_MANUAL_STORE_USER_MAIN_TEXT'];?>';
                                                       
                                                        var buttonTxt = '<?= $otherButtonName; ?>';
                                                        if (serviceId == 1) {
                                                            buttonTxt = '<?= $foodButtonName; ?>';
                                                            toptxt = '<?= $langage_lbl['LBL_MANUAL_STORE_USER_MAIN_TEXT'] ?>';
                                                        }
                                                       
                                                        $('#search-head').html(toptxt);
                                                        $("#submitbtn").text(buttonTxt);
                                                    }
        </script>

    </body>
</html>
