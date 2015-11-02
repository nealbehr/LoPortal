<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/27/15
 * Time: 4:27 PM
 */
namespace LO\Controller;

use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;

class QueueProvider implements ControllerProviderInterface {

    /**
     * Returns routes to connect to the given application.
     *
     * @param Application $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Application $app) {

        $app['queue.controller'] = $app->share(function() use ($app) {
            return new QueueController();
        });

        /** @var ControllerCollection $controllers */
        $controllers = $app["controllers_factory"];

        $controllers->get('/{id}', 'queue.controller:getAction');

        $controllers
            ->patch("/cancel/{id}", "queue.controller:cancelAction");

        $controllers
            ->delete("/{id}", "queue.controller:deleteAction");

        return $controllers;
    }
}