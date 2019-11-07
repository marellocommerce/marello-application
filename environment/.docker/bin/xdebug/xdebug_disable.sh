#!/bin/sh
sed -i -e "s/zend_extension/;zend_extension/g" /etc/php/7.1/cli/conf.d/20-xdebug.ini
sed -i -e "s/zend_extension/;zend_extension/g" /etc/php/7.1/fpm/conf.d/20-xdebug.ini
supervisorctl restart php-fpm
