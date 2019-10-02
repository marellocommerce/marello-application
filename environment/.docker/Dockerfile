FROM ubuntu:xenial

ARG PHP_VERSION
ENV PHP_VERSION=$PHP_VERSION
ARG SYMFONY_ENV
ENV SYMFONY_ENV=$SYMFONY_ENV

COPY ["bin/setup.sh", "bin/configure.sh", "/opt/bin/"]
COPY ["bin/sources.list", "/etc/apt/sources.list"]

RUN /bin/bash /opt/bin/setup.sh
RUN /bin/bash /opt/bin/configure.sh

COPY ["bin/*", "/usr/local/bin/"]
RUN chmod +x /usr/local/bin/*

COPY conf/nginx/nginx.conf          /etc/nginx/nginx.conf
COPY conf/nginx/nginx-bap.conf      /etc/nginx/sites-enabled/bap.conf
COPY ["conf/supervisord/*", "/etc/"]

COPY conf/php/php.fpm.ini   /etc/php/${PHP_VERSION}/fpm/conf.d/10-php.ini
COPY conf/php/php.cli.ini   /etc/php/${PHP_VERSION}/cli/php.ini
COPY conf/fpm/fpm.conf      /etc/php/${PHP_VERSION}/fpm/php-fpm.conf
COPY conf/fpm/fpm.www.conf  /etc/php/${PHP_VERSION}/fpm/pool.d/www.conf

VOLUME ["/var/www/var/cache",  "/var/www/var/attachment", "/var/www/var/sessions",  "/var/www/var/import_export", "/var/www/public/uploads", "/var/www/public/media"]
EXPOSE 443 80 8080

CMD ["run.sh"]
