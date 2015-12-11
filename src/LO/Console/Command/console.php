#!/usr/bin/env php
<?php

require_once __DIR__.'/../../../../vendor/autoload.php';

use LO\Console\Command\SyncCommand;
use LO\Console\Command\UsersImportCommand;
use Symfony\Component\Console\Application;

$app = new \LO\Application(['prod', 'config'], __DIR__.'/../../../../config/');
$app->bootstrap()->initRoutes();
$app->boot();

$application = new Application();
$application->add(new SyncCommand($app));
$application->add(new UsersImportCommand($app->getEntityManager()));
$application->run();
