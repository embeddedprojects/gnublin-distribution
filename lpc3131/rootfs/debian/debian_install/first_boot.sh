
#! /bin/sh



if [ ! -f /opt/.success  ]
then

echo "This is your first boot. Some applications will now be configured. This may take some time!"
echo "After this process the system is going down for reboot!..."

touch /opt/tmp.txt

update-rc.d apache2 remove
echo "Apache removed successfull." > tmp.txt

update-rc.d avahi-daemon remove
echo "Avahi removed successfull." >> tmp.txt

update-rc.d gpsd remove
echo "GPSD removed successfull." >> tmp.txt

update-rc.d lighttpd remove
echo "Lighttp removed successfull." >> tmp.txt

update-rc.d nscd remove
echo "NSCD removed successfull." >> tmp.txt

update-rc.d vsftpd remove
echo "VSFTP removed successfull." >> tmp.txt

update-rc.d nfs-common remove
echo "NFS removed successfull." >> tmp.txt

update-rc.d rsync remove
echo "RSYNC removed successfull." >> tmp.txt


mv /opt/tmp.txt /opt/.success

echo  "
#!/bin/sh -e
#
# rc.local
#
# This script is executed at the end of each multiuser runlevel.
# Make sure that the script will exit 0 on success or any other
# value on error.
#
# In order to enable or disable this script just change the execution
# bits.
#
# By default this script does nothing.

if [ -e /ramzswap_setup.sh ]
then
        /ramzswap_setup.sh 2>/ramzswap_setup_log.txt && rm /ramzswap_setup.sh
fi
/setup.sh 2>/setup_log.txt && rm /setup.sh
swapon /swapfile
exit 0" > /etc/rc.local

echo "Everything done successfull! Reboot now..."
reboot

else

echo "Boot process speed already fixed."

fi 
