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
		apt-get install apache2 php rrdtool sqlite3 wiringpi

		mkdir $HOME_DIR
		cp $THIS_DIR/core/* $HOME_DIR/
		chmod 755 $HOME_DIR/beerlog
		
		cp $THIS_DIR/systemd/beerlog.service /etc/systemd/system/
		systemctl daemon-reload
		systemctl beerlog enable
		
		sed 's/|OUTDIR|/$OUTDIR/g' $THIS_DIR/apache/beerlogger.conf > /etc/apache2/sites-available/beerlogger.conf
		ln -s /etc/apache2/sites-available/beerlogger.conf /etc/apache2/sites-enabled/beerlogger.conf
		mkdir -p $OUT_DIR/html
		mkdir -p $OUT_DIR/logs
		
		mkdir -p $DATA_DIR
		$HOME_DIR/rrd/rrd.sh create
		mycron=`mktemp`
		crontab -l > $mycron
		echo "*/1 * * * $HOME_DIR/rrd/rrd.sh update" >> mycron
		echo "*/1 * * * $HOME_DIR/rrd/rrd.sh graph" >> mycron
		crontab $mycron
		rm $mycron
		
		sqlite3 $DATA_DIR/beerlog.db < $THIS_DIR/web/sql.sql
	;;
	uninstall)
		# TODO Warning about data loss
		rm -r $HOME_DIR
		rm -r $RUN_DIR
		systemctl stop beerlog
		systemctl disable beerlog
		rm /etc/systemd/system/beerlog.service
		systemctl daemon-reload
		rm /etc/apache2/sites-enabled/beerlog.conf
		rm /etc/apache2/sites-available/beerlog.conf
		
		mycron=`mktemp`
		mycron2=`mktemp`
		crontab -l > $mycron
		sed 's/\*\/1 \* \* \* \* $HOME_DIR\/rrd\/rrd\.sh update//' $mycron > $mycron2
		sed 's/\*\/1 \* \* \* \* $HOME_DIR\/rrd\/rrd\.sh graph//' $mycron2 > $mycron
		crontab $mycron
		rm $mycron $mycron2
	;;
	*)
		echo "Unknown command ${1}"
		echo $USAGE
		exit 2
	;;
esac
