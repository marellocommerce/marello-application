#!/usr/bin/env bash
APP_ROOT=$1 #use input else use default path

if [ -z "$1" ];then
    APP_ROOT=~/domains/demo.marello.com/current/applications/demo-marello-com
fi

PHP_BIN=`which php73`
LOCAL_BIN_DIR=$APP_ROOT/bin/listener.php
/bin/sh -c "exec $PHP_BIN $LOCAL_BIN_DIR ~/supervisor/log/oro-cron.log nginx $PHP_BIN $APP_ROOT/bin/console --env=prod oro:cron" nginx