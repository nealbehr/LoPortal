<?php namespace LO\Controller;

use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;

class PublicProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $app['status.controller'] = $app->share(function() use ($app) {
            return new StatusController();
        });

        /** @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];

        /**
         * Routes for StatysController
         */
        $controllers->post('responsecatch', 'status.controller:postUpdateAction');

        return $controllers;
    }
}
