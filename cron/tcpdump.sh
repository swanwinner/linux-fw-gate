
. /www/gate/config/config.sh

cd $GATE_PATH/cron
echo $GATE_PATH/cron

echo $$ > $GATE_PATH/var/tcpdump.pid

exec tcpdump -n -p  -i eth1  \(dst host $DRM_SERVER_1 or dst host $DRM_SERVER_2\) 2> /dev/null

