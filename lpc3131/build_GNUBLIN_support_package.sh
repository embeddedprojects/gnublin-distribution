#!/bin/bash
# Author: Benedikt Niedermayr (niedermayr@embedded-projects.net)
# Board support package building script


# Parameters 
export distro_version="max"    								#paste "-min" if you want to build a minimal version of debian.
#export tmp=$(cat /etc/lsb-release | grep DISTRIB_RELEASE)    #check for current OS Version
#export host_os_version=${tmp:16:2}
export filesystem_vers="ext3"


#############
# Variables #
#############
export build_time="$(date '+%D %H:%M:%S') ->"
export root_path=$(pwd)
export toolchain_path=$root_path/toolchain
export cross_compiler_path=$toolchain_path/armv5te/sysroots/i686-oesdk-linux/usr/bin/armv5te-linux-gnueabi
export kernel_version=2.6.33
export kernel_name=linux-$kernel_version-lpc313x
export kernel_path=$root_path/kernel/$kernel_name
export debian_build_path=$root_path/rootfs/debian/debian_install
export debian_installed_files_path=$root_path/rootfs/debian/debian_install/debian_process
export bootloader_install_dir=$root_path/bootloader/apex/1.6.8
export logfile_build=$root_path/install.log


# Including settings through an additional file
source $root_path/rootfs/debian/debian_install/general_settings.sh	"$distro_version"






# Get sure that root starts this script #
user=$(whoami)

if [ "$user" != "root" ]
then
	echo "You have to be root in order to start the build process!"
	exit 0
fi


# Install libncurses for using make menuconfig #
dpkg -l | grep libncurses5-dev >/dev/null
if [ "$?" != 0 ]
then
	apt-get install libncurses5-dev
else
	echo "You have already installed libncurses5-dev" >>$logfile_build
fi


##########################################################
# Only cleaning the whole board-support-package and exit #
##########################################################
if [ "$1" = "clean" ]
then
	rm -rf $root_path/kernel/$kernel_name 
	rm -rf $debian_installed_files_path 
	rm -rf $bootloader_install_dir/apex-1.6.8 
	rm -rf $toolchain_path/armv5te 
	rm -rf $root_path/kernel/set.sh 
	rm -rf $root_path/.stamp* 
	rm -rf $root_path/tools/gnublin-installer/apex.bin 
	rm -rf $root_path/tools/gnublin-installer/zImage	
	rm -rf $root_path/tools/gnublin-installer/${output_filename}.tar.${tar_format} 
	echo "Successfully cleaned!"
	# Uninstall also the toolchain	
	if [ "$2" = "all" ]
	then	
		#echo "This step will delete your toolchain installed at /opt/eldk-*"
		#echo "continue?(y/n)"
		#read desicion
		
		#if [ "$desicion" = "y" ]
		#then		
		#rm -r /opt/eldk-*
		rm -rf $root_path/Downloads/*
		rm -rf $root_path/output
		#fi
	fi
	exit 1
fi


 


# Building Stages
# Now the complete board support package will be built.
rm -r $logfile_build
touch $logfile_build
chmod 0777 $logfile_build

#############################################
# 1st Stage:Build toolchain                 #
#############################################
if [ ! -e $root_path/.stamp_toolchain ]
then
	source $root_path/toolchain/build_toolchain.sh || exit 0
	touch $root_path/.stamp_toolchain
fi




# Always set PATH environment but first after building toolchain#
source $root_path/kernel/set.sh



#############################################
# 2nd Stage:Build bootlader                 #
#############################################
if [ ! -e $root_path/.stamp_bootloader ]
then
	
	source $root_path/bootloader/apex/1.6.8/build_bootloader.sh
	touch $root_path/.stamp_bootloader
fi


######################################
# 3rd Stage: Kernel build		     # 
######################################
if [ ! -e $root_path/.stamp_kernel ]
then
		 
	source $root_path/kernel/build_kernel.sh

	# Move set.sh file into the kernel
	cp $root_path/kernel/set.sh $kernel_path
	touch $root_path/.stamp_kernel
fi


######################################
# 4rd Stage: rootfs build		     # 
######################################
if [ ! -e $root_path/.stamp_rootfs ]
then
	source $debian_build_path/build_debian_system.sh
	touch $root_path/.stamp_rootfs
fi

######################################
# 5th Stage: Rootfs completion       # 
######################################
if [ ! -e $root_path/.stamp_rootfs_post ]
then
	
	# Correct files (interfaces, passwd)
	
		
	# Copy created .deb packages into rootfs

		

	compress_debian_rootfs || exit 0 # compress the resulting rootfs
	
	# Copy the most important files #
	# It's not necessary but better for the user #
	mkdir $root_path/output	
	cp $bootloader_install_dir/apex-1.6.8/src/arch-arm/rom/apex.bin $root_path/output
	cp $kernel_path/arch/arm/boot/zImage $root_path/output
	cp ${output_dir}/${output_filename}.tar.${tar_format} $root_path/output
	

	# Create stamp #
	touch $root_path/.stamp_rootfs_post
fi




## Start Gnublin Installer ##
#cd $root_path/tools/gnublin-installer/
#if [ $host_os_version -ge 12 ]
#then
#	$root_path/tools/gnublin-installer/gnublin-installer-neu || $root_path/tools/gnublin-installer/gnublin-installer-alt
	
#else
#	$root_path/tools/gnublin-installer/gnublin-installer-alt
	
#fi

exit 1








