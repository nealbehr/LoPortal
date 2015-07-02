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
            return new Admin\AdminUserController();
        });

        $app['admin.queue.controller'] = $app->share(function() use ($app) {
            return new Admin\QueueController();
        });

        $app['admin.lender.controller'] = $app->share(function() use ($app) {
            return new Admin\LenderController();
        });

        $app['admin.realty.controller'] = $app->share(function() use ($app) {
            return new Admin\RealtyCompanyController();
        });

        $app['admin.property.approval.controller'] = $app->share(function() use ($app) {
            return new Admin\PropertyApproval();
        });

        $app['admin.request.flyer.controller'] = $app->share(function() use ($app) {
            return new Admin\AdminRequestFlyerController();
        });

        $app['admin.sales.director.controller'] = $app->share(function() use ($app) {
            return new Admin\SalesDirectorController();
        });

        $app['admin.realtor.controller'] = $app->share(function() use ($app) {
            return new Admin\RealtorController();
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

        $controllers->get('/lender', "admin.lender.controller:getAllAction");
        $controllers->get('/json/lenders', "admin.lender.controller:getAllJson");
        $controllers->post('/lender', "admin.lender.controller:addLenderAction");
        $controllers->get('/lender/{id}', "admin.lender.controller:getByIdAction");
        $controllers->put('/lender/{id}', "admin.lender.controller:updateLenderAction");
        $controllers->delete('/lender/{id}', "admin.lender.controller:deleteAction");

        $controllers->get('/realty', "admin.realty.controller:getAllAction");
        $controllers->post('/realty', "admin.realty.controller:addCompanyAction");
        $controllers->get('/realty/{id}', "admin.realty.controller:getByIdAction");
        $controllers->put('/realty/{id}', "admin.realty.controller:updateCompanyAction");
        $controllers->delete('/realty/{id}', "admin.realty.controller:deleteAction");

        $controllers
            ->get("/approval/{id}", "admin.property.approval.controller:getAction");
        $controllers
            ->put("/approval/{id}", "admin.property.approval.controller:updateAction");
        $controllers
            ->put("/flyer/{id}", "admin.request.flyer.controller:updateAction");

        /**
         * Routes for SalesDirectorController
         */
        $controllers->get('/salesdirector', 'admin.sales.director.controller:getListAction');
        $controllers->get('/salesdirector/{id}', 'admin.sales.director.controller:getByIdAction');
        $controllers->post('/salesdirector', 'admin.sales.director.controller:addAction');
        $controllers->put('/salesdirector/{id}', 'admin.sales.director.controller:updateAction');
        $controllers->delete('/salesdirector/{id}', 'admin.sales.director.controller:deleteAction');

        /**
         * Routes for RealtorController
         */
        $controllers->get('/realtor', 'admin.realtor.controller:getListAction');
        $controllers->get('/realtor/{id}', 'admin.realtor.controller:getByIdAction');
        $controllers->post('/realtor', 'admin.realtor.controller:addAction');
        $controllers->put('/realtor/{id}', 'admin.realtor.controller:updateAction');
        $controllers->delete('/realtor/{id}', 'admin.realtor.controller:deleteAction');
        
        return $controllers;
    }
}