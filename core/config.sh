#! /bin/bash

# Config file

IN=24		# Pin to monitor the interrupt on
LED_R=23	# Pin that drives the Red LED
LED_G=27	# Pin that drives the Green LED
LED_O=22	# Pin that drives the Orange LED
OW=4		#1-wire pin. We don't reference it anywhere but just a reminder not to use it

RATE_LIMIT=0.5

# The two temp sensor 1-wire addresses. T1 is the remote, T2 is the home
T2_ADDR=/sys/bus/w1/devices/28-00000ab5657e/w1_slave
T1_ADDR=/sys/bus/w1/devices/28-00000ab52c83/w1_slave

HOME_DIR=/usr/local/beerlog
DATA_DIR=/usr/share/beerlog
RUN_DIR=/var/run/beerlog		# Where the run files live
SQL_DB=$DATA_DIR/beerlog.db
OUTRRD=/dev/shm/rrd.in

OUT_DIR=/var/www/beerlog