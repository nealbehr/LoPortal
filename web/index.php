<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__.'/../vendor/autoload.php';

$app = new \LO\Application(['prod', 'config'], __DIR__.'/../config/');

$app->bootstrap()->initRoutes();

$app->boot();

$app->run();