#! /bin/bash

# emulate leds for remote testing
# this just outputs one screen. Watch the output with `watch --color -n0.1 /usr/local/beerlog/led-emu.sh`

. /usr/local/beerlog/config.sh

RED='\033[0;31m'
ORG='\033[0;33m'
GRE='\033[0;32m'
NC='\033[0m' # No Color

RED_VAL=`gpio -g read $LED_R`
ORG_VAL=`gpio -g read $LED_O`
GRE_VAL=`gpio -g read $LED_G`

LED_RS=" "
LED_OS=" "
LED_GS=" "

if [ $RED_VAL == "1" ]
then
	LED_RS=$RED"*"$NC
fi
if [ $ORG_VAL == "1" ]
then
	LED_OS=$ORG"*"$NC
fi
if [ $GRE_VAL == "1" ]
then
	LED_GS=$GRE"*"$NC
fi

echo
echo -e "GREEN       ($LED_GS)" 
echo
echo -e "ORANGE      ($LED_OS)"
echo
echo -e "RED         ($LED_RS)"
echo

#last reading: 1532952082,0,20.12,20.06
IFS=',' read TS BLC T1 T2 <<< tail -1 $OUTLOG

AGO=$(echo `date +%s` - $TS | bc)
echo "Last Reading $AGO seconds ago"
echo "Bloop Count: $BLC"
echo "Beer Temp: $T1"
echo "Beer Temp: $T2"
echo