#!/bin/sh

##############################################
# Execute arp command
# save the result at a file arp.txt
##############################################

#echo $0
path=`readlink -f $0`
dir=`dirname $path`
#echo $path
#echo $dir

. /www/gate/config/config.sh

cd $dir
file="$dir/arp.txt"

echo -n "UPDATE @" > $file
date +"%Y-%m-%d %H:%M:%S" >> $file
date >> $file

echo "/sbin/arp -n -i $DEVINT" >> $file
/sbin/arp -n -i $DEVINT >> $file

# update arp table
/usr/local/bin/php  ./readarp.php


