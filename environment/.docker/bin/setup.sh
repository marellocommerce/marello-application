#!/usr/bin/env bash

PHP_VERSION="7.2"

export DEBIAN_FRONTEND=noninteractive \
&& export LC_ALL='en_US.UTF-8' \
&& export LANG='en_US.UTF-8' \
&& export LANGUAGE='en_US.UTF-8' \
&& apt-get -qq update \
&& apt-get -qqy install --no-install-recommends \
  software-properties-common \
  python-software-properties \
  python-setuptools \
  curl \
  wget \
  gettext \
  git \
  nodejs \
  nodejs-legacy \
  npm \
  bzip2 \
  locales \
  apt-transport-https \
  ca-certificates \
  vim \
  make \
  procps \
  mysql-client \
  zip \
  unzip \
  redis-tools \
  netcat-openbsd \
&& localedef -c -f UTF-8 -i en_US en_US.UTF-8 \
&& locale-gen en en_US en_US.UTF-8 && dpkg-reconfigure locales \
&& add-apt-repository -y ppa:ondrej/php && apt-get -qq update \
&& apt-get -qqy install --no-install-recommends \
  php${PHP_VERSION}-fpm \
  php${PHP_VERSION}-cli \
  php${PHP_VERSION}-common \
  php${PHP_VERSION}-mysql \
  php${PHP_VERSION}-pgsql \
  php${PHP_VERSION}-curl \
  php${PHP_VERSION}-gd \
  php${PHP_VERSION}-xmlrpc \
  php${PHP_VERSION}-ldap \
  php${PHP_VERSION}-xsl \
  php${PHP_VERSION}-intl \
  php${PHP_VERSION}-soap \
  php${PHP_VERSION}-mbstring \
  php${PHP_VERSION}-zip \
  php${PHP_VERSION}-bz2 \
  php${PHP_VERSION}-tidy \
  php${PHP_VERSION}-bcmath \
  php${PHP_VERSION}-imagick \
&& apt-get -qy autoremove --purge software-properties-common python-software-properties \
&& apt-get autoclean || exit 1
#  php${PHP_VERSION}-mcrypt \

# install PostgreSQL
apt-get install -qqy libpq-dev postgresql postgresql-contrib || exit 1
apt-get install -qqy --no-install-recommends php-bcmath php-pgsql || exit 1

# Install nginx
apt-get install -qqy nginx || exit 1

# Install librsvg2
apt-get install -qqy librsvg2-2 librsvg2-dev || exit 1

# Install Ghostscript
apt-get install -qqy ghostscript || exit 1

# Update nodejs to at least v6, change setup_6 to 7,8 or whatever version to get something new and shiny
(curl -sL https://deb.nodesource.com/setup_6.x | bash -) || exit 1
apt-get install -qqy nodejs || exit 1

# Install composer
(curl -sS https://getcomposer.org/installer | php) || exit 1
mv composer.phar /usr/local/bin/composer.phar

# Create composer home dirs
mkdir -p -m 0744 /opt/composer/root
mkdir -p -m 0744 /opt/composer/www-data
chown www-data:www-data /opt/composer/www-data

ln -sf /usr/sbin/php-fpm${PHP_VERSION} /usr/local/bin/php-fpm
ln -sf /etc/php/${PHP_VERSION} /etc/php/current

# Create composer wrapper
echo '#!/usr/bin/env bash' >> /usr/local/bin/composer
echo 'COMPOSER_HOME=/opt/composer/$(whoami) /usr/local/bin/composer.phar $@' >> /usr/local/bin/composer
chmod 0755 /usr/local/bin/composer

# Install node.js
apt-get install -qqy nodejs || exit 1

# Install supervisor
easy_install supervisor || exit 1
easy_install supervisor-stdout || exit 1

apt-get -qq clean
rm -rf /var/lib/apt/lists/*