#!/usr/bin/env bash
APP_ROOT=$1 #use input else use default path

if [ -z "$1" ];then
    APP_ROOT=~/domains/demo.marello.com/current/applications/demo-marello-com
fi

PHP_BIN=`which php73`
/bin/sh -c "exec $PHP_BIN $APP_ROOT/bin/console --env=prod gos:websocket:server" nginx