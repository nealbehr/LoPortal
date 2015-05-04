<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 5/4/15
 * Time: 1:02 PM
 */

namespace LO\Provider;


use Monolog\Handler\StreamHandler;
use Silex\Application;
use LO\Application as LoApplication;
use Silex\Provider\MonologServiceProvider;
use Silex\ServiceProviderInterface;

class MonologS3ServiceProvider extends MonologServiceProvider{
    public function register(Application $app){
        parent::register($app);
        $app['monolog.handler'] = function () use ($app) {
            $level = MonologServiceProvider::translateLevel($app['monolog.level']);
            /** @var LoApplication $app */
            $app->getS3()->registerStreamWrapper();
            return new StreamHandler($app['monolog.config']['s3.logs'], $level, $app['monolog.bubble'], $app['monolog.permission']);
        };
    }

    public function boot(Application $app){

    }
} 