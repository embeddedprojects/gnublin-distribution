#!/bin/bash 

# This script installs the eldk-toolchain automaticly.
#Check if there is a previuos installation of eldk on your computer
export eldk_version=$(ls /opt/ | grep --max-count=1 eldk) 
logfile_build=$root_path/install.log
echo " " >> $logfile_build
echo " " >> $logfile_build
echo "#############################################" >> $logfile_build
echo "#         First Stage: Build toolchain      #" >> $logfile_build 
echo "#############################################" >> $logfile_build	

#check if the output of ls | grep is not empty
if [ ${#eldk_version} -lt  3 ]
then

	export eldk_version="NotFound"
	
fi


if [ -d "/opt/$eldk_version" ]
then

	#Create only a softlink to your installation
	ln -s /opt/$eldk_version/armv5te $toolchain_path/armv5te 2>>$logfile_build
	echo "Toolchain already installed!"
	echo "$build_time Toolchain is already installed on your PC." >> $logfile_build
	chown $user:$user /opt/$eldk_version/
	chown $user:$user $toolchain_path/armv5te

elif [ -d "$toolchain_path/armv5te" ]
then
	#echo if you have already installed the toolchain in your toolchain directory
	echo "Toolchain already installed!"
	echo "$build_time Toolchain is already installed on your PC." >> $logfile_build 
	exit 1	

else
	#Install the toolchain

	if [ ! -d "$root_path/Downloads" ]
	then
    	mkdir $root_path/Downloads || exit 0
		chown $user:$user $root_path/Downloads
		mkdir /opt/$eldk_name || exit 0
		chown $user:$user /opt/$eldk_name 
		echo "$build_time Folder $root_path/Downloads created successfully." >> $logfile_build 
		echo "$build_time Folder /opt/$eldk_name created successfully." >> $logfile_build 
	fi

    cd $root_path/Downloads 
	
	
	
	#Downloading the ELDK 5.0 iso if you dont have it
	if [ ! -f "$root_path/Downloads/armv5te-qte-5.0.iso" ]
	then
    	wget ftp://ftp.denx.de/pub/eldk/5.0/iso/armv5te-qte-5.0.iso || exit 0
		echo "$build_time Toolchain downloaded successfully." >> $logfile_build  
	fi
	

	# Calculate Checksum # 
	wget ftp://ftp.denx.de/pub/eldk/5.0/iso/iso.sha256 || exit 0
	sha256sum armv5te-qte-5.0.iso > $root_path/Downloads/armv5te-qte-5.0.sha256 || exit 0
	export diff_tmp=$(diff $root_path/Downloads/armv5te-qte-5.0.sha256 $root_path/Downloads/iso.sha256)	
	
	#echo "testvar===>$diff_tmp"
	if [ -n "$diff_tmp"  ]
	then 
		 echo "$build_time Checksum error in armv5te-qte-5.0.iso." >> $logfile_build	
		 rm -rf $root_path/Downloads/armv5te-qte-5.0.sha256
		 exit 0
	fi
		 echo "$build_time Checksum of armv5te-qte-5.0.iso is correct." >> $logfile_build
		 echo "$build_time ==>File downloaded correctly" >> $logfile_build
		 rm $root_path/Downloads/armv5te-qte-5.0.sha256

	
	cd /media 
    mkdir eldk-iso
	

	# Mount the iso file
    mount -o loop $root_path/Downloads/armv5te-qte-5.0.iso /media/eldk-iso || exit 0
	echo "$build_time Folder /media/eldk-iso mounted successfully." >> $logfile_build
	chown $user:$user /media/eldk-iso
    cd /media/eldk-iso
 	
	#Start the installation
    ./install.sh -s -i qte armv5te 2>>$logfile_build

	if [ ! -h "$toolchain_path/armv5te" ]
	then
		eldk_version=$(ls /opt/ | grep eldk)
		#Create a softlink after installation
    	ln -s /opt/$eldk_version/armv5te $toolchain_path/armv5te 2>>$logfile_build
		chown $user:$user $toolchain_path/armv5te
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

# Creating set.sh for eldk v5.2.1

  if [ $eldk_version = "eldk-5.2.1" ]
  then
	#Create the set.sh file
	echo "$build_time Create a new set.sh file" >> $logfile_build	
	
	touch $root_path/kernel/set.sh
	chmod 0777 $root_path/kernel/set.sh


	echo     "	 P1=$toolchain_path/armv5te/sysroots/i686-eldk-linux/usr/bin/armv5te-linux-gnueabi/
	 P2=$toolchain_path/armv5te/sysroots/i686-eldk-linux/bin/armv5te-linux-gnueabi/

	 export ARCH=arm 
	 export CROSS_COMPILE=arm-linux-gnueabi-
	 export PATH=\$P1:\$P2:\$PATH " > $root_path/kernel/set.sh

# Creating set.sh for older version of eldk
   else
	#Create the set.sh file
	echo "$build_time Create a new set.sh file" >> $logfile_build	
	
	touch $root_path/kernel/set.sh
	chmod 0777 $root_path/kernel/set.sh


	echo     "	 P1=$toolchain_path/armv5te/sysroots/i686-oesdk-linux/usr/bin/armv5te-linux-gnueabi/
	 P2=$toolchain_path/armv5te/sysroots/i686-oesdk-linux/bin/armv5te-linux-gnueabi/

	 export ARCH=arm 
	 export CROSS_COMPILE=arm-linux-gnueabi-
	 export PATH=\$P1:\$P2:\$PATH " > $root_path/kernel/set.sh

fi
fi


exit 1 # everything ok
