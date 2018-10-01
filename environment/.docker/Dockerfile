FROM ubuntu:xenial

ARG PHP_VERSION="7.1"
ARG SYMFONY_ENV
ARG COMPOSERFILE
ARG TIMEOUT

COPY ["bin/setup.sh", "bin/configure.sh", "/opt/bin/"]
COPY ["bin/sources.list", "/etc/apt/sources.list"]

RUN /bin/bash /opt/bin/setup.sh
RUN /bin/bash /opt/bin/configure.sh

COPY ["bin/*", "/usr/local/bin/"]
RUN chmod +x /usr/local/bin/*

COPY conf/nginx/nginx.conf          /etc/nginx/nginx.conf
COPY conf/nginx/nginx-bap.conf      /etc/nginx/sites-enabled/bap.conf
COPY ["conf/supervisord/supervisord-2.x.conf", "/etc/"]

COPY conf/php/php.fpm.ini   /etc/php/${PHP_VERSION}/fpm/conf.d/10-php.ini
COPY conf/php/php.cli.ini   /etc/php/${PHP_VERSION}/cli/php.ini
COPY conf/fpm/fpm.conf      /etc/php/${PHP_VERSION}/fpm/php-fpm.conf
COPY conf/fpm/fpm.www.conf  /etc/php/${PHP_VERSION}/fpm/pool.d/www.conf

VOLUME ["/var/www/app/cache", "/var/www/web/uploads", "/var/www/web/media", "/var/www/app/attachment"]
EXPOSE 443 80 8080

CMD ["run.sh"]

ENV SYMFONY_ENV=$SYMFONY_ENV