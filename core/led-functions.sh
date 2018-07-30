#! /bin/bash

#Lib for gpio LED functions

# Put the LED on
led_on() {
	PIN=$1;
	gpio -g write $PIN 1
}

# Put the LED off
led_off() {
	PIN=$1;
	gpio -g write $PIN 0
}

# Blink the LED until further notice (ie turn it off)
# gpio blink is blocking don't use
#led_blink() {
#	PIN=$1;
#	gpio -g blink $PIN &
#}

# Do a little LED dance
led_hello() {
	led_off $LED_G
	led_off $LED_O
	led_off $LED_R
	led_on $LED_G
	sleep 0.2
	led_on $LED_O
	led_off $LED_G
	sleep 0.2
	led_on $LED_R
	led_off $LED_O
	sleep 0.5
	led_on $LED_O
	led_off $LED_R
	sleep 0.2
	led_on $LED_G
	led_off $LED_O
}

# Error Alert led

led_error() {
	led_on $LED_R
}

# Flash the LED once
led_flash() {
	PIN=$1
	led_on $PIN
	led_off $PIN
}

# UnFlash the LED once
led_unflash() {
	PIN=$1
	led_off $PIN
	sleep 0.1
	led_on $PIN
}

# flash but the opposite of the current status
# this is bound to be slower than flash because it involves a read so use that if you know it's status
led_toggle_flash() {
	PIN=$1
	val=$(gpio -g read $PIN)
	if [[ $val -eq 0 ]]
	then
		led_flash $PIN
	else
		led_unflash $PIN
	fi
}