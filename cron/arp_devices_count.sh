#!/bin/sh

. /www/gate/config/config.sh

/sbin/arp -i $DEVINT | grep -v incomplete | grep ether | wc -l

