## Instructions on how to run the project locally

1. Add in /etc/hosts: `192.168.50.9 lo.portal.1rex.com`
1. `cd first-rex-lo-portal`
1. `cd config/ && ln -s dev.yml config.yml && cd ../`
1. `vagrant up`
1. `vagrant ssh`
1. `cd /vagrant`
1. `php bin/phinx migrate -e dev`

## Deploy
* Prod: `grunt deploy-prod`
AWS environment variables
PARAM1=prod

* Stage: `grunt deploy-stage`
AWS environment variables
PARAM1=stage
