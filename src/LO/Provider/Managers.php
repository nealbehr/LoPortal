<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/13/15
 * Time: 4:44 PM
 */

namespace LO\Provider;

use LO\Model\Manager\User;
use Silex\Application;
use Silex\ServiceProviderInterface;

class Managers implements ServiceProviderInterface{
    /**
     * {@inheritdoc}
     */
    public function boot(Application $app){
    }

    /**
     * {@inheritdoc}
     */
    public function register(Application $app){
        $app['manager.user'] = $app->share(function() use ($app) {
            return new User($app);
        });

    }
} 