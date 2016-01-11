<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/12/15
 * Time: 7:08 PM
 */
namespace LO\Controller;

use LO\Application;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use LO\Model\Entity\Template;
use LO\Model\Entity\TemplateAddress;
use LO\Model\Entity\TemplateLender;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Doctrine\ORM\Query\Expr;

class DashboardController
{
    public function indexAction(Application $app)
    {
        return $app->json([
            'dashboard' => $app->getDashboardManager()->getByUserId($app->getSecurityTokenStorage()->getToken()->getUser()->getId(), false),
        ]);
    }

    public function getCollateralAction(Application $app)
    {
        return $app->json(
            $app->getDashboardManager()->getCollateralByUserId($app->getSecurityTokenStorage()->getToken()->getUser()->getId(), AbstractQuery::HYDRATE_ARRAY)
        );
    }

    public function getTemplatesAction(Application $app)
    {
        try {
            return $app->json(
                $app->getDashboardManager()->getTemplateList($app->getSecurityTokenStorage()->getToken()->getUser())
            );
        }
        catch (HttpException $e) {
            $app->getMonolog()->addWarning($e);
            return $app->json(['message' => $e->getMessage()], $e->getStatusCode());
        }
    }
} 