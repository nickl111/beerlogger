#! /bin/bash

# Install script. I am almost certainly not going to package this and it almost certainly only works on raspbian/debian

USAGE="USAGE: $0 install|uninstall"

THIS_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root" 
   exit 1
fi

. $THIS_DIR/core/config.sh

case $1 in
	install)
		apt-get -y install apache2 php mariadb-server php-mysql bc bluez bluez-hcidump wiringpi

		mkdir -p $HOME_DIR
		mkdir -p $RUN_DIR
		cp $THIS_DIR/core/* $HOME_DIR/
		chmod 755 $HOME_DIR/beerlog
		chmod 755 $HOME_DIR/cron
		
		sed -i "s@|SQL_USER|@$SQL_USER@g" $HOME_DIR/cron
		sed -i "s@|SQL_PASS|@$SQL_PASS@g" $HOME_DIR/cron
		sed -i "s@|SQL_HOST|@$SQL_HOST@g" $HOME_DIR/cron
		sed -i "s@|SQL_DB|@$SQL_DB@g" $HOME_DIR/cron
		
		sed s"@|HOME_DIR|@$HOME_DIR@g" $THIS_DIR/systemd/beerlog.service > /etc/systemd/system/beerlog.service
		sed -i "s@|RUN_DIR|@$RUN_DIR@g" /etc/systemd/system/beerlog.service
		systemctl daemon-reload
		systemctl enable beerlog
		
		sed "s@|OUT_DIR|@$OUT_DIR@g" $THIS_DIR/apache/beerlogger.conf > /etc/apache2/sites-available/beerlogger.conf
		ln -s /etc/apache2/sites-available/beerlogger.conf /etc/apache2/sites-enabled/beerlogger.conf
		mkdir -p $OUT_DIR/html
		mkdir -p $OUT_DIR/logs

		rsync -a $THIS_DIR/web/ $OUT_DIR/html/
		
		mkdir -p $DATA_DIR/db
		chgrp www-data $DATA_DIR/db
		chmod -R 777 $DATA_DIR/db	# This is the only permission set I can get it to work on under apache
		
		sed -i "s@|SQL_USER|@$SQL_USER@g" $OUT_DIR/html/sql.sql
		sed -i "s@|SQL_PASS|@$SQL_PASS@g" $OUT_DIR/html/sql.sql
		sed -i "s@|SQL_HOST|@$SQL_HOST@g" $OUT_DIR/html/sql.sql
		sed -i "s@|SQL_DB|@$SQL_DB@g" $OUT_DIR/html/sql.sql
		
		sed -i "s@|SQL_USER|@$SQL_USER@g" $OUT_DIR/html/db.php
		sed -i "s@|SQL_PASS|@$SQL_PASS@g" $OUT_DIR/html/db.php
		sed -i "s@|SQL_HOST|@$SQL_HOST@g" $OUT_DIR/html/db.php
		sed -i "s@|SQL_DB|@$SQL_DB@g" $OUT_DIR/html/db.php
		
		mysql < $OUT_DIR/html/sql.sql
		
		# This turns power save on the wifi off
		! grep -q "/sbin/iwconfig wlan0 power off" /etc/rc.local && sed -i 's/^exit 0$/\/sbin\/iwconfig wlan0 power off\nexit 0/' /etc/rc.local
		
		
		systemctl start beerlog
		systemctl restart apache2
		
		mycron=`mktemp`
		crontab -l > $mycron
		echo "*/10 * * * * $HOME_DIR/cron" >> $mycron
		crontab $mycron
		rm $mycron
		
		echo "dtoverlay=w1-gpio" >> /boot/config.txt
		dtoverlay w1-gpio gpiopin=4 pullup=0
		
		
	;;
	uninstall)
		# TODO Warning about data loss
		rm -r $HOME_DIR
		rm -r $RUN_DIR
		systemctl stop beerlog
		sleep 2
		systemctl disable beerlog
		rm /etc/systemd/system/beerlog.service
		systemctl daemon-reload
		rm /etc/apache2/sites-enabled/beerlogger.conf
		rm /etc/apache2/sites-available/beerlogger.conf
		
		mycron=`mktemp`
		crontab -l > $mycron
		sed -i "s@\*/10 \* \* \* \* $HOME_DIR/cron@@" $mycron 
		crontab $mycron
		rm $mycron
		
	;;
	*)
		echo "Unknown command ${1}"
		echo $USAGE
		exit 2
	;;
esac
