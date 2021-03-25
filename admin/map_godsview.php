<?php
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();
function fetchtripstatustimeMAXinterval() {
    global $generalobjAdmin, $FETCH_TRIP_STATUS_TIME_INTERVAL;
    $FETCH_TRIP_STATUS_TIME_INTERVAL_ARR = explode("-", $FETCH_TRIP_STATUS_TIME_INTERVAL);
    $FETCH_TRIP_STATUS_TIME_INTERVAL_MAX = $FETCH_TRIP_STATUS_TIME_INTERVAL_ARR[1];
    return $FETCH_TRIP_STATUS_TIME_INTERVAL_MAX;
}
$intervalmins = INTERVAL_SECONDS; // Added By HJ On 13-03-2020 Which is Defined In configuration_variables.php 
$cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
$str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
$script = "LiveMap";
$ssql = "SELECT  count(rd.iDriverId) as available FROM register_driver AS rd LEFT JOIN driver_vehicle AS dv ON dv.iDriverVehicleId=rd.iDriverVehicleId WHERE rd.vLatitude !='' AND rd.vLongitude !='' AND rd.vAvailability = 'Available' AND rd.vTripStatus != 'Active' AND rd.tLocationUpdateDate > '$str_date' AND rd.eStatus='Active'";
$db_record = $obj->MySQLSelect($ssql);
$available = $db_record[0]['available'];
/* $ssql= "SELECT  tLatitude,tLongitude,vLocationName,iLocationId FROM location_master WHERE eStatus = 'Active' AND eFor = 'VehicleType' ORDER BY `iLocationId` ASC ";
  $db_latlong = $obj->MySQLSelect($ssql);
  $count=count($db_latlong);
  $count=count($db_latlong);
  if($count>0)
  {
  $Latitudes=explode(",",$db_latlong[0]['tLatitude']);
  $Longitudes=explode(",",$db_latlong[0]['tLongitude']);
  for($i=0;$i<count($Latitudes);$i++)
  {
  $Latitude=	$Latitudes[0];
  $Longitude=$Longitudes[0];
  }
  }else{
  $ssql.=" AND (vTripStatus = 'Active' OR vTripStatus = 'On Going Trip' OR vTripStatus = 'Arrived') ";
  $db_latlongs = $obj->MySQLSelect($ssql);
  if($db_latlongs[0]['vLatitude']==null && $db_latlongs[0]['vLongitude']==null)
  {
  $ssql.=" or ( rd.eStatus='Active')  ORDER BY  `rd`.`vTripStatus` DESC";
  $db_activelatlongs = $obj->MySQLSelect($ssql);
  $Latitude=$db_activelatlongs[0]['vLatitude'];
  $Longitude=$db_activelatlongs[0]['vLongitude'];
  } else {
  $Latitude=$db_latlongs[0]['vLatitude'];
  $Longitude=$db_latlongs[0]['vLongitude'];
  }
  } */
$ssql = "SELECT  SUM(CASE WHEN vTripStatus = 'Arrived' THEN 1 ELSE 0 END) arrived,
SUM(CASE WHEN vTripStatus = 'On Going Trip' THEN 1 ELSE 0 END) ontrip,
SUM(CASE WHEN vTripStatus = 'Active' THEN 1 ELSE 0 END) active,
SUM(CASE WHEN rd.eStatus = 'Active' THEN 1 ELSE 0 END) allr,rd.vLatitude,rd.vLongitude FROM register_driver AS rd LEFT JOIN driver_vehicle AS dv ON dv.iDriverVehicleId=rd.iDriverVehicleId WHERE rd.vLatitude !='' AND rd.vLongitude !='' AND rd.eStatus='Active'";
$db_records = $obj->MySQLSelect($ssql);
$arrived = $db_records[0]['arrived'];
$active = $db_records[0]['active'];
$ontrip = $db_records[0]['ontrip'];
$all = $db_records[0]['allr'];

$per_page = 10;	
?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME; ?> | Live Map</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <!-- GLOBAL STYLES -->
        <? include_once('global_files.php'); ?>
        <link rel="stylesheet" href="css/style.css" />
        <script src="https://maps.google.com/maps/api/js?sensor=true&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>" type="text/javascript"></script>
        <script type='text/javascript' src='../assets/map/gmaps.js'></script>
        <script type='text/javascript' src='../assets/js/jquery-ui.min.js'></script>
        <!--END GLOBAL STYLES -->
        <style>
            .map-page {
                    height: 100%;
                   width: 100%;
                   margin: 0px;
                   padding: 0px;
            }
        </style>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <? include_once('header.php'); ?>
            <? include_once('left_menu.php'); ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner" style="min-height: 700px;">
                    <div class="row">
                        <div class="col-lg-12">
                            <h1> God's View</h1>
                        </div>
                    </div>
                    <hr/>
                    <?php /* if($count>0){ ?>
                      <table width="30%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                      <tr>
                      <td width="5%"><label for="textfield"><strong>Select Location Name:</strong></label></td>
                      <td width="10%" class=" padding-right10">
                      <select name="option" id="option" class="form-control" onChange="activeLocation(option)">
                      <?php for($ii=0;$ii<count($db_latlong);$ii++)
                      { 	$LocationName=ucfirst($db_latlong[$ii]['vLocationName']);
                      $iLocationId=$db_latlong[$ii]['iLocationId']; ?>
                      <option  value="<?php echo $iLocationId; ?>"	 <?php if ($option =="$iLocationId") {   echo "selected";} ?> ><?php echo$LocationName; ?></option>
                      <?php } ?>
                      </select>
                      </td>
                      </tr>
                      </table>
                      <?php }else{ ?>
                      <input type="hidden" name="option" id="option"  value="">
                      <?php } */ ?>
                    <div class="map-color-code">
                        <ul>
                            <ul>
                                <li> 
                                    <button class="btn btn-default col-sm-12 status_filter enroute" onClick="activeTab('Active')"> <img src="../assets/img/red.png"><?php echo $langage_lbl['LBL_ENROUTE_TO']; ?>&nbsp;(<?php echo $active; ?>)</button>
                                </li>
                                <li> 
                                    <button class="btn btn-default col-sm-12 status_filter reached" onClick="activeTab('Arrived')"><img src="../assets/img/blue.png"> <?php echo $langage_lbl['LBL_REACHED_PICKUP']; ?>&nbsp;(<?php echo $arrived; ?>)</button>
                                </li>
                                <li> 
                                    <button class="btn btn-default col-sm-12 status_filter tripstart" onClick="activeTab('On Going Trip')"><img src="../assets/img/yellow.png"> <?php echo $langage_lbl['LBL_JOURNEY_STARTED']; ?>&nbsp;(<?php echo $ontrip; ?>)</button> 
                                </li>
                                <li> 
                                    <button class="btn btn-default col-sm-12 status_filter available"  onClick="activeTab('Available')"> <img src="../assets/img/green.png"> <?= $langage_lbl['LBL_AVAILABLE']; ?>&nbsp;(<?php echo $available; ?>) </button>
                                </li>
                                <li>
                                    <button class="btn btn-default col-sm-12 status_filter active all" onClick="activeTab('')"><?php echo $langage_lbl['LBL_ALL']; ?>&nbsp;(<?php echo $all; ?>)</button>
                                </li>
                            </ul>
                    </div>
                    <div align="right" style="margin-bottom:10px;">
                        <a href="map_godsview.php" class="btn btn-primary"  >Refresh</a>
                    </div>
                    <div class="map-main-page-inner">
                        <div class="map-main-page-inner-tab">
                            <h3 class="list_title title">All</h3>
                            <span>
                                <input name="" type="text" id="finddriver" placeholder="Search <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>" onkeyup="setFilter('keyword', this.value);">
                            </span>
                            <ul id="driver_main_list" class="driver_main_list_scroll"  style="display:none" onscroll="get_driver_list_on_scroll();">

                            </ul>
			<input type="hidden" name="page" id="page" value="2"> 
                        </div>
                        <div class="map-page">
                            <div class="panel-heading location-map" style="background:none;">
                                <div class="google-map-wrap">
                                    <div id="google-map" class="google-map map001"> </div>
                                    <!-- #google-map -->
                                </div>
                            </div>
                        </div>
                        <!-- popup -->
                        <div class="map-popup" style="display:none" id="driver_popup"></div>
                        <!-- popup end -->
                    </div>
                    <input type="hidden" name="newType" id="newType" value="">
                    <input type="hidden" name="drivename" id="drivename" value="">
                    <div style="clear:both;"></div>
                    <?php if (SITE_TYPE != 'Demo') { ?>
                        <div class="admin-notes">
                            <h4>Notes:</h4>
                            <ul>
                                <li>
                                    IMPORTANT: Please note that the <?= $langage_lbl_admin['LBL_DRIVERS_TXT_ADMIN'] ?> shown as Available/Online ( Green ) are only those <?= $langage_lbl_admin['LBL_DRIVERS_TXT_ADMIN'] ?> which are available in real with <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> application running properly in their phone. If <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> has put his application in background since long time then there is major possibility of application being killed in background, which makes them unavailable for <?php echo strtolower($langage_lbl_admin['LBL_RIDERS_ADMIN']); ?> and also shown offline in map despite of they are online in application.
                                </li>
                            </ul>
                        </div>
                    <?php } ?>
                </div>
                <!--END PAGE CONTENT -->
            </div>
            <? include_once('footer.php'); ?>
            <div style="clear:both;"></div>
            <script>
                //var is_touch_device = 'ontouchstart' in document.documentElement;
                var map,
                        active_tab,
                        infowindow,
                        filters = {
                            type: '',
                            keyword: "",
                        },
                        all_markers = [],
                        markers = [],
                        bounds = [];
                jQuery(document).ready(function ($) {
                    map = new google.maps.Map($('.google-map')[0], {
                        zoom: 4,
                        center: {lat: -28.024, lng: 140.887}
                    });
                    activeTab();
                    var $window = $(window);
                    function mapWidth() {
                        var size = $('.google-map-wrap').width();
                        $('.google-map').css({width: size + 'px', height: (size / 2) + 'px'});
                    }
                    mapWidth();
                    $(window).resize(mapWidth);
                    /*  setInterval(function() { active_tab(active_tab);}, 120000);  */
                    $(document).mouseup(function (e) {
                        var container = $("#driver_popup");
                        var container1 = $("#driver_main_list");
                        if (!container.is(e.target) && !container1.is(e.target) // if the target of the click isn't the container...
                                && container.has(e.target).length === 0 && container1.has(e.target).length === 0) // ... nor a descendant of the container
                        {
                            container.hide("slide", {direction: "right"}, 700);
                        }
                    });
                });
	var myVar = null;
	function get_driver_list_on_scroll() {
		var option=$("#option").val();
		var page=$("#page").val();
		var obj = $('.driver_main_list_scroll');
		var search = $('#finddriver').val();
        clearTimeout(myVar);
		//if(search.length >= 2 && ( obj.scrollTop() === (obj[0].scrollHeight - obj[0].offsetHeight)))
		
		if((obj.scrollTop() === (obj[0].scrollHeight - obj[0].offsetHeight)))
        {
                    myVar = setTimeout(function () {
							$.ajax({
					
								type: "POST",
					
								url: "get_map_drivers_list_godsview.php",
					
								dataType: "json",
					
								data: {type: filters.type,option:option,page:page},
					
								success: function(data){
									page= data.page;
									 $("#page").val(page);
									update_list();
									/*all_markers_new = data.markers;
									$.merge(all_markers,all_markers_new);
									//var all_markers = all_markers.concat(all_markers_new);
									all_main_location_new= data.main_location;
									$.merge(all_main_location,all_main_location_new);
									//var all_main_location_new = all_main_location.concat(all_main_location_new);
									page= data.page;
									 $("#page").val(page);
									applyFilter();*/
								},
								error: function(dataHtml){
								}
							});
					}, 500);
        }
	}
                function activeLocation(option) {
                    var option = $("#option").val();
                    get_map_data();
                }
                function activeTab(type) {
                    $("#newType").val(type);
                    var option = $("#option").val();
                    if (type == 'Active') {
                        title = 'Enroute to Pickup';
                        classname = 'enroute';
                    } else if (type == 'Arrived') {
                        title = 'Reached Pickup';
                        classname = 'reached';
                    } else if (type == 'On Going Trip') {
                        title = 'Journey Started';
                        classname = 'tripstart';
                    } else if (type == 'Available') {
                        title = 'Available';
                        classname = 'available';
                    } else {
                        title = 'All';
                        classname = 'all';
                    }
                    $('.status_filter').removeClass('active');
                    $('.status_filter.' + classname).addClass('active');
                    $('.list_title').removeClass('enroute reached tripstart available').addClass(classname);
                    $('.list_title').html(title);
                    setFilter('type', type, false);
                    setFilter('keyword', "", false);
                    get_map_data();
                }
                function setFilter(key, value, update) {
                    switch (key) {
                        case "keyword":
                            $('#finddriver').val(value);
                            filters[key] = value;
                            break;
                        default:
                            filters[key] = value;
                            break;
                    }
                    if (update == undefined) {
                        update = true;
                    }
                    if (update) {
                        applyFilter();
                    }
                }
                function applyFilter() {
                    keyword = filters.keyword;
                    if (keyword == "" || keyword == undefined) {
                        keyword = null;
                    }
                    if (keyword == null) {
                        update_map(all_markers);
                        update_mapl(all_main_location);
                    } else {
                        var filter_markers = [];
                        $.each(all_markers, function (index, marker) {
                            var name = marker.fullname.toLowerCase();
                            var phone = marker.phone.toLowerCase();
                            keyword = filters.keyword;
                            if (name.search(keyword.toLowerCase()) !== -1 || phone.search(keyword.toLowerCase()) !== -1) {
                                filter_markers.push(marker);
                            }
                        });
                        update_map(filter_markers);
                        update_mapl(all_main_location);
                    }
                }
                function get_map_data() {
                    var option = $("#option").val();
                    $(".loader-default").show();
                    $.ajax({
                        type: "POST",
                        url: "get_map_drivers_list_godsview.php",
                        dataType: "json",
                        data: {type: filters.type, option: option},
                        success: function (data) {
                            $(".loader-default").hide();
                            all_markers = data.markers;
                            all_main_location = data.main_location;
                            applyFilter();
                        },
                        error: function (dataHtml) {
                        }
                    });
                }
                /* Change map fix zoom in loacate to area*/
                function update_mapl(all_main_location) {
                    var bounds = new google.maps.LatLngBounds();
                    if (all_main_location.length > 0) {
                        for (var ii = 0; ii < all_main_location.length; ii++) {
                            var finall = all_main_location[ii];
                            var myLatLng = new google.maps.LatLng(finall.Latitude, finall.Longitude);
                            bounds.extend(myLatLng);
                        }
                    } else {
                        var myLatLng = new google.maps.LatLng("<?php echo $Latitude; ?>", "<?php echo $Longitude; ?>");
                        bounds.extend(myLatLng);
                    }
                    //map.fitBounds(bounds);
                    //zoomChangeBoundsListener = google.maps.event.addListenerOnce(map, 'bounds_changed', function (event) {
                    //    if (this.getZoom()) {
                    //        this.setZoom(7);
                    //    }
                    //    map.setZoom(4);
                    //});
                    
                }
                function update_map(locations) {
                    for (var i = 0; i < markers.length; i++) {
                        markers[i].setMap(null);
                    }
                    markers = [];
                    if (locations.length == 0) {
                        update_list();
                        return;
                    }     
                    var bounds = new google.maps.LatLngBounds();
                    for (var i = 0; i < locations.length; i++) {
                        var driver = locations[i];
                        if (driver.data != undefined) {
                            marker = driver;
                            bounds.extend(marker.position);
                        } else {
                            var latlng = new google.maps.LatLng(driver.location.lat, driver.location.lng);
                            /*	bounds.extend(myLatLng);*/
                            var marker = new google.maps.Marker({
                                position: latlng,
                                map: map,
                                icon: driver.location.icon,
                            });
                            marker.data = driver;
                            marker.addListener('click', function (e) {
                                activeItem(this);
                                scrollList(this);
                            });
                           
                           
                    
                        }
                        
                            bounds.extend(marker.position);
                        markers.push(marker);
                       
                         
                    }
                     map.fitBounds(bounds);                   
                    update_list();
                }
                function alerts(ids)
                {
                    var confirmLeave = confirm('Please note that the real time tracking of drivers consumes the pubnub.com messages which will increase the pubnub.com costing.');
                    if (confirmLeave == true)
                    {
                        window.open(ids, '_blank');
                    } else
                    {
                        return false;
                    }
                }
                function showInfoWindow(marker) {
                    hideInfoWindow();
                    var driver = marker.data;
                    var trip = driver.trip;
                    trip = String(trip);
                    var content = "<table><tr><td rowspan='5'><img src=" + driver.image + " height='70' width='auto'/></td>";
                    content += "<tr><td>&nbsp;&nbsp;Name: </td><td><b>" + driver.fullname + "</b></td></tr>";
                    content += "<tr><td>&nbsp;&nbsp;Email: </td><td><b>" + driver.email + "</b></td></tr>";
                    content += "<tr><td>&nbsp;&nbsp;Mobile: </td><td><b>" + driver.phone + "</b></td></tr>";
                    if (trip != "")
                    {
<?php if ($trackingMethod == "Pubnub") { ?>
                            content += "<tr><td></td><td><a onClick =alerts('" + driver.trip + "')><b>Live tracking</b></a></td></tr>";
<?php } else { ?>
                            content += "<tr><td></td><td><a href='" + driver.trip + "' target='_blank'><b>Live tracking</b></a></td></tr>";
<?php } ?>
                    }
                    content += "</table>";
                    infowindow = new google.maps.InfoWindow({
                        content: content
                    });
                    infowindow.open(map, marker);
                }
                function hideInfoWindow() {
                    if (infowindow != undefined && infowindow != null) {
                        infowindow.close();
                    }
                }
                function update_list() {
                    $('#driver_main_list').html("");
                    if (markers.length == 0) {
                        var li = $('<li class="not-found">').html('<p class="driver_1">No <?= $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"]; ?> Found</p>')
                        $('#driver_main_list').html(li);
                    }
		var filter_markers = [];
		per_page = '<?= $per_page ?>';
		page1 = $("#page").val() * per_page;
		if(markers.length < page1) page1 = markers.length;
		for (var i = 0; i < page1; i++) {
                        var item = markers[i];
                        var driver = item.data;
                        var onClick = function (item) {
                            return function () {
                                activeItem(item);
                            }
                        }
                        // var li = $('<li class="drver-list-item driver-list-item-' + item.data.id + '">').html(`
                        //             <label class="map-tab-img">
                        //                     <label class="map-tab-img1">
                        //                             <img src="` + driver.image + `">
                        //                     </label>
                        //                     <img src="` + driver.status_icon + `">
                        //             </label>
                        //             <p class="driver_1">` + driver.fullname + ` <b>` + driver.phone + `</b></p>
                        //     `).on('click', onClick(item));
                         var li = $('<li class="drver-list-item driver-list-item-' + item.data.id + '">')
                        .html('<label class="map-tab-img"><label class="map-tab-img1"><img src="' + driver.image + '"></label><img src="' + driver.status_icon + '"></label><p class="driver_1">' + driver.fullname + ' <b>' + driver.phone + '</b></p>').on('click', onClick(item));
                        $('#driver_main_list').append(li);
                    }
                    $('#driver_main_list').show();
                }
                function activeItem(marker) {
                    showInfoWindow(marker);
		markerdataid = 'driver-list-item-'+marker.data.id;
		var markers1 = [];
		markers1 = markers;
		var filter_markers = [];
		if($("." + markerdataid).length>0) {
			
		} else {
			//this is when at left side 5 driver data is there and in map all markers are loaded as KS said..and then in any marker clicked at that time at left side that driver will be highlighted so that would be done here..temp add that driver data to main array..and then reset it...
			filters.keyword = "";
			keyword = filters.keyword;
			filter_markers.push(marker.data);
			
			per_page = '<?= $per_page ?>';
			page1 = $("#page").val() * per_page;
			
			if(markers.length < page1) page1 = markers.length;
			for (var i = 0; i < page1; i++) {
				filter_markers.push(markers[i].data);
			}
			
			update_map(filter_markers);
			update_mapl(all_main_location);
			markers = markers1;
			//setFilter('keyword', marker.data.fullname);
			/*var onClick = function(item){
		
						return function() {
		
							activeItem(item);
		
						}
		
					}
		
					$("#driver_main_list").off("scroll", get_driver_list_on_scroll);
		
					var li = $('<li class="drver-list-item driver-list-item-'+marker.data.id+'">').html(`
		
						<label class="map-tab-img">
		
							<label class="map-tab-img1">
		
								<img src="`+marker.data.image+`">
		
							</label>
		
							<img src="`+marker.data.status_icon+`">
		
						</label>
		
						<p class="driver_1">`+marker.data.fullname+` <b>`+marker.data.phone+`</b></p>
		
					`).on('click', onClick(marker));
					
					$('#driver_main_list').append(li);*/
					
		}
                    $('#driver_main_list li').removeClass('active');
                    $('#driver_main_list').find('.driver-list-item-' + marker.data.id).addClass('active');
                    map.setCenter(marker.position);
                }
                function scrollList(marker) {
                    var current_li = $('#driver_main_list').find('.driver-list-item-' + marker.data.id);
                    if (current_li) {
                        var top_post = $("#driver_main_list").scrollTop() - $("#driver_main_list").offset().top + current_li.offset().top;
                        $("#driver_main_list").animate({scrollTop: top_post}, 500);
                    }
                }
            </script>
            
    </body>
    <!-- END BODY-->
</html>