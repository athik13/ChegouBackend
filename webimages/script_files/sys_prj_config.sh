
#!/bin/sh
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PANEL_PATH="`echo $SCRIPT_DIR | rev | cut -d'/' -f3- | rev`"

CRONTAB_LIST_OUTPUT="`crontab -l`"
CURRENT_LOCAL_IP_DATA="`ip addr show`"

if [[ "$CRONTAB_LIST_OUTPUT" != *sys_prj_config.sh* ]]
then
	(crontab -l 2>/dev/null; echo "@reboot $SCRIPT_DIR/sys_prj_config.sh") | crontab -
fi

if [[ ("$CURRENT_LOCAL_IP_DATA" == *192.168.1.131*) || ("$CURRENT_LOCAL_IP_DATA" == *192.168.1.151*) ]]; then
	
	forever stop /root/SocketClsNode/server.js
	forever stop $PANEL_PATH/assets/libraries/SocketClsNode/server.js
	
	SOCKETCLUSTER_SOCKET_CHANNEL_LIMIT=9999999999999999 SOCKETCLUSTER_PORT=1033 ENV='prod' SOCKETCLUSTER_LOG_LEVEL=0 forever start /root/SocketClsNode/server.js

	if [[  -f "/root/adminMongo/app.js" ]] ; then
		forever stop $PANEL_PATH/assets/libraries/adminMongo/app.js
		forever stop /root/adminMongo/app.js
		forever start /root/adminMongo/app.js
	fi

	if [[  -f "/root/serviceInfoProvider/app.js" ]] ; then
		forever stop $PANEL_PATH/assets/libraries/mapsApiServiceProvider/app.js
		forever stop /root/serviceInfoProvider/app.js
		forever start /root/serviceInfoProvider/app.js
	fi
	
	echo "===================================="
	echo "Service has been initialized."
	echo "===================================="

	exit 1
fi


forever stop $PANEL_PATH/assets/libraries/SocketClsNode/server.js
	
SOCKETCLUSTER_SOCKET_CHANNEL_LIMIT=9999999999999999 SOCKETCLUSTER_PORT=9244 ENV='prod' SOCKETCLUSTER_LOG_LEVEL=0 forever start $PANEL_PATH/assets/libraries/SocketClsNode/server.js

if [[  -f "$PANEL_PATH/assets/libraries/adminMongo/app.js" ]] ; then
    forever stop $PANEL_PATH/assets/libraries/adminMongo/app.js
    forever start $PANEL_PATH/assets/libraries/adminMongo/app.js
fi

if [[  -f "$PANEL_PATH/assets/libraries/mapsApiServiceProvider/app.js" ]] ; then
    forever stop $PANEL_PATH/assets/libraries/mapsApiServiceProvider/app.js
    forever start $PANEL_PATH/assets/libraries/mapsApiServiceProvider/app.js
fi


echo "===================================="
echo "Service has been initialized."
echo "===================================="