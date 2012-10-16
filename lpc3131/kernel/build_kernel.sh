#!/bin/bash
# Author: Benedikt Niedermayr (niedermayr@embedded-projects.net)
#
# Script for building the kernel




# This is the root directory for building the rootfs
cur_path=$debian_build_path



##################################
# Install and compile the Kernel #
##################################
if [ ! -d "$cur_path/debian_process" ]
then
	mkdir $cur_path/debian_process
	
	echo "installation folder $cur_path/debian_process created"
fi

if [ ! -e "$cur_path/debian_process/$std_kernel_pkg_name" ]
then
       
	if [ ! -d "$cur_path/../../../kernel/$default_kernel_pkg_name" ]	
	then
		cd $cur_path	
		#Get kernel from repository 
		git clone https://code.google.com/p/gnublin-develop-kernel/

		#Move Kernel to kernel directory
		mv $cur_path/$git_name_kernel/$default_kernel_pkg_name $cur_path/../../../kernel/$default_kernel_pkg_name
		rm -r $cur_path/$git_name_kernel
	fi
	
	#Copy std. kernel to installation folder
	cp -rp $cur_path/../../../kernel/$default_kernel_pkg_name $cur_path/debian_process

	#Change to kernel directory
	cd $cur_path/debian_process/$default_kernel_pkg_name/
	cp $cur_path/debian_process/$default_kernel_pkg_name/.config $cur_path/debian_process/config_backup
	

	#gnublin kernel build process	
	make zImage
	make modules
	make modules_install INSTALL_MOD_PATH=$cur_path/debian_process/$default_kernel_pkg_name
       
	
	# Create the tar.gz file for debian build
	tar -zc -f $cur_path/debian_process/$std_kernel_pkg_name *
	
	#cleaning all
	rm -r $cur_path/$git_name_kernel
	rm -r $cur_path/debian_process/$default_kernel_pkg_name
	cd $cur_path
fi
