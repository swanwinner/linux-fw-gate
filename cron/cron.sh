#!/bin/sh

##
### /www/gate/cron/cron.sh
##

/www/gate/cron/update.php >> /www/gate/cron/update.php.log

# permit.sh 파일에 기록한다.
/www/gate/cron/permit.php >> /www/gate/cron/permit.php.log

/sbin/iptables -t nat -nv -L PREROUTING  > /www/gate/var/dnat_data

/root/firewall

