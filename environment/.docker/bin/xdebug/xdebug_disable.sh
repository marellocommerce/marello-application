#!/bin/sh
sed -i -e "s/zend_extension/;zend_extension/g" /etc/php/${PHP_VERSION}/cli/conf.d/20-xdebug.ini
sed -i -e "s/zend_extension/;zend_extension/g" /etc/php/${PHP_VERSION}/fpm/conf.d/20-xdebug.ini
supervisorctl restart php-fpm
