#!/usr/bin/env bash
/usr/local/bin/waitinstall.sh
exec /sbin/runuser -s /bin/sh -c "exec /usr/bin/php /var/www/app/console --env=prod clank:server" www-data