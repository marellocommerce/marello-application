#FROM ubuntu:xenial
#
#ARG PHP_VERSION="7.1"
#ARG SYMFONY_ENV
#ARG COMPOSERFILE="dev.json"
#ARG TIMEOUT
#ARG APP_ROOT
#
#COPY ["bin/sources.list", "/etc/apt/sources.list"]
#
#RUN set -x \
#    export DEBIAN_FRONTEND=noninteractive \
##    && export LC_ALL='en_US.UTF-8' \
##    && export LANG='en_US.UTF-8' \
##    && export LANGUAGE='en_US.UTF-8' \
#    && apt-get -qq update \
#    && apt-get -qqy install --no-install-recommends \
#      software-properties-common \
#      python-software-properties \
#      python-setuptools \
#      curl \
#      wget \
#      gettext \
#      git \
#      nodejs \
#      nodejs-legacy \
#      npm \
#      bzip2 \
#      locales \
#      apt-transport-https \
#      ca-certificates \
#      vim \
#      make \
#      procps \
#      mysql-client \
#      zip \
#      unzip \
#      redis-tools \
#      netcat-openbsd \
##    && localedef -c -f UTF-8 -i en_US en_US.UTF-8 \
##    && locale-gen en en_US en_US.UTF-8 && dpkg-reconfigure locales \
#    && export LC_ALL=en_US.UTF-8 \
#    && export LANG=en_US.UTF-8 \
#    && apt-get install -y language-pack-en-base \
#    && LC_ALL=en_US.UTF-8 add-apt-repository -y ppa:ondrej/php && apt-get update \
#    && apt-get -qqy install --no-install-recommends \
#      php${PHP_VERSION}-fpm \
#      php${PHP_VERSION}-cli \
#      php${PHP_VERSION}-common \
#      php${PHP_VERSION}-mysql \
#      php${PHP_VERSION}-pgsql \
#      php${PHP_VERSION}-curl \
#      php${PHP_VERSION}-gd \
#      php${PHP_VERSION}-mcrypt \
#      php${PHP_VERSION}-xmlrpc \
#      php${PHP_VERSION}-ldap \
#      php${PHP_VERSION}-xsl \
#      php${PHP_VERSION}-intl \
#      php${PHP_VERSION}-soap \
#      php${PHP_VERSION}-mbstring \
#      php${PHP_VERSION}-zip \
#      php${PHP_VERSION}-bz2 \
#      php${PHP_VERSION}-tidy \
#      php${PHP_VERSION}-bcmath \
#    && apt-get -qy autoremove --purge software-properties-common python-software-properties \
#    && apt-get autoclean
#
## Install nginx
#RUN apt-get install -qqy --reinstall nginx
#
## Install composer
#RUN (curl -sS https://getcomposer.org/installer | php)
#RUN mv composer.phar /usr/local/bin/composer.phar
#
## Create composer home dirs
#RUN mkdir -p -m 0744 /opt/composer/root
#RUN mkdir -p -m 0744 /opt/composer/www-data
#RUN chown www-data:www-data /opt/composer/www-data
#
#RUN ln -sf /usr/sbin/php-fpm${PHP_VERSION} /usr/local/bin/php-fpm
#RUN ln -sf /etc/php/${PHP_VERSION} /etc/php/current
#
## Create composer wrapper
#RUN echo '#!/usr/bin/env bash' >> /usr/local/bin/composer
#RUN echo 'COMPOSER_HOME=/opt/composer/$(whoami) /usr/local/bin/composer.phar $@' >> /usr/local/bin/composer
#RUN chmod 0755 /usr/local/bin/composer
#
## Install required composer-plugins
#RUN runuser -s /bin/sh -c 'composer global require fxp/composer-asset-plugin:1.2.2' www-data
#RUN runuser -s /bin/sh -c 'composer global require hirak/prestissimo' www-data
#
## Install node.js
#RUN apt-get install -qqy nodejs
#
## Install supervisor
#RUN easy_install supervisor
#RUN easy_install supervisor-stdout
#
#RUN apt-get -qq clean
#RUN rm -rf /var/lib/apt/lists/*
#
#COPY ["bin/configure.sh", "/opt/bin/"]
#
#RUN /bin/bash /opt/bin/configure.sh
#
#COPY ["bin/*", "/usr/local/bin/"]
#RUN chmod +x /usr/local/bin/*
#
#COPY conf/nginx/nginx.conf          /etc/nginx/nginx.conf
#COPY conf/nginx/nginx-bap.conf      /etc/nginx/sites-enabled/bap.conf
#COPY ["conf/supervisord/supervisord-2.x.conf", "/etc/"]
#
#COPY conf/php/php.fpm.ini   /etc/php/${PHP_VERSION}/fpm/conf.d/10-php.ini
#COPY conf/php/php.cli.ini   /etc/php/${PHP_VERSION}/cli/php.ini
#COPY conf/fpm/fpm.conf      /etc/php/${PHP_VERSION}/fpm/php-fpm.conf
#COPY conf/fpm/fpm.www.conf  /etc/php/${PHP_VERSION}/fpm/pool.d/www.conf
#
#VOLUME ["/var/www/app/cache", "/var/www/web/uploads", "/var/www/web/media", "/var/www/app/attachment"]
#EXPOSE 443 80 8080
#
##RUN runuser -s /bin/sh -c "cd $APP_ROOT && COMPOSER=$COMPOSERFILE COMPOSER_PROCESS_TIMEOUT=TIMEOUT composer install --no-interaction --no-scripts --prefer-dist --no-suggest" www-data
#
#CMD ["run.sh"]
#
#ENV SYMFONY_ENV=$SYMFONY_ENV

FROM ubuntu:xenial

ARG PHP_VERSION="7.1"
ARG SYMFONY_ENV
ARG COMPOSERFILE
ARG TIMEOUT
ARG APP_ROOT

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

RUN mkdir -p /var/www/vendor
RUN mkdir -p /var/www/web/bundles/components

VOLUME ["/var/www/app/cache", "/var/www/web/uploads", "/var/www/web/media", "/var/www/app/attachment"]
EXPOSE 443 80 8080

CMD ["run.sh"]

ENV SYMFONY_ENV=$SYMFONY_ENV