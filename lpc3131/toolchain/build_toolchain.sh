#!/bin/bash 

# This script installs the eldk-toolchain automaticly.

#Check if there is a previuos installation of eldk on your computer
eldk_version=$(ls /opt/ | grep eldk)

echo " " >> $logfile_build
echo " " >> $logfile_build
echo "#############################################" >> $logfile_build
echo "#         First Stage: Build toolchain      #" >> $logfile_build 
echo "#############################################" >> $logfile_build	

#check if the output of ls | grep is not empty
if [ ${#eldk_version} -lt  3 ]
then

	eldk_version="NotFound"
	
fi


if [ -d "/opt/$eldk_version" ]
then

	#Create only a softlink to your installation
	ln -s /opt/$eldk_version/armv5te $toolchain_path/armv5te 2>>$logfile_build || exit 0
	

elif [ -d "$toolchain_path/armv5te" ]
then
	#echo if you have already installed the toolchain in your toolchain directory
	echo "Toolchain already installed!"
	echo "$build_time Toolchain is already installed on your PC." >> $logfile_build 

else
	#Install the toolchain

	if [ ! -d "$root_path/Downloads" ]
	then
    	mkdir $root_path/Downloads 
	fi

    cd $root_path/Downloads 

	#Downloading the ELDK 5.0 iso if you dont have it
	if [ ! -f "$root_path/Downloads/armv5te-qte-5.0.iso" ]
	then
    	wget ftp://ftp.denx.de/pub/eldk/5.0/iso/armv5te-qte-5.0.iso || exit 0 
	fi

   	cd /media 
    mkdir eldk-iso 

	# Mount the iso file
    mount -o loop $root_path/Downloads/armv5te-qte-5.0.iso /media/eldk-iso 2>>$logfile_build || exit 0
	echo "$build_time Folder /media/eldk-iso mounted successfully." >> $logfile_build
    cd /media/eldk-iso
 
	#Start the installation
    ./install.sh -s -i qte armv5te 2>>$logfile_build

	if [ ! -h "$toolchain_path/armv5te" ]
	then
		eldk_version=$(ls /opt/ | grep eldk)
		#Create a softlink after installation
    	ln -s /opt/$eldk_version/armv5te $toolchain_path/armv5te 2>>$logfile_build
	fi
	#Remove temp directories of the installation
    cd $root_path 
    umount /media/eldk-iso 2>>$logfile_build || exit 0
	echo "$build_time Folder /media/eldk-iso unmounted successfully." >> $logfile_build
    rmdir /media/eldk-iso  


fi



#Creating the source-path-script, also known as set.sh

if [ ! -e "$root_path/kernel/set.sh" ]
then
	#Create the set.sh file
	echo "$build_time Create a new set.sh file" >> $logfile_build	
	
	touch $root_path/kernel/set.sh
	chmod +x $root_path/kernel/set.sh


	echo     "	 P1=$toolchain_path/armv5te/sysroots/i686-oesdk-linux/usr/bin/armv5te-linux-gnueabi/
	 P2=$toolchain_path/armv5te/sysroots/i686-oesdk-linux/bin/armv5te-linux-gnueabi/

	 export ARCH=arm 
	 export CROSS_COMPILE=arm-linux-gnueabi-
	 export PATH=\$P1:\$P2:\$PATH " > $root_path/kernel/set.sh

fi
