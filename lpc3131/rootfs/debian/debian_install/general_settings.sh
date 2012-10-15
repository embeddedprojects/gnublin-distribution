#!/bin/bash
# Author: Ingmar Klein (ingmar.klein@hs-augsburg.de)
# Edited by Benedikt Niedermayr (niedermayr@embedded-projects.net)


# pre build script for installing the rootfilesystem inclusive the GNUBLIN kernel.
# It contains some very important settings.


#############################
##### GENERAL SETTINGS: #####
#############################

host_os="Ubuntu" # Debian or Ubuntu (YOU NEED TO EDIT THIS!)

debian_mirror_url="http://ftp.debian.org/debian/" # mirror for debian

debian_target_version="squeeze" # The version of debian that you want to build (ATM, 'squeeze', 'wheezy' and 'sid' are supported)

nameserver_addr="8.8.8.8"  

# where to get the standard kernel #kernel_pkg_path="${HOME}/gnublin/built_kernels"
std_kernel_pkg_path="$debian_installed_files_path"                                    

# where to get the qemu kernel
qemu_kernel_pkg_path="http://www.hs-augsburg.de/~ingmar_k/gnublin/kernels/2.6.33"  

# qemu kernel file name
qemu_kernel_pkg_name="kernel_2.6.33-gnublin-qemu-1.2_1335647673.tar.bz2"

# where to put the files in general
output_dir_base="$debian_installed_files_path"                                 

std_kernel_pkg_name="linux-2.6.33.tar.gz" # standard kernel file name

#Kernel package name before compression
default_kernel_pkg_name="$kernel_name"

git_name_kernel="gnublin-develop-kernel"


add_packages_max="samba php5 gpsd gpsd-clients fswebcam uvccapture lm-sensors firmware-linux-free firmware-linux-nonfree firmware-realtek firmware-ralink firmware-linux firmware-brcm80211 firmware-atheros rcconf cgilib cgiemail cgi-mapserver lrzsz libnss-mdns libpam-modules nscd ssh libpcsclite1 libnl1 nfs-common tree lighttpd vsftpd rsync ruby git fakeroot"

add_packages_base="i2c-tools makedev module-init-tools dhcp3-client netbase ifupdown iproute iputils-ping wget net-tools vim nano hdparm bzip2 p7zip unrar unzip zip p7zip-full screen less usbutils psmisc strace info ethtool wireless-tools wpasupplicant python rsyslog whois time procps perl parted build-essential ccache bison flex autoconf automake gcc libc6 cpp curl ftp gettext subversion"


###################################
##### CONFIGURATION SETTINGS: #####
###################################

use_ramzswap="no" # set if you want to use a compressed SWAP space in RAM (can potentionally improve performance)

ramzswap_size_kb="3072" # size of the ramzswap device in KB

ramzswap_kernel_module_name="ramzswap" # name of the ramzswap kernel module (could have a different name on newer kernel versions)

vm_swappiness="100" # Setting for general kernel RAM swappiness: With RAMzswap and low RAM, a high number (like 100) could be good. Default in Linux mostly is 60.

i2c_hwclock="no" # say "yes" here, if you connected a RTC to your gnublin board, otherwise say "no"

i2c_hwclock_name="ds1307" # name of the hardware RTC (if one is connected)

i2c_hwclock_addr="0x68" # hardware address of the RTC (if one is connected)

rtc_kernel_module_name="rtc-ds1307" # kernel module name of the hardware RTC (if one is connected)

bootloader_bin_path="http://www.hs-augsburg.de/~ingmar_k/gnublin/bootloader" # where to get the bootloader

bootloader_bin_name="apex.bin" # bootloader binary

tar_format="gz" # bz2(=bzip2) or gz(=gzip)

output_dir="${output_dir_base}/build_`date +%s`" # Subdirectory for each build-run, ending with the unified Unix-Timestamp (seconds passed since Jan 01 1970)

work_image_size_MB=4096 # size of the temporary image file, in which the installation process is carried out

output_filename="debian_rootfs_gnublin" # base name of the output file (compressed rootfs)

apt_prerequisites_debian="debootstrap binfmt-support qemu-user-static qemu qemu-kvm qemu-system parted" # packages needed for the build process on debian

apt_prerequisites_ubuntu="debootstrap binfmt-support qemu qemu-system qemu-kvm qemu-kvm-extras-static parted" # packages needed for the build process on ubuntu

clean_tmp_files="no" # delete the temporary files, when the build process is done?

create_disk="no" # create a bootable SD-card after building the rootfs?




####################################
#check for min or max installation.#
####################################
if [ "$1" = "-min" ]
then
	additional_packages="$add_packages_base"
	#echo "minimal distribution choosen" >> 
else
	additional_packages="$add_packages_base $add_packages_max"
fi

echo "$additional_packages"
#exit 0










 
















