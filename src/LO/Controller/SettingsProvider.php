<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/7/15
 * Time: 4:10 PM
 */

namespace LO\Controller;

use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;

class SettingsProvider implements ControllerProviderInterface {

    /**
     * Returns routes to connect to the given application.
     *
     * @param Application $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Application $app) {

        $app['settings.controller'] = $app->share(function() use ($app) {
            return new Settings();
        });

        /** @var ControllerCollection $controllers */
        $controllers = $app["controllers_factory"];

        $controllers
            ->get("/request/type", "settings.controller:getRequestTypeAction");

        $controllers
            ->get("/request/state", "settings.controller:getRequestStateAction");


        return $controllers;
    }
}