<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/13/15
 * Time: 4:44 PM
 */

namespace LO\Provider;

use LO\Model\Manager\DashboardManager;
use LO\Model\Manager\UserManager;
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
            return new UserManager($app);
        });

        $app['manager.dashboard'] = $app->share(function() use ($app) {
            return new DashboardManager($app);
        });
    }
} 