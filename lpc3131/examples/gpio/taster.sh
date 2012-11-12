#!/bin/bash

# ask for pin state


GPIO=14

cleanup() { # Release the GPIO port
  echo $GPIO > /sys/class/gpio/unexport
  exit
}

# Open the GPIO port
#
echo $GPIO > /sys/class/gpio/export 
echo "in" > /sys/class/gpio/gpio$GPIO/direction 

trap cleanup 2 # call cleanup on Ctrl-C


#ask for gpio state

state=$(cat /sys/class/gpio/gpio$GPIO/value)

if [ "$state" = "1" ]
then
    echo "Taster auf HIGH"
else
    echo "Taster auf LOW"
fi

cleanup
