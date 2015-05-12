#!/bin/sh
sudo yum install -y nodejs npm --enablerepo=epel

sudo npm install -y -g npm

mkdir -p /home/ec2-user/node_modules
ln -s /home/ec2-user/node_modules/ node_modules

eval "ln -s ../config/"$1".yml config/config.yml"

eval "touch logs/"$1".log && chown webapp logs/"$1".log"