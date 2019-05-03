#!/usr/bin/env bash
/usr/local/bin/waitinstall.sh
exec /sbin/runuser -s /bin/sh -c "exec /usr/bin/php /var/www/bin/console --env=prod gos:websocket:server" www-data