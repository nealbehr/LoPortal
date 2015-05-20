#!/usr/bin/env bash

sudo rpm -ivh https://yum.puppetlabs.com/el/6/products/x86_64/puppetlabs-release-6-7.noarch.rpm
sudo yum install -y puppet

cd /tmp
wget http://downloads.sourceforge.net/wkhtmltopdf/wkhtmltox-0.12.2.1_linux-centos6-amd64.rpm
sudo yum install wkhtmltox-0.12.2.1_linux-centos6-amd64.rpm