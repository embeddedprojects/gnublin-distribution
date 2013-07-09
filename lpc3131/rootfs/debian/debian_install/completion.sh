#!/bin/bash
#
# This script completes some files for starting the GNUBLIN system more
# comfortably for the user

echo " " >> $logfile_build
echo " " >> $logfile_build
echo "#############################################################" >> $logfile_build
echo "#         Completion Script at $debian_build_path           #" >> $logfile_build 
echo "#############################################################" >> $logfile_build
	
	
    rm -r ${output_dir}/mnt_debootstrap/arch 
	rm -r ${output_dir}/mnt_debootstrap/Documentation
	rm -r ${output_dir}/mnt_debootstrap/drivers
	rm -r ${output_dir}/mnt_debootstrap/sound
	rm -r ${output_dir}/mnt_debootstrap/mm
	rm -r ${output_dir}/mnt_debootstrap/crypto
	rm -r ${output_dir}/mnt_debootstrap/scripts
	rm -r ${output_dir}/mnt_debootstrap/security
	rm -r ${output_dir}/mnt_debootstrap/virt
	rm -r ${output_dir}/mnt_debootstrap/selinux
	rm -r ${output_dir}/mnt_debootstrap/vmlinux
	rm -r ${output_dir}/mnt_debootstrap/vmlinux.o
	rm -r ${output_dir}/mnt_debootstrap/samples
	rm -r ${output_dir}/mnt_debootstrap/tools
	rm -r ${output_dir}/mnt_debootstrap/ipc
	rm -r ${output_dir}/mnt_debootstrap/block
	rm -r ${output_dir}/mnt_debootstrap/fs
	rm -r ${output_dir}/mnt_debootstrap/net
	rm -r ${output_dir}/mnt_debootstrap/Kbuild
	rm    ${output_dir}/mnt_debootstrap/COPYING
	rm    ${output_dir}/mnt_debootstrap/modules.builtin
	rm    ${output_dir}/mnt_debootstrap/modules.order
	rm    ${output_dir}/mnt_debootstrap/install_SD.sh
	rm    ${output_dir}/mnt_debootstrap/CREDITS
	rm -r ${output_dir}/mnt_debootstrap/lib/modules/2.6.33-gnublin-qemu-*/
	rm -r ${output_dir}/mnt_debootstrap/linux-2.6.33-lpc313x
	rm    ${output_dir}/mnt_debootstrap/deboostrap_stg2_errors.txt
	rm    ${output_dir}/mnt_debootstrap/elektor_defconfig
	rm    ${output_dir}/mnt_debootstrap/post_deboostrap_errors.txt
	rm    ${output_dir}/mnt_debootstrap/config_backup
	rm    ${output_dir}/mnt_debootstrap/setup_log.txt

	cp -v $root_path/rootfs/debian/debian_install/first_boot.sh ${output_dir}/mnt_debootstrap/opt/first_boot.sh || exit 0
	chmod +x ${output_dir}/mnt_debootstrap/opt/first_boot.sh
	echo "$build_time Script for first boot copied to ${output_dir}/mnt_debootstrap/opt/first_boot.sh" >> $logfile_build
        cp -rf $root_path/gnublin_package/src/NODEB_mdev/etc ${output_dir}/mnt_debootstrap/
        cp -rf $root_path/gnublin_package/src/NODEB_mdev/sbin ${output_dir}/mnt_debootstrap/
	
	
	sed 's/root:x:0:0:root:\/root:\/bin\/bash/root::0:0:root:\/root:\/bin\/bash/g' "${output_dir}/mnt_debootstrap/etc/passwd" > tmp_file || exit 0
	echo "$(cat tmp_file)" > "${output_dir}/mnt_debootstrap/etc/passwd" || exit 0
	echo "alias ll='ls -l --color=auto'" >>	"${output_dir}/mnt_debootstrap/etc/bash.bashrc"
	rm tmp_file
	echo "alias grep='grep --colour=auto'" >> "${output_dir}/mnt_debootstrap/etc/bash.bashrc"
	echo "alias ls='ls --color=auto'" >> "${output_dir}/mnt_debootstrap/etc/bash.bashrc"
	echo "alias la='ls -a --color=auto'" >> "${output_dir}/mnt_debootstrap/etc/bash.bashrc"
	echo "COLUMNS=175" >> "${output_dir}/mnt_debootstrap/etc/bash.bashrc"
	echo "set lines=35" >> "${output_dir}/mnt_debootstrap/etc/vim/vimrc"
	echo "kernel.printk = 3 3 1 7" > "${output_dir}/mnt_debootstrap/etc/sysctl.conf"
	
	cp -r $root_path/Downloads/gnublin-api/ ${output_dir}/mnt_debootstrap/root/ || exit 0
	
	cp -r $root_path/examples/ ${output_dir}/mnt_debootstrap/root/ || exit 0
	cp -r $root_path/gnublin_package/deb/ ${output_dir}/mnt_debootstrap/root/ || exit 0
	cp -r $root_path/output/kernel/* ${output_dir}/mnt_debootstrap/ || exit 0
		

	dd if=/dev/zero of=${output_dir}/mnt_debootstrap/swapfile bs=1M count=64 || exit 0


