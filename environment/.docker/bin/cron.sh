#!/usr/bin/env bash
/usr/local/bin/waitinstall.sh
exec /usr/local/bin/listener.php /var/log/oro-cron.log /var/www/app/console --env=prod oro:cron