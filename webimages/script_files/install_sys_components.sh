
#!/bin/sh
YUM_CMD=$(which yum)
APT_GET_CMD=$(which apt-get)
DIG_CMD=$(which dig)
MONGOD_CMD=$(which mongod)
SYS_CTL_CMD=$(which systemctl)
LSB_REL_CMD=$(which lsb_release)
CURRENT_PATH=$(pwd)
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CENTOS_VERSION="`rpm --eval '%{centos_ver}'`"

INSTALL_MONGO_DB=false
PHP_EXTENSION_MONGO_INSTALLED=true

PANEL_PATH="`echo $SCRIPT_DIR | rev | cut -d'/' -f3- | rev`"
SCRIPT_PATH=$SCRIPT_DIR/sys_prj_config.sh

chmod +x $SCRIPT_PATH

if [[ -z $DIG_CMD ]]; then
	if [[ ! -z $YUM_CMD ]]; then
		yum install -y bind-utils
	elif [[ ! -z $APT_GET_CMD ]]; then
		sudo apt-get install -y dnsutils
	else
		echo "===================================="
		echo "Error can't install required components. May be System version is not supported. Please contact to technical team."
		echo "===================================="
		exit 1
	fi
fi
	
CURRENT_SERVER_IP="`dig +short myip.opendns.com @resolver1.opendns.com`"
CURRENT_LOCAL_IP_DATA="`ip addr show`"

if [[ ("$CURRENT_LOCAL_IP_DATA" == *192.168.1.131*) || ("$CURRENT_LOCAL_IP_DATA" == *192.168.1.141*) || ("$CURRENT_LOCAL_IP_DATA" == *173.231.205.149*) || ("$CURRENT_LOCAL_IP_DATA" == *209.182.192.34*) || ("$CURRENT_LOCAL_IP_DATA" == *67.205.155.33*) ]]; then
	echo "===================================="
	echo "You are not allowed to run script on this server. Please contact to technical team."
	echo "===================================="
	exit 1
fi

if [[ ! -z $YUM_CMD ]]; then
	yum clean all
elif [[ ! -z $APT_GET_CMD ]]; then
	sudo apt-get autoclean
else
	echo "===================================="
    echo "Error can't install required components. May be System version is not supported. Please contact to technical team."
	echo "===================================="
	exit 1
fi

if [[ -z $MONGOD_CMD ]]; then
	if [ "$INSTALL_MONGO_DB" == true ]; then
		if [[ ! -z $YUM_CMD ]]; then
			rm -rf /etc/yum.repos.d/mongodb-org-4.2.repo
		elif [[ ! -z $APT_GET_CMD ]]; then
			rm -rf /etc/apt/sources.list.d/mongodb-org-4.2.list
		else
			echo "===================================="
			echo "Error can't install required components. May be System version is not supported. Please contact to technical team."
			echo "===================================="
			exit 1
		fi
	fi
fi

forever stopall

if [[ ! -z $YUM_CMD ]]; then
	echo "===================================="
	yum remove -y nodejs
	rm -rf /var/cache/yum
	rm /etc/yum.repos.d/nodesource*
	yum clean all
    yum install -y gcc-c++ make
	yum install -y git
	yum install -y mlocate
	yum install -y php-devel
	# if [[ -z "$CENTOS_VERSION" ]]; then
		# curl -sL https://rpm.nodesource.com/setup_10.x | bash -
	# else
		# if [[ "$CENTOS_VERSION" == "6" ]]; then
			# curl -sL https://rpm.nodesource.com/setup_8.x | bash -
		# else
			# curl -sL https://rpm.nodesource.com/setup_10.x | bash -
		# fi
	# fi
	curl -sL https://rpm.nodesource.com/setup_10.x | bash -
	yum install -y nodejs
	npm install -y -g npm
	echo "===================================="
elif [[ ! -z $APT_GET_CMD ]]; then
	echo "===================================="
    sudo apt-get -y purge --auto-remove nodejs
	sudo apt-get autoclean
	sudo apt-get install -y curl
	sudo apt-get install -y unzip
	sudo apt-get install -y build-essential
	sudo apt-get install -y git-all
	sudo apt-get install -y mlocate
	sudo apt-get install -y php-dev
	curl -sL https://deb.nodesource.com/setup_10.x | sudo bash -
	sudo apt-get install nodejs-dev node-gyp libssl1.0-dev
	sudo apt-get install -y nodejs
	sudo apt-get install -y build-essential
	sudo apt-get install -y npm
	echo "===================================="
else
	echo "===================================="
    echo "Error can't install required components. May be System version is not supported. Please contact to technical team."
	echo "===================================="
	exit 1
fi


VERIFY_INSTALLATION_NODE=$(which node)
VERIFY_INSTALLATION_NPM=$(which npm)

NODE_INSTALLED=false

if [[ ! -z $VERIFY_INSTALLATION_NODE ]]; then
	NODE_INSTALLED=true
	if [[ ! -z $VERIFY_INSTALLATION_NPM ]]; then
		NODE_INSTALLED=true
	else
		NODE_INSTALLED=false
	fi
else
	NODE_INSTALLED=false
fi

if [ "$NODE_INSTALLED" == false ]; then
	echo "===================================="
    echo "Error can't install required components. May be System version is not supported. Please contact to technical team."
	echo "===================================="
	exit 1
fi

if [[ -z $MONGOD_CMD ]]; then
	if [ "$INSTALL_MONGO_DB" == true ]; then
		if [[ ! -z $YUM_CMD ]]; then
			echo "===============MongoDB====================="
			cp $SCRIPT_DIR/mongodb-org-4.2.repo /etc/yum.repos.d/mongodb-org-4.2.repo
			yum install -y mongodb-org
			
			if [[ ! -z $SYS_CTL_CMD ]]; then
				sudo systemctl daemon-reload
				sudo systemctl start mongod
				sudo systemctl enable mongod
			else
				service mongod start
				chkconfig mongod on
			fi
		elif [[ ! -z $APT_GET_CMD ]]; then
			echo "===================================="
			sudo apt-get install gnupg
			wget -qO - https://www.mongodb.org/static/pgp/server-4.2.asc | sudo apt-key add -
			cp $SCRIPT_DIR/mongodb-org-4.2_bionic.list /etc/apt/sources.list.d/mongodb-org-4.2.list
			sudo apt-get update
			sudo apt-get install -y mongodb-org
			
			if [[ ! -z $SYS_CTL_CMD ]]; then
				sudo systemctl daemon-reload
				sudo systemctl start mongod
				sudo systemctl enable mongod
			else
				service mongod start
				chkconfig mongod on
			fi
			echo "===================================="
		else
			echo "===================================="
			echo "Error can't install required components. May be System version is not supported. Please contact to technical team."
			echo "===================================="
			exit 1
		fi
		
		MONGOD_CMD=$(which mongod)
		
		if [[ -z $MONGOD_CMD ]]; then
			echo "===================================="
			echo "Error can't install required components (Specially MongoDB). May be System version is not supported. Please contact to technical team."
			echo "===================================="
			exit 1
		fi
		
		echo "=============Installing PHP-Mongo Driver=============="
		
		VERIFY_INSTALLATION_PHPIZE=$(which phpize)
		
		if [[ ! -z $VERIFY_INSTALLATION_PHPIZE ]]; then
			rm -rf /root/mongo-php-driver-git
			mkdir /root/mongo-php-driver-git
			git clone https://github.com/mongodb/mongo-php-driver.git /root/mongo-php-driver-git/mongo-php-driver.git
			
			CURRENT_CMD_PATH=$(pwd)
			
			cd /root/mongo-php-driver-git/mongo-php-driver.git
			
			git submodule update --init
			
			phpize
			
			./configure
			
			make all
			
			sudo make install
			
			echo "extension=mongodb.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`
			
			cd $CURRENT_CMD_PATH
			
			rm -rf /root/mongo-php-driver-git
			
		else
			PHP_EXTENSION_MONGO_INSTALLED=false
			echo "Skipping PHP-Mongo Driver installation due to phpize is not available. Please contact to technical team for this."
		fi

		echo "===================================="
		
	fi
fi

if [[ ! -z $YUM_CMD ]]; then
	service httpd restart
elif [[ ! -z $APT_GET_CMD ]]; then
	sudo systemctl restart apache2
else
	echo "===================================="
	echo "Error can't install required components. May be System version is not supported. Please contact to technical team."
	echo "===================================="
	exit 1
fi

npm install forever -g

npm install -g socketcluster

sh $SCRIPT_PATH


if [[ -z $PHP_EXTENSION_MONGO_INSTALLED ]]; then
	echo "===================================="
	echo "We have installed all required components. Now, We need to install MongoDB drivers manually for php (extension). We have tried to install but due to some reasons its failing. Please contact to technical team to do this. OR You can also contact to Server's support team to do this."
	echo "===================================="
else
	echo "===================================="
	echo "Component installation is finished."
	echo "===================================="
fi

