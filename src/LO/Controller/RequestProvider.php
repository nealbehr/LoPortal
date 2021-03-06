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
use Silex\ControllerCollection;

class RequestProvider implements ControllerProviderInterface{
    public function connect(Application $app) {

        $app['request.approval.controller'] = $app->share(function() use ($app) {
            return new RequestApprovalController();
        });

        $app['request.flyer.controller'] = $app->share(function() use ($app) {
            return new RequestFlyerController();
        });

        $app['request.realty.controller'] = $app->share(function() use ($app) {
            return new UserRealtyCompanyController();
        });

        $app['template.controller'] = $app->share(function() use ($app) {
            return new TemplateController();
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
            ->get("/flyer/{id}/download", "request.flyer.controller:download");

        $controllers->get('/flyer/realtors', 'request.flyer.controller:getRealtorListAction');

        $controllers
            ->get("/flyer/{id}/html", "request.flyer.controller:contentForPDF")
            ->bind('contentForPDF');

        $controllers
            ->get("/approval/{id}", "request.approval.controller:getAction");

        $controllers
            ->post("/approval", "request.approval.controller:AddAction");

        $controllers
            ->post("/draft", "request.flyer.controller:draftAddAction");

        $controllers
            ->put("/flyer/{id}", "request.flyer.controller:flyerUpdateAction");

        $controllers
            ->put("/from/approval/draft/{id}", "request.flyer.controller:draftFromFlyerUpdateAction");

        $controllers
            ->put("/approved/{id}", "request.flyer.controller:approvedUpdateAction");

        $controllers
            ->delete("/draft/{id}", "request.flyer.controller:deleteDraftAction");

        $controllers->get("/realty-company", "request.realty.controller:getAction");

        /**
         * Routes for TemplateController
         */
        $controllers->get('/document/{id}/download', 'template.controller:downloadAction');
        $controllers->get('/document/{id}/html', 'template.controller:htmlAction');
        $controllers->get('/template/categories', 'template.controller:getCategoriesAction');
        $controllers->get('/template/formats', 'template.controller:getFormatsAction');

        return $controllers;
    }
} 