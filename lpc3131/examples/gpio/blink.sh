#!/bin/sh

# Blink the onboard LED

GPIO=3

cleanup() { # Release the GPIO port
  echo $GPIO > /sys/class/gpio/unexport
  exit
}

# Open the GPIO port
#
echo $GPIO > /sys/class/gpio/export 
echo "high" > /sys/class/gpio/gpio$GPIO/direction 

trap cleanup 2 # call cleanup on Ctrl-C

# Blink forever
while [ "1" = "1" ]; do
  echo 1 > /sys/class/gpio/gpio$GPIO/value
  sleep 1
  echo 0 > /sys/class/gpio/gpio$GPIO/value
  sleep 1
done

cleanup # call the cleanup routine

