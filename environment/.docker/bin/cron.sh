#!/usr/bin/env bash
/usr/local/bin/waitinstall.sh
#exec /usr/local/bin/listener.php /var/log/oro-cron.log /var/www/app/console --env=prod oro:cron
exec /usr/local/bin/listener.php /var/www/logs/oro-cron.log /sbin/runuser -s /bin/sh -c "exec /usr/bin/php /var/www/bin/console oro:cron" www-data
