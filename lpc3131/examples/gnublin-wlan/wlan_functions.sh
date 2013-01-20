ip_gateway=""
ip_client=""
ssid=""
key=""
pairwise=""
group=""
proto="WPA"
conf=""
manualf=""
autom_con=""
con_cnt="15"
find_cnt=""
cnt_up=0
catch_string=0
#cnt_second=0
shutdownf=""
entry_valid="false"
device="wlan0"
search_string="qqqqqqqqqq"
cnt=1
string="true"
helpstring='This wrapper Script connects your Wlan Module to an Access Point:

Output:
-h			Show this help.
-j			Convert output to json format.

WPA Connection:
-a		    Connect automatically to wlan AP.
-k			Select the WPA key for your AP.
-s			Select the name (SSID) of your AP.
-c 			Specify a config file wich should be used instead of scanning the AP`s.
-w			Specify hardware address, of your access point. This option is important
			for scanning the correct encryption Data of your AP with -a option.
-d 			Shutdown wlan.


IP Receivment:
-i			Assign an ip address to your wlan module. Only possible with static option.
-t 			Specify the ip receive type. dhcp or static.

-g			Select the ip address of your gateway, only take this option with static.

-v 			Be verbose.

Examples:

gnublin-wlan -s FritzBox! -h 45:66:23:23:77 
'

parse_iwlist_res () {
#check_par "$hwaddr" "hwaddr"
check_par "$ssid" "ssid"

cnt=1

echo "Will now scan the specified Network: ${ssid} for encryption method...."
sleep 2

ps aux | grep "\[${device}\]"
if [ "$?" != "0" ]; then
	echo "[${device}] Network device not configured yet."
	echo "Configuring ${device}...."
	ifconfig ${device} up
	sleep 1
fi

iwlist wlan0 scanning essid ${ssid} > tmpfile

if [ "$?" != "0" ]; then
	echo "Error while scanning wlan with command iwlist"
	echo "Exiting now!"
fi

echo "$search_string" >> tmpfile



while [ "$string" != "$search_string" ]; do 

	string=$(sed -n "$cnt p" tmpfile)
	
	# Start of next entry, check if last entry has a valid ssid #
	echo "$string" | grep "Cell"  >/dev/null
	if [ "$?" == "0" ]; then
	cnt_up=0
		echo "# NEXT entry # "
	
		# Check valid entry #
		echo "${ssid_information[1]}" | grep -o "${ssid}" >/dev/null
		if [ "$?" == "0" ]; then
			entry_valid="true"
			cnt_catch=$cnt_up
			#cnt_second=`expr $cnt_second + 1`
			echo "Found $cnt_second valid entry"
			break 
		fi
	fi
	
	ssid_information[$cnt_up]="$string"


	cnt=`expr $cnt + 1`
	cnt_up=`expr $cnt_up + 1`
done


# Check valid entry again --> Last entry! #
#		echo "${ssid_information[1]}" | grep -o "${ssid}" >/dev/null
#		if [ "$?" == "0" ]; then
#			entry_valid="true"
#			cnt_catch=`expr $cnt_up - 1`
#			#cnt_second=`expr $cnt_second + 1`
#			echo "Found $cnt_second valid entry at last position"
#		fi



#if [ $cnt_second -ge 2 ]; then
#	echo "Unfortunatelly more than one AP's with your specified essid were found!!"
#	echo "Have to specify an unique essid of your AP!"
#	echo "you have to set the hardware address of your AP via the -w option!"
#	exit 1
#fi

echo $cnt_catch

while [ $cnt_catch -ge 0 ]; do
	string=${ssid_information[$cnt_catch]}
		
	echo "$string" | grep "Pairwise" | grep TKIP >/dev/null
	if [ "$?" == "0" ]; then
		pairwise="TKIP $pairwise"	
	fi
	echo "$string" | grep "Pairwise" | grep CCMP >/dev/null
	if [ "$?" == "0" ]; then
		pairwise="CCMP $pairwise"	
	fi

	echo "$string" | grep "Group" | grep TKIP >/dev/null
	if [ "$?" == "0" ]; then
		group="TKIP $group"	
	fi
	echo "$string" | grep "Group" | grep CCMP >/dev/null
	if [ "$?" == "0" ]; then
		group="CCMP $group"	
	fi

	cnt_catch=`expr $cnt_catch - 1`
done

echo $cnt_second
echo "Group=$group"
echo "Pairwise=$pairwise"

}




# Startup the wlan connection.
# First ensure that all needed parameters
# for the wpa_supplicant config file are
# valid and start wpa_suppl.
# Afterwards startup the dhclient
wpa_supplicant_start () {
  echo "Startup the wlan connection with dhcp"
  check_par "$ssid" "-s ssid"
  check_par "$key" "-k key"
  check_val "$pairwise" "pairwise"
  check_val "$proto" "proto"
  check_val "$group" "group"	
 
	echo "network={
    ssid=\"${ssid}\"
    key_mgmt=WPA-PSK
    proto=${proto}
    pairwise=${pairwise}
    group=${group}
    psk=\"${key}\"
}" > "/etc/gnublin/wlan/${ssid}"

	wpa_supplicant -i ${device} -D wext -c /etc/gnublin/wlan/${ssid} -d -B
	check_connection
	sleep 3
}

shutdown_system () {
    echo "shutting down the system."

	ifconfig | grep "$device"	> /dev/null
	if [ "$?" == "0" ]; then
		ifdown $device
	fi
		kill $(pidof ${device})
		kill $(pidof dhclient)
		kill $(pidof wpa_supplicant) && exit 0

		echo "wpa_supplicant not running"
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
