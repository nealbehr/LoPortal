<?php
die('ddd');
require_once __DIR__.'/../vendor/autoload.php';

$app = new \LO\Application(['prod', 'prod'], __DIR__.'/../config/');

$app->bootstrap()->initRoutes();

$app->boot();

$app->run();