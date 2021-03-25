$(function () {
   
});
var pubSubInstance = '';
function initializeInstance(techniqueName,ChannelArray){
    var js = ChannelArray[techniqueName].js;
    
    var script  = document.createElement('script');
    script.src  = js;
    script.type = 'text/javascript';
    $("#map-canvas").after(script);
    
    if(techniqueName == 'SocketCluster') {
        
        var host = ChannelArray[techniqueName].host;
        var portNo = ChannelArray[techniqueName].portNo;
        var options = {
                    hostname: host,
                    secure: false,
                    port: portNo,
                    rejectUnauthorized: false // Only necessary during debug if using a self-signed certificate
                };
        return socketCluster.create(options);
        
    } else if(techniqueName == 'PubNub') {
        var js = ChannelArray[techniqueName].js;
        var publishKey = ChannelArray[techniqueName].publishKey;
        var subscribeKey = ChannelArray[techniqueName].subscribeKey;
        var uuid = ChannelArray[techniqueName].uuid;
        
        return new PubNub({subscribeKey: subscribeKey, publishKey: publishKey, uuid: uuid}); 
        
    } else if(techniqueName == 'Yalgaar') {
        var js = ChannelArray[techniqueName].js;
        var YalgaarClientKey = ChannelArray[techniqueName].YalgaarClientKey;
        
        connectToYalgaar({
            ClientKey: YalgaarClientKey,
            SSL: false,
            Callback: function (acknowledgement) {

            },
            Error: function (err) {
            }
        });
    }
}
function subsToChannel(ChannelArray,callback) {
    var techniqueName = ChannelArray.technique;
    var channelName = ChannelArray[techniqueName].channelName;
    
    //if(pubSubInstance == ''){
    pubSubInstance = initializeInstance(techniqueName,ChannelArray);
        //console.log(pubSubInstance);
    //}
    
    if(techniqueName == 'SocketCluster') {
        pubSubInstance.on('connect', function (status) {
            var channelObject = pubSubInstance.subscribe(channelName);
            //console.log(channelObject);
            channelObject.watch(function (handlerFn) {
                //console.log(handlerFn);
                var response = JSON.parse(handlerFn);
                //console.log("Socket Cluster Message Found");
                //handleResponse(response);
                callback(response,channelName);
            });
        });
    } else if(techniqueName == 'PubNub') {
        
        pubSubInstance.addListener({
            status: function (statusEvent) {

            },
            message: function (message) {
                // handle message
                var response = JSON.parse(message.message);
                callback(response);
            },
            presence: function (presenceEvent) {
                // handle presence
            }
        });
        pubSubInstance.subscribe({
            channels: [channelName],
        });
    } else if(techniqueName == 'Yalgaar') {
        
        SubscribeMessage({
            ChannelName: channelName,
            Callback: function (message, channel, acknowledgement) {
                // Shows alert when message receive
                alert(message);
            }
        });
    }
}

//function handleResponse(response) {
//    //var response = JSON.parse(response.message);
//    //var response = response.message;
//    //console.log(response);
//    if (response.vLatitude != "" && response.vLongitude != "") {
//        $('#map-canvas').show();
//        latlng = new google.maps.LatLng(response.vLatitude, response.vLongitude);
//        myOptions = {
//            zoom: 4,
//            center: latlng,
//        }
//        var duration = parseInt(950);
//        if (duration < 0) {
//            duration = 1;
//        }
//        setTimeout(function () {
//            //marker.setAnimation(null)
//            marker.animateTo(latlng, {easing: 'linear', duration: duration});
//            map.panTo(latlng); // For Move Google Map By Animate
//        }, 2000);
//        //map.setCenter(latlng); // For Set Center Location of Google Map Marker
//        //changeMarker(90);
//    }
//}