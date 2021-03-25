<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <title>Waypoints in Directions</title>
    <style>
      #right-panel {
        font-family: 'Roboto','sans-serif';
        line-height: 30px;
        padding-left: 10px;
      }

      #right-panel select, #right-panel input {
        font-size: 15px;
      }

      #right-panel select {
        width: 100%;
      }

      #right-panel i {
        font-size: 12px;
      }
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
      #map {
        height: 100%;
        float: left;
        width: 70%;
        height: 100%;
      }
      #right-panel {
        margin: 20px;
        border-width: 2px;
        width: 20%;
        height: 400px;
        float: left;
        text-align: left;
        padding-top: 0;
      }
      #directions-panel {
        margin-top: 10px;
        background-color: #FFEE77;
        padding: 10px;
        overflow: scroll;
        height: 174px;
      }
    </style>
  </head>
  <body>
    <div id="map"></div>
    <div id="right-panel">
    <div>
    <b>Start:</b>
    <select id="start">
      <option value="Halifax, NS">Halifax, NS</option>
      <option value="Boston, MA">Boston, MA</option>
      <option value="New York, NY">New York, NY</option>
      <option value="Miami, FL">Miami, FL</option>
    </select>
    <br>
    <b>Waypoints:</b> <br>
    <i>(Ctrl+Click or Cmd+Click for multiple selection)</i> <br>
    <select multiple id="waypoints">
      <option value="montreal, quebec">Montreal, QBC</option>
      <option value="toronto, ont">Toronto, ONT</option>
      <option value="chicago, il">Chicago</option>
      <option value="winnipeg, mb">Winnipeg</option>
      <option value="fargo, nd">Fargo</option>
      <option value="calgary, ab">Calgary</option>
      <option value="spokane, wa">Spokane</option>
    </select>
    <br>
    <b>End:</b>
    <select id="end">
      <option value="Vancouver, BC">Vancouver, BC</option>
      <option value="Seattle, WA">Seattle, WA</option>
      <option value="San Francisco, CA">San Francisco, CA</option>
      <option value="Los Angeles, CA">Los Angeles, CA</option>
    </select>
    <br>
      <input type="submit" id="submit">
    </div>
    <div id="directions-panel"></div>
    </div>
    <script>
      function initMap() {
        var directionsService = new google.maps.DirectionsService;
        var directionsDisplay = new google.maps.DirectionsRenderer;
        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 6,
          center: {lat: 23.0123259, lng: 72.5033536}
        });
        directionsDisplay.setMap(map);

        document.getElementById('submit').addEventListener('click', function() {
          calculateAndDisplayRoute(directionsService, directionsDisplay);
        });
      }

      function calculateAndDisplayRoute(directionsService, directionsDisplay) {
        var waypts = [];
        var checkboxArray = document.getElementById('waypoints');
		checkboxArray = ["23.0125799,72.5035839","23.012408897083,72.503812499471","23.0122636,72.5038843","23.0118367,72.5038727","23.011863196631,72.504512002766","23.0119087,72.5056142","23.012369486104,72.505601550515","23.012733951612,72.505592536822","23.0130604,72.5055879","23.013231322145,72.505576113185","23.0130604,72.5055879","23.0126591,72.5055936","23.0119087,72.5056142","23.011938004728,72.506270344753","23.01195465542,72.506643187615","23.0119557,72.5067589","23.0118671,72.5067681","23.011503769234,72.506784604463","23.0112441,72.5067964","23.0102926,72.5068286","23.01029,72.5067374","23.01149858736,72.506683144694","23.0118652,72.5066669","23.0118534,72.5061651","23.011832353753,72.505744083535"];
        for (var i = 0; i < checkboxArray.length; i++) {
          // if (checkboxArray.options[i].selected) {
			
			var loc_arr = checkboxArray[i].split(",");
			
            waypts.push({
              location: new google.maps.LatLng(loc_arr[0],loc_arr[1]),
              stopover: true
            });
          // }
        }

          /* origin: document.getElementById('start').value,
          destination: document.getElementById('end').value, */
        directionsService.route({
          origin: '23.0123259,72.5033536',
          destination: '23.011218990339287,72.50353027673728',
          // destination: '23.011717196893,72.503159124763',
          waypoints: waypts,
          optimizeWaypoints: false,
          travelMode: 'DRIVING'
        }, function(response, status) {
          if (status === 'OK') {
            directionsDisplay.setDirections(response);
            var route = response.routes[0];
            var summaryPanel = document.getElementById('directions-panel');
            summaryPanel.innerHTML = '';
            // For each route, display summary information.
            for (var i = 0; i < route.legs.length; i++) {
              var routeSegment = i + 1;
              summaryPanel.innerHTML += '<b>Route Segment: ' + routeSegment +
                  '</b><br>';
              summaryPanel.innerHTML += route.legs[i].start_address + ' to ';
              summaryPanel.innerHTML += route.legs[i].end_address + '<br>';
              summaryPanel.innerHTML += route.legs[i].distance.text + '<br><br>';
            }
          } else {
            window.alert('Directions request failed due to ' + status);
          }
        });
      }
    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyApVnJSAbArPkqDUoEoSOlVTXOa8MJLJwg&callback=initMap">
    </script>
  </body>
</html>