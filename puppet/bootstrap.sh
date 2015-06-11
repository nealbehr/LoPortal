#!/usr/bin/env bash

#sudo rpm -ivh https://yum.puppetlabs.com/el/6/products/x86_64/puppetlabs-release-6-7.noarch.rpm
#sudo yum install -y puppet

cd /tmp
sudo rpm -qa | grep wkhtmltox || sudo yum -y install https://s3-us-west-1.amazonaws.com/1rex/pdf/wkhtmltox-0.12.2.1_linux-centos6-amd64.rpm
sudo ls /etc/fonts/conf.d/ | grep 10-wkhtmltopdf.conf || sudo wget --no-check-certificate https://s3-us-west-1.amazonaws.com/1rex/pdf/10-wkhtmltopdf.conf -O /etc/fonts/conf.d/10-wkhtmltopdf.conf