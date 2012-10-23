#!/bin/bash
# Author: Benedikt Niedermayr (niedermayr@embedded-projects.net)
#
# Script for building the kernel. Some Variables needed by this file
# are definded in the general settings.sh file which is called one step
# before this.

echo " " >> $logfile_build
echo " " >> $logfile_build
echo "#############################################" >> $logfile_build
echo "#         3rd Stage: Build kernel           #" >> $logfile_build 
echo "#############################################" >> $logfile_build	


# This is the root directory for building the rootfs
cur_path=$debian_build_path



##################################
# Install and compile the Kernel #
##################################


# Create directories if not aviable
if [ ! -d "$cur_path/debian_process" ]
then
	mkdir $cur_path/debian_process || exit 0
	echo "$build_time Folder $cur_path/debian_process created correctly " >> $logfile_build
	
fi

if [ ! -d "$root_path/Downloads" ]
then
	mkdir $root_path/Downloads || exit 0
	echo "$build_time Folder $root_path/Downloads created correctly " >> $logfile_build
fi

# Start installing and compiling the Kernel
if [ ! -e "$cur_path/debian_process/$std_kernel_pkg_name" ]
then
       
	if [ ! -d "$root_path/kernel/$kernel_name" ]	
	then
		cd $root_path/Downloads	
		#Get kernel from repository 
		git clone https://code.google.com/p/gnublin-develop-kernel || exit 0
		echo "$build_time Repository cloned correctly " >> $logfile_build

		#Move Kernel to kernel directory
		mv $root_path/Downloads/$git_name_kernel/$kernel_name $root_path/kernel/$kernel_name || exit 0
		rm -r $root_path/Downloads/$git_name_kernel
		echo "$build_time Kernel from $root_path/Downloads/$git_name_kernel/$kernel_name moved correctly to $root_path/kernel/$kernel_name" >> $logfile_build
	fi
	
	#Copy std. kernel to installation folder
	#cp -rp $root_path/kernel/$kernel_name $cur_path/debian_process

	#Change to kernel directory
	cd $root_path/kernel/$kernel_name
	#cp $cur_path/debian_process/$kernel_name/.config $cur_path/debian_process/config_backup
	

	#gnublin kernel build process	
	make menuconfig || exit 0
	echo "$build_time Make menuconfig called correctly" >> $logfile_build	
	make zImage || exit 0
	echo "$build_time Kernel compiled successfully" >> $logfile_build	
	make modules || exit 0
	echo "$build_time Kernel compiled successfully" >> $logfile_build
	make modules_install INSTALL_MOD_PATH=$root_path/kernel/$kernel_name || exit 0
    echo "$build_time Kernel installed correctly" >> $logfile_build   
	
	cp $root_path/kernel/$kernel_name/arch/arm/boot/zImage $root_path/kernel/$kernel_name/zImage || exit 0
	# Create the tar.gz file for debian build
	tar -zc -f $cur_path/debian_process/$std_kernel_pkg_name * || exit 0
	echo "$build_time Kernel installed correctly" >> $logfile_build
	cd $cur_path
fi
