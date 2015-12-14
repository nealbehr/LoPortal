## Instructions on how to run the project locally

1. Add in /etc/hosts: `192.168.50.9 lo.portal.1rex.com`
1. `cd first-rex-lo-portal/config/ && ln -s dev.yml config.yml && cd ../`
1. `vagrant up`
1. `vagrant ssh`
1. `cd /vagrant && php bin/phinx migrate -e dev`

## Database administration
* `http://lo.portal.1rex.com/adminer.php`

## Deploy
### Switch to the branch, that you want deploy on instance.
* Prod: `grunt deploy-prod`
AWS environment variables
PARAM1=prod

* Stage: `grunt deploy-stage`
AWS environment variables
PARAM1=stage

## Console commands
Sync from BaseCRM: `/vagrant/bin/console portal:sync`
