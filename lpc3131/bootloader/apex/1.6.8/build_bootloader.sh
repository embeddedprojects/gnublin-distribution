#!/bin/bash

#this script compiles the apex bootloader

echo " " >> $logfile_build
echo " " >> $logfile_build
echo "#############################################" >> $logfile_build
echo "#         2nd Stage: Build bootloader       #" >> $logfile_build 
echo "#############################################" >> $logfile_build	


tar zxvf  $bootloader_install_dir/apex-1.6.8.tar.gz -C $bootloader_install_dir/ || exit 0
echo "$build_time Bootloader install dir extracted to $bootloader_install_dir" >> $logfile_build

cd $bootloader_install_dir/apex-1.6.8 || exit 0



#With the following command the configuration of your apex bootloader takes place. You should set the correct size of your Gnublin-boards RAM via "Platform Setup -> Choose RAM size"
if [ "$start_mkmenuconfig" = "yes" ]
then
	make menuconfig || exit 0
	echo "$build_time Menuconfig called successfully." >> $logfile_build
fi

#After configuration, run the following command to make the binary file.
make -j $parallel_jobs  apex.bin || exit 0 
echo "$build_time apex.bin build successfully." >> $logfile_build
echo "$build_time Bootloader installed successfully." >> $logfile_build



