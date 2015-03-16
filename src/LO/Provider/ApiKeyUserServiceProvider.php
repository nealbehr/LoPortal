<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/16/15
 * Time: 2:17 PM
 */

namespace LO\Provider;


use Silex\Application,
    Silex\ServiceProviderInterface;

class ApiKeyUserServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app){
        $app['security.user_provider.apikey'] = $app->protect(function () use ($app) {
            return new ApiKeyUserProvider($app, $app['manager.user']);
        });

        return true;
    }

    public function boot(Application $app)
    {
    }

}