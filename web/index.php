<?php
require_once __DIR__.'/../vendor/autoload.php';

$app = new \LO\Application(['prod', 'config'], __DIR__.'/../config/');

$app->bootstrap()->initRoutes();

$app->boot();

$app->run();