#!/bin/bash
# Author: Benedikt Niedermayr (niedermayr@embedded-projects.net)
# Author: Benjamin Wöster (benjamin.woester@gmail.com)
# Board support package building script

function printHelp() {
	echo '
Usage:
 build_GNUBLIN_support_package.sh [options]
 build_GNUBLIN_support_package.sh clean [all]
 build_GNUBLIN_support_package.sh clean-all

Options:
 -h, -?, --help                     This help message.
 --bootloader-install-dir <dir>     Defaults to "<root_path>/bootloader/apex/1.6.8".
 --crosscompiler-dir <dir>          Defaults to "<toolchain-dir>/armv5te/sysroots/i686-oesdk-linux/usr/bin/armv5te-linux-gnueabi",
 --debian-build-dir <dir>           Defaults to "<root-path>/rootfs/debian/debian_install".
 --debian-installed-files-dir <dir> Defaults to "<root-path>/rootfs/debian/debian_install/debian_process".
 --distr-version <version>          Allowed values are "min" and "max".
                                    Defaults to "max".
 --fs-type <type>                   FS type used for the image. Possible values
                                    include "ext2" and "ext3".
                                    Defaults to "ext3".
 --host-os <os>                     Allowed values are "Debian" and "Ubuntu".
                                    Defaults to "Ubuntu".
 --kernel-name <name>               Name of the compiled kernel.
                                    Defaults to "linux-<version>-lpc313x".
 --kernel-dir <dir>                 Defaults to "<root-path>/kernel/<kernel-name>".
 --kernel-version <version>         The version of the kernel you want to use.
                                    Allowed values are "2.6.33" and "3.3.0".
                                    Defaults to "2.6.33".
 --log-file <filepath>              Defaults to "<root-path>/install.log".
 --nightly-build <yesno>            Set this option to "yes" if you use the
                                    script for nightly builds. Please note, that
                                    also have to specify the --root-dir option.
                                    Defaults to "no".
 --parallel-jobs <n>                Number of parallel jobs make calls.
                                    Defaults to "8".
 --root-dir <dir>                   Root directoy for the build.
                                    Defaults to the current directoy.
 --start-mkmenuconfig <yesno>       Allowed values are "yes" and "not". Set to
                                    "yes" if you want to configure bootloader
                                    and kernel.
                                    Defaults to "no".
 --toolchain-dir <dir>              Defaults to <root-dir>/toolchain
'
}


# Available arguments

ARG_BOOTLOADER_INSTALL_DIR=""
ARG_CROSSCOMPILER_DIR=""
ARG_DEBIAN_BUILD_DIR=""
ARG_DEBIAN_INSTALLED_FILES_DIR=""
ARG_DISTR_VERSION="max"
ARG_FS_TYPE="ext3"
ARG_HOST_OS="Ubuntu"
ARG_KERNEL_NAME=""
ARG_KERNEL_DIR=""
ARG_KERNEL_VERSION="2.6.33"
ARG_LOG_FILE=""
ARG_NIGHTLY_BUILD="no"
ARG_PARALLEL_JOBS="8"
ARG_ROOT_DIR=""
ARG_START_MKMENUCONFIG="no"
ARG_TOOLCHAIN_DIR=""
ARG_CLEAN="no"
ARG_CLEAN_ALL="no"

# Parse arguments
# All arguments can be given in the format "--option=xyz" or "--option xyz"
# If we don't find a '=', we consider proper input and use the next parameter
# as option value
# If we do find a '=', we delete everything up till '=' and take that as option
# value

while :
do
	case $1 in
	-h | --help | -\?)
	    printHelp
	    exit 0
	    ;;
	--bootloader-install-dir)
	    ARG_BOOTLOADER_INSTALL_DIR=$2
	    shift 2
	    ;;
	--bootloader-install-dir=*)
	    ARG_BOOTLOADER_INSTALL_DIR=${1#*=}
	    shift
	    ;;
	--crosscompiler-dir)
	    ARG_CROSSCOMPILER_DIR=$2
	    shift 2
	    ;;
	--crosscompiler-dir=*)
		ARG_CROSSCOMPILER_DIR=${1#*=}
	    shift
	    ;;
	--debian-build-dir)
	    ARG_DEBIAN_BUILD_DIR=$2
	    shift 2
	    ;;
	--debian-build-dir=*)
	    ARG_DEBIAN_BUILD_DIR=${1#*=}
	    shift
	    ;;
	--debian-installed-files-dir)
	    ARG_DEBIAN_INSTALLED_FILES_DIR=$2
	    shift 2
	    ;;
	--debian-installed-files-dir=*)
	    ARG_DEBIAN_INSTALLED_FILES_DIR=${1#*=}
	    shift
	    ;;
	--distr-version)
	    ARG_DISTR_VERSION=$2
	    shift 2
	    ;;
	--distr-version=*)
	    ARG_DISTR_VERSION=${1#*=}
	    shift
	    ;;
	--fs-type)
	    ARG_FS_TYPE=$2
	    shift 2
	    ;;
	--fs-type=*)
	    ARG_FS_TYPE=${1#*=}
	    shift
	    ;;
	--host-os)
	    ARG_HOST_OS=$2
	    shift 2
	    ;;
	--host-os=*)
	    ARG_HOST_OS=${1#*=}
	    shift
	    ;;
	--kernel-name)
	    ARG_KERNEL_NAME=$2
	    shift 2
	    ;;
	--kernel-name=*)
	    ARG_KERNEL_NAME=${1#*=}
	    shift
	    ;;
	--kernel-dir)
	    ARG_KERNEL_DIR=$2
	    shift 2
	    ;;
	--kernel-dir=*)
	    ARG_KERNEL_DIR=${1#*=}
	    shift
	    ;;
	--kernel-version)
	    ARG_KERNEL_VERSION=$2
	    shift 2
	    ;;
	--kernel-version=*)
	    ARG_KERNEL_VERSION=${1#*=}
	    shift
	    ;;
	--log-file)
	    ARG_LOG_FILE=$2
	    shift 2
	    ;;
	--log-file=*)
	    ARG_LOG_FILE=${1#*=}
	    shift
	    ;;
	--nightly-build)
	    ARG_NIGHTLY_BUILD=$2
	    shift 2
	    ;;
	--nightly-build=*)
	    ARG_NIGHTLY_BUILD=${1#*=}
	    shift
	    ;;
	--parallel-jobs)
	    ARG_PARALLEL_JOBS=$2
	    shift 2
	    ;;
	--parallel-jobs=*)
	    ARG_PARALLEL_JOBS=${1#*=}
	    shift
	    ;;
	--root-dir)
	    ARG_ROOT_DIR=$2
	    shift 2
	    ;;
	--root-dir=*)
	    ARG_ROOT_DIR=${1#*=}
	    shift
	    ;;
	--start-mkmenuconfig)
	    ARG_START_MKMENUCONFIG=$2
	    shift 2
	    ;;
	--start-mkmenuconfig=*)
	    ARG_START_MKMENUCONFIG=${1#*=}
	    shift
	    ;;
	--toolchain-dir)
	    ARG_TOOLCHAIN_DIR=$2
	    shift 2
	    ;;
	--toolchain-dir=*)
	    ARG_TOOLCHAIN_DIR=${1#*=}
	    shift
	    ;;
	clean)
	    ARG_CLEAN="yes"
	    shift
	    ;;
	all)
		if [ "$ARG_CLEAN" == "yes" ]
		then
	    	ARG_CLEAN_ALL="yes"
		fi
	    shift
	    ;;
	clean-all)
	    ARG_CLEAN="yes"
	    ARG_CLEAN_ALL="yes"
	    shift
	    ;;
	--) # End of all options
	    shift
	    break
	    ;;
	-*)
	    echo "WARN: Unknown option (ignored): $1" >&2
	    shift
	    ;;
	*)  # no more options. Stop while loop
	    break
	    ;;
	esac
done


# Generate default values for arguments that haven't been explicitly set

if [ "$ARG_ROOT_DIR" == '' ]
then
	if [ "$ARG_NIGHTLY_BUILD" == 'yes' ]
	then
		echo 'You must specify option --root-dir for nightly builds.' >&2
		exit 1
	fi
	ARG_ROOT_DIR=$(pwd)
fi

if [ "$ARG_BOOTLOADER_INSTALL_DIR" == '' ]
then
  ARG_BOOTLOADER_INSTALL_DIR="$ARG_ROOT_DIR/bootloader/apex/1.6.8"
fi

if [ "$ARG_TOOLCHAIN_DIR" == '' ]
then
	ARG_TOOLCHAIN_DIR="$ARG_ROOT_DIR/toolchain"
fi

if [ "$ARG_CROSSCOMPILER_DIR" == '' ]
then
	ARG_CROSSCOMPILER_DIR="$ARG_TOOLCHAIN_DIR/armv5te/sysroots/i686-oesdk-linux/usr/bin/armv5te-linux-gnueabi"
fi

if [ "$ARG_DEBIAN_BUILD_DIR" == '' ]
then
	ARG_DEBIAN_BUILD_DIR="$ARG_ROOT_DIR/rootfs/debian/debian_install"
fi

if [ "$ARG_DEBIAN_INSTALLED_FILES_DIR" == '' ]
then
	ARG_DEBIAN_INSTALLED_FILES_DIR="$ARG_ROOT_DIR/rootfs/debian/debian_install/debian_process"
fi

if [ "$ARG_KERNEL_NAME" == '' ]
then
	ARG_KERNEL_NAME="linux-$ARG_KERNEL_VERSION-lpc313x"
fi

if [ "$ARG_KERNEL_DIR" == '' ]
then
	ARG_KERNEL_DIR="$ARG_ROOT_DIR/kernel/$ARG_KERNEL_NAME"
fi

if [ "$ARG_LOG_FILE" == '' ]
then
	ARG_LOG_FILE="$ARG_ROOT_DIR/install.log"
fi


# Do exports depending on default/ user provided arguments 

export distro_version="$ARG_DISTR_VERSION"
export filesystem_vers="$ARG_FS_TYPE"
export host_os="$ARG_HOST_OS"
export start_mkmenuconfig="$ARG_START_MKMENUCONFIG"
export parallel_jobs="$ARG_PARALLEL_JOBS"
export root_path="$ARG_ROOT_DIR"
export kernel_version="$ARG_KERNEL_VERSION"
export kernel_name="$ARG_KERNEL_NAME"
export toolchain_path="$ARG_TOOLCHAIN_DIR"
export cross_compiler_path="$ARG_CROSSCOMPILER_DIR"
export kernel_path="$ARG_KERNEL_DIR"
export debian_build_path="$ARG_DEBIAN_BUILD_DIR"
export debian_installed_files_path="$ARG_DEBIAN_INSTALLED_FILES_DIR"
export bootloader_install_dir="$ARG_BOOTLOADER_INSTALL_DIR"
export logfile_build="$ARG_LOG_FILE"

# Check if nightly_build_mode is selected
if [ "$ARG_NIGHTLY_BUILD" == 'yes' ]
then
	export nightly_build="yes"
fi

export build_time="$(date '+%D %H:%M') ->"
export user=$(whoami)

# not important for now
export eldk_name="eldk-5.0"



# Include settings through an additional file
source $root_path/rootfs/debian/debian_install/general_settings.sh	"$distro_version"



##########################################################
# Only cleaning the whole board-support-package and exit #
##########################################################

if [ "$ARG_CLEAN" == "yes" ]
then
	 
	sudo -s -E rm -rf $debian_installed_files_path 
	sudo -s -E rm -rf $bootloader_install_dir/apex-1.6.8 
	sudo -s -E rm -rf $toolchain_path/armv5te 
	sudo -s -E rm -rf $root_path/kernel/set.sh 
	sudo -s -E rm -rf $root_path/.stamp* 
	sudo -s -E rm -rf $root_path/tools/gnublin-installer/apex.bin 
	sudo -s -E rm -rf $root_path/tools/gnublin-installer/zImage	
	sudo -s -E rm -rf $root_path/tools/gnublin-installer/${output_filename}.tar.${tar_format} 
	#sudo -s -E rm -rf $root_path/gnublin_package/deb/*
	sudo -s -E rm -rf $logfile_build
	sudo -s -E rm -rf $root_path/output
	sudo -s -E rm -rf $root_path/gnublin_package/deb/*

	
		
	if [ "$ARG_CLEAN_ALL" == "yes" ]
	then
		sudo -s -E rm -rf $root_path/kernel/$kernel_name
		sudo -s -E rm -rf $root_path/Downloads/*
		sudo -s -E rm -rf $root_path/output
		if [ -d $root_path/backup ]; then
                sudo -s -E rm -rf $root_path/backup
		fi
	fi
	echo "Successfully cleaned!"
	exit 1
	
fi






# Building Stages
# Now the complete board support package will be built.
rm -r $logfile_build
touch $logfile_build
chown $user:$user $logfile_build

echo "$PATH" >> $logfile_build
echo "$(env)" >> $logfile_build

# Install utils for using make menuconfig #
sudo -s -E apt-get install make libncurses5-dev g++ dpkg-dev 
sudo -s -E apt-get install git
sudo -s -E apt-get install git-core

echo "$build_time All necessary packages installed." >> $logfile_build

# Create output folder #
if [ ! -d "$root_path/output" ]
then	
	mkdir $root_path/output	
fi	


#get the newest version of the build_script and files
cd $root_path
git pull


#############################################
# 1st Stage:Build toolchain                 #
#############################################
if [ ! -e $root_path/.stamp_toolchain ]
then
	if [ "$nightly_build" = "yes" ]
	then
		source $root_path/toolchain/build_toolchain.sh
	else

		sudo -s -E source $root_path/toolchain/build_toolchain.sh
		if [ "$?" = "0" ]
		then
			echo "$build_time An error ocured during build_toolchain." >> $logfile_build 
			echo "ERROR while building toolchain"
			exit 0
		fi

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
	if [ "$nightly_build" = "yes" ]
	then
		source $debian_build_path/build_debian_system.sh
	else
		sudo -s -E source $debian_build_path/build_debian_system.sh
	fi
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
        rm -r $root_path/gnublin_package/deb/*
	cd $root_path/gnublin_package/src/
	$root_path/gnublin_package/src/mkdeb_package
        echo "Gnublin Packages Successfully created" >> $logfile_build
    

    # Following script calls the script completion.sh!
	# compress the resulting rootfs
	if [ "$nightly_build" = "yes" ]
	then
		$debian_build_path/compress_debian_rootfs.sh || exit 0 # compress the resulting rootfs
	else
		sudo -s -E $debian_build_path/compress_debian_rootfs.sh || exit 0 
	fi

	
	# Copy the most important files #
	# It's not necessary but better for the user to find all final created files at the same place #
	
	cp $bootloader_install_dir/apex-1.6.8/src/arch-arm/rom/apex.bin $root_path/output
	
	cp ${output_dir}/${output_filename}.tar.${tar_format} $root_path/output
	
	 

	# Create stamp #
	touch $root_path/.stamp_rootfs_post
fi

if [ "$nightly_build" = "yes" ]
then
	echo "$(date) ---Nightly Build READY" >> $logfile_build 
	#################################################################################################
	# Create a history of 7 builds if nightly_build is selected.
	#################################################################################################
	
	#check if the backup folder already exists.
	if [ ! -d $root_path/backup ]; then
	     sudo -s -E mkdir $root_path/backup
	     sudo -s -E mkdir $root_path/backup/000
	     sudo -s -E mkdir $root_path/backup/001
	     sudo -s -E mkdir $root_path/backup/002
	     sudo -s -E mkdir $root_path/backup/003
	     sudo -s -E mkdir $root_path/backup/004
	     sudo -s -E mkdir $root_path/backup/005
	     sudo -s -E mkdir $root_path/backup/006
	fi
	
	#move the directories that you always have the latest build in /000
	     sudo -s -E rm -rf $root_path/backup/006
	     sudo -s -E mv $root_path/backup/005 $root_path/backup/006
	     sudo -s -E mv $root_path/backup/004 $root_path/backup/005
	     sudo -s -E mv $root_path/backup/003 $root_path/backup/004
	     sudo -s -E mv $root_path/backup/002 $root_path/backup/003
	     sudo -s -E mv $root_path/backup/001 $root_path/backup/002
	     sudo -s -E mv $root_path/backup/000 $root_path/backup/001
	     sudo -s -E mkdir $root_path/backup/000
	
	#copy the output files plus the install.log file to the newest backup directory
	     sudo -s -E cp -rf $root_path/output/* $root_path/backup/000
	     sudo -s -E cp -f  $logfile_build $root_path/backup/000
	     sudo -s -E echo   $build_time > $root_path/backup/000/build_time
	
	##################################################################################################
fi

exit 1








