#!/bin/sh
#
# This is a simple script which initalizes the required gpio pin
# and writes down a few strings.
#
#
#
##############################################

gpio_pin=11
export GPIO_PIN=gpio$gpio_pin

#Is gpio aviable?
if [ ! -d "/sys/class/gpio/gpio$gpio_pin"  ]
then
        echo "$gpio_pin" > /sys/class/gpio/export
        echo out > "/sys/class/gpio/gpio$gpio_pin/direction"
        echo 0 > "/sys/class/gpio/gpio$gpio_pin/value"
        echo "if applied"
fi

#Starting the "embedded projects" app

./display_test -w "embedded" -n
./display_test -w "projects" -o 194  
./display_test -s "+5"
./display_test -s "-5"
./display_test -n
./display_test -w "First Display App in use" -o 128
./display_test -s -10
./display_test -n -s 10








