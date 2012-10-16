#!/bin/bash 

#this script compiles the apex bootloader

tar zxvf  $bootloader_install_dir/apex-1.6.8.tar.gz -C $bootloader_install_dir/
cd $bootloader_install_dir/apex-1.6.8


#With the following command the configuration of your apex bootloader takes place. You should set the correct size of your Gnublin-boards RAM via "Platform Setup -> Choose RAM size"
make menuconfig

#After configuration, run the following command to make the binary file.
make apex.bin
