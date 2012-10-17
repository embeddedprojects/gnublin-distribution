#!/bin/bash 

# This script installs the eldk-toolchain automaticly.

#Check if there is a previuos installation of eldk on your computer
eldk_version=$(ls /opt/ | grep eldk)


#check if the output of ls | grep is not empty
if [ ${#eldk_version} -lt  3 ]
then

eldk_version="NotFound"

fi


if [ -d "/opt/$eldk_version" ]
then

#Create only a softlink to your installation
ln -s /opt/$eldk_version/armv5te $toolchain_path/armv5te 


elif [ -d "$toolchain_path/armv5te" ]
then
#echo if you have already installed the toolchain in your toolchain directory
echo "Toolchain already installed!"


else
#Install the toolchain
    cd /tmp 

#Downloading the ELDK 5.0 iso
    wget ftp://ftp.denx.de/pub/eldk/5.0/iso/armv5te-qte-5.0.iso 
    cd /media 
    mkdir eldk-iso 

# Mount the iso file
    mount -o loop /tmp/armv5te-qte-5.0.iso /media/eldk-iso 
    cd /media/eldk-iso
 
#Start the installation
    ./install.sh -d $toolchain_path -s -i qte armv5te 


#Remove temp directories of the installation
    cd .. 
    umount /media/eldk-iso 
    rmdir /media/eldk-iso  
fi



#Creating the source-path-script, also known as set.sh

if [ ! -e "$root_path/kernel/set.sh" ]

then
#Create the set.sh file

touch $root_path/kernel/set.sh
chmod +x $root_path/kernel/set.sh


echo     "	 P1=$toolchain_path/armv5te/sysroots/i686-oesdk-linux/usr/bin/armv5te-linux-gnueabi/
	 P2=$toolchain_path/armv5te/sysroots/i686-oesdk-linux/bin/armv5te-linux-gnueabi/

	 export ARCH=arm 
	 export CROSS_COMPILE=arm-linux-gnueabi-
	 export PATH=\$P1:\$P2:\$PATH " > $root_path/kernel/set.sh

fi
