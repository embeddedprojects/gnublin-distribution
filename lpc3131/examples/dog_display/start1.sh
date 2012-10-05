#!/bin/sh

# Start script for preparing connected gpio pin!


gpio_pin=11
export GPIO_PIN=gpio$gpio_pin

#Is gpio aviable?
if [ ! -d "/sys/class/gpio/gpio$gpio_pin"  ]
then
	echo "$gpio_pin" > /sys/class/gpio/export
	echo out > "/sys/class/gpio/gpio$gpio_pin/direction"
	echo 0 > "/sys/class/gpio/gpio$gpio_pin/value"
	echo "GPIO PIN $GPIO_PIN configured"
fi


