<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/1/15
 * Time: 2:46 PM
 */

namespace LO\Controller;

use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;

class AdminProvider implements ControllerProviderInterface {
    public function connect(Application $app) {

        $app['admin.controller'] = $app->share(function() use ($app) {
            return new Admin();
        });

        /** @var ControllerCollection $controllers */
        $controllers = $app["controllers_factory"];

        $controllers
            ->get("/roles", "admin.controller:getRolesAction");

        $controllers
            ->post("/user", "admin.controller:addUserAction");

        return $controllers;
    }
}