#!/bin/bash
# Author: Benedikt Niedermayr (niedermayr@embedded-projects.net)


user=$(whoami)
build_file="/home/eproo/Desktop/nightly_build_gnublin_distribution/gnublin/lpc3131/build_GNUBLIN_support_package.sh"  #specify where your build_GNUBLIN_support_package.sh is located
filename="release_rootfs.tar.gz"


if [ "$user" != "root" ]
then
	echo "you are not root!"
	exit 0
fi



echo "PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/games
01 01 * * * /bin/bash "$build_file" clean > install_log.txt
02 01 * * * /bin/bash "$build_file" nightly_build  > install_log.txt


#Insert Cronjob for ROOT
crontab cronfile

