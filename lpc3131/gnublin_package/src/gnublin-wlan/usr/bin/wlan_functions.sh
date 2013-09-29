ip_gateway=""
ip_client=""
ssid=""
key=""
psk=""
proto="WPA2"
conf=""
manualf=""
autom_con=""
con_cnt="25"
shutdownf=""
entry_valid="false"
device="wlan0"
cnt=""
helpstring='This wrapper Script connects your Wlan Module to an Access Point:

gnublin-wlan [-h/-e] [wpa option: -k/-s || -c] [Ip: -t static -i/-g || -t dhcp]

Output:
-h Show this help.
-e Show lots of wpa_supplicant.conf examples 

WPA Connection:
-k Select the WPA key for your AP.
-s Select the name (SSID) of your AP.
-c Specify a config file wich wpa_supplicant can use to conect to an AP.
   With this option selected, you dont need to specify the -k and -s option.

-d Shutdown wlan.

IP Receivment:
-t Specify the ip receive type. dhcp or static.
-i Assign an ip address to your wlan module. Only possible with static option.
-g Select the ip address of your gateway, only take this option with static.

-v Be verbose.

Device:
-w Specify the wlan device

Examples:
*********

gnublin-wlan -k wpakey08612 -s AcesspointDSL -t static -i 192.168.1.12 -g 192.168.1.1 

gnublin-wlan -k wpakey08612 -s AcesspointDSL -t dhcp

gnublin-wlan -c /path_to_own_wpa_supplicant.config -t static -i 192.168.1.12 -g 192.168.1.1

gnublin-wlan -c /path_to_own_wpa_supplicant.config -t dhcp
'






# Startup the wlan connection.
# First ensure that all needed parameters
# for the wpa_supplicant config file are
# valid and start wpa_suppl.
# Afterwards startup the dhclient
wpa_supplicant_autostart () {
  echo "Startup the wlan connection with dhcp"
  check_par "$key"	"-k key"
  check_par "$ssid" "-s ssid"
  check_val "$proto" "proto"
 
  ###-----> GENERATE PSK KEY WITH WPA_PASSPHRASSE!!! ######

  psk=$( wpa_passphrase "${ssid}" "${key}" | tail -n 2 | grep "psk" )
  check_val "$psk" "pre-shared key"

  echo "network={
        ssid=\"${ssid}\"
        key_mgmt=WPA-PSK
        proto=WPA2
        pairwise=CCMP TKIP
        group=CCMP TKIP
        ${psk}
  }" > "/etc/wpa_supplicant/wpa_supplicant.conf"



	wpa_supplicant -i ${device} -D wext -c /etc/wpa_supplicant/wpa_supplicant.conf -d -B
	sleep 3
}

shutdown_system () {
	check_par "$device" "device (e.g wlan0)"
    echo "shutting down the system."
	ifconfig | grep "$device"	> /dev/null
	if [ "$?" == "0" ]; then
		ifconfig $device down
	fi
		kill $(pidof ${device})

		kill $(pidof dhclient)
		kill $(pidof wpa_supplicant) 

		echo "Wlan connection was shutdowned."
		echo "If you wan to connect your wlan stick again, then"
		echo "first disconnect you wlan stick, before starting this"
		echo "script again!"
		exit 1
}

check_connection () {
  res=""
  cnt=""
  
  echo "Checking wlan connection."
  while [ "$res" != "true" ]; do

	   iwconfig ${device} | grep "ESSID" | grep "${ssid}" > /dev/null	
	  if [ "$?" == "0" ]; then
	  		echo "Wlan connection established"
			return 0
	  fi

	  cnt=`expr $cnt + 1`
	  sleep 1
	  echo "."

	  if [ "$cnt" -ge "$con_cnt" ]; then
	  		echo "Maximal connection tries '$con_cnt' reached."
			echo "Exiting now!"
			return 1
	  fi
	  
  done
  return 0
}

startup_device () {
	check_par "$device" "device (e.g. wlan0)"
	ps aux | grep "\[${device}\]" >/dev/null
	if [ "$?" == "0" ]; then
			echo "Device already configured"
	else
			echo "Setting up ${device}..."
			sleep 3
			ifconfig ${device} up && echo "Device ${device} successfully set."
			sleep 1
	fi

}


check_par () {
	if [ -z "$1" ]; then
		echo "Parameter '$2' empty."
		echo "Exiting now!"
		exit 1
	fi

}

check_val () {
	if [ -z "$1" ]; then
		echo "Value '$2' empty."
		echo "Exiting now!"
		exit 1
	fi

}

cleanup_trap() {
	shutdown_system 
}

