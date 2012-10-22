#!/bin/bash
# Author: Benedikt Niedermayr (niedermayr@embedded-projects.net)
#
# Script for building the kernel. Some Variables needed by this file
# are definded in the general settings.sh file which is called one step
# before this.


echo "         #############################################" >> $logfile_build
echo "         #         3rd Stage: Build kernel           #" >> $logfile_build 
echo "         #############################################" >> $logfile_build	


# This is the root directory for building the rootfs
cur_path=$debian_build_path



##################################
# Install and compile the Kernel #
##################################


# Create directories if not aviable
if [ ! -d "$cur_path/debian_process" ]
then
	mkdir $cur_path/debian_process
	
	echo "$build_time Installation folder $cur_path/debian_process created" >> $logfile_build
fi

if [ ! -d "$root_path/Downloads" ]
then
	mkdir $root_path/Downloads
	echo "$build_time Download folder $root_path/Downloads created" >> $logfile_build
fi

# Start installing and compiling the Kernel
if [ ! -e "$cur_path/debian_process/$std_kernel_pkg_name" ]
then
       
	if [ ! -d "$root_path/kernel/$kernel_name" ]	
	then
		cd $root_path/Downloads	
		#Get kernel from repository 
		git clone https://code.google.com/p/gnublin-develop-kernel && echo "$build_time Kernel downloaded successfully" >> $logfile_build

		#Move Kernel to kernel directory
		mv $root_path/Downloads/$git_name_kernel/$kernel_name $root_path/kernel/$kernel_name
		rm -r $root_path/Downloads/$git_name_kernel
	fi
	
	#Copy std. kernel to installation folder
	#cp -rp $root_path/kernel/$kernel_name $cur_path/debian_process

	#Change to kernel directory
	cd $root_path/kernel/$kernel_name
	#cp $cur_path/debian_process/$kernel_name/.config $cur_path/debian_process/config_backup
	

	#gnublin kernel build process	
	make zImage || echo "$build_time Error while compiling the kernel" >> $logfile_build
	make modules || echo "$build_time Error while compiling the kernel modules" >> $logfile_build
	make modules_install INSTALL_MOD_PATH=$root_path/kernel/$kernel_name || echo "$build_time Error while installing the kernel modules" >> $logfile_build
       
	
	cp $root_path/kernel/$kernel_name/arch/arm/boot/zImage $root_path/kernel/$kernel_name/zImage
	# Create the tar.gz file for debian build
	tar -zc -f $cur_path/debian_process/$std_kernel_pkg_name * || echo "$build_time Error while compressing the kernel" >> $logfile_build
	
	#cleaning all
	#rm -r $root_path/Downloads/$git_name_kernel
	#rm -r $cur_path/debian_process/$kernel_name
	cd $cur_path
fi
