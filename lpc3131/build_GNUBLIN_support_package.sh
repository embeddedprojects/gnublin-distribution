#!/bin/bash
# Author: Benedikt Niedermayr (niedermayr@embedded-projects.net)
# Board support package building script



# Parameters 
distro_version="-max"    				#paste "-min" if you want to build a minimal version of debian.
									    #

 
#############
# Variables #
#############
root_path=$(pwd)
toolchain_path=$root_path/toolchain
cross_compiler_path=$toolchain_path/armv5te/sysroots/i686-oesdk-linux/usr/bin/armv5te-linux-gnueabi
cross_compiler_path2=/home/brenson/Downloads/armv5te/sysroots/i686-oesdk-linux/usr/bin/armv5te-linux-gnueabi
cross_compiler_path3=/home/brenson/Downloads/armv5te/sysroots/i686-oesdk-linux/bin/armv5te-linux-gnueabi/
kernel_version=2.6.33
kernel_name=linux-$kernel_version-lpc313x
kernel_path=$root_path/kernel/$kernel_name
debian_build_path=$root_path/rootfs/debian/debian_install
debian_installed_files_path=$root_path/rootfs/debian/debian_install/debian_process
bootloader_install_dir=$root_path/bootloader/apex/1.6.8


##########################################################
# Only cleaning the whole board-support-package and exit #
##########################################################
if [ "$1" = "clean" ]
then
	rm -r $root_path/kernel/$kernel_name
	rm -r $debian_installed_files_path
	rm -r $bootloader_install_dir/apex-1.6.8
	rm -r $toolchain_path/armv5te
	rm -r $root_path/kernel/set.sh
	
	# Uninstall also the toolchain	
	if [ "$2" = "all" ]
	then	
		rm -r "/opt/eldk-*"
		rm -r "$root_path/Downloads"
	fi
	exit 0
fi




# Deciding Stage : In this stage all required/selected adons 
# will be added to the build process. 





# Building Stages
# Now the complete board support package will be built.


#############################################
# 1st Stage:Build toolchain and bootloader  #
#############################################
source $root_path/toolchain/build_toolchain.sh
source $root_path/kernel/set.sh



#export PATH=$cross_compiler_path2:$cross_compiler_path3:$PATH
#export ARCH=arm
#export CROSS_COMPILE=arm-linux-gnueabi-

echo "$ARCH"
echo "$CROSS_COMPILE"
echo "$PATH"

source $root_path/bootloader/apex/1.6.8/build_bootloader.sh



######################################
# 2nd Stage: Kernel and rootfs build # 
######################################

# Including settings through an additional file
source $root_path/rootfs/debian/debian_install/general_settings.sh	"$distro_version"	 
source $root_path/kernel/build_kernel.sh


#exit 0

# Move set.sh file into the kernel
mv $root_path/kernel/set.sh $kernel_path

source $root_path/rootfs/debian/debian_install/build_debian_system.sh 


exit 0
# 2.Copy some important support files into the rootfs
#   (e.g. example applications(im home ordner),debian packages---->add to packages list???)

### Uninstall unimportant files out of the rootfs.
### (Avahi, samba, Documentation, ARCH, )
### Use a script which does that but at the first start of the BOARD and after that deinstall it!!!



#--->pre-compiled (examples??)

# 3.Build support folder
#   (e.g. for how-to files,examles,source_code)


# 4. User rights im Wiki nicht hier!!!


# 5. Alles wenn möglich für den Gnblin installer komform machen!
# 6. Auf die CD einen Ordner mit einem Abbild dieser Struktur + alles fertig ordner(für die Oma)





