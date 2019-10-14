#! /bin/bash

# Config file

IN=22		# Pin to monitor the interrupt on
LED_R=12	# Pin that drives the Red LED
LED_G=16	# Pin that drives the Green LED
LED_O=13	# Pin that drives the Orange LED
OW=4		#1-wire pin. We don't reference it anywhere but just a reminder not to use it

RATE_LIMIT=0.5

# The two temp sensor 1-wire addresses. T1 is the beer, T2 is the ambient
T2_ADDR=28-00000ab657b7
T1_ADDR=28-00000ab52c83

HOME_DIR=/usr/local/beerlog
DATA_DIR=/usr/share/beerlog
RUN_DIR=/var/run/beerlog		# Where the run files live
SQL_DB=$DATA_DIR/db/beerlog.db
OUTRRD=/dev/shm/rrd.in

OUT_DIR=/var/www/beerlog