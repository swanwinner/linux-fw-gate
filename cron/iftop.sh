#!/bin/sh

time=`date +'%H%M%S'`

path=`readlink -f $0`
dir=`dirname $path`
#echo $path
#echo $dir

cd $dir
file="$dir/iftop.txt"
tmp="$dir/iftop.tmp"

# 임시파일에 저장
date > $tmp
uptime >> $tmp

/usr/local/sbin/iftop  -t  -c /www/gate/cron/iftoprc  -s 55 >> $tmp

# 임시파일을 txt 파일로 이름 변경
/bin/mv -f $tmp $file

# 통계처리를 위하여 php 프로그램으로 
$dir/iftop_proc.php  $file

