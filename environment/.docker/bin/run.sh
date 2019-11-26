#!/usr/bin/env bash
APP_ROOT="/var/www"
DATA_ROOT="/srv/app-data"

function info {
    printf "\033[0;36m===> \033[0;33m${1}\033[0m\n"
}

# Check and fix ownership if invalid
VOLUMES[0]="/var/www/var/cache"
VOLUMES[1]="/var/www/public/uploads";
VOLUMES[2]="/var/www/public/media";
VOLUMES[3]="/var/www/var/attachment";
VOLUMES[4]="/var/www/var/import_export";
VOLUMES[5]="/var/www/var/sessions";

for i in ${!VOLUMES[*]}; do
  if [[ `stat -c '%u:%g' ${VOLUMES[$i]}` != `getent passwd | grep www-data | awk -F ':' '{print $3 ":" $4}'` ]]; then
      info "Fix ownership for ${VOLUMES[$i]}"
      chown -R $(getent passwd | grep www-data | awk -F ':' '{print $3 ":" $4}') ${VOLUMES[$i]}
  fi
done

# Check if the local usage
if [[ -z ${IS_LOCAL} ]]; then
    # Map environment variables
    info "Map parameters.yml to environment variables"
    composer-map-env.php ${APP_ROOT}/composer.json

    # Generate parameters.yml
    info "Run composer script 'post-install-cmd'"
    runuser -s /bin/sh -c "composer --no-interaction run-script post-install-cmd -n -d ${APP_ROOT}" www-data
fi

if [[ -z ${APP_DB_PORT} ]]; then
    if [[ "pdo_pgsql" = ${APP_DB_DRIVER} ]]; then
        APP_DB_PORT="5432"
    else
        APP_DB_PORT="3306"
    fi
fi

until nc -z ${APP_DB_HOST} ${APP_DB_PORT}; do
    info "Waiting database on ${APP_DB_HOST}:${APP_DB_PORT}"
    sleep 2
done

info "Checking if application is already installed"
if [[ ! -z ${APP_IS_INSTALLED} ]] \
    || [[ `MYSQL_PWD="${APP_DB_PASSWORD}" mysql -e "show databases like '${APP_DB_NAME}'" -h${APP_DB_HOST} -u${APP_DB_USER} -N | wc -l` -gt 0 ]] \
    && [[ `MYSQL_PWD="${APP_DB_PASSWORD}" mysql -e "show tables from ${APP_DB_NAME}" -h${APP_DB_HOST} -u${APP_DB_USER} -N | wc -l` -gt 0 ]]; then
  sed -i -e "s/installed:.*/installed: true/g" /var/www/config/parameters.yml
  info "Application is already installed!"
  APP_IS_INSTALLED=true
else
  info "Application is not installed!"
fi


if [[ ! -z ${CMD_INIT_BEFORE} ]]; then
    info "Running pre init command: ${CMD_INIT_BEFORE}"
    sh -c "${CMD_INIT_BEFORE}"
fi

cd ${APP_ROOT}

# If already installed
if [[ -z ${APP_IS_INSTALLED} ]]
then
    if [[ ! -z ${CMD_INIT_CLEAN} ]]; then
        info "Running init command: ${CMD_INIT_CLEAN}"
        sh -c "${CMD_INIT_CLEAN}"
    fi
else
    info "Updating application..."
    if [[ -d ${APP_ROOT}/var/cache ]] && [[ $(ls -l ${APP_ROOT}/var/cache/ | grep -v total | wc -l) -gt 0 ]]; then
        rm -r ${APP_ROOT}/var/cache/*
    fi

    if [[ ! -z ${CMD_INIT_INSTALLED} ]]; then
        info "Running init command: ${CMD_INIT_INSTALLED}"
        sh -c "${CMD_INIT_INSTALLED}"
    fi

fi

if [[ ! -z ${CMD_INIT_AFTER} ]]; then
    info "Running post init command: ${CMD_INIT_AFTER}"
    sh -c "${CMD_INIT_AFTER}"
fi

# Starting services
if php -r 'foreach(json_decode(file_get_contents("'${APP_ROOT}'/dev.lock"))->{"packages"} as $p) { echo $p->{"name"} . ":" . $p->{"version"} . PHP_EOL; };' | grep 'platform:4' > /dev/null
then
  info "Starting supervisord..."
  exec /usr/local/bin/supervisord -n -c /etc/supervisord.conf
else
  info "Could not start supervisor, did not recognise platform from dev.lock"
fi
