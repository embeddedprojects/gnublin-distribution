#!/bin/bash

# Description: Compress the resulting rootfs
source $debian_build_path/build_functions.sh

fn_my_echo "Compressing the rootfs now!"

mount |grep ${output_dir}/${output_filename}.img >/dev/null
if [ ! "$?" = "0" ]
then 
	fsck.ext3 -fy ${output_dir}/${output_filename}.img
	if [ "$?" = "0" ]
	then
		fn_my_echo "Temporary filesystem checked out, OK!"
	else
		fn_my_echo "ERROR: State of Temporary filesystem is NOT OK! Exiting now."
		umount_img all
		exit 60
	fi
else
	fn_my_echo "ERROR: Image file still mounted. Exiting now!"
	umount_img all
	exit 61
fi

mount ${output_dir}/${output_filename}.img ${output_dir}/mnt_debootstrap -o loop
if [ "$?" = "0" ]
then

    ## Gnublin support package ##
	source $debian_build_path/completion.sh


	rm -r ${output_dir}/mnt_debootstrap/lib/modules/2.6.33-gnublin-qemu-*/
	cd ${output_dir}/mnt_debootstrap
	if [ "${tar_format}" = "bz2" ]
	then
		tar_all compress "${output_dir}/${output_filename}.tar.${tar_format}" .
	elif [ "${tar_format}" = "gz" ]
	then
		tar_all compress "${output_dir}/${output_filename}.tar.${tar_format}" .
	else
		fn_my_echo "Incorrect setting '${tar_format}' for the variable 'tar_format' in the general_settings.sh.
Please check! Only valid entries are 'bz2' or 'gz'. Could not compress the Rootfs!"
	fi

	cd ${output_dir}
	sleep 5
else
	fn_my_echo "ERROR: Image file could not be remounted correctly. Exiting now!"
	umount_img all
	exit 62
fi

umount ${output_dir}/mnt_debootstrap
sleep 10
sync
mount | grep ${output_dir}/mnt_debootstrap > /dev/null
if [ ! "$?" = "0" ] && [ "${clean_tmp_files}" = "yes" ]
then
	rm -r ${output_dir}/mnt_debootstrap
	rm -r ${output_dir}/qemu-kernel
	rm ${output_dir}/${output_filename}.img
elif [ "$?" = "0" ] && [ "${clean_tmp_files}" = "yes" ]
then
	fn_my_echo "Directory '${output_dir}/mnt_debootstrap' is still mounted, so it can't be removed. Exiting now!"
	umount_img all
	exit 15
elif [ "$?" = "0" ] && [ "${clean_tmp_files}" = "no" ]
then
	fn_my_echo "Directory '${output_dir}/mnt_debootstrap' is still mounted, please check. Exiting now!"
	umount_img all
	exit 16
fi

fn_my_echo "Rootfs successfully DONE!"

