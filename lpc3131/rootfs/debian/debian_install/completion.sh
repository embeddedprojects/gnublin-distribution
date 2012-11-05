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
	
	
	cp -v $root_path/rootfs/debian/debian_install/first_boot.sh ${output_dir}/mnt_debootstrap/opt/first_boot.sh || exit 0
	chmod +x ${output_dir}/mnt_debootstrap/opt/first_boot.sh
	echo "$build_time Script for first boot copied to ${output_dir}/mnt_debootstrap/opt/first_boot.sh" >> $logfile_build
	
	
	sed 's/root:x:0:0:root:\/root:\/bin\/bash/root::0:0:root:\/root:\/bin\/bash/g' "${output_dir}/mnt_debootstrap/etc/passwd" > tmp_file || exit 0
	echo "$(cat tmp_file)" > "${output_dir}/mnt_debootstrap/etc/passwd" || exit 0
	echo "alias ll='ls -l --color=auto'" >>	"${output_dir}/mnt_debootstrap/etc/bash.bashrc"
	rm tmp_file
	echo "alias grep='grep --colour=auto'" >> "${output_dir}/mnt_debootstrap/etc/bash.bashrc"
	echo "alias ls='ls --color=auto'" >> "${output_dir}/mnt_debootstrap/etc/bash.bashrc"
	echo "alias la='ls -a --color=auto'" >> "${output_dir}/mnt_debootstrap/etc/bash.bashrc"
	echo "COLUMNS=175" >> "${output_dir}/mnt_debootstrap/etc/bash.bashrc"
	echo "set lines=35" >> "${output_dir}/mnt_debootstrap/etc/vim/vimrc"
	
	if [ "$distro_version" = "max" ]
	then	
		cp -r $root_path/examples/ ${output_dir}/mnt_debootstrap/root/ || exit 0
		cp -r $root_path/gnublin_package/deb/ ${output_dir}/mnt_debootstrap/root/ || exit 0
	fi

	dd if=/dev/zero of=${output_dir}/mnt_debootstrap/swapfile bs=1M count=64 || exit 0


