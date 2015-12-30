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
            $user  = $app->getSecurityTokenStorage()->getToken()->getUser();
            $query = $app->getEntityManager()
                ->createQueryBuilder()
                ->select('t')
                ->from(Template::class, 't')
                ->leftJoin(TemplateLender::class, 'tl', Expr\Join::WITH, 't.id = tl.template_id')
                ->leftJoin(TemplateAddress::class, 'ta', Expr\Join::WITH, 't.id = ta.template_id')
                ->where("t.deleted = '0'")
                ->andWhere("t.archive = '0'")
                ->andWhere("(tl.lender_id = :lenderId OR t.lenders_all = '1')")
                ->andWhere("(ta.state = :stateCode OR t.states_all = '1')");

            $query->setParameters([
                'lenderId'  => $user->getLenderId(),
                'stateCode' => $user->getAddress()->getState()
            ]);

            $templates = $query->getQuery()->getResult(Query::HYDRATE_ARRAY);

            $data = [];
            foreach ($templates as $template) {
                $data[$template['category_id']][] = $template;
            }

            return $app->json($data);
        }
        catch (HttpException $e) {
            $app->getMonolog()->addWarning($e);
            return $app->json(['message' => $e->getMessage()], $e->getStatusCode());
        }
    }
} 