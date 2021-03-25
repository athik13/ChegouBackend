var http = require('http');
var yalgaar = require('yalgaar');

 
var port = 8081;

var isYalgaarConnected = false;
 
var serverObj = http.createServer();

var dataObj = '';

serverObj.on('request', function(request, response) {
   // response.writeHead(200);
   // console.log(request.post);
   // console.log(request.headers);
   // console.log(request.url);
 
	var data = '';
	request.on('data', function(chunk) {
		data += chunk.toString();
	}); 
	
	request.on('end', function() {
	   
		//console.log("\nDATA:\n");
		console.log(data);
		console.log("\n\n");
		//response.write(""+data);
		
		dataObj = JSON.parse(data);
		
		var returnArr = {Action: "1"};
		response.write(JSON.stringify(returnArr));
		response.end();
			
		if(isYalgaarConnected == false){
			console.log("ConnectingToYalgaar");
			connectToYalgaar(dataObj.yalgaarClientKey);
		}else{
			PublishMessage();
		}
		
		//PublishMessage();
		
	}); 
	    
	
});
 
serverObj.listen(port, "127.0.0.1");

function connectToYalgaar(clientKey){
	yalgaar.ConnectToYalgaar({
		ClientKey: clientKey,
		SSL: false,
		Callback: function (acknowledgment) {
			console.log("ConnectionSuccessfullToYalgaar");
			//console.log(acknowledgment);
			//SubscribeMessage();
			isYalgaarConnected = true;
			PublishMessage();
		},
		Error: function (err) {
			//console.log(err);
			isYalgaarConnected = false;
		}
	});
}


function PublishMessage() {
	//console.log("channelName:::\n"+dataObj.channelName);
	//console.log("messageData:::\n"+JSON.stringify(dataObj.messageData));
    yalgaar.PublishMessage({
        ChannelName: dataObj.channelName,
        Message: JSON.stringify(dataObj.messageData) ,
        Callback: function (acknowledgment) {
            //console.log("PublishACK::"+acknowledgment);
        }
    });
}
/*function SubscribeMessage() {
    yalgaar.SubscribeMessage({
        ChannelName: 'YourChannel',
        Callback: function (message, channel, acknowledgment) {
            if (acknowledgment)
            {
                console.log(acknowledgment);
                PublishMessage();
            }
            if (message)
            {
                console.log(message);
            }
        },
        CallbackPresence: function (message, channel, acknowledgment) {
            if (acknowledgment)
                console.log(acknowledgment);
            if (message)
                console.log(message);
        }
    });
}*/