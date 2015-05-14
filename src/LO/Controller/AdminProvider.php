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
            return new Admin\User();
        });

        $app['admin.queue.controller'] = $app->share(function() use ($app) {
            return new Admin\Queue();
        });

        $app['admin.property.approval.controller'] = $app->share(function() use ($app) {
            return new Admin\PropertyApproval();
        });

        $app['admin.request.flyer.controller'] = $app->share(function() use ($app) {
            return new Admin\RequestFlyer();
        });

        /** @var ControllerCollection $controllers */
        $controllers = $app["controllers_factory"];

        $controllers
            ->get("/roles", "admin.controller:getRolesAction");

        $controllers
            ->get("/user", "admin.controller:getUsersAction");

        $controllers
            ->post("/user", "admin.controller:addUserAction");

        $controllers
            ->put("/user/{id}", "admin.controller:updateUserAction");

        $controllers
            ->patch("/user/{userId}", "admin.controller:resetPasswordAction");

        $controllers
            ->delete("/user/{id}", "admin.controller:deleteAction");

        $controllers->get('/queue', "admin.queue.controller:getAction");
        $controllers->patch('/queue/decline/{id}', "admin.queue.controller:declineAction");
        $controllers->patch('/queue/approve/flyer/{id}', "admin.queue.controller:approveRequestFlyerAction");
        $controllers->patch('/queue/approve/{id}', "admin.queue.controller:approveRequestApprovalAction");

        $controllers
            ->get("/approval/{id}", "admin.property.approval.controller:getAction");
        $controllers
            ->put("/approval/{id}", "admin.property.approval.controller:updateAction");
        $controllers
            ->put("/flyer/{id}", "admin.request.flyer.controller:updateAction");


        return $controllers;
    }
}