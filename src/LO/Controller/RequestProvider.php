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
            ->get("/{id}", "request.flyer.controller:getAction");

        $controllers
            ->post("/", "request.flyer.controller:addAction");

        $controllers
            ->put("/{id}", "request.flyer.controller:updateAction");

        $controllers
            ->put("/from/approval/{id}", "request.flyer.controller:createFromApprovalRequestAction");

        $controllers
            ->get("/approval/{id}", "request.approval.controller:getAction");

        $controllers
            ->post("/approval", "request.approval.controller:AddAction");

        $controllers
            ->post("/draft", "request.flyer.controller:draftAddAction");

        $controllers
            ->put("/draft/{id}", "request.flyer.controller:draftUpdateAction");


        return $controllers;
    }
} 