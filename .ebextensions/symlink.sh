#!/bin/sh
mkdir -p /home/node_modules
ln -s /home/node_modules/ node_modules

eval "ln -s ../config/"$1".yml config/config.yml"

eval "touch logs/"$1".log && chown webapp logs/"$1".log"