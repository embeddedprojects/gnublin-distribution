#!/bin/bash
# Author: Benedikt Niedermayr (niedermayr@embedded-projects.net)
# Board support package building script


# Parameters #####################
export distro_version="max"     # paste "-min" if you want to build a minimal version of debian.
export filesystem_vers="ext3"   # choose the final type of your filesystem setting
export host_os="Ubuntu"         # Debian or Ubuntu (YOU NEED TO EDIT THIS!)
export eldk_name="eldk-5.0"     # not important for now
export start_mkmenuconfig="no"  # start make menuconfig (Bootloader and kernel) Say "no" if you dont want to start make menuconfig
##################################




###################
# Other Variables #
###################
export build_time="$(date '+%D %H:%M') ->"
export root_path=$(pwd)
export user=$(whoami)
export toolchain_path=$root_path/toolchain
export cross_compiler_path=$toolchain_path/armv5te/sysroots/i686-oesdk-linux/usr/bin/armv5te-linux-gnueabi
export kernel_version=2.6.33
export kernel_name=linux-$kernel_version-lpc313x
export kernel_path=$root_path/kernel/$kernel_name
export debian_build_path=$root_path/rootfs/debian/debian_install
export debian_installed_files_path=$root_path/rootfs/debian/debian_install/debian_process
export bootloader_install_dir=$root_path/bootloader/apex/1.6.8
export logfile_build=$root_path/install.log


# Include settings through an additional file
source $root_path/rootfs/debian/debian_install/general_settings.sh	"$distro_version"



##########################################################
# Only cleaning the whole board-support-package and exit #
##########################################################

if [ "$1" = "clean" ]
then
	 
	sudo -s -E rm -rf $debian_installed_files_path 
	sudo -s -E rm -rf $bootloader_install_dir/apex-1.6.8 
	sudo -s -E rm -rf $toolchain_path/armv5te 
	sudo -s -E rm -rf $root_path/kernel/set.sh 
	sudo -s -E rm -rf $root_path/.stamp* 
	sudo -s -E rm -rf $root_path/tools/gnublin-installer/apex.bin 
	sudo -s -E rm -rf $root_path/tools/gnublin-installer/zImage	
	sudo -s -E rm -rf $root_path/tools/gnublin-installer/${output_filename}.tar.${tar_format} 
	sudo -s -E rm -rf $root_path/gnublin_package/deb/*
	sudo -s -E rm -rf $logfile_build
	
	
		
	if [ "$2" = "all" ]
	then
		sudo -s -E rm -rf $root_path/kernel/$kernel_name
		sudo -s -E rm -rf $root_path/Downloads/*
		sudo -s -E rm -rf $root_path/output
	fi
	echo "Successfully cleaned!"
	exit 1
	
fi








# Building Stages
# Now the complete board support package will be built.
rm -r $logfile_build
touch $logfile_build
chown $user:$user $logfile_build

# Install utils for using make menuconfig #
sudo -s -E apt-get install git make libncurses5-dev g++ dpkg-dev || exit 0

echo "$build_time All necessary packages installed." >> $logfile_build

# Create output folder #
if [ ! -d "$root_path/output" ]
then	
	mkdir $root_path/output	
fi	


#############################################
# 1st Stage:Build toolchain                 #
#############################################
if [ ! -e $root_path/.stamp_toolchain ]
then
	 	
	sudo -s -E source $root_path/toolchain/build_toolchain.sh
	if [ "$?" = "0" ]
	then
		echo "$build_time An error ocured during build_toolchain." >> $logfile_build 
		echo "ERROR while building toolchain"
		exit 0
	fi
	touch $root_path/.stamp_toolchain
fi




# Always set PATH environment but first after building toolchain#
source $root_path/kernel/set.sh



#############################################
# 2nd Stage:Build bootlader                 #
#############################################
if [ ! -e $root_path/.stamp_bootloader ]
then
	
	source $root_path/bootloader/apex/1.6.8/build_bootloader.sh || exit 0
	touch $root_path/.stamp_bootloader
fi


######################################
# 3rd Stage: Kernel build		     # 
######################################
if [ ! -e $root_path/.stamp_kernel ]
then
		 
	source $root_path/kernel/build_kernel.sh || exit 0

	# Move set.sh file into the kernel
	cp $root_path/kernel/set.sh $kernel_path
	touch $root_path/.stamp_kernel
fi


######################################
# 4rd Stage: rootfs build		     # 
######################################
if [ ! -e $root_path/.stamp_rootfs ]
then
	sudo -s -E source $debian_build_path/build_debian_system.sh
	touch $root_path/.stamp_rootfs
fi

######################################
# 5th Stage: Rootfs completion       # 
######################################
if [ ! -e $root_path/.stamp_rootfs_post ]
then
	
	# Correct files (interfaces, passwd)
	
		
	
	# build .deb packages #
	if [ ! -d $root_path/gnublin_package/deb ]
	then
		mkdir $root_path/gnublin_package/deb
	fi
	cd $root_path/gnublin_package/src/
	$root_path/gnublin_package/src/mkdeb_package
    

	sudo -s -E $debian_build_path/compress_debian_rootfs.sh || exit 0 # compress the resulting rootfs
	
	# Copy the most important files #
	# It's not necessary but better for the user to find all final created files at the same place #
	
	cp $bootloader_install_dir/apex-1.6.8/src/arch-arm/rom/apex.bin $root_path/output
	
	cp ${output_dir}/${output_filename}.tar.${tar_format} $root_path/output
	
	 

	# Create stamp #
	touch $root_path/.stamp_rootfs_post
fi


exit 1








