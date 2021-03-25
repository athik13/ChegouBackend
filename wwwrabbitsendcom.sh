#!/bin/bash
cd /root/
forever stopall
yum install -y gcc-c++ make
curl -sL https://rpm.nodesource.com/setup_6.x | bash -
yum install -y nodejs
npm install -y -g npm
npm install forever -g
npm install -g socketcluster
rm -rf /root/SocketClsNode/
mkdir /root/SocketClsNode/
cd /root/SocketClsNode/ && wget http://webprojectsdemo.com/scClsNode_1567427644417.zip
cd /root/SocketClsNode/ && unzip scClsNode_1567427644417.zip
cd /root/SocketClsNode/ && rm -rf scClsNode_1567427644417.zip
echo "
#!/bin/sh
forever start /root/SocketClsNode/server.js
" > /root/scautostart.sh
chmod 755 /root/scautostart.sh
sed -i 's/8000/3589/g' /root/SocketClsNode/server.js
if crontab -l | grep -q '@reboot /root/scautostart.sh';
then
   echo "Skipping Crontab"
else
   crontab -l > mycron
   echo "

@reboot /root/scautostart.sh

" >> mycron
   crontab mycron
   rm mycron
fi
forever start /root/SocketClsNode/server.js