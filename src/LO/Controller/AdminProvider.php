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

        return $controllers;
    }
}