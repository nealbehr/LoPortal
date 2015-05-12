#!/bin/sh
curl -sL https://rpm.nodesource.com/setup | bash -

yum install -y nodejs

npm install -y -g npm

mkdir -p /home/ec2-user/node_modules
ln -s /home/ec2-user/node_modules/ node_modules

eval "ln -s ../config/"$1".yml config/config.yml"

eval "touch logs/"$1".log && chown webapp logs/"$1".log"