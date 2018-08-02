#! /bin/bash

# Configure temp sensors

THIS_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"		# Where this script lives

. $THIS_DIR/config.sh

echo "Temperature Sensor selector."
echo "Unfortunately it's impossible to determine which sensor is which from the outside."
echo "The only way is to try it and see. After running this once and selecting randomly"
echo "hold one in your hand for a few seconds and look at the graphs to see if you guessed"
echo "correctly. If not just run $0 again."
echo

beer=0
ambient=0
menu=0

count=1
while read line; do
		if [[ $T1_ADDR == $line ]]
		then
			beer=$count
		fi
		if [[ $T2_ADDR == $line ]]
		then
			ambient=$count
		fi
		count=`expr $count + 1`
done < /sys/bus/w1/devices/w1_bus_master1/w1_master_slaves

origBeer=$beer
origAmbient=$ambient

while true; do
	echo "The following temperature sensors were found:"
	count=1
	addrs=("dummy")
	while read line; do
		addrs[$count]=$line
		if [[ $beer -eq $count ]]
		then
			star="B"
		else
			if [[ $ambient -eq $count ]]
			then
				star="A"
			else
				star=" "
			fi
		fi
		
		echo "$star $count) $line"
		count=`expr $count + 1`
	done < /sys/bus/w1/devices/w1_bus_master1/w1_master_slaves
	
	case $menu in
		0)
			read -p "Please select one for measuring the Beer temperature: " beerin
			case $beerin in
				[0-9]) beer=$beerin; menu=1 ;;
				[Qq])	break;;
				*) menu=0 ;;
			esac
			
		;;
		1)
			read -p "Please select one for measuring the Ambient temperature: " ambientin
			case $beerin in
				[0-9]) ambient=$ambientin; menu=2 ;;
				[Qq])	break;;
				*) menu=1 ;;
			esac
		;;
		2)
			read -p "Is this OK? Press Y to save, N to try again, Q to Quit: " okin
			case $okin in
				[Yy])
					sed -i s/^T1_ADDR=.*$/T1_ADDR=${addrs[$beer]}/g $THIS_DIR/config.sh
					sed -i s/^T2_ADDR=.*$/T2_ADDR=${addrs[$ambient]}/g $THIS_DIR/config.sh
					echo "Saved. Restarting service." ; systemctl restart beerlog; break ;;
				[Qq])	echo "Not Saved" ; break ;;
				[Nn]) beer=$origBeer ; ambient=$origAmbient; menu=0 ;;
				*) menu=2 ;;
			esac
		;;
		*)
			menu=0
		;;
	esac
	
done