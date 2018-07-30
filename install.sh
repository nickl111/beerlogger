#! /bin/bash

# Install script. I am almost certainly not going to package this and it almost certainly only works on raspbian/debian

USAGE="USAGE: $0 install|uninstall"

#TODO make sure we're root

case $1 in
	install)
		apt-get install apache2 php rrdtool sqlite3 wiringpi
		#TODO get directory this script is in (atm running from a different directory wouldprobably be messy)
		. core/config.sh
		mkdir $HOME_DIR
		cp core/* $HOME_DIR/
		chmod 755 $HOME_DIR/beerlog
		
		cp systemd/beerlog.service /etc/systemd/system/
		systemctl daemon-reload
		systemctl beerlog enable
		
		cp apache/beerlog.conf /etc/apache2/mods-available/
		ln -s /etc/apache2/mods-available/beerlog.conf /etc/apache2/mods-enabled/beerlog.conf
		
	;;
	uninstall)
		# TODO Warning about data loss
		rm -rf $HOME_DIR
		rm -rf $RUN_DIR
		systemctl beerlog disable
		rm -f /etc/systemd/system/beerlog.service
		systemctl daemon-reload
		rm -f /etc/apache2/mods-enabled/beerlog.conf
		rm -f /etc/apache2/mods-available/beerlog.conf
	;;
	*)
		echo "Unknown command ${1}"
		echo $USAGE
	;;
esac
