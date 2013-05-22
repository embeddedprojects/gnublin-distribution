#!/bin/bash


# Description: Compress the resulting rootfs
source $debian_build_path/build_functions.sh






fn_my_echo "Compressing the rootfs now!"
echo "checking output directory second time"
	ls -alF ${output_dir}
#Mount the Image Again
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
		exit 0
	fi
else
	fn_my_echo "ERROR: Image file still mounted. Exiting now!"
	umount_img all
	exit 0
fi

mount ${output_dir}/${output_filename}.img ${output_dir}/mnt_debootstrap -o loop
if [ "$?" != "0" ]
then
	exit 0
fi
    ############## Gnublin support package ##############################
	source $debian_build_path/completion.sh


	sync
	umount ${output_dir}/mnt_debootstrap
	sleep 5
	sync



	#echo "DEBUG!!! ENTER KEY......(completion_script)"
	#read fff

	## Before compressing the rootfs, first let qemu start on time again ##  
	qemu-system-arm -M versatilepb -cpu arm926 -nographic -no-reboot -kernel ${root_path}/kernel/qemu_kernel/zImage -hda ${output_dir}/${output_filename}.img -m 512 -append "root=/dev/sda rootfstype=ext3 mem=512M devtmpfs.mount=0 rw ip=dhcp" && echo "qemu_second_start successfull" > $root_path/qemu_sec_start.txt
	
	echo "checking output directory second time"
	ls -alF ${output_dir}
	#Mount the Image Again
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
			exit 0
		fi
	else
		fn_my_echo "ERROR: Image file still mounted. Exiting now!"
		umount_img all
	exit 0
	fi

	mount ${output_dir}/${output_filename}.img ${output_dir}/mnt_debootstrap -o loop
	if [ "$?" != "0" ]
	then
		fn_my_echo "Image could not be mountet. Exiting now!" 
		umount_img all		
		exit 0
	fi
	############# END GNUBLIN SUPPORT PACKAGE ############################

	
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
#else
#	fn_my_echo "ERROR: Image file could not be remounted correctly. Exiting now!"
#	umount_img all
#	exit 0


sync
umount ${output_dir}/mnt_debootstrap
sleep 5
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
	exit 0
elif [ "$?" = "0" ] && [ "${clean_tmp_files}" = "no" ]
then
	fn_my_echo "Directory '${output_dir}/mnt_debootstrap' is still mounted, please check. Exiting now!"
	umount_img all
	exit 0
fi

fn_my_echo "Rootfs successfully DONE!"

