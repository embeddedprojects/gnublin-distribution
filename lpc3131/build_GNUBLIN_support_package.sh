#!/bin/bash
# Author: Benedikt Niedermayr (niedermayr@embedded-projects.net)
# Board support package building script


# Parameters 
distro_version="max"    								#paste "-min" if you want to build a minimal version of debian.
tmp=$(cat /etc/lsb-release | grep DISTRIB_RELEASE)    #check for current OS Version
host_os_version=${tmp:16:2}


#############
# Variables #
#############
build_time="$(date '+%D %H:%M:%S') ->"
root_path=$(pwd)
toolchain_path=$root_path/toolchain
cross_compiler_path=$toolchain_path/armv5te/sysroots/i686-oesdk-linux/usr/bin/armv5te-linux-gnueabi
kernel_version=2.6.33
kernel_name=linux-$kernel_version-lpc313x
kernel_path=$root_path/kernel/$kernel_name
debian_build_path=$root_path/rootfs/debian/debian_install
debian_installed_files_path=$root_path/rootfs/debian/debian_install/debian_process
bootloader_install_dir=$root_path/bootloader/apex/1.6.8
logfile_build=$root_path/install.log

# Including settings through an additional file
source $root_path/rootfs/debian/debian_install/general_settings.sh	"$distro_version"




user=$(whoami)

if [ "$user" != "root" ]
then
	echo "You have to be root in order to start the build process!"
	exit 0
fi





##########################################################
# Only cleaning the whole board-support-package and exit #
##########################################################
if [ "$1" = "clean" ]
then
	rm -r $root_path/kernel/$kernel_name 2>/dev/null
	rm -r $debian_installed_files_path 2>/dev/null
	rm -r $bootloader_install_dir/apex-1.6.8 2>/dev/null
	rm -r $toolchain_path/armv5te 2>/dev/null
	rm -r $root_path/kernel/set.sh 2>/dev/null
	rm -r $root_path/.stamp* 2>/dev/null
	rm -r $root_path/tools/gnublin-installer/apex.bin 2>/dev/null
	rm -r $root_path/tools/gnublin-installer/zImage	2>/dev/null
	rm -r $root_path/tools/gnublin-installer/${output_filename}.tar.${tar_format} 2>/dev/null
	echo "Successfully cleaned!"
	# Uninstall also the toolchain	
	if [ "$2" = "all" ]
	then	
		echo "This step will delete your toolchain installed at /opt/eldk-*"
		echo "continue?(y/n)"
		read desicion
		
		if [ "$desicion" = "y" ]
		then		
		rm -r "/opt/eldk-5.0"
		rm -r "$root_path/Downloads"
		fi
	fi
	exit 1
fi


 


# Building Stages
# Now the complete board support package will be built.
rm -r $logfile_build


#############################################
# 1st Stage:Build toolchain                 #
#############################################
if [ ! -e $root_path/.stamp_toolchain ]
then
	source $root_path/toolchain/build_toolchain.sh
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
# 2.Copy some important support files into the rootfs
#   (e.g. example applications(im home ordner),debian packages---->add to packages list???)
if [ ! -e $root_path/.stamp_rootfs_post ]
then
	
	compress_debian_rootfs || exit 0 # compress the resulting rootfs
	
	# Copy the most important files #
	# It's not necessary but better for the user #	
	cp $bootloader_install_dir/apex-1.6.8/src/arch-arm/rom/apex.bin $root_path/tools/gnublin-installer/
	cp $kernel_path/arch/arm/boot/zImage $root_path/tools/gnublin-installer/
	cp ${output_dir}/${output_filename}.tar.${tar_format} $root_path/tools/gnublin-installer/
	

	# Create stamp #
	touch $root_path/.stamp_rootfs_post
fi




## Start Gnublin Installer ##
cd $root_path/tools/gnublin-installer/
if [ $host_os_version -ge 12 ]
then
	$root_path/tools/gnublin-installer/gnublin-installer-neu || $root_path/tools/gnublin-installer/gnublin-installer-alt
	
else
	$root_path/tools/gnublin-installer/gnublin-installer-alt
	
fi

exit 1


######################################
# 7th Stage: Built Support package   # 
######################################
# --> User rights im Wiki nicht hier!!!
# --> Auf die CD einen Ordner mit einem Abbild dieser Struktur + alles fertig ordner(für die Oma)
# --> Build support folder
#   (e.g. for how-to files,examles,source_code)
# --> Alles wenn möglich für den Gnblin installer komform machen!




