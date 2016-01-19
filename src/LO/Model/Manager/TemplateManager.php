<?php
/**
 * User: Eugene Lysenko
 * Date: 1/19/16
 * Time: 17:57
 */
namespace LO\Model\Manager;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use LO\Model\Entity\User;
use LO\Model\Entity\Template;
use LO\Model\Entity\TemplateLender;
use LO\Model\Entity\TemplateAddress;

class TemplateManager extends Base
{
    /**
     * @param User $model
     * @return array
     */
    public function getList(User $model)
    {
        $query = $this->getApp()
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('t')
            ->from(Template::class, 't')
            ->leftJoin(TemplateLender::class, 'tl', Expr\Join::WITH, 't.id = tl.template_id')
            ->leftJoin(TemplateAddress::class, 'ta', Expr\Join::WITH, 't.id = ta.template_id')
            ->where("t.deleted = '0'")
            ->andWhere("t.archive = '0'")
            ->andWhere("(t.lenders_all = '1' OR tl.lender_id = :lenderId)")
            ->andWhere("(t.states_all = '1' OR ta.state = :stateCode)")
            ->groupBy('t.id');

        $query->setParameters([
            'lenderId'  => $model->getLenderId(),
            'stateCode' => $model->getAddress()->getState()
        ]);

        $data = [];
        foreach ($query->getQuery()->getResult(Query::HYDRATE_ARRAY) as $template) {
            $data[$template['category_id']][] = $template;
        }

        return $data;
    }

    /**
     * @param integer $id
     * @return mixed|null|object
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getById($id)
    {
        if ($this->getApp()->getAuthorizationChecker()->isGranted(User::ROLE_ADMIN)) {
            if (($model = $this->getApp()->getEntityManager()->getRepository(Template::class)->find($id))
                && $model->getDeleted() === '0'
                && $model->getArchive() === '0'
            ) {
                return $model;
            }
        }
        else {
            $user  = $this->getApp()->getSecurityTokenStorage()->getToken()->getUser();
            $query = $this->getApp()
                ->getEntityManager()
                ->createQueryBuilder()
                ->select('t')
                ->from(Template::class, 't')
                ->leftJoin(TemplateLender::class, 'tl', Expr\Join::WITH, 't.id = tl.template_id')
                ->leftJoin(TemplateAddress::class, 'ta', Expr\Join::WITH, 't.id = ta.template_id')
                ->where("t.id = :templateId")
                ->andWhere("t.deleted = '0'")
                ->andWhere("t.archive = '0'")
                ->andWhere("(t.lenders_all = '1' OR tl.lender_id = :lenderId)")
                ->andWhere("(t.states_all = '1' OR ta.state = :stateCode)")
                ->groupBy('t.id');

            $query->setParameters([
                'lenderId'   => $user->getLenderId(),
                'stateCode'  => $user->getAddress()->getState(),
                'templateId' => $id
            ]);

            return $query->getQuery()->getOneOrNullResult();
        }

        return null;
    }
}
