<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/12/15
 * Time: 4:06 PM
 */

namespace LO\Controller;

use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;

class AuthorizeProvider implements ControllerProviderInterface {

    /**
     * Returns routes to connect to the given application.
     *
     * @param Application $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Application $app) {

        $app['auth.controller'] = $app->share(function() use ($app) {
            return new Authorize();
        });

        /** @var ControllerCollection $controllers */
        $controllers = $app["controllers_factory"];

        $controllers
            ->post("signin", "auth.controller:signinAction")
            ->bind("signin");

        $controllers
            ->get("/autocomplete/{email}", "auth.controller:autocompleteAction");

        $controllers
            ->post("/reset/{email}", "auth.controller:resetPasswordAction");

        $controllers
            ->put("/confirm/password/{id}", "auth.controller:confirmPassword")
            ->bind('auth-confirm-password')
        ;

        return $controllers;
    }
}