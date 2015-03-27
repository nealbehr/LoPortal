<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/25/15
 * Time: 12:45 PM
 */

namespace LO\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;

class LoRequestProvider implements ControllerProviderInterface{
    public function connect(Application $app) {

        $app['lo.request.controller'] = $app->share(function() use ($app) {
            return new LoRequest();
        });

        /** @var ControllerCollection $controllers */
        $controllers = $app["controllers_factory"];

        $controllers
            ->post("/", "lo.request.controller:addAction");

        return $controllers;
    }
} 