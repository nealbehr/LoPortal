<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/12/15
 * Time: 7:06 PM
 */

namespace LO\Controller;

use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;

class DashboardProvider implements ControllerProviderInterface {

    /**
     * Returns routes to connect to the given application.
     *
     * @param Application $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Application $app) {

        $app['dashboard.controller'] = $app->share(function() use ($app) {
            return new DashboardController();
        });

        /** @var ControllerCollection $controllers */
        $controllers = $app["controllers_factory"];

        $controllers
            ->get("/", "dashboard.controller:indexAction")
            ->bind("dashboard-index");

        $controllers
            ->get("/collateral", "dashboard.controller:getCollateralAction");


        return $controllers;
    }
}