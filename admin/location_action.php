<?php
include_once '../common.php';
if (!isset($generalobjAdmin)) {
    require_once TPATH_CLASS . "class.general_admin.php";
    $generalobjAdmin = new General_admin();
}

////$generalobjAdmin->check_member_login();
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : '';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$tbl_name = 'location_master';
$script = 'Location';
// set all variables with either post (when submit) either blank (when insert)
$vLocationName = isset($_POST['vLocationName']) ? $_POST['vLocationName'] : '';
$tLongitude = isset($_POST['tLongitude']) ? $_POST['tLongitude'] : '';
$tLatitude = isset($_POST['tLatitude']) ? $_POST['tLatitude'] : '';
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';
$eFor = isset($_POST['eFor']) ? $_POST['eFor'] : '';
$iCountry = isset($_POST['iCountry']) ? $_POST['iCountry'] : '';
if ($iCountry != "") {
    $sql = "SELECT iCountryId FROM country WHERE vCountry LIKE '" . $iCountry . "'";
    $data = $obj->MySQLSelect($sql);
    $iCountryId = $data[0]['iCountryId'];
}
if (isset($_POST['submit'])) {
    if ($action == "Add" && !$userObj->hasPermission('create-geo-fence-locations')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create Loaction.';
        header("Location:company_action.php");
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission('edit-geo-fence-locations')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update Loaction.';
        header("Location:company_action.php");
        exit;
    }
    if (SITE_TYPE == 'Demo') {
        header("Location:location_action.php?id=" . $id . '&success=2');
        exit;
    }
    if (empty($tLongitude) || empty($tLatitude)) {
        $var_msg = 'Please select/draw the area on map shown in right hand side.';
        header("Location:location_action.php?id=" . $id . '&success=3&var_msg=' . $var_msg);
        exit;
    } else {
        if ($id != '') {
            $q = "UPDATE ";
            $where = " WHERE `iLocationId` = '" . $id . "'";
            $eforqry = "";
        } else {
            $q = "INSERT INTO ";
            $where = '';
            $eforqry = ",`eFor` = '" . $eFor . "'";
        }
        $query1 = $q . " `" . $tbl_name . "` SET
			`vLocationName` = '" . $vLocationName . "',
			`iCountryId` = '" . $iCountryId . "',
			`tLongitude` = '" . $tLongitude . "',
			`tLatitude` = '" . $tLatitude . "',
			`eStatus` = '" . $eStatus . "'
			$eforqry"
            . $where; //die;
        $obj->sql_query($query1);
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
}
// for Edit
if ($action == 'Edit') {
    $sql = "SELECT lm.*,c.vCountry FROM location_master AS lm LEFT JOIN country AS c ON c.iCountryId= lm.iCountryId WHERE lm.iLocationId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    $vLabel = $id;
    if (count($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $vLocationName = $value['vLocationName'];
            $tLongitude = $value['tLongitude'];
            $tLatitude = $value['tLatitude'];
            $eStatus = $value['eStatus'];
            $vCountry = $value['vCountry'];
            $eFor = $value['eFor'];
        }
    }
}
$sql = "SELECT iCountryId,vCountry,vCountryCode FROM country WHERE eStatus = 'Active'";
$db_country = $obj->MySQLSelect($sql);
//for default country
$sql = "SELECT vCountry,vCountryCode from country where eStatus = 'Active' and vCountryCode = '$DEFAULT_COUNTRY_CODE_WEB'";
$db_con = $obj->MySQLSelect($sql);
// Get lat and long by address
$vPhoneCode = $generalobjAdmin->clearPhone($db_con[0]['vPhoneCode']);
$vCountryx = $db_con[0]['vCountryCode'];

$sqlcont="select vCountry,tLatitude,tLongitude from country where vCountryCode='".$vCountryx."'";
$db_contry = $obj->MySQLSelect($sqlcont);

 $cLatitude = $db_contry[0]['tLatitude'];

 $cLongitude = $db_contry[0]['tLongitude'];

// $address = $db_contry[0]['vCountry']; // Google HQ
// $prepAddr = str_replace(' ','+',$address);

// $geocode=file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.$prepAddr.'&sensor=true&key='. $GOOGLE_SEVER_API_KEY_WEB);
// $output= json_decode($geocode);

// $latLng = $output->results[0]->geometry->location;
// $latitude = $latLng->lat;
// $longitude = $latLng->lng;
// $latitude = $output->results[0]->geometry->location->lat;
// $longitude = $output->results[0]->geometry->location->lng;

if($cLatitude == ''){
  $cLatitude = '23.0685865';
} 
if($cLongitude == ''){
  $cLongitude = '72.6535961';
}
$cubeDeliverallOnly = isDeliverAllOnlySystem();
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
        <title>Admin | Location <?=$action;?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <?php include_once 'global_files.php';?>
        <!-- On OFF switch -->
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
        <style>
            .location_icon  li {background: none;}.notes-main{ float: left;line-height: 1; } .notes-main-text{float: left;line-height: 1;padding-left: 10px;}.map-icon{width: 16px; height: 16px; overflow: hidden; position: relative;}.map-icon-img{position: absolute;left: 0px;user-select: none;border: 0px;padding: 0px;margin: 0px;max-width: none;width: 16px;height: 192px;}
            @media screen and (max-width: 480px) { .notes-main-text {float: none;padding-left: 25px;} }
        </style>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?php include_once 'header.php';?>
            <?php include_once 'left_menu.php';?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2><?=$action;?> Location</h2>
                            <a href="location.php">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />
                    <div class="body-div">
                        <?php if ($success == 1) {?>
                            <div class="alert alert-success alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                            </div><br/>
                        <?php } elseif ($success == 2) {?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                            </div><br/>
                        <?php } elseif ($success == 3) {?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                <?php echo $var_msg; ?>
                            </div><br/>
                        <?php }?>
                        <div class="row">
                            <div class="col-lg-5">
                                <div class="form-group">
                                    <form method="post" name="location_form" id="location_form" action="">
                                        <input type="hidden" name="id" value="<?=$id;?>" id="iLocationid"/>
                                        <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                        <input type="hidden" name="backlink" id="backlink" value="location.php"/>
                                        <input type="hidden" class="form-control" name="tLatitude"  id="tLatitude" value="<?=$tLatitude;?>">
                                        <input type="hidden" class="form-control" name="tLongitude"  id="tLongitude" value="<?=$tLongitude;?>">
                                        <input type="hidden" class="form-control" name="cLatitude"  id="cLatitude" value="<?= $cLatitude ?>">
                                        <input type="hidden" class="form-control" name="cLongitude"  id="cLongitude" value="<?= $cLongitude ?>">

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Location Name<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="vLocationName"  id="vLocationName" value="<?=$vLocationName;?>" placeholder="Location Name" required>
                                            </div>
                                        </div>
                                        <?php 
                                                        if(count($db_country) > 1){ ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Country <span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <select class="form-control" name ="iCountry" id="iCountry" required="required" onChange="getGeoCounty(this.value);">
                                                
                                                    <option value="">Select Country</option>
                                                    <?php for ($i = 0; $i < count($db_country); $i++) {?>
                                                        <option value = "<?=$db_country[$i]['vCountry']?>" <?php if ($vCountry == $db_country[$i]['vCountry']) {?>selected<?php }?> ><?=$db_country[$i]['vCountry']?></option>
                                                    <?php }?>
                                                </select>
                                            </div>
                                        </div>
                                        <?php }else{ ?>
                                                    <input type="hidden" name ="iCountry" id="iCountry" value="<?=$db_country[0]['vCountry']?>" >
                                                <?php } ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Location For<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <select class="form-control" name ="eFor" id="eFor" required="required" <? if ($action == 'Edit') { ?>disabled <? } ?>>
                                                    <option value="">Select Location For</option>
                                                    <option value ="Restrict" <?php if ($eFor == 'Restrict') {?>selected<?php }?>><?php echo $langage_lbl_admin['LBL_LOCATION_AREA_RESTRICTION']; ?></option>
                                                    <option value ="VehicleType" <?php if ($eFor == 'VehicleType') {?>selected<?php }?> ><?php echo $langage_lbl_admin['LBL_VEHICLE_SERVICE_TYPE_TXT']; ?></option>
                                                    <?php
                                                    if ($APP_TYPE != 'UberX' && $APP_TYPE != 'Delivery' && $PACKAGE_TYPE != 'STANDARD' && $onlyDeliverallModule == 'NO') {
                                                    ?>
                                                        <option value ="FixFare" <?php if ($eFor == 'FixFare') {?>selected<?php }?> ><?php echo $langage_lbl_admin['LBL_FIXFARE_SMALL_TXT']; ?></option>
                                                        <?php
}
?>
                                                    <?php if ((DELIVERALL == "Yes") or ($onlyDeliverallModule == "YES")) {?>

                                                        <option value ="UserDeliveryCharge" <?php if ($eFor == 'UserDeliveryCharge') {?>selected<?php }?> ><?php echo $langage_lbl_admin['LBL_LOCATION_USER_DELIVERY_CHARGE']; ?></option>
                                                    <?php }?>
                                                    <?php
if ($ENABLE_AIRPORT_SURCHARGE_SECTION == "Yes" && $APP_TYPE != 'UberX' && $APP_TYPE != 'Delivery') {
    if ($onlyDeliverallModule == 'NO') {
        ?>
                                                            <option value ="AirportSurcharge" <?php if ($eFor == 'AirportSurcharge') {?>selected<?php }?> ><?php echo 'Airport Surcharge'; ?></option>
                                                            <?php
}
}
?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row" style="display: none;">
                                            <div class="col-lg-12">
                                                <label>Status</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="make-switch" data-on="success" data-off="warning" id="mySwitch">
                                                    <input type="checkbox" name="eStatus" <?=($id != '' && $eStatus == 'Inactive') ? '' : 'checked';?> id="eStatus"/>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <?php if (($action == 'Edit' && $userObj->hasPermission('edit-geo-fence-locations')) || ($action == 'Add' && $userObj->hasPermission('create-geo-fence-locations'))) {?>
                                                    <input type="submit" class=" btn btn-default" name="submit" id="submit"  value="<?php if ($action == 'Add') {?><?=$action;?> Location<?php } else {?>Update<?php }?>" onclick="return IsEmpty();">
                                                    <input type="reset" value="Reset" class="btn btn-default">
                                                <?php }?>
                                                <!-- <a href="javascript:void(0);" onclick="reset_form('location_form');" class="btn btn-default">Reset</a> -->
                                                <a href="location.php" class="btn btn-default">Cancel</a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="admin-notes">
                                    <h4>Notes:</h4>
                                    <ul class="location_icon">
                                        <li>
                                            <div class="notes-main">
                                                <div class="map-icon">
                                                    <img src="images/icon/drawing.png" class="map-icon-img" style="top: -144px;">
                                                </div>
                                            </div>
                                            <div class="notes-main-text">  With use of this icon, you can edit exist polygon shape. </div>
                                        </li>
                                        <li>
                                            <div class="notes-main">
                                                <div class="map-icon">
                                                    <img src="images/icon/drawing.png" class="map-icon-img" style="top: -64px;">
                                                </div>
                                            </div>
                                            <div class="notes-main-text"> With use of this icon, you can draw new polygon shape. </div>
                                        </li>
                                        <li>
                                            <div class="notes-main">
                                                <div class="map-icon">
                                                    <img src="images/icon/drawing.png" class="map-icon-img" style="top: -32px;">
                                                </div>
                                            </div>
                                            <div class="notes-main-text"> With use of this icon, you can draw new polygon lines. </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <label>Draw Location Point Here In Map :<span class="red">*</span></label>
                                <p><span>Please select the area by putting the points around it. Please <a href="../assets/videos/geofence/geofencenew_player.html" target="_blank" alt="Link"><b>click here</b></a> to view how to select the area and add it.</span></p>
                                <div class="panel-heading location-map" style="background:none;">
                                    <div class="google-map-wrap box_in_map">
                                        <input id="pac-input" type="text" placeholder="Enter Location For More Focus" style="padding:4px;width: 200px;margin-top: 5px;">
                                        <div id="map-canvas" class="google-map" style="width:100%; height:500px;"></div>
                                    </div>
                                    <div style="text-align: center;margin-top: 5px;">
                                        <button id="delete-button">Delete Selected Shape</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
        <?php include_once 'footer.php';?>
        <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
        <script src="//maps.google.com/maps/api/js?sensor=fasle&key=<?=$GOOGLE_SEVER_API_KEY_WEB?>&libraries=places,drawing" type="text/javascript"></script>
        <script>
                                                    $(document).ready(function () {
                                                        var referrer;
                                                        if ($("#previousLink").val() == "") { //alert('pre1');
                                                            referrer = document.referrer;
                                                        } else {
                                                            referrer = $("#previousLink").val();
                                                        }
                                                        if (referrer == "") {
                                                            referrer = "location.php";
                                                        } else {
                                                            $("#backlink").val(referrer);
                                                        }
                                                        $(".back_link").attr('href', referrer);
                                                    });
                                                    function IsEmpty() {
                                                        if ((document.forms['location_form'].tLatitude.value === "") || (document.forms['location_form'].tLongitude.value === ""))
                                                        {
                                                            alert("Please select/draw the area on map shown in right hand side.");
                                                            return false;
                                                        }
                                                        return true;
                                                    }
                                                    var drawingManager;
                                                    var selectedShape;
                                                    function clearSelection() {
                                                        if (selectedShape) {
                                                            if (typeof selectedShape.setEditable == 'function') {
                                                                selectedShape.setEditable(false);
                                                            }
                                                            selectedShape = null;
                                                        }
                                                    }
                                                    function deleteSelectedShape() {
                                                        if (selectedShape) {
                                                            selectedShape.setMap(null);
                                                            $('#tLatitude').val("");
                                                            $('#tLongitude').val("");
                                                        }
                                                    }
                                                    function updateCurSelText(shape) {
                                                        var latt = "";
                                                        var longi = "";
                                                        if (typeof selectedShape.getPath == 'function') {
                                                            for (var i = 0; i < selectedShape.getPath().getLength(); i++) {
                                                                var latlong = selectedShape.getPath().getAt(i).toUrlValue().split(",");
                                                                latt += (latlong[0]) + ",";
                                                                longi += (latlong[1]) + ",";
                                                            }
                                                        }
                                                        $('#tLatitude').val(latt);
                                                        $('#tLongitude').val(longi);
                                                    }
                                                    function setSelection(shape, isNotMarker) {
                                                        clearSelection();
                                                        selectedShape = shape;
                                                        if (isNotMarker)
                                                            shape.setEditable(true);
                                                        updateCurSelText(shape);
                                                    }
                                                    function getGeoCounty(Countryname) {
                                                        // var geocoder = new google.maps.Geocoder();
                                                        // var address = Countryname;
                                                        // var lat, long;
                                                        // geocoder.geocode({'address': address}, function (results, status) {
                                                        //     if (status == google.maps.GeocoderStatus.OK)
                                                        //     {
                                                        //         lat = results[0].geometry.location.lat();
                                                        //         $('#cLatitude').val(lat);
                                                        //         long = results[0].geometry.location.lng();
                                                        //         $('#cLongitude').val(long);
                                                        //         var tlat = $("#tLatitude").val();
                                                        //         var tlong = $("#tLatitude").val();
                                                        //         if (tlat == '' && tlong == '') {
                                                        //             play();
                                                        //         }
                                                        //     }
                                                        // });
                                                        deleteSelectedShape(); // Added By HJ On 07-01-2020 For Solved 141 Mantis Bug #2051
                                                    }
                                                    /////////////////////////////////////
                                                    var map;
                                                    var searchBox;
                                                    var placeMarkers = [];
                                                    var input;
													var markers = [];
                                                    /////////////////////////////////////
                                                    function initialize() {
                                                        var myLatLng = new google.maps.LatLng("<?=$latitude?>", "<?=$longitude?>");
                                                        map = new google.maps.Map(document.getElementById('map-canvas'), {
                                                            zoom: 10,
                                                            center: myLatLng,
                                                            mapTypeId: google.maps.MapTypeId.ROADMAP,
                                                            disableDefaultUI: false,
                                                            zoomControl: true
                                                        });
                                                        var polyOptions = {
                                                            strokeWeight: 0,
                                                            fillOpacity: 0.45,
                                                            editable: true
                                                        };
<?php if ($action == "Edit") {?>
                                                            drawingModevalue = null;
<?php } else {?>
                                                            drawingModevalue = google.maps.drawing.OverlayType.POLYGON;
<?php }?>
                                                        drawingManager = new google.maps.drawing.DrawingManager({
                                                            drawingMode: drawingModevalue,
                                                            drawingControl: true,
                                                            drawingControlOptions: {
                                                                position: google.maps.ControlPosition.TOP_RIGHT,
                                                                drawingModes: ['polygon', 'polyline']
                                                            },
                                                            polygonOptions: polyOptions,
                                                            map: map
                                                        });
                                                        google.maps.event.addListener(drawingManager, 'overlaycomplete', function (e) {
                                                            var isNotMarker = (e.type != google.maps.drawing.OverlayType.MARKER);
                                                            drawingManager.setDrawingMode(null);
                                                            var newShape = e.overlay;
                                                            newShape.type = e.type;
                                                            google.maps.event.addListener(newShape, 'click', function () {
                                                                setSelection(newShape, isNotMarker);
                                                            });
                                                            google.maps.event.addListener(newShape, 'drag', function () {
                                                                updateCurSelText(newShape);
                                                            });
                                                            google.maps.event.addListener(newShape, 'dragend', function () {
                                                                updateCurSelText(newShape);
                                                            });
                                                            setSelection(newShape, isNotMarker);
                                                        });
                                                        google.maps.event.addListener(drawingManager, 'drawingmode_changed', clearSelection);
                                                        google.maps.event.addListener(map, 'click', clearSelection);
                                                        google.maps.event.addDomListener(document.getElementById('delete-button'), 'click', deleteSelectedShape);
                                                        google.maps.event.addListener(map, 'bounds_changed', function () {
                                                            var bounds = map.getBounds();
                                                        });
                                                        //~ initSearch(); ============================================
                                                        // Create the search box and link it to the UI element.
                                                        input = /** @type {HTMLInputElement} */(//var
                                                                document.getElementById('pac-input'));
                                                        map.controls[google.maps.ControlPosition.TOP_RIGHT].push(input);
                                                        //searchBox = new google.maps.places.SearchBox((input));

                                                        var tLongitude = $('#tLongitude').val();
                                                        var tLatitude = $('#tLatitude').val();
                                                        var Country = $("#iCountry").val();
                                                        if (Country != "" && (tLongitude == "" || tLatitude == "")) {
                                                             <?php if(count($db_country) > 1){ ?>
                                                            getGeoCounty(Country);
                                                            myLatLng = new google.maps.LatLng($("#cLatitude").val(), $("#cLongitude").val());
                                                            map.fitBounds(myLatLng);
                                                            <?php } ?>
                                                        } else {
                                                            if (tLongitude != "" || tLatitude != "") {
                                                                var tlat = tLatitude.split(",");
                                                                var tlong = tLongitude.split(",");
                                                                var triangleCoords = [];
                                                                var bounds = new google.maps.LatLngBounds();
                                                                for (var i = 0, len = tlat.length; i < len; i++) {
                                                                    if (tlat[i] != "" || tlong[i] != "") {
                                                                        triangleCoords.push(new google.maps.LatLng(tlat[i], tlong[i]));
                                                                        var point = new google.maps.LatLng(tlat[i], tlong[i]);
                                                                        bounds.extend(point);
                                                                    }
                                                                }
                                                                // Styling & Controls
                                                                myPolygon = new google.maps.Polygon({
                                                                    paths: triangleCoords,
                                                                    draggable: false, // turn off if it gets annoying
                                                                    editable: true,
                                                                    strokeColor: '#FF0000',
                                                                    strokeOpacity: 0.8,
                                                                    strokeWeight: 2,
                                                                    fillColor: '#FF0000',
                                                                    fillOpacity: 0.35
                                                                });
                                                                map.fitBounds(bounds);
                                                                myPolygon.setMap(map);
                                                                //google.maps.event.addListener(myPolygon, "dragend", getPolygonCoords);
                                                                google.maps.event.addListener(myPolygon.getPath(), "insert_at", getPolygonCoords);
                                                                //google.maps.event.addListener(myPolygon.getPath(), "remove_at", getPolygonCoords);
                                                                google.maps.event.addListener(myPolygon.getPath(), "set_at", getPolygonCoords);
                                                                google.maps.event.addDomListener(document.getElementById('delete-button'), 'click', deleteEditShape);
                                                            }
                                                        }
                                                        // $('#pac-input').keyup(function (e) {
                                                            // buildAutoCompleteLat("pac-input", e.keyCode, "<?=$MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE;?>","<?=$_SESSION['sess_lang'];?>",'23.073892','72.5033506', function(latitude, longitude, address){
                                                             // // set map marker...
                                                                    // var myLatlng;
                                                                    // myLatlng = new google.maps.LatLng(latitude,longitude);
                                                                   // if (marker && marker.setMap) {
                                                                            // marker.setMap(null);
                                                                        // }
                                                                    // var marker = new google.maps.Marker({
                                                                                // map: map,
                                                                                // anchorPoint: new google.maps.Point(0, -29),
                                                                                // position:   myLatlng
                                                                            // });

                                                                            // // map.setCenter(new google.maps.LatLng(23.073892, 72.5033506));
                                                                            // map.setCenter(marker.getPosition());
                                                                    // marker.setVisible(false);
                                                                    // map.setZoom(15);
                                                                    // marker.setVisible(true);
                                                            // });
                                                        // });
														show_locations();
														$('#pac-input').keyup(function (e) {
														buildAutoComplete("pac-input",e, "<?=$MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE;?>","<?=$_SESSION['sess_lang'];?>", function(latitude, longitude, address){
															 var cLongitude = $('#cLongitude').val(longitude);
															var cLatitude = $('#cLatitude').val(latitude);
															show_locations();
														});
													});

                                                        return;
                                                        // var autocomplete = new google.maps.places.Autocomplete(input);
                                                        // autocomplete.bindTo('bounds', map);
                                                        // // Listen for the event fired when the user selects an item from the
                                                        // // pick list. Retrieve the matching places for that item.
                                                        // var marker = new google.maps.Marker({
                                                        //     map: map
                                                        // });
                                                        // autocomplete.addListener('place_changed', function () {
                                                        //     marker.setVisible(false);
                                                        //     var place = autocomplete.getPlace();
                                                        //     if (!place.geometry) {
                                                        //         window.alert("Autocomplete's returned place contains no geometry");
                                                        //         return;
                                                        //     }
                                                        //     // If the place has a geometry, then present it on a map.
                                                        //     placeMarkers = [];
                                                        //     if (place.geometry.viewport) {
                                                        //         map.fitBounds(place.geometry.viewport);
                                                        //     } else {
                                                        //         map.setCenter(place.geometry.location);
                                                        //         map.setZoom(14);
                                                        //     }
                                                        //     // Create a marker for each place.
                                                        //     marker = new google.maps.Marker({
                                                        //         map: map,
                                                        //         title: place.name,
                                                        //         position: place.geometry.location
                                                        //     });
                                                        //     marker.setIcon(({
                                                        //         url: place.icon,
                                                        //         size: new google.maps.Size(71, 71),
                                                        //         origin: new google.maps.Point(0, 0),
                                                        //         anchor: new google.maps.Point(17, 34),
                                                        //         scaledSize: new google.maps.Size(25, 25)
                                                        //     }));
                                                        //     marker.setVisible(true);
                                                        // });

                                                        var tLongitude = $('#tLongitude').val();
                                                        var tLatitude = $('#tLatitude').val();
                                                        var Country = $("#iCountry").val();
                                                        if (Country != "" && (tLongitude == "" || tLatitude == "")) {
                                                            getGeoCounty(Country);
                                                            myLatLng = new google.maps.LatLng($("#cLatitude").val(), $("#cLongitude").val());
                                                            map.fitBounds(myLatLng);
                                                        } else {
                                                            if (tLongitude != "" || tLatitude != "") {
                                                                var tlat = tLatitude.split(",");
                                                                var tlong = tLongitude.split(",");
                                                                var triangleCoords = [];
                                                                var bounds = new google.maps.LatLngBounds();
                                                                for (var i = 0, len = tlat.length; i < len; i++) {
                                                                    if (tlat[i] != "" || tlong[i] != "") {
                                                                        triangleCoords.push(new google.maps.LatLng(tlat[i], tlong[i]));
                                                                        var point = new google.maps.LatLng(tlat[i], tlong[i]);
                                                                        bounds.extend(point);
                                                                    }
                                                                }
                                                                // Styling & Controls
                                                                myPolygon = new google.maps.Polygon({
                                                                    paths: triangleCoords,
                                                                    draggable: false, // turn off if it gets annoying
                                                                    editable: true,
                                                                    strokeColor: '#FF0000',
                                                                    strokeOpacity: 0.8,
                                                                    strokeWeight: 2,
                                                                    fillColor: '#FF0000',
                                                                    fillOpacity: 0.35
                                                                });
                                                                map.fitBounds(bounds);
                                                                myPolygon.setMap(map);
                                                                //google.maps.event.addListener(myPolygon, "dragend", getPolygonCoords);
                                                                google.maps.event.addListener(myPolygon.getPath(), "insert_at", getPolygonCoords);
                                                                //google.maps.event.addListener(myPolygon.getPath(), "remove_at", getPolygonCoords);
                                                                google.maps.event.addListener(myPolygon.getPath(), "set_at", getPolygonCoords);
                                                                google.maps.event.addDomListener(document.getElementById('delete-button'), 'click', deleteEditShape);
                                                            }
                                                        }
                                                    }
													
													function show_locations() {
															DeleteMarkers('from_loc');
															var latlng = new google.maps.LatLng($("#cLatitude").val(), $("#cLongitude").val());
															setMarker(latlng, 'from_loc');
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
													
													      function setMarker(postitions, valIcon) {
																var marker = new google.maps.Marker({
																	map: map,
																	draggable: true,
																	animation: google.maps.Animation.DROP,
																	position: postitions,
																	// icon: newIcon
																});
																marker.id = valIcon;
																markers.push(marker);
																map.setCenter(marker.getPosition());
																map.setZoom(14);
														  }

                                                    function deleteEditShape() {
                                                        if (myPolygon) {
                                                            myPolygon.setMap(null);
                                                        }
                                                        $('#tLatitude').val("");
                                                        $('#tLongitude').val("");
                                                    }
                                                    function play() {
                                                        var pt = new google.maps.LatLng($("#cLatitude").val(), $("#cLongitude").val());
                                                        map.setCenter(pt);
                                                        map.setZoom(5);
                                                    }
                                                    //Display Coordinates below map
                                                    function getPolygonCoords() {
                                                        var len = myPolygon.getPath().getLength();
                                                        var latt = "";
                                                        var longi = "";
                                                        for (var i = 0; i < len; i++) {
                                                            var latlong = myPolygon.getPath().getAt(i).toUrlValue().split(",");
                                                            latt += (latlong[0]) + ",";
                                                            longi += (latlong[1]) + ",";
                                                        }
                                                        $('#tLatitude').val(latt);
                                                        $('#tLongitude').val(longi);
                                                    }

// * * * * * * * * * * * * START AUTO COMPLETE* * * * * * * * * * * * * * * *
                                                    $(document).ready(function () {

                                                    google.maps.event.addDomListener(window, 'load', initialize);

});
// * * * * * * * * * * * * END AUTO COMPLETE* * * * * * * * * * * * * * * *

        </script>
    </body>
    <!-- END BODY-->
</html>
