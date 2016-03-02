<?php
/**
 * User: Eugene Lysenko
 * Date: 1/19/16
 * Time: 17:57
 */

namespace LO\Model\Manager;

use Doctrine\ORM\Query,
    Doctrine\ORM\Query\Expr,
    LO\Model\Entity\User,
    LO\Model\Entity\Template,
    LO\Model\Entity\TemplateLender,
    LO\Model\Entity\TemplateAddress,
    Symfony\Component\HttpFoundation\Response,
    LO\Exception\Http;

class TemplateManager extends Base
{
    /**
     * @param User $model
     * @return array
     */
    public function getList(User $model)
    {
        $query = $this->getQueryBuild()->setParameters([
            'lenderId'  => $model->getLenderId(),
            'stateCode' => $model->getAddress()->getState()
        ]);

        $data  = [];
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
        // Role admin
        if ($this->getApp()->getAuthorizationChecker()->isGranted(User::ROLE_ADMIN)) {
            $model = $this->getApp()->getEntityManager()->getRepository(Template::class)->find($id);
        }
        // Role user
        else {
            $user  = $this->getApp()->getSecurityTokenStorage()->getToken()->getUser();
            $query = $this->getQueryBuild()->andWhere('t.id = :templateId')->setParameters([
                'lenderId'   => $user->getLenderId(),
                'stateCode'  => $user->getAddress()->getState(),
                'templateId' => $id
            ]);
            $model = $query->getQuery()->getOneOrNullResult();
        }

        if (!($model instanceof Template) || $model->getDeleted() === '1') {
            throw new Http('Document not found.', Response::HTTP_BAD_REQUEST);
        }

        return $model;
    }

    private function getQueryBuild()
    {
        return $this->getApp()
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('t')
            ->from(Template::class, 't')
            ->leftJoin(TemplateLender::class, 'tl', Expr\Join::WITH, 't.id = tl.template_id')
            ->leftJoin(TemplateAddress::class, 'ta', Expr\Join::WITH, 't.id = ta.template_id')
            ->where("t.deleted = '0'")
            ->andWhere("t.archive = '0'")
            ->andWhere(
                "t.co_branded = '0' OR (t.lenders_all = '1' OR tl.lender_id = :lenderId) AND (t.states_all = '1'"
                    ." OR ta.state = :stateCode)"
            )
            ->groupBy('t.id');
    }
}
