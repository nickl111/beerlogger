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
		apt-get install apache2 php rrdtool mysql php-mysql bc

		mkdir -p $HOME_DIR
		mkdir -p $RUN_DIR
		cp $THIS_DIR/core/* $HOME_DIR/
		chmod 755 $HOME_DIR/beerlog
		chmod 755 $HOME_DIR/led-emu.sh
		chmod 755 $HOME_DIR/selectTemp.sh
		
		sed s"@|HOME_DIR|@$HOME_DIR@g" $THIS_DIR/systemd/beerlog.service >  /etc/systemd/system/beerlog.service
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
		
		mkdir -p $HOME_DIR/rrd
		cp $THIS_DIR/rrd/* $HOME_DIR/rrd
		chmod 755 $HOME_DIR/rrd/rrd.sh
		$HOME_DIR/rrd/rrd.sh create
		mycron=`mktemp`
		crontab -l > $mycron
		echo "*/1 * * * * $HOME_DIR/rrd/rrd.sh update" >> $mycron
		echo "*/1 * * * * $HOME_DIR/rrd/rrd.sh graph" >> $mycron
		crontab $mycron
		rm $mycron
		
		sqlite3 $SQL_DB < $THIS_DIR/web/sql.sql
		
		systemctl start beerlog
		systemctl restart apache2
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
		mycron2=`mktemp`
		crontab -l > $mycron
		sed "s@\*/1 \* \* \* \* $HOME_DIR/rrd/rrd\.sh update@@" $mycron > $mycron2
		sed "s@\*/1 \* \* \* \* $HOME_DIR/rrd/rrd\.sh graph@@" $mycron2 > $mycron
		crontab $mycron
		rm $mycron $mycron2
	;;
	*)
		echo "Unknown command ${1}"
		echo $USAGE
		exit 2
	;;
esac
