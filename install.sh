#! /bin/bash

# Install script. I am almost certainly not going to package this and it almost certainly only works on raspbian/debian

USAGE="USAGE: $0 install|uninstall"

THIS_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
#TODO make sure we're root

. $THIS_DIR/core/config.sh

case $1 in
	install)
		apt-get install apache2 php rrdtool sqlite3 wiringpi
		
		. $THIS_DIR/core/config.sh
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
		rm -rf $HOME_DIR
		rm -rf $RUN_DIR
		systemctl beerlog disable
		rm -f /etc/systemd/system/beerlog.service
		systemctl daemon-reload
		rm -f /etc/apache2/sites-enabled/beerlog.conf
		rm -f /etc/apache2/sites-available/beerlog.conf
	;;
	*)
		echo "Unknown command ${1}"
		echo $USAGE
	;;
esac
