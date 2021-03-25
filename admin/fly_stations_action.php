<?php
include_once '../common.php';
if (!isset($generalobjAdmin)) {
    require_once TPATH_CLASS . "class.general_admin.php";
    $generalobjAdmin = new General_admin();
}
include_once "../app_common_functions.php";

////$generalobjAdmin->check_member_login();
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : '';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$tbl_name = 'location_master';
$script = 'fly_stations';
// set all variables with either post (when submit) either blank (when insert)
$vLocationName = isset($_POST['vLocationName']) ? $_POST['vLocationName'] : '';
$vLocationAddress = isset($_POST['vLocationAddress']) ? $_POST['vLocationAddress'] : '';
$tLongitude = isset($_POST['tLongitude']) ? $_POST['tLongitude'] : '';
$tLatitude = isset($_POST['tLatitude']) ? $_POST['tLatitude'] : '';

$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';
$eFor = 'FlyStation';
$iCountry = isset($_POST['iCountry']) ? $_POST['iCountry'] : '';
if ($iCountry != "") {
    $sql = "SELECT iCountryId FROM country WHERE vCountry LIKE '" . $iCountry . "'";
    $data = $obj->MySQLSelect($sql);
    $iCountryId = $data[0]['iCountryId'];
}
if (isset($_POST['submit'])) {
    if ($action == "Add" && !$userObj->hasPermission('create-fly-stations')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create ' . strtolower($langage_lbl_admin['LBL_FLY_STATIONS']);
        header("Location:fly_stations.php");
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission('edit-fly-stations')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update ' . strtolower($langage_lbl_admin['LBL_FLY_STATIONS']);
        header("Location:fly_stations.php");
        exit;
    }
    if (SITE_TYPE == 'Demo') {
        header("Location:fly_stations_action.php?id=" . $id . '&success=2');
        exit;
    }
    if (empty($tLongitude) || empty($tLatitude)) {
        $var_msg = 'Please select/draw the area on map shown in right hand side.';
        header("Location:fly_stations_action.php?id=" . $id . '&success=3&var_msg=' . $var_msg);
        exit;
    } else {
        if ($id != '') {
            $q = "UPDATE ";
            $where = " WHERE `iLocationId` = '" . $id . "'";
        } else {
            $q = "INSERT INTO ";
            $where = '';
        }

        $lattitudeArr = array();
        $latArr = explode(',', $tLatitude);
        $longArr = explode(',', $tLongitude);
        $latlongArr = array();
        $key = 0;
        foreach ($latArr as $key1 => $value1) {
            if (!empty($value1)) {
                $lattitudeArr[$key][$key1][] = $value1;
                $lattitudeArr[$key][$key1][] = $longArr[$key1];
            }
        }

        //$polygon = new stdClass();
        //$latlongArr[] = $lattitudeArr[$key];
        //$polygon->rings = $latlongArr;
        //$a = $generalobj->getCentroidOfPolygon($polygon);
        //echo '<pre>';print_R($a);exit;

        //print_R($lattitudeArr); echo "aaaa";exit;
        $error_res = 0;
        foreach ($lattitudeArr[$key] as $key12 => $value) {

            $pickuplocationarr = array(
                $value[0],
                $value[1],
            );
            $allowed_ans = checkAllowedAreaNew($pickuplocationarr, "No");
            $dropofflocationarr = $pickuplocationarr;
            $allowed_ans_drop = checkAllowedAreaNew($pickuplocationarr, "Yes");
            if ($allowed_ans == "No" && $allowed_ans_drop == "No") {
                //$errmessage = $langage_lbl_admin["LBL_PICK_DROP_LOCATION_NOT_ALLOW"];
                $error_res = 1;
            }
            if ($allowed_ans == "Yes" && $allowed_ans_drop == "No") {
                //$errmessage = $langage_lbl_admin["LBL_DROP_LOCATION_NOT_ALLOW"];
                $error_res = 1;
            }
            if ($allowed_ans == "No" && $allowed_ans_drop == "Yes") {
                //$errmessage = $langage_lbl_admin["LBL_PICKUP_LOCATION_NOT_ALLOW"];
                $error_res = 1;
            }
        }
        if ($error_res == 1) {
            $var_msg = $langage_lbl_admin['LBL_AREA_RESTRICT'];
            header("Location:fly_stations_action.php?id=" . $id . '&success=3&var_msg=' . $var_msg);
            exit;
        }

        $query1 = $q . " `" . $tbl_name . "` SET
			`vLocationName` = '" . $vLocationName . "',
			`vLocationAddress` = '" . $vLocationAddress . "',
			`iCountryId` = '" . $iCountryId . "',
			`tLongitude` = '" . $tLongitude . "',
			`tLatitude` = '" . $tLatitude . "',
            `tCentroidLongitude` = '" . $tLongitude . "',
			`tCentroidLattitude` = '" . $tLatitude . "',
			`eStatus` = '" . $eStatus . "',
			`eFor` = '" . $eFor . "'"
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
            $vLocationAddress = $value['vLocationAddress'];
            $tLongitude = $value['tLongitude'];
            $tLatitude = $value['tLatitude'];
            $eStatus = $value['eStatus'];
            $vCountry = $value['vCountry'];
            $eFor = $value['eFor'];
        }
    }
}
$sql = "SELECT iCountryId,vCountry,vCountryCode FROM country WHERE eStatus = 'Active' ";
$db_country = $obj->MySQLSelect($sql);
//for default country
$sql = "SELECT vCountry from country where eStatus = 'Active' and vCountryCode = '$DEFAULT_COUNTRY_CODE_WEB'";
$db_def_con = $obj->MySQLSelect($sql);
// Get lat and long by address
$address = $db_def_con[0]['vCountry']; // Google HQ
$prepAddr = str_replace(' ', '+', $address);
// $geocode = file_get_contents('//maps.google.com/maps/api/geocode/json?address=' . $prepAddr . '&sensor=false');
// $output = json_decode($geocode);
// $latitude = $output->results[0]->geometry->location->lat;
// $longitude = $output->results[0]->geometry->location->lng;

?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title>Admin | <?=$langage_lbl_admin['LBL_FLY_STATIONS'];?> <?=$action;?></title>
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
                            <h2><?=$action;?> <?=$langage_lbl_admin['LBL_FLY_STATION'];?></h2>
                            <a href="fly_stations.php">
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
                                        <input type="hidden" name="backlink" id="backlink" value="fly_stations.php"/>
                                        <input type="hidden" class="form-control" name="tLatitude"  id="tLatitude" value="<?=$tLatitude;?>">
                                        <input type="hidden" class="form-control" name="tLongitude"  id="tLongitude" value="<?=$tLongitude;?>">
                                        <input type="hidden" class="form-control" name="cLatitude"  id="cLatitude">
                                        <input type="hidden" class="form-control" name="cLongitude"  id="cLongitude">

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?=$langage_lbl_admin['LBL_FLY_STATIONS'];?> Name<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="vLocationName"  id="vLocationName" value="<?=$vLocationName;?>" placeholder="<?=$langage_lbl_admin['LBL_FLY_STATIONS'];?> Name" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?=$langage_lbl_admin['LBL_FLY_STATIONS'];?> Address<span class="red"> * </span><i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Add specific Address like Street, Area, City, Pincode.'></i></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="vLocationAddress"  id="vLocationAddress" value="<?=$vLocationAddress;?>" placeholder="<?=$langage_lbl_admin['LBL_FLY_ADDRESS'];?>" required >
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <?php 
                                                        if(count($db_country) > 1){ ?>
                                                <label>Country <span class="red"> *</span></label>
                                                 <?php } ?>
                                            </div>
                                            <div class="col-lg-6">
                                                 <?php 
                                                        if(count($db_country) > 1){ ?>
                                                <!-- <select class="form-control" name ="iCountry" id="iCountry" required="required" onChange="getGeoCounty(this.value);"> -->

                                                <select class="form-control" name ="iCountry" id="iCountry" required="required" >
                                                    <option value="">Select Country</option>
                                                    <?php for ($i = 0; $i < count($db_country); $i++) {?>
                                                        <option value = "<?=$db_country[$i]['vCountry']?>" <?php if ($vCountry == $db_country[$i]['vCountry']) {?>selected<?php }?> ><?=$db_country[$i]['vCountry']?></option>
                                                    <?php }?>
                                                </select>
                                                <?php }else{ ?>
                                                    <input type="hidden" name ="iCountry" id="iCountry" value="<?=$db_country[0]['vCountry']?>" >
                                                <?php } ?>
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
                                                <?php if (($action == 'Edit' && $userObj->hasPermission('edit-fly-stations')) || ($action == 'Add' && $userObj->hasPermission('create-fly-stations'))) {?>
                                                    <input type="submit" class=" btn btn-default" name="submit" id="submit"  value="<?php if ($action == 'Add') {?><?=$action;?> <?=$langage_lbl_admin['LBL_FLY_STATIONS'];?><?php } else {?>Update<?php }?>" onclick="return IsEmpty();">
                                                    <input type="reset" value="Reset" class="btn btn-default">
                                                <?php }?>
                                                <!-- <a href="javascript:void(0);" onclick="reset_form('location_form');" class="btn btn-default">Reset</a> -->
                                                <a href="fly_stations.php" class="btn btn-default">Cancel</a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="admin-notes">
                                    <h4>Notes:</h4>
                                    <ul>
                                        <li>
                                            1. Enter the fly station name and address. This will be visible in the user application when they book the station..
                                        </li>
                                        <li>
                                            2. Users will able to see this station who are X.X KM away in the radius range. To set the radius for the users to view the stations set it from the Admin -> Settings -> General -> App Settings -> Radius for the fly stations.
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <label>Pin <?=$langage_lbl_admin['LBL_FLY_STATIONS'];?> Point Here In Map :<span class="red">*</span></label>
                                <p><span>Search the Station location and mark the pin point of the station area on the map by clicking on it.</span></p>
                                <div class="panel-heading location-map" style="background:none;">
                                    <div class="google-map-wrap box_in_map">
												<input id="pac-input" type="text" placeholder="Enter <?=$langage_lbl_admin['LBL_FLY_STATIONS'];?> For More Focus" style="padding:4px;width: 200px;margin-top: 5px;">
											
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
                                                            referrer = "fly_stations.php";
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
                                                    }
                                                    /////////////////////////////////////
                                                    var map;
                                                    var searchBox;
                                                    var placeMarkers = [];
                                                    var input;
                                                    // var geocoder = new google.maps.Geocoder();
                                                    /////////////////////////////////////
                                                    function initialize() {
                                                        var myLatLng = new google.maps.LatLng("<?=$latitude?>", "<?=$longitude?>");
                                                        map = new google.maps.Map(document.getElementById('map-canvas'), {
                                                            zoom: 5,
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
                                                            // map.setZoom(15);
                                                            //drawingModevalue = null;
                                                                <?php } else {?>
                                                            //drawingModevalue = google.maps.drawing.OverlayType.POLYGON;
                                                            <?php }?>
                                                        /*drawingManager = new google.maps.drawing.DrawingManager({
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
                                                        });*/

                                                        google.maps.event.addListener(map, 'click', function( event ){

                                                            //alert( "Latitude: "+event.latLng.lat()+" "+", longitude: "+event.latLng.lng() );
                                                            var lat = event.latLng.lat();
                                                            var lng = event.latLng.lng();

                                                            $('#tLatitude').val(event.latLng.lat());
                                                            $('#tLongitude').val(event.latLng.lng());
                                                            $('#cLatitude').val(event.latLng.lat());
                                                            $('#cLongitude').val(event.latLng.lng());
                                                            marker.setVisible(false);
                                                            latlng = new google.maps.LatLng(lat, lng);
                                                            marker = new google.maps.Marker({
                                                                position: latlng,
                                                                map: map,
                                                                icon: {
                                                                    scaledSize: new google.maps.Size(50, 50),
                                                                    rotation: 90
                                                                },
                                                                id: 'marker'
                                                            });
                                                            map.setCenter(latlng);
                                                            //map.setZoom(14);
                                                        });


                                                        //~ initSearch(); ============================================
                                                        // Create the search box and link it to the UI element.
                                                        input = /** @type {HTMLInputElement} */(//var
                                                                document.getElementById('pac-input'));
                                                        map.controls[google.maps.ControlPosition.TOP_RIGHT].push(input);
                                                        var marker = new google.maps.Marker({
                                                            map: map
                                                        });
                                                        //searchBox = new google.maps.places.SearchBox((input));

                                                        var tLongitude = $('#tLongitude').val();
                                                        var tLatitude = $('#tLatitude').val();
                                                        var Country = $("#iCountry").val();
                                                        if (Country != "" && (tLongitude == "" || tLatitude == "")) {
                                                            // getGeoCounty(Country);
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
                                                                map.setZoom(14);
                                                                map.fitBounds(bounds);

                                                                myPolygon.setMap(map);
                                                                //google.maps.event.addListener(myPolygon, "dragend", getPolygonCoords);
                                                                google.maps.event.addListener(myPolygon.getPath(), "insert_at", getPolygonCoords);
                                                                //google.maps.event.addListener(myPolygon.getPath(), "remove_at", getPolygonCoords);
                                                                google.maps.event.addListener(myPolygon.getPath(), "set_at", getPolygonCoords);
                                                                google.maps.event.addDomListener(document.getElementById('delete-button'), 'click', deleteEditShape);

                                                            }
                                                        }

                                                        $('#pac-input').keyup(function (e) {
                                                            buildAutoComplete("pac-input", e, "<?=$MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE;?>","<?=$_SESSION['sess_lang'];?>", function(latitude, longitude, address){
                                                             // set map marker...
                                                                    var myLatlng;
                                                                    myLatlng = new google.maps.LatLng(latitude,longitude);
                                                                   if (marker && marker.setMap) {
                                                                            marker.setMap(null);
                                                                        }
                                                                    map.setCenter(myLatlng);
                                                                    map.setZoom(15);
                                                            });
                                                        });

                                                        return;

                                                        var autocomplete = new google.maps.places.Autocomplete(input);

                                                        autocomplete.bindTo('bounds', map);
                                                        // Listen for the event fired when the user selects an item from the
                                                        // pick list. Retrieve the matching places for that item.
                                                        var marker = new google.maps.Marker({
                                                            map: map
                                                        });
                                                        autocomplete.addListener('place_changed', function () {
                                                            marker.setVisible(false);
                                                            var place = autocomplete.getPlace();
                                                            if (!place.geometry) {
                                                                window.alert("Autocomplete's returned place contains no geometry");
                                                                return;
                                                            }
                                                            // If the place has a geometry, then present it on a map.
                                                            placeMarkers = [];
                                                            if (place.geometry.viewport) {
                                                                map.fitBounds(place.geometry.viewport);
                                                            } else {
                                                                map.setCenter(place.geometry.location);
                                                                map.setZoom(14);
                                                            }
                                                            // Create a marker for each place.
                                                            /*marker = new google.maps.Marker({
                                                                map: map,
                                                                title: place.name,
                                                                position: place.geometry.location
                                                            });
                                                            marker.setIcon(({
                                                                url: place.icon,
                                                                size: new google.maps.Size(71, 71),
                                                                origin: new google.maps.Point(0, 0),
                                                                anchor: new google.maps.Point(17, 34),
                                                                scaledSize: new google.maps.Size(25, 25)
                                                            }));*/
                                                            //marker.setVisible(true);
                                                        });
                                                        /*        google.maps.event.addListener(searchBox, 'places_changed', function() {
                                                         var places = searchBox.getPlaces();

                                                         if (places.length == 0) {
                                                         return;
                                                         }
                                                         for (var i = 0, marker; marker = placeMarkers[i]; i++) {
                                                         marker.setMap(null);
                                                         }

                                                         // For each place, get the icon, place name, and location.
                                                         placeMarkers = [];
                                                         var bounds = new google.maps.LatLngBounds();
                                                         for (var i = 0, place; place = places[i]; i++) {
                                                         var image = {
                                                         url: place.icon,
                                                         size: new google.maps.Size(71, 71),
                                                         origin: new google.maps.Point(0, 0),
                                                         anchor: new google.maps.Point(17, 34),
                                                         scaledSize: new google.maps.Size(25, 25)
                                                         };

                                                         // Create a marker for each place.
                                                         var marker = new google.maps.Marker({
                                                         map: map,
                                                         icon: image,
                                                         title: place.name,
                                                         position: place.geometry.location
                                                         });

                                                         placeMarkers.push(marker);
                                                         bounds.extend(place.geometry.location);
                                                         }

                                                         map.fitBounds(bounds);
                                                         map.setZoom(14);
                                                         });*/
                                                        //~ EndSearch(); ============================================
                                                        // Polygon Coordinates
                                                        var tLongitude = $('#tLongitude').val();
                                                        var tLatitude = $('#tLatitude').val();
                                                        var Country = $("#iCountry").val();
                                                        if (Country != "" && (tLongitude == "" || tLatitude == "")) {
                                                            // getGeoCounty(Country);
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
                                                    // google.maps.event.addDomListener(window, 'load', initialize);
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
                            $(document).ready(function () {
    google.maps.event.addDomListener(window, 'load', initialize);
});
        </script>
    </body>
    <!-- END BODY-->
</html>
