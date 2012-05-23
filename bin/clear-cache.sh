#!/bin/bash
#
# cleanup cache and log directories
# fix permissions
#
if id wwwrun >/dev/null 2>&1
then
	# suse
	user=wwwrun
elif id www-data >/dev/null 2>&1
then
	# ubuntu
	user=www-data
fi

group=$( id -g $user )
directories="data app/cache app/log pub/css/_cache pub/js/_cache"

find app/log -mtime +10 -exec /bin/rm -fv '{}' \;
rm -rf app/cache/*

chgrp -R $group $directories
chmod -R ug+rwX $directories
find $directories -type d -print0 | xargs -0 /bin/chmod 02775

