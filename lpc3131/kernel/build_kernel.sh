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
#if [ ! -e "$cur_path/debian_process/$std_kernel_pkg_name" ]
#then
       
	if [ ! -d "$root_path/Downloads/$git_name_kernel/$kernel_name" ]  #"$root_path/kernel/$kernel_name"	
	then
		cd $root_path/Downloads	|| exit 0
		#Get kernel from repository 
		git clone https://code.google.com/p/gnublin-develop-kernel || exit 0
		echo "$build_time Repository cloned correctly " >> $logfile_build
	
	else 
		cd $root_path/Downloads/$git_name_kernel
		rm -r $root_path/kernel/$kernel_name
		git pull
	fi

	
		
	
	#Copy Kernel to kernel directory
	cp -rp $root_path/Downloads/$git_name_kernel/$kernel_name $root_path/kernel/$kernel_name || exit 0
	echo "$build_time Kernel from $root_path/Downloads/$git_name_kernel/$kernel_name copied correctly to $root_path/kernel/$kernel_name" >> $logfile_build


	#Change to kernel directory and copy .config
	cd $root_path/kernel/$kernel_name
	#cp $root_path/kernel/$kernel_name/config_backup $root_path/kernel/$kernel_name/.config || exit 0
	#cp $root_path/kernel/$kernel_name/arch/arm/configs/gnublin_defconfig $root_path/kernel/$kernel_name/.config || exit 0
	echo "$build_time Kernelconfig copied correctly!" >> $logfile_build
	

	#gnublin kernel build process
	make gnublin_defconfig	
	
	if [ "$start_mkmenuconfig" = "yes" ]
	then	
		make menuconfig || exit 0
		echo "$build_time Make menuconfig called correctly" >> $logfile_build	
	fi	
	make zImage || exit 0
	echo "$build_time Kernel compiled successfully" >> $logfile_build	
	make modules || exit 0
	echo "$build_time Modules compiled successfully" >> $logfile_build
	make modules_install INSTALL_MOD_PATH=$root_path/kernel/$kernel_name || exit 0
    echo "$build_time Kernel installed correctly" >> $logfile_build   
	
	cp $root_path/kernel/$kernel_name/arch/arm/boot/zImage $root_path/kernel/$kernel_name/zImage || exit 0
	# Create the tar.gz file for debian build
	rm -rf $cur_path/debian_process/$std_kernel_pkg_name 
	tar -zc -f $cur_path/debian_process/$std_kernel_pkg_name * || exit 0
	echo "$build_time Kernel compressed correctly" >> $logfile_build
	cd $cur_path
	cp -r $kernel_path/arch/arm/boot/zImage $root_path/output
	cp -r $root_path/kernel/$kernel_name/lib/modules/* $root_path/output
#fi


