#!/usr/bin/env php
<?php

require_once __DIR__.'/../../../../vendor/autoload.php';

use LO\Console\Command,
    Symfony\Component\Console\Application;

$app = new \LO\Application(['prod', 'config'], __DIR__.'/../../../../config/');
$app->bootstrap()->initRoutes();
$app->boot();

$application = new Application();
$application->add(new Command\SyncDbCommand($app));
$application->add(new Command\UploadDbCommand($app));
$application->add(new Command\UsersImportCommand($app->getEntityManager()));
$application->run();
