<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/12/15
 * Time: 7:08 PM
 */

namespace LO\Controller;

use Doctrine\ORM\AbstractQuery;
use LO\Application;

class Dashboard {
    public function indexAction(Application $app){
        return $app->json([
            'dashboard' => $app->getDashboardManager()->getByUserId($app->user()->getId()),
        ]);
    }

    public function getCollateralAction(Application $app){
        return $app->json(
            $app->getDashboardManager()->getCollateralByUserId($app->user()->getId(), AbstractQuery::HYDRATE_ARRAY)
        );
    }
} 