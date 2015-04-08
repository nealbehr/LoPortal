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

class RequestProvider implements ControllerProviderInterface{
    public function connect(Application $app) {

        $app['request.approval.controller'] = $app->share(function() use ($app) {
            return new RequestApprovalController();
        });

        $app['request.flyer.controller'] = $app->share(function() use ($app) {
            return new RequestFlyerController();
        });

        /** @var ControllerCollection $controllers */
        $controllers = $app["controllers_factory"];

        $controllers
            ->post("/", "request.flyer.controller:addAction");

        $controllers
            ->post("/approval", "request.approval.controller:AddAction");

        return $controllers;
    }
} 