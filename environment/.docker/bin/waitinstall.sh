#!/usr/bin/env bash
while [ ! -f /var/www/config/config/parameters.yml ] || [ 0 -eq `cat /var/www/config/config/parameters.yml | grep ".*installed:\s*[\']\{0,1\}[a-zA-Z0-9\:\+\-]\{1,\}[\']\{0,1\}" | grep -v "null\|false" | wc -l` ]
do
    sleep 2;
done