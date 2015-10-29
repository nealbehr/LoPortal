<?php namespace LO\Controller;

use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;

class PublicProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $app['queue.controller'] = $app->share(function() use ($app) {
            return new QueueController();
        });

        /** @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];

        /**
         * Routes for StatysController
         */
        $controllers->post('responsecatch', 'queue.controller:updateStatusAction');

        return $controllers;
    }
}
